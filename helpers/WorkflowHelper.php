<?php

declare(strict_types=1);

class WorkflowHelper
{
    public static function canApprove(array $item): bool
    {
        return ($item['estado'] ?? '') === ESTADO_PENDIENTE_APROBACION
            && (int) ($item['aprobada'] ?? 0) === 0;
    }
}
