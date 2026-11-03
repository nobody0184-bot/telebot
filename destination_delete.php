<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_login(); verify_csrf();
db()->prepare('DELETE FROM destinations WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
header('Location: destinations.php');
