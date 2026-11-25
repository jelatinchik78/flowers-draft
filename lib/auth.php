<?php
/**
 * Авторизация, CSRF и сообщения между запросами.
 */

defined('APP') or exit;

function auth_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $https,
        'samesite' => 'Lax',
    ]);
    session_name('flowers_admin');
    session_start();
}

function auth_config(array $config): array
{
    return $config;
}

function auth_hash(array $config): string
{
    $file = $config['auth_file'];
    if (is_file($file)) {
        $json = json_decode((string) file_get_contents($file), true);
        if (is_array($json) && !empty($json['password_hash'])) {
            return (string) $json['password_hash'];
        }
    }
    return (string) $config['password_hash'];
}

function auth_check(): bool
{
    return !empty($_SESSION['auth']);
}

function auth_attempts_key(): string
{
    return 'login_attempts';
}

function auth_login(string $password, array $config): bool
{
    $attempts = (int) ($_SESSION[auth_attempts_key()] ?? 0);
    if ($attempts >= 6) {
        flash_set('error', 'Слишком много попыток. Подождите несколько минут.');
        return false;
    }

    if (password_verify($password, auth_hash($config))) {
        session_regenerate_id(true);
        $_SESSION['auth'] = true;
        $_SESSION[auth_attempts_key()] = 0;
        unset($_SESSION['csrf']);
        return true;
    }

    $_SESSION[auth_attempts_key()] = $attempts + 1;
    usleep(400000);
    flash_set('error', 'Неверный пароль.');
    return false;
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', (bool) $p['secure'], (bool) $p['httponly']);
    }
    session_destroy();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void
{
    $sent = (string) ($_POST['csrf'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Ошибка проверки формы. Обновите страницу и попробуйте снова.');
    }
}

function flash_set(string $type, string $text): void
{
    $_SESSION['flash'][] = ['type' => $type, 'text' => $text];
}

function flash_take(): array
{
    $list = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $list;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
