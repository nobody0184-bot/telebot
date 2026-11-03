<?php
declare(strict_types=1);

session_start();

const DB_HOST = 'localhost';
const DB_NAME = 'telegram_scheduler';
const DB_USER = 'root';
const DB_PASS = '';
const TELEGRAM_BOT_TOKEN = 'PUT_BOT_TOKEN_HERE';
const APP_TIMEZONE = 'Asia/Dhaka';

date_default_timezone_set(APP_TIMEZONE);

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
