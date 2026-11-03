<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_header.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $names = $_POST['names'] ?? [];
        $chatIds = $_POST['chat_ids'] ?? [];
        $saved = 0;
        foreach ($chatIds as $index => $chatId) {
            $chatId = trim($chatId);
            $name = trim($names[$index] ?? '');
            if ($chatId === '') continue;
            if ($name === '') $name = str_starts_with($chatId, '@') ? substr($chatId, 1) : $chatId;
            db()->prepare('INSERT INTO destinations (name, chat_id) VALUES (?,?) ON DUPLICATE KEY UPDATE name=VALUES(name)')->execute([$name, $chatId]);
            $saved++;
        }
        if ($saved === 0) throw new RuntimeException('Enter at least one Chat ID.');
        header('Location: destinations.php'); exit;
    } catch (Throwable $exception) { $error = $exception->getMessage(); }
}
$destinations = db()->query('SELECT * FROM destinations ORDER BY name')->fetchAll();
?>
<div class="page-head"><h1>Destinations</h1></div>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form class="card form-grid" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Destination 1 name<input name="names[]" placeholder="Survey Nerds"></label><label>Destination 1 Chat ID<input name="chat_ids[]" placeholder="@surveynerds" required></label><label>Destination 2 name<input name="names[]" placeholder="Optional"></label><label>Destination 2 Chat ID<input name="chat_ids[]" placeholder="-100..."></label><div class="full"><button>Add destinations</button></div></form>
<?php foreach ($destinations as $destination): ?><article class="card row"><span><?= e($destination['name']) ?> <small><?= e($destination['chat_id']) ?></small></span><form method="post" action="destination_delete.php"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$destination['id'] ?>"><button class="link danger">Delete</button></form></article><?php endforeach; ?>
<?php require __DIR__ . '/footer.php'; ?>
