<?php
/**
 * Загрузка фотографий: поворот по EXIF, уменьшение до 1600 px, JPEG.
 */

defined('APP') or exit;

function image_fail_reason(int $code): string
{
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Файл слишком большой для сервера. Уменьшите фотографию или поднимите лимит upload_max_filesize.';
        case UPLOAD_ERR_PARTIAL:
            return 'Файл загрузился не полностью. Попробуйте ещё раз.';
        case UPLOAD_ERR_NO_FILE:
            return 'Файл не выбран.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'На сервере нет временной папки для загрузки.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Сервер не смог записать файл на диск.';
        default:
            return 'Не удалось загрузить фотографию (код ' . $code . ').';
    }
}

function image_store_upload(array $file, string $id, array $config): string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException(image_fail_reason((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)));
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Некорректная загрузка файла.');
    }
    if ((int) $file['size'] > (int) $config['max_upload']) {
        throw new RuntimeException('Файл больше ' . round($config['max_upload'] / 1048576) . ' МБ.');
    }

    $info = @getimagesize($file['tmp_name']);
    if (!$info || empty($info['mime'])) {
        throw new RuntimeException('Это не похоже на изображение. Нужен JPEG, PNG или WebP.');
    }
    $allowed = ['image/jpeg' => true, 'image/png' => true, 'image/webp' => true];
    if (!isset($allowed[$info['mime']])) {
        throw new RuntimeException('Поддерживаются только JPEG, PNG и WebP.');
    }

    $raw = file_get_contents($file['tmp_name']);
    $src = $raw === false ? false : @imagecreatefromstring($raw);
    if (!$src) {
        throw new RuntimeException('Не удалось прочитать изображение.');
    }

    /* поворот по EXIF — иначе фото с телефона ляжет на бок */
    if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name']);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        switch ($orientation) {
            case 2:
                imageflip($src, IMG_FLIP_HORIZONTAL);
                break;
            case 3:
                $rotated = imagerotate($src, 180, 0);
                if ($rotated) { imagedestroy($src); $src = $rotated; }
                break;
            case 4:
                imageflip($src, IMG_FLIP_VERTICAL);
                break;
            case 5:
                $rotated = imagerotate($src, -90, 0);
                if ($rotated) { imagedestroy($src); $src = $rotated; }
                imageflip($src, IMG_FLIP_HORIZONTAL);
                break;
            case 6:
                $rotated = imagerotate($src, -90, 0);
                if ($rotated) { imagedestroy($src); $src = $rotated; }
                break;
            case 7:
                $rotated = imagerotate($src, 90, 0);
                if ($rotated) { imagedestroy($src); $src = $rotated; }
                imageflip($src, IMG_FLIP_HORIZONTAL);
                break;
            case 8:
                $rotated = imagerotate($src, 90, 0);
                if ($rotated) { imagedestroy($src); $src = $rotated; }
                break;
        }
    }

    $width  = imagesx($src);
    $height = imagesy($src);
    $maxSide = max(400, (int) $config['max_side']);
    $scale = min(1, $maxSide / max($width, $height));
    $newW = max(1, (int) round($width * $scale));
    $newH = max(1, (int) round($height * $scale));

    $dst = imagecreatetruecolor($newW, $newH);
    if (!$dst) {
        imagedestroy($src);
        throw new RuntimeException('Не хватило памяти на обработку фотографии.');
    }

    /* прозрачный PNG кладём на белый фон */
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
    imagedestroy($src);

    $dir = $config['img_dir'];
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        imagedestroy($dst);
        throw new RuntimeException('Не удалось создать папку img/catalog.');
    }

    $safeId = preg_replace('/[^a-z0-9]/i', '', $id) ?: 'item';
    $name = $safeId . '-' . time() . '.jpg';
    $path = $dir . '/' . $name;

    if (!imagejpeg($dst, $path, (int) $config['jpeg_quality'])) {
        imagedestroy($dst);
        throw new RuntimeException('Не удалось сохранить фотографию. Проверьте права на папку img/catalog.');
    }
    imagedestroy($dst);
    @chmod($path, 0644);

    return $config['img_url'] . '/' . $name;
}

function image_delete(?string $url, array $config): void
{
    $url = (string) $url;
    $prefix = rtrim($config['img_url'], '/') . '/';
    if ($url === '' || strpos($url, $prefix) !== 0) {
        return;
    }

    $name = basename($url);
    if ($name === '' || strpos($name, '.') === false) {
        return;
    }

    $path = realpath($config['img_dir'] . '/' . $name);
    $root = realpath($config['img_dir']);
    if ($path && $root && strpos($path, $root) === 0 && is_file($path)) {
        @unlink($path);
    }
}
