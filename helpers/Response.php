<?php

declare(strict_types=1);

class Response
{
    public static function redirect(string $location): void
    {
        if (!preg_match('/^https?:\/\//i', $location) && !str_starts_with($location, '/')) {
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
