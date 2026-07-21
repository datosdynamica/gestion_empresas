ALTER TABLE EmpresasNuevas
    ADD COLUMN MigrateRequestXml LONGTEXT NULL AFTER ClienteIdCreado,
    ADD COLUMN MigrateResponseXml LONGTEXT NULL AFTER MigrateRequestXml;
