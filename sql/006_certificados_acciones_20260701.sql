CREATE TABLE IF NOT EXISTS CertificadosAcciones (
    Id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    EmpresaId INT NOT NULL,
    Rut VARCHAR(20) NOT NULL DEFAULT '',
    Accion VARCHAR(40) NOT NULL,
    Descripcion VARCHAR(255) NOT NULL DEFAULT '',
    UsuarioLogin VARCHAR(100) NOT NULL DEFAULT '',
    UsuarioNombre VARCHAR(150) NOT NULL DEFAULT '',
    FechaAccion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Id),
    KEY idx_cert_acciones_empresa_fecha (EmpresaId, FechaAccion),
    KEY idx_cert_acciones_rut (Rut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
