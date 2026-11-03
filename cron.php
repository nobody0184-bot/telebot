<?php
declare(strict_types=1);

try {
    require_once __DIR__ . '/bootstrap.php';
    require_once __DIR__ . '/telegram.php';

    $now = new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));
    $dayBit = 1 << ((int)$now->format('N') - 1);
    $runKey = $now->format('Y-m-d H:i');
    $stmt = db()->prepare('SELECT * FROM schedules WHERE active=1 AND (days_mask & ?) <> 0 AND TIME_FORMAT(time_of_day, "%H:%i") = ? AND (last_run_key IS NULL OR last_run_key <> ?)');
    $stmt->execute([$dayBit, $now->format('H:i'), $runKey]);
    foreach ($stmt->fetchAll() as $schedule) {
        $destinations = db()->prepare('SELECT d.* FROM destinations d JOIN schedule_destinations sd ON sd.destination_id=d.id WHERE sd.schedule_id=?');
        $destinations->execute([$schedule['id']]);
        foreach ($destinations->fetchAll() as $destination) {
            try {
                send_schedule_message($schedule, $destination['chat_id']);
            } catch (Throwable $exception) {
                db()->prepare('INSERT INTO delivery_errors (schedule_id,destination_id,error_message) VALUES (?,?,?)')->execute([$schedule['id'], $destination['id'], $exception->getMessage()]);
            }
        }
        db()->prepare('UPDATE schedules SET last_run_key=? WHERE id=?')->execute([$runKey, $schedule['id']]);
    }
    echo 'OK ' . $now->format('Y-m-d H:i:s');
} catch (Throwable $exception) {
    http_response_code(500);
    error_log('Telegram scheduler cron error: ' . $exception->getMessage());
    echo 'Scheduler error: ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
}
