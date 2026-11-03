<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_login(); verify_csrf();
$stmt = db()->prepare('DELETE FROM schedules WHERE id=?');
$stmt->execute([(int)($_POST['id'] ?? 0)]);
header('Location: index.php');
