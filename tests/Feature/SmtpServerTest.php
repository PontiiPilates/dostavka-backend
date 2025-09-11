<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class SmtpServerTest extends TestCase
{
    /**
     * Проверка работоспособности SMTP-сервера.
     */
    public function test_mail_delivery(): void
    {
        // установка конфигурации
        config([
            'mail.driver' => 'smtp',
            'mail.host' => 'mailhog',
            'mail.port' => 1025,
        ]);

        // очистка почтового ящика
        file_get_contents('http://mailhog:8025/api/v1/messages', false, stream_context_create([
            'http' => ['method' => 'DELETE']
        ]));

        // отправка письма
        $user = User::where('email', config('custom.frontend_email'))->first();
        $user->sendEmailVerificationNotification();

        sleep(1);

        // запрос писем в ящике
        $emails = @file_get_contents('http://mailhog:8025/api/v2/messages');
        $emailData = json_decode($emails, true);

        assertEquals(1, count($emailData['items']));
    }
}
