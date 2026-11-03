<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth_header.php';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$schedule = ['title' => '', 'message' => '', 'time_of_day' => '09:00:00', 'days_mask' => 127, 'active' => 1, 'media_path' => null, 'media_type' => 'none'];
$selectedChatIds = ['', ''];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM schedules WHERE id = ?');
    $stmt->execute([$id]);
    $schedule = $stmt->fetch() ?: $schedule;
    $stmt = db()->prepare('SELECT d.chat_id FROM destinations d JOIN schedule_destinations sd ON sd.destination_id = d.id WHERE sd.schedule_id = ? ORDER BY d.id LIMIT 2');
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $index => $chatId) {
        $selectedChatIds[$index] = $chatId;
    }
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $daysMask = array_sum(array_map('intval', $_POST['days'] ?? []));
        $chatIds = array_values(array_unique(array_filter(array_map('trim', $_POST['chat_ids'] ?? []))));
        if ($title === '' || $message === '' || $daysMask === 0 || !$chatIds) throw new RuntimeException('Title, message, at least one day and destination are required.');
        if (count($chatIds) > 2) throw new RuntimeException('A maximum of two destinations is supported.');
        $mediaPath = $schedule['media_path'];
        $mediaType = $schedule['media_type'];
        if (!empty($_FILES['media']['name'])) {
            $type = mime_content_type($_FILES['media']['tmp_name']);
            $mediaType = str_starts_with($type, 'image/') ? 'photo' : (str_starts_with($type, 'video/') ? 'video' : 'none');
            if ($mediaType === 'none' || $_FILES['media']['size'] > 49 * 1024 * 1024) throw new RuntimeException('Only image/video files up to 49 MB are supported.');
            $dir = __DIR__ . '/uploads';
            if (!is_dir($dir) && !mkdir($dir, 0755, true)) throw new RuntimeException('Could not create uploads directory.');
            $name = bin2hex(random_bytes(12)) . '_' . basename($_FILES['media']['name']);
            if (!move_uploaded_file($_FILES['media']['tmp_name'], $dir . '/' . $name)) throw new RuntimeException('Media upload failed.');
            $mediaPath = 'uploads/' . $name;
        }
        db()->beginTransaction();
        $destinationIds = [];
        foreach ($chatIds as $chatId) {
            $destinationName = str_starts_with($chatId, '@') ? substr($chatId, 1) : $chatId;
            $stmt = db()->prepare('INSERT INTO destinations (name, chat_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name)');
            $stmt->execute([$destinationName, $chatId]);
            $lookup = db()->prepare('SELECT id FROM destinations WHERE chat_id=?');
            $lookup->execute([$chatId]);
            $destinationIds[] = (int)$lookup->fetchColumn();
        }
        if ($id) {
            $stmt = db()->prepare('UPDATE schedules SET title=?, message=?, media_path=?, media_type=?, time_of_day=?, days_mask=?, active=? WHERE id=?');
            $stmt->execute([$title, $message, $mediaPath, $mediaType, $_POST['time_of_day'], $daysMask, isset($_POST['active']) ? 1 : 0, $id]);
            db()->prepare('DELETE FROM schedule_destinations WHERE schedule_id=?')->execute([$id]);
        } else {
            $stmt = db()->prepare('INSERT INTO schedules (title,message,media_path,media_type,time_of_day,days_mask,active) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$title, $message, $mediaPath, $mediaType, $_POST['time_of_day'], $daysMask, isset($_POST['active']) ? 1 : 0]);
            $id = (int)db()->lastInsertId();
        }
        $stmt = db()->prepare('INSERT INTO schedule_destinations (schedule_id,destination_id) VALUES (?,?)');
        foreach ($destinationIds as $destinationId) $stmt->execute([$id, $destinationId]);
        db()->commit();
        header('Location: index.php');
        exit;
    } catch (Throwable $exception) {
        if (db()->inTransaction()) db()->rollBack();
        $error = $exception->getMessage();
    }
}
?>
<div class="page-head"><h1><?= $id ? 'Edit' : 'Create' ?> schedule</h1><a href="index.php">Back</a></div>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form class="card form-grid" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>">
<label>Title<input name="title" value="<?= e($schedule['title']) ?>" required></label><label>Time<input type="time" name="time_of_day" value="<?= e(substr($schedule['time_of_day'],0,5)) ?>" required></label>
<label class="full">Message / caption<textarea name="message" rows="6" required><?= e($schedule['message']) ?></textarea></label>
<fieldset class="full"><legend>Days</legend><?php foreach (day_options() as $bit => $label): ?><label class="inline"><input type="checkbox" name="days[]" value="<?= $bit ?>" <?= (($schedule['days_mask'] & $bit) ? 'checked' : '') ?>> <?= e($label) ?></label><?php endforeach; ?></fieldset>
<label>Destination 1 (Chat ID)<input name="chat_ids[]" value="<?= e($selectedChatIds[0]) ?>" placeholder="@channel or -100..." required></label><label>Destination 2 (Chat ID)<input name="chat_ids[]" value="<?= e($selectedChatIds[1]) ?>" placeholder="Optional"></label>
<label>Photo/video (optional)<input type="file" name="media" accept="image/*,video/*"></label><label class="inline"><input type="checkbox" name="active" <?= $schedule['active'] ? 'checked' : '' ?>> Active</label><div class="full"><button>Save schedule</button></div></form>
<?php require __DIR__ . '/footer.php'; ?>
