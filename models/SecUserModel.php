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
}
