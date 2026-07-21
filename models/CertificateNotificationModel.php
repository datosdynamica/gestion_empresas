<?php

declare(strict_types=1);

class CertificateNotificationModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function wasSent(int $empresaId, string $rut, string $fechaVencimiento, int $diasObjetivo): bool
    {
        $sql = 'SELECT 1
                FROM ' . TABLA_CERTIFICADOS_NOTIFICACIONES . '
                WHERE EmpresaId = ?
                  AND Rut = ?
                  AND FechaVencimiento = ?
                  AND DiasObjetivo = ?
                  AND Estado = ?
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $empresaId,
            trim($rut),
            $fechaVencimiento,
            $diasObjetivo,
            'ENVIADO',
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO ' . TABLA_CERTIFICADOS_NOTIFICACIONES . ' (
                    EmpresaId,
                    Rut,
                    RazonSocial,
                    Apodo,
                    CerStatus,
                    DiasObjetivo,
                    DiasRestantes,
                    FechaVencimiento,
                    TipoNotificacion,
                    Destinatarios,
                    Copias,
                    Asunto,
                    Plantilla,
                    Estado,
                    Detalle,
                    BodyHtml,
                    PayloadJson,
                    FechaEnvio
                ) VALUES (
                    :empresa_id,
                    :rut,
                    :razon_social,
                    :apodo,
                    :cer_status,
                    :dias_objetivo,
                    :dias_restantes,
                    :fecha_vencimiento,
                    :tipo_notificacion,
                    :destinatarios,
                    :copias,
                    :asunto,
                    :plantilla,
                    :estado,
                    :detalle,
                    :body_html,
                    :payload_json,
                    NOW()
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':empresa_id' => (int) ($data['empresa_id'] ?? 0),
            ':rut' => mb_substr(trim((string) ($data['rut'] ?? '')), 0, 20),
            ':razon_social' => mb_substr(trim((string) ($data['razon_social'] ?? '')), 0, 255),
            ':apodo' => mb_substr(trim((string) ($data['apodo'] ?? '')), 0, 120),
            ':cer_status' => mb_substr(trim((string) ($data['cer_status'] ?? '')), 0, 5),
            ':dias_objetivo' => (int) ($data['dias_objetivo'] ?? 0),
            ':dias_restantes' => (int) ($data['dias_restantes'] ?? 0),
            ':fecha_vencimiento' => trim((string) ($data['fecha_vencimiento'] ?? '')),
            ':tipo_notificacion' => mb_substr(trim((string) ($data['tipo_notificacion'] ?? 'CERTIFICADO_VENCIMIENTO')), 0, 40),
            ':destinatarios' => trim((string) ($data['destinatarios'] ?? '')),
            ':copias' => trim((string) ($data['copias'] ?? '')),
            ':asunto' => mb_substr(trim((string) ($data['asunto'] ?? '')), 0, 255),
            ':plantilla' => mb_substr(trim((string) ($data['plantilla'] ?? '')), 0, 150),
            ':estado' => mb_substr(trim((string) ($data['estado'] ?? 'ENVIADO')), 0, 20),
            ':detalle' => trim((string) ($data['detalle'] ?? '')),
            ':body_html' => (string) ($data['body_html'] ?? ''),
            ':payload_json' => (string) ($data['payload_json'] ?? ''),
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function ensureTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . TABLA_CERTIFICADOS_NOTIFICACIONES . ' (
                    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    EmpresaId INT NOT NULL,
                    Rut VARCHAR(20) NOT NULL DEFAULT \'\',
                    RazonSocial VARCHAR(255) NOT NULL DEFAULT \'\',
                    Apodo VARCHAR(120) NOT NULL DEFAULT \'\',
                    CerStatus VARCHAR(5) NOT NULL DEFAULT \'\',
                    DiasObjetivo SMALLINT NOT NULL DEFAULT 0,
                    DiasRestantes SMALLINT NOT NULL DEFAULT 0,
                    FechaVencimiento DATE NOT NULL,
                    TipoNotificacion VARCHAR(40) NOT NULL DEFAULT \'CERTIFICADO_VENCIMIENTO\',
                    Destinatarios TEXT NULL,
                    Copias TEXT NULL,
                    Asunto VARCHAR(255) NOT NULL DEFAULT \'\',
                    Plantilla VARCHAR(150) NOT NULL DEFAULT \'\',
                    Estado VARCHAR(20) NOT NULL DEFAULT \'ENVIADO\',
                    Detalle TEXT NULL,
                    BodyHtml MEDIUMTEXT NULL,
                    PayloadJson LONGTEXT NULL,
                    FechaEnvio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    CreatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UpdatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (Id),
                    KEY idx_cert_notif_empresa_fecha (EmpresaId, FechaVencimiento, DiasObjetivo),
                    KEY idx_cert_notif_rut_fecha (Rut, FechaVencimiento),
                    KEY idx_cert_notif_estado (Estado, FechaEnvio)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->db->exec($sql);
    }
}
