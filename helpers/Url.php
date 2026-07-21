<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Helpers de URL
|--------------------------------------------------------------------------
| Generan rutas absolutas del modulo y de los assets publicos para que las
| vistas no tengan que reconstruir manualmente enlaces repetidos.
*/

/**
 * Construye una URL interna del modulo respetando el alias configurado.
 */
function app_url(string $path = ''): string
{
    $base = rtrim(APP_BASE_URL, '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base;
    }

    return $base . '/' . $path;
}

/**
 * Devuelve la URL publica de un asset del panel.
 */
function asset_url(string $path): string
{
    return app_url($path);
}
