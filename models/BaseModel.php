<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Modelo base
|--------------------------------------------------------------------------
| Todos los modelos heredan de esta clase para compartir la conexion PDO y
| metodos cortos de consulta. Esto evita repetir el mismo codigo en cada tabla.
*/

/**
 * Clase base para modelos del modulo.
 */
abstract class BaseModel
{
    protected $db;

    /**
     * Toma la conexion compartida al instanciar cualquier modelo.
     */
    public function __construct()
    {
        $this->db = Db::conn();
    }

    /**
     * Ejecuta una consulta y devuelve todas las filas encontradas.
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta una consulta y devuelve una sola fila o null si no existe.
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
