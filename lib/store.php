<?php
/**
 * Хранилище каталога: чтение, запись, резервные копии.
 */

defined('APP') or exit;

const STORE_TYPES = [
    'bouquet' => 'bouquets',
    'flower'  => 'flowers',
];

function store_type_key(string $type): string
{
    if (!isset(STORE_TYPES[$type])) {
        throw new InvalidArgumentException('Неизвестный раздел: ' . $type);
    }
    return STORE_TYPES[$type];
}

function store_empty(): array
{
    return [
        'version'  => 1,
        'updated'  => date('c'),
        'bouquets' => [],
        'flowers'  => [],
    ];
}

function store_normalize_item(array $item, string $type): array
{
    $out = [
        'id'             => preg_replace('/[^a-z0-9]/i', '', (string) ($item['id'] ?? '')),
        'name'           => trim((string) ($item['name'] ?? '')),
        'price'          => max(0, (int) ($item['price'] ?? 0)),
        'priceWholesale' => max(0, (int) ($item['priceWholesale'] ?? 0)),
        'img'            => (string) ($item['img'] ?? ''),
        'available'      => !empty($item['available']),
    ];
    if ($type === 'bouquet') {
        $out['desc'] = trim((string) ($item['desc'] ?? ''));
    }
    return $out;
}

function store_load(array $config): array
{
    $file = $config['data_file'];
    if (!is_file($file)) {
        return store_empty();
    }

    $raw = file_get_contents($file);
    $json = json_decode((string) $raw, true);
    if (!is_array($json)) {
        return store_empty();
    }

    $data = store_empty();
    $data['version'] = (int) ($json['version'] ?? 1);
    $data['updated'] = (string) ($json['updated'] ?? date('c'));

    foreach (['bouquets' => 'bouquet', 'flowers' => 'flower'] as $key => $type) {
        foreach ((array) ($json[$key] ?? []) as $item) {
            if (is_array($item)) {
                $data[$key][] = store_normalize_item($item, $type);
            }
        }
    }

    /* оптовые цены лежат отдельно и на сайт не попадают */
    $wholesale = [];
    $wholesaleFile = $config['wholesale_file'] ?? '';
    if ($wholesaleFile !== '' && is_file($wholesaleFile)) {
        $decoded = json_decode((string) file_get_contents($wholesaleFile), true);
        if (is_array($decoded)) {
            $wholesale = $decoded;
        }
    }
    foreach (['bouquets', 'flowers'] as $key) {
        foreach ($data[$key] as &$item) {
            $item['priceWholesale'] = max(0, (int) ($wholesale[$item['id']] ?? 0));
        }
        unset($item);
    }

    return $data;
}

function store_backup(array $config): void
{
    $file = $config['data_file'];
    if (!is_file($file)) {
        return;
    }
    $dir = $config['backup_dir'];
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return;
    }

    $name = 'catalog-' . date('Ymd-His') . '.json';
    @copy($file, $dir . '/' . $name);

    $files = glob($dir . '/catalog-*.json') ?: [];
    sort($files);
    $keep = max(1, (int) $config['backups_keep']);
    while (count($files) > $keep) {
        $old = array_shift($files);
        if ($old) {
            @unlink($old);
        }
    }
}

function store_write_json(string $file, array $data): void
{
    $dir = dirname($file);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Не удалось создать папку ' . $dir);
    }

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        throw new RuntimeException('Не удалось собрать JSON: ' . json_last_error_msg());
    }

    $tmp = $file . '.tmp-' . bin2hex(random_bytes(4));
    if (file_put_contents($tmp, $json . "\n", LOCK_EX) === false) {
        throw new RuntimeException('Не удалось записать файл. Проверьте права на папку data.');
    }
    @chmod($tmp, 0644);

    if (!rename($tmp, $file)) {
        @unlink($tmp);
        throw new RuntimeException('Не удалось заменить файл каталога.');
    }
}

function store_save(array $config, array $data): void
{
    $file = $config['data_file'];

    store_backup($config);

    $data['version'] = (int) ($data['version'] ?? 1);
    $data['updated'] = date('c');

    /* оптовые цены — в отдельный закрытый файл, на сайт они не попадают */
    $public = $data;
    $wholesale = [];
    foreach (['bouquets', 'flowers'] as $key) {
        foreach ($public[$key] as $i => $item) {
            $value = (int) ($item['priceWholesale'] ?? 0);
            if ($value > 0) {
                $wholesale[(string) $item['id']] = $value;
            }
            unset($public[$key][$i]['priceWholesale']);
        }
        $public[$key] = array_values($public[$key]);
    }

    store_write_json($file, $public);

    $wholesaleFile = $config['wholesale_file'] ?? '';
    if ($wholesaleFile !== '') {
        store_write_json($wholesaleFile, $wholesale);
    }
}

function store_next_id(array $items, string $prefix): string
{
    $max = 0;
    foreach ($items as $item) {
        if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', (string) ($item['id'] ?? ''), $m)) {
            $max = max($max, (int) $m[1]);
        }
    }
    return $prefix . str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
}

function store_index_of(array $items, string $id): ?int
{
    foreach ($items as $i => $item) {
        if ((string) ($item['id'] ?? '') === $id) {
            return $i;
        }
    }
    return null;
}

function store_move(array &$data, string $type, string $id, string $dir): bool
{
    $key = store_type_key($type);
    $index = store_index_of($data[$key], $id);
    if ($index === null) {
        return false;
    }
    $target = $dir === 'up' ? $index - 1 : $index + 1;
    if ($target < 0 || $target >= count($data[$key])) {
        return false;
    }
    $tmp = $data[$key][$index];
    $data[$key][$index] = $data[$key][$target];
    $data[$key][$target] = $tmp;
    return true;
}
