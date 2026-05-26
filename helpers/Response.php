<?php

declare(strict_types=1);

class Response
{
    public static function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }

    public static function flash(string $key, string $message): void
    {
        $_SESSION[$key] = $message;
    }
}
