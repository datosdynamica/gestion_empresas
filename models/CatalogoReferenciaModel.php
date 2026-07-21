<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Catalogos de referencia
|--------------------------------------------------------------------------
| Expone listas maestras que alimentan selects y reglas del formulario, como
| giros, ciudades, departamentos, vendedores, productos y formas de pago.
*/

/**
 * Modelo de catalogos usados por el onboarding.
 */
class CatalogoReferenciaModel extends BaseModel
{
    public function listGiros(int $idEmpresa): array
    {
        return $this->fetchAll(
            "SELECT IdGiro AS id, Giro AS nombre
             FROM giros
             WHERE IdEmpresa = ?
               AND TRIM(COALESCE(Giro, '')) <> ''
             ORDER BY Giro",
            [$idEmpresa]
        );
    }

    public function listFidelizaciones(int $idEmpresa): array
    {
        return $this->fetchAll("SELECT IdFidelizacion AS id, Fidelizacion AS nombre FROM Fidelizacion WHERE IdEmpresa = ? AND TRIM(COALESCE(Fidelizacion, '')) <> '' ORDER BY Fidelizacion", [$idEmpresa]);
    }

    public function listCiudades(int $idEmpresa): array
    {
        return $this->fetchAll("SELECT IdCiudad AS id, Ciudad AS nombre FROM Ciudades WHERE IdEmpresa = ? AND TRIM(COALESCE(Ciudad, '')) <> '' ORDER BY Ciudad", [$idEmpresa]);
    }

    public function listDepartamentos(int $idEmpresa): array
    {
        return $this->fetchAll(
            "SELECT IdDepartamento AS id, Departamento AS nombre
             FROM Departamentos
             WHERE IdEmpresa = ?
               AND TRIM(COALESCE(Departamento, '')) <> ''
             ORDER BY Departamento",
            [$idEmpresa]
        );
    }

    public function listVendedores(int $idEmpresa): array
    {
        return $this->fetchAll("SELECT login AS id, COALESCE(NULLIF(name, ''), login) AS nombre FROM sec_users WHERE IdEmpresa = ? AND active = 'Y' AND TRIM(COALESCE(login, '')) <> '' ORDER BY COALESCE(NULLIF(name, ''), login)", [$idEmpresa]);
    }

    public function listProductosAbonado(int $idEmpresa): array
    {
        return $this->fetchAll(
            "SELECT IdProducto AS id, Descripcion AS nombre
             FROM Productos
             WHERE IdEmpresa = ?
               AND TRIM(COALESCE(Descripcion, '')) <> ''
             ORDER BY Descripcion",
            [$idEmpresa]
        );
    }

    public function listFormasPago(int $idEmpresa): array
    {
        return $this->fetchAll(
            "SELECT IdFormaPago AS id, forma AS nombre
             FROM FormaPago
             WHERE IdEmpresa = ?
               AND TRIM(COALESCE(forma, '')) <> ''
             ORDER BY forma, dias, IdFormaPago",
            [$idEmpresa]
        );
    }
}
