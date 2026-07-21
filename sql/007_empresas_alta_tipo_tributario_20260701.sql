SET @db_name = DATABASE();

SET @has_alta_tipoempresa = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'Empresas'
      AND COLUMN_NAME = 'AltaTipoEmpresa'
);

SET @sql_add_alta_tipoempresa = IF(
    @has_alta_tipoempresa = 0,
    'ALTER TABLE Empresas ADD COLUMN AltaTipoEmpresa VARCHAR(20) NULL DEFAULT NULL',
    'SELECT ''Empresas.AltaTipoEmpresa ya existe'' AS info'
);
PREPARE stmt FROM @sql_add_alta_tipoempresa;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_alta_tributario = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'Empresas'
      AND COLUMN_NAME = 'AltaTributario'
);

SET @sql_add_alta_tributario = IF(
    @has_alta_tributario = 0,
    'ALTER TABLE Empresas ADD COLUMN AltaTributario VARCHAR(30) NULL DEFAULT NULL',
    'SELECT ''Empresas.AltaTributario ya existe'' AS info'
);
PREPARE stmt FROM @sql_add_alta_tributario;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE Empresas e
INNER JOIN (
    SELECT ne1.Rut,
           ne1.AltaTipoEmpresa,
           ne1.AltaEspecial AS AltaTributario
    FROM EmpresasNuevas ne1
    INNER JOIN (
        SELECT Rut, MAX(Id) AS MaxId
        FROM EmpresasNuevas
        WHERE COALESCE(Rut, '') <> ''
        GROUP BY Rut
    ) ne2 ON ne2.MaxId = ne1.Id
) ne ON ne.Rut = e.Rut
SET e.AltaTipoEmpresa = CASE
        WHEN COALESCE(TRIM(e.AltaTipoEmpresa), '') = '' THEN ne.AltaTipoEmpresa
        ELSE e.AltaTipoEmpresa
    END,
    e.AltaTributario = CASE
        WHEN COALESCE(TRIM(e.AltaTributario), '') = '' THEN ne.AltaTributario
        ELSE e.AltaTributario
    END
WHERE COALESCE(e.Rut, '') <> '';
