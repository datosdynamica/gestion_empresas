<?php

declare(strict_types=1);

class EmpresaModel extends BaseModel
{
    public function existsByRut(string $rut): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM Empresas WHERE Rut = ? LIMIT 1');
        $stmt->execute([$rut]);
        return (bool) $stmt->fetchColumn();
    }

    public function createFromNuevaEmpresa(array $item): int
    {
        $sql = "INSERT INTO Empresas (
                    NombreFantasia, RazonSocial, Domicilio, Email, Habilitada,
                    Usuarios, Plan, cUsuarioEmailInv, cPassInv, Rut,
                    UsuarioEF, ClaveUsuarioEF, Licencia, Notificar,
                    NotificarSuspension, Suspension, LiteralE
                ) VALUES (
                    :NombreFantasia, :RazonSocial, :Domicilio, :Email, 'SI',
                    :Usuarios, :Plan, :cUsuarioEmailInv, NULL, :Rut,
                    :UsuarioEF, :ClaveUsuarioEF, :Licencia, 20,
                    20, 30, :LiteralE
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'NombreFantasia' => $item['nombre_fantasia'] ?: null,
            'RazonSocial' => $item['razon_social'],
            'Domicilio' => mb_substr((string) $item['domicilio'], 0, 50),
            'Email' => mb_substr((string) $item['email_principal'], 0, 50),
            'Usuarios' => $item['usuarios'] ?: 1,
            'Plan' => $item['plan'] ?: null,
            'cUsuarioEmailInv' => $item['email_principal'] ?: null,
            'Rut' => $item['rut'],
            'UsuarioEF' => mb_substr((string) $item['usuario_ef'], 0, 20),
            'ClaveUsuarioEF' => mb_substr((string) $item['clave_usuario_ef'], 0, 40),
            'Licencia' => $item['licencia'] ?: null,
            'LiteralE' => strtoupper((string) ($item['alta_credito_fiscal'] ?? '')) === 'LITERAL E' ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
