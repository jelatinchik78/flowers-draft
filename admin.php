<?php
/**
 * Админ-панель Flowers: цены, наличие, ассортимент, фотографии.
 *
 * Открывается по адресу /admin.php. Пароль по умолчанию — Flowers2026,
 * меняется в разделе «Пароль».
 */

define('APP', true);

$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone'] ?? 'Asia/Yekaterinburg');
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/store.php';
require __DIR__ . '/lib/images.php';

auth_boot();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(int $value): string
{
    return number_format($value, 0, ',', ' ') . ' ₽';
}

function post_type(): string
{
    return ($_POST['type'] ?? '') === 'flower' ? 'flower' : 'bouquet';
}

function get_type(): string
{
    return ($_GET['type'] ?? '') === 'flower' ? 'flower' : 'bouquet';
}

function type_word(string $type): string
{
    return $type === 'flower' ? 'цветок' : 'букет';
}

function find_item(array $data, string $type, string $id): ?array
{
    $key = store_type_key($type);
    $index = store_index_of($data[$key], $id);
    return $index === null ? null : $data[$key][$index];
}

$errors = [];
$flash = flash_take();
$method = $_SERVER['REQUEST_METHOD'];
$action = (string) ($_POST['action'] ?? $_GET['action'] ?? '');

/* ————— обработка форм ————— */

if ($method === 'POST' && $action === 'login') {
    if (auth_login((string) ($_POST['password'] ?? ''), $config)) {
        redirect('admin.php');
    }
    $flash = flash_take();
}

if ($method === 'POST' && $action !== 'login') {
    if (!auth_check()) {
        redirect('admin.php');
    }
    csrf_check();

    $type = post_type();
    $key = store_type_key($type);

    try {
        switch ($action) {
            case 'logout':
                auth_logout();
                redirect('admin.php');

            case 'toggle':
            case 'delete':
            case 'move':
                $data = store_load($config);
                $id = (string) ($_POST['id'] ?? '');
                $index = store_index_of($data[$key], $id);
                if ($index === null) {
                    throw new RuntimeException('Позиция не найдена — возможно, её уже удалили.');
                }

                if ($action === 'toggle') {
                    $data[$key][$index]['available'] = !$data[$key][$index]['available'];
                    store_save($config, $data);
                    flash_set('ok', $data[$key][$index]['available'] ? 'Отмечено как «в наличии».' : 'Отмечено как «под заказ».');
                } elseif ($action === 'delete') {
                    image_delete($data[$key][$index]['img'] ?? '', $config);
                    $name = (string) $data[$key][$index]['name'];
                    array_splice($data[$key], $index, 1);
                    store_save($config, $data);
                    flash_set('ok', 'Удалено: ' . $name);
                } else {
                    $dir = ($_POST['dir'] ?? '') === 'up' ? 'up' : 'down';
                    if (store_move($data, $type, $id, $dir)) {
                        store_save($config, $data);
                    }
                }
                redirect('admin.php?type=' . $type);

            case 'save':
                $data = store_load($config);
                $items = $data[$key];
                $id = preg_replace('/[^a-z0-9]/i', '', (string) ($_POST['id'] ?? ''));
                $index = $id !== '' ? store_index_of($items, $id) : null;
                $isNew = $id === '';

                $name = trim((string) ($_POST['name'] ?? ''));
                $desc = trim((string) ($_POST['desc'] ?? ''));
                $price = (int) ($_POST['price'] ?? 0);
                $wholesale = (int) ($_POST['priceWholesale'] ?? 0);
                $img = $index !== null ? (string) ($items[$index]['img'] ?? '') : '';
                $oldImg = $img;

                if ($name === '') {
                    $errors[] = 'Укажите название.';
                }
                if ($price <= 0) {
                    $errors[] = 'Цена должна быть больше нуля.';
                }
                if ($wholesale < 0) {
                    $wholesale = 0;
                }

                if (!$isNew && $index === null) {
                    throw new RuntimeException('Позиция не найдена — возможно, её уже удалили.');
                }

                $newId = $isNew ? store_next_id($items, $type === 'flower' ? 'f' : 'b') : $id;

                if (!empty($_POST['remove_img'])) {
                    image_delete($img, $config);
                    $img = '';
                }

                if (!$errors && !empty($_FILES['photo']['name'])) {
                    $uploaded = image_store_upload($_FILES['photo'], $newId, $config);
                    if ($oldImg !== '' && $oldImg !== $uploaded) {
                        image_delete($oldImg, $config);
                    }
                    $img = $uploaded;
                }

                $item = [
                    'id'             => $newId,
                    'name'           => $name,
                    'price'          => $price,
                    'priceWholesale' => $wholesale,
                    'img'            => $img,
                    'available'      => !empty($_POST['available']),
                ];
                if ($type === 'bouquet') {
                    $item['desc'] = $desc;
                }

                if (!$errors) {
                    if ($isNew) {
                        $items[] = $item;
                    } else {
                        $items[$index] = $item;
                    }
                    $data[$key] = $items;
                    store_save($config, $data);
                    flash_set('ok', ($isNew ? 'Добавлено: ' : 'Сохранено: ') . $name);
                    redirect('admin.php?type=' . $type);
                }

                /* есть ошибки — показываем форму с введёнными данными */
                if ($isNew) {
                    $item['id'] = '';
                }
                $view = 'edit';
                $editItem = $item;
                $editItem['desc'] = $type === 'bouquet' ? $desc : '';
                break;

            case 'password':
                $current = (string) ($_POST['current'] ?? '');
                $new = (string) ($_POST['new'] ?? '');
                $repeat = (string) ($_POST['repeat'] ?? '');

                if (!password_verify($current, auth_hash($config))) {
                    $errors[] = 'Текущий пароль неверный.';
                } elseif ((function_exists('mb_strlen') ? mb_strlen($new) : strlen($new)) < 8) {
                    $errors[] = 'Новый пароль короче 8 символов.';
                } elseif ($new !== $repeat) {
                    $errors[] = 'Пароли не совпадают.';
                } else {
                    $hash = password_hash($new, PASSWORD_DEFAULT);
                    $json = json_encode(['password_hash' => $hash, 'changed' => date('c')], JSON_UNESCAPED_SLASHES);
                    if (file_put_contents($config['auth_file'], $json . "\n", LOCK_EX) === false) {
                        throw new RuntimeException('Не удалось сохранить пароль: проверьте права на папку data.');
                    }
                    @chmod($config['auth_file'], 0644);
                    flash_set('ok', 'Пароль изменён.');
                    redirect('admin.php');
                }
                $view = 'password';
                break;
        }
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

/* ————— данные для страницы ————— */

if (!auth_check()) {
    $pageTitle = 'Вход';
    include __DIR__ . '/admin/login.php';
    exit;
}

$type = get_type();
$key = store_type_key($type);
$data = store_load($config);
$items = $data[$key];

$view = $view ?? (string) ($_GET['view'] ?? 'list');
$pageTitle = $view === 'edit' ? 'Редактирование' : ($view === 'password' ? 'Пароль' : 'Каталог');

$editItem = $editItem ?? null;
if ($view === 'edit' && $editItem === null) {
    $editId = preg_replace('/[^a-z0-9]/i', '', (string) ($_GET['id'] ?? ''));
    $found = $editId !== '' ? find_item($data, $type, $editId) : null;
    $editItem = $found ?? [
        'id' => '', 'name' => '', 'desc' => '', 'price' => 0,
        'priceWholesale' => 0, 'img' => '', 'available' => true,
    ];
    $isNew = $found === null;
}

include __DIR__ . '/admin/layout-top.php';
if ($view === 'edit') {
    include __DIR__ . '/admin/edit.php';
} elseif ($view === 'password') {
    include __DIR__ . '/admin/password.php';
} else {
    include __DIR__ . '/admin/list.php';
}
include __DIR__ . '/admin/layout-bottom.php';
