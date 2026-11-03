<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_header.php';
$schedules = db()->query('SELECT * FROM schedules ORDER BY time_of_day, title')->fetchAll();
?>
<div class="page-head"><h1>Schedules</h1><a class="button" href="schedule_form.php">Create schedule</a></div>
<?php if (!$schedules): ?><div class="empty">No schedules yet.</div><?php endif; ?>
<?php foreach ($schedules as $schedule): ?><article class="card"><div><h2><?= e($schedule['title']) ?></h2><p><?= e($schedule['message']) ?></p><small><?= e(substr($schedule['time_of_day'], 0, 5)) ?> · <?= e(format_days((int)$schedule['days_mask'])) ?> · <?= $schedule['active'] ? 'Active' : 'Paused' ?></small></div><div class="actions"><a href="schedule_form.php?id=<?= (int)$schedule['id'] ?>">Edit</a><form method="post" action="schedule_delete.php" onsubmit="return confirm('Delete this schedule?')"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$schedule['id'] ?>"><button class="link danger">Delete</button></form></div></article><?php endforeach; ?>
<?php require __DIR__ . '/footer.php'; ?>
