<?php

declare(strict_types=1);

function app_url(string $path = ''): string
{
    $base = rtrim(APP_BASE_URL, '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base;
    }

    return $base . '/' . $path;
}

function asset_url(string $path): string
{
    return app_url($path);
}
