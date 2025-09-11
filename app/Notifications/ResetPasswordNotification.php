<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    private $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Сброс пароля'))
            ->line(Lang::get('Вы получили это письмо, потому что мы приняли запрос на сброс пароля для вашей учётной записи.'))
            ->action(Lang::get('Сбросить пароль'), $this->url)
            ->line(Lang::get('Срок действия этой ссылки заканчивается через :count минут.', ['count' =>config('auth.passwords.users.expire')]))
            ->line(Lang::get('Если вы не запрашивали сброс пароля, то просто проигнорируйте это сообщение.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
