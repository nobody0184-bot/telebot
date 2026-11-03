<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_header.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $chatId = trim($_POST['chat_id'] ?? '');
        if ($name === '' || $chatId === '') throw new RuntimeException('Name and chat ID are required.');
        db()->prepare('INSERT INTO destinations (name, chat_id) VALUES (?,?)')->execute([$name, $chatId]);
        header('Location: destinations.php'); exit;
    } catch (Throwable $exception) { $error = $exception->getMessage(); }
}
$destinations = db()->query('SELECT * FROM destinations ORDER BY name')->fetchAll();
?>
<div class="page-head"><h1>Destinations</h1></div>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form class="card inline-form" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input name="name" placeholder="Group or channel name" required><input name="chat_id" placeholder="@channel or -100..." required><button>Add</button></form>
<?php foreach ($destinations as $destination): ?><article class="card row"><span><?= e($destination['name']) ?> <small><?= e($destination['chat_id']) ?></small></span><form method="post" action="destination_delete.php"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$destination['id'] ?>"><button class="link danger">Delete</button></form></article><?php endforeach; ?>
<?php require __DIR__ . '/footer.php'; ?>
