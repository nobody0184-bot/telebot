<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$error = '';
$done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || strlen($password) < 8) {
            throw new RuntimeException('Username is required and password must contain at least 8 characters.');
        }
        $schema = file_get_contents(__DIR__ . '/schema.sql');
        if ($schema === false) {
            throw new RuntimeException('Could not read schema.sql.');
        }
        foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
            db()->exec($statement);
        }
        $stmt = db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        $done = true;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Install Telegram Scheduler</title><link rel="stylesheet" href="style.css"></head>
<body><main class="auth-card"><h1>Install Telegram Scheduler</h1>
<?php if ($done): ?><div class="notice success">Installation complete. Delete install.php, then <a href="login.php">log in</a>.</div>
<?php else: ?>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><label>Admin username<input name="username" required></label><label>Password<input type="password" name="password" minlength="8" required></label><button>Install</button></form>
<?php endif; ?></main></body></html>
