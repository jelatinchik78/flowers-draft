<?php
/**
 * Настройки сайта Flowers.
 *
 * Пароль по умолчанию: Flowers2026
 * Сменить его можно в админ-панели: «Пароль» в правом верхнем углу.
 * После смены хеш хранится в data/auth.json и этот файл больше не используется.
 */

defined('APP') or exit;

return [
    'timezone' => 'Asia/Yekaterinburg',

    /* стартовый пароль, пока не сменён через админку */
    'password_hash' => '$2y$10$vFOfn0S.3FtDmlMvbWs9MeWQh3gbww9wV.AUazn1g3heh3xBXlg1.',

    'data_file'  => __DIR__ . '/data/catalog.json',
    'wholesale_file' => __DIR__ . '/data/wholesale.json',
    'auth_file'  => __DIR__ . '/data/auth.json',
    'backup_dir' => __DIR__ . '/data/backups',
    'img_dir'    => __DIR__ . '/img/catalog',
    'img_url'    => 'img/catalog',

    'max_upload'   => 15 * 1024 * 1024,
    'max_side'     => 1600,
    'jpeg_quality' => 82,
    'backups_keep' => 10,

    'site_url' => 'index.html',
];
