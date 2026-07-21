<?php

declare(strict_types=1);

class WorkflowHelper
{
    private const LICENCIAS_USUARIO_DYNAMICA = [0, 2, 3, 10];
    private const LICENCIAS_USUARIO_MIGRATE = [12, 14];

    public static function canApprove(array $item): bool
    {
        $estado = (string) ($item['estado'] ?? '');
        $aprobada = (int) ($item['aprobada'] ?? 0) === 1;
        $empresaCreada = (int) ($item['empresa_creada'] ?? 0) === 1;
        $clienteCreado = (int) ($item['cliente_creado'] ?? 0) === 1;

        if ($estado === ESTADO_PENDIENTE_APROBACION && !$aprobada) {
            return true;
        }

        if ($estado === ESTADO_ERROR_APROBACION && !$empresaCreada && !$clienteCreado) {
            return true;
        }

        return false;
    }

    public static function licenseCreatesDynamicaUser(int $licencia): bool
    {
        return in_array($licencia, self::LICENCIAS_USUARIO_DYNAMICA, true);
    }

    public static function licenseCreatesMigrateUser(int $licencia): bool
    {
        return in_array($licencia, self::LICENCIAS_USUARIO_MIGRATE, true);
    }

    public static function dynamicaUserSubstepLabel(int $licencia): string
    {
        return self::licenseCreatesDynamicaUser($licencia)
            ? 'Usuario Dynamica requerido para esta licencia.'
            : 'Usuario Dynamica no aplica para esta licencia.';
    }

    public static function migrateUserSubstepLabel(int $licencia): string
    {
        return self::licenseCreatesMigrateUser($licencia)
            ? 'Usuario Migrate requerido para esta licencia.'
            : 'Usuario Migrate no aplica para esta licencia.';
    }
}
