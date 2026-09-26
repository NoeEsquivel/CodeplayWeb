<?php

namespace App\Services;

use App\Mail\SecurityNotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SecurityMailService
{
    public function send(string $email, string $subject, string $body, ?string $actionUrl = null, ?string $actionLabel = null): bool
    {
        try {
            Mail::to($email)->send(new SecurityNotificationMail($subject, $body, $actionUrl, $actionLabel));
            return true;
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar una notificación de seguridad.', [
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);
            return false;
        }
    }
}
