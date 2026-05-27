<?php

declare(strict_types=1);

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION[AUTH_SESSION_KEY]) && is_array($_SESSION[AUTH_SESSION_KEY]);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return $_SESSION[AUTH_SESSION_KEY];
    }

    public static function login(array $user): void
    {
        if (function_exists('session_regenerate_id')) {
            session_regenerate_id(true);
        }

        $_SESSION[AUTH_SESSION_KEY] = [
            'login' => (string) $user['login'],
            'name' => (string) ($user['name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'role' => (string) ($user['role'] ?? ''),
            'IdEmpresa' => (int) ($user['IdEmpresa'] ?? 0),
        ];

        $_SESSION['usuario'] = (string) $user['login'];
    }

    public static function logout(): void
    {
        unset($_SESSION[AUTH_SESSION_KEY], $_SESSION['usuario']);
    }

    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        Response::redirect('login');
    }

    public static function rememberLogin(string $login): void
    {
        setcookie(AUTH_REMEMBER_LOGIN_COOKIE, $login, time() + (86400 * 30), APP_BASE_URL);
    }

    public static function forgetRememberedLogin(): void
    {
        setcookie(AUTH_REMEMBER_LOGIN_COOKIE, '', time() - 3600, APP_BASE_URL);
    }

    public static function rememberedLogin(): string
    {
        return isset($_COOKIE[AUTH_REMEMBER_LOGIN_COOKIE]) ? trim((string) $_COOKIE[AUTH_REMEMBER_LOGIN_COOKIE]) : '';
    }
}
