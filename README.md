# Telegram Scheduler

## Local setup

1. Create a MySQL database named `telegram_scheduler`.
2. Copy `config.local.example.php` to `config.local.php`, then set the database password and BotFather token in `config.local.php`. This file is ignored by Git and must never be committed. The database name and user are already configured for the supplied Hostinger database.
3. Open `install.php` once and create the admin account.
4. Delete `install.php`.
5. In Telegram, add the bot as an administrator of each target group/channel. The bot needs permission to post messages.
6. Log in, add destinations, then create schedules.

The schedule time uses `Asia/Dhaka`. Select one or more weekdays and one or more destinations. The scheduler records delivery errors in the **Errors** page.

## Cron

Configure the hosting cron to request this URL every minute:

`https://your-domain.example/cron.php`

The script is safe to call repeatedly within the same minute. If InfinityFree does not provide a usable cron feature, use an external HTTP cron service to request the same URL every minute.
