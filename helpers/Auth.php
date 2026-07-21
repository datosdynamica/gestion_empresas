<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Helper de autenticacion
|--------------------------------------------------------------------------
| Centraliza la lectura y escritura de la sesion del modulo. Tambien conserva
| el ultimo login recordado para facilitar el ingreso desde el navegador.
*/

/**
 * Utilidades estaticas para manejar la sesion del usuario.
 */
class Auth
{
    /**
     * Indica si existe una sesion valida del modulo.
     */
    public static function check(): bool
    {
        return !empty($_SESSION[AUTH_SESSION_KEY]) && is_array($_SESSION[AUTH_SESSION_KEY]);
    }

    /**
     * Devuelve los datos del usuario autenticado o null si no hay sesion.
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return $_SESSION[AUTH_SESSION_KEY];
    }

    /**
     * Guarda en sesion los datos minimos que necesita el panel.
     */
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

    /**
     * Limpia los datos de autenticacion del usuario actual.
     */
    public static function logout(): void
    {
        unset($_SESSION[AUTH_SESSION_KEY], $_SESSION['usuario']);
    }

    /**
     * Fuerza redireccion al login cuando la sesion no existe.
     */
    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        Response::redirect('login');
    }

    /**
     * Recuerda el ultimo login usado para precargar el formulario.
     */
    public static function rememberLogin(string $login): void
    {
        setcookie(AUTH_REMEMBER_LOGIN_COOKIE, $login, time() + (86400 * 30), APP_BASE_URL);
    }

    /**
     * Elimina el login recordado del navegador.
     */
    public static function forgetRememberedLogin(): void
    {
        setcookie(AUTH_REMEMBER_LOGIN_COOKIE, '', time() - 3600, APP_BASE_URL);
    }

    /**
     * Lee el login recordado si el navegador lo conserva.
     */
    public static function rememberedLogin(): string
    {
        return isset($_COOKIE[AUTH_REMEMBER_LOGIN_COOKIE]) ? trim((string) $_COOKIE[AUTH_REMEMBER_LOGIN_COOKIE]) : '';
    }
}
