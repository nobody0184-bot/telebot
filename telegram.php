<?php
declare(strict_types=1);

function telegram_request(string $method, array $fields): array
{
    if (TELEGRAM_BOT_TOKEN === 'PUT_BOT_TOKEN_HERE') {
        throw new RuntimeException('Set TELEGRAM_BOT_TOKEN in config.php first.');
    }
    $ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/' . $method);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $fields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $body = curl_exec($ch);
    if ($body === false) {
        throw new RuntimeException(curl_error($ch));
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $result = json_decode($body, true);
    if ($status >= 400 || !is_array($result) || empty($result['ok'])) {
        throw new RuntimeException($result['description'] ?? 'Telegram API request failed.');
    }
    return $result;
}

function send_schedule_message(array $schedule, string $chatId): void
{
    $method = 'sendMessage';
    $fields = ['chat_id' => $chatId, 'text' => $schedule['message']];
    if ($schedule['media_type'] === 'photo') {
        $method = 'sendPhoto';
        $fields = ['chat_id' => $chatId, 'photo' => new CURLFile(__DIR__ . '/' . $schedule['media_path']), 'caption' => $schedule['message']];
    } elseif ($schedule['media_type'] === 'video') {
        $method = 'sendVideo';
        $fields = ['chat_id' => $chatId, 'video' => new CURLFile(__DIR__ . '/' . $schedule['media_path']), 'caption' => $schedule['message']];
    }
    telegram_request($method, $fields);
}
