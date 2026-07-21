<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Helper de respuestas HTTP
|--------------------------------------------------------------------------
| Reune utilidades para redirecciones, mensajes flash y respuestas JSON. La
| intencion es mantener una salida consistente desde controladores y helpers.
*/

/**
 * Utilidades estaticas para responder al navegador.
 */
class Response
{
    public static function redirect(string $location): void
    {
        if (!preg_match('/^https?:\/\//i', $location) && substr($location, 0, 1) !== '/') {
            $location = app_url($location);
        }

        header('Location: ' . $location);
        exit;
    }

    public static function flash(string $key, string $message): void
    {
        $_SESSION[$key] = $message;
    }
}
