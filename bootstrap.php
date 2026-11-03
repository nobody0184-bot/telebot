<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function day_options(): array
{
    return [1 => 'Monday', 2 => 'Tuesday', 4 => 'Wednesday', 8 => 'Thursday', 16 => 'Friday', 32 => 'Saturday', 64 => 'Sunday'];
}

function selected_days(int $mask): array
{
    return array_keys(array_filter(day_options(), static fn ($label, $bit) => ($mask & $bit) !== 0, ARRAY_FILTER_USE_BOTH));
}

function format_days(int $mask): string
{
    $days = [];
    foreach (day_options() as $bit => $label) {
        if (($mask & $bit) !== 0) {
            $days[] = substr($label, 0, 3);
        }
    }
    return implode(', ', $days);
}
