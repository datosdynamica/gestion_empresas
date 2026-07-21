<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Conexion PDO compartida
|--------------------------------------------------------------------------
| Mantiene una unica conexion reutilizable a MySQL para todo el modulo. Esto
| evita crear conexiones nuevas en cada modelo y deja centralizado el manejo
| basico de errores de base de datos.
*/

/**
 * Fabrica de conexion PDO usada por todos los modelos.
 */
class Db
{
    private static $instance = null;

    public static function conn(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new PDO(DB_DSN, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return self::$instance;
    }
}
