<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Reglas cortas del workflow
|--------------------------------------------------------------------------
| Este helper concentra decisiones rapidas del flujo: quien puede aprobar, en
| que licencias aplica usuario Dynamica y en que licencias aplica usuario
| Migrate. Se usa para no repetir reglas de negocio simples en varias capas.
*/

/**
 * Reglas estaticas de negocio asociadas al onboarding.
 */
class WorkflowHelper
{
    private const LICENCIAS_USUARIO_DYNAMICA = [0, 2, 3, 10];
    private const LICENCIAS_USUARIO_MIGRATE = [12, 14];

    /**
     * Define si el registro puede volver a pasar por aprobacion manual.
     */
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

    /**
     * Indica si la licencia exige crear usuario en Dynamica.
     */
    public static function licenseCreatesDynamicaUser(int $licencia): bool
    {
        return in_array($licencia, self::LICENCIAS_USUARIO_DYNAMICA, true);
    }

    /**
     * Indica si la licencia exige crear usuario en Migrate.
     */
    public static function licenseCreatesMigrateUser(int $licencia): bool
    {
        return in_array($licencia, self::LICENCIAS_USUARIO_MIGRATE, true);
    }

    /**
     * Texto visible para el subhito de usuario Dynamica.
     */
    public static function dynamicaUserSubstepLabel(int $licencia): string
    {
        return self::licenseCreatesDynamicaUser($licencia)
            ? 'Usuario Dynamica requerido para esta licencia.'
            : 'Usuario Dynamica no aplica para esta licencia.';
    }

    /**
     * Texto visible para el subhito de usuario Migrate.
     */
    public static function migrateUserSubstepLabel(int $licencia): string
    {
        return self::licenseCreatesMigrateUser($licencia)
            ? 'Usuario Migrate requerido para esta licencia.'
            : 'Usuario Migrate no aplica para esta licencia.';
    }
}
