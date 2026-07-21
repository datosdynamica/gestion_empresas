<?php

declare(strict_types=1);

class SecUserModel extends BaseModel
{
    public function findByLoginAndEmpresa(string $login, int $idEmpresa): ?array
    {
        $sql = 'SELECT login, pswd, name, email, active, priv_admin, role, IdEmpresa
                FROM ' . TABLA_SEC_USERS . '
                WHERE login = ? AND IdEmpresa = ?
                LIMIT 1';

        return $this->fetchOne($sql, [$login, $idEmpresa]);
    }

    public function findAnyByEmpresa(int $idEmpresa): ?array
    {
        return $this->fetchOne(
            'SELECT login, pswd, name, email, active, priv_admin, role, IdEmpresa, IdLocal
             FROM ' . TABLA_SEC_USERS . '
             WHERE IdEmpresa = ?
             ORDER BY login ASC
             LIMIT 1',
            [$idEmpresa]
        );
    }

    public function ensureDefaultAdminForEmpresa(array $item, int $empresaId, int $idLocal): array
    {
        $login = preg_replace('/\D+/', '', (string) ($item['rut'] ?? ''));
        if ($login === '') {
            throw new RuntimeException('No fue posible generar el usuario Dynamica porque el RUT es invalido.');
        }

        $existing = $this->findByLoginAndEmpresa($login, $empresaId);
        if ($existing) {
            $this->ensureAdminGroup($login);

            return [
                'created' => false,
                'login' => $login,
                'password' => (string) ($existing['pswd'] ?? ''),
                'detail' => 'Usuario Dynamica ya existente.',
            ];
        }

        $password = $this->buildDefaultPassword($item);
        $name = mb_substr(trim((string) (($item['razon_social'] ?? '') ?: ($item['nombre_fantasia'] ?? ''))), 0, 64);
        $email = mb_substr(trim((string) ($item['email_principal'] ?? '')), 0, 64);

        $stmt = $this->db->prepare(
            'INSERT INTO ' . TABLA_SEC_USERS . ' (
                login, pswd, name, email, active, activation_code, priv_admin, IdEmpresa, IdLocal
            ) VALUES (
                :login, :pswd, :name, :email, :active, :activation_code, :priv_admin, :id_empresa, :id_local
            )'
        );

        $stmt->execute([
            'login' => $login,
            'pswd' => $password,
            'name' => $name !== '' ? $name : $login,
            'email' => $email !== '' ? $email : null,
            'active' => 'Y',
            'activation_code' => null,
            'priv_admin' => null,
            'id_empresa' => $empresaId,
            'id_local' => $idLocal,
        ]);

        $this->ensureAdminGroup($login);

        return [
            'created' => true,
            'login' => $login,
            'password' => $password,
            'detail' => 'Usuario Dynamica creado.',
        ];
    }

    private function ensureAdminGroup(string $login): void
    {
        $exists = $this->fetchOne(
            'SELECT login, group_id FROM sec_users_groups WHERE login = ? AND group_id = 1 LIMIT 1',
            [$login]
        );

        if ($exists) {
            return;
        }

        $stmt = $this->db->prepare('INSERT INTO sec_users_groups (login, group_id) VALUES (?, 1)');
        $stmt->execute([$login]);
    }

    private function buildDefaultPassword(array $item): string
    {
        $baseDate = trim((string) ($item['fecha_aprobacion'] ?? $item['fecha_creacion'] ?? ''));
        $ts = $baseDate !== '' ? strtotime($baseDate) : time();
        if ($ts === false) {
            $ts = time();
        }

        return date('dmY', $ts);
    }
}
