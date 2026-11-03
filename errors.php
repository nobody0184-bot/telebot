<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_header.php';
$errors = db()->query('SELECT e.*, s.title, d.name FROM delivery_errors e JOIN schedules s ON s.id=e.schedule_id JOIN destinations d ON d.id=e.destination_id ORDER BY e.created_at DESC LIMIT 100')->fetchAll();
?>
<div class="page-head"><h1>Delivery errors</h1></div>
<?php foreach ($errors as $error): ?><article class="card"><strong><?= e($error['title']) ?> → <?= e($error['name']) ?></strong><p><?= e($error['error_message']) ?></p><small><?= e($error['created_at']) ?></small></article><?php endforeach; ?>
<?php require __DIR__ . '/footer.php'; ?>
