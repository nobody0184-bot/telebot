<?php
declare(strict_types=1);

session_start();

$localConfig = __DIR__ . '/config.local.php';
$local = is_file($localConfig) ? require $localConfig : [];

define('DB_HOST', $local['db_host'] ?? 'localhost');
define('DB_NAME', $local['db_name'] ?? 'u556541716_telebot');
define('DB_USER', $local['db_user'] ?? 'u556541716_telebot');
define('DB_PASS', $local['db_pass'] ?? '');
define('TELEGRAM_BOT_TOKEN', $local['telegram_bot_token'] ?? 'PUT_BOT_TOKEN_HERE');
define('APP_TIMEZONE', $local['timezone'] ?? 'Asia/Dhaka');

date_default_timezone_set(APP_TIMEZONE);

function config_value(string $name, string $fallback): string
{
    $value = getenv($name);
    return $value === false ? $fallback : $value;
}

function app_url(string $path = ''): string
{
    return $path === '' ? 'index.php' : $path;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Invalid request token.');
    }
}

function require_login(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
