SET FOREIGN_KEY_CHECKS = 0;

RENAME TABLE
    nuevas_empresas TO EmpresasNuevas,
    nuevas_empresas_archivos TO EmpresasNuevasArchivos,
    nuevas_empresas_historial TO EmpresasNuevasHistorial;

ALTER TABLE EmpresasNuevasArchivos DROP FOREIGN KEY fk_nea_nueva_empresa;
ALTER TABLE EmpresasNuevasHistorial DROP FOREIGN KEY fk_neh_nueva_empresa;

ALTER TABLE EmpresasNuevas
    CHANGE COLUMN id Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN estado Estado VARCHAR(30) NOT NULL DEFAULT 'PENDIENTE_APROBACION',
    CHANGE COLUMN estado_detalle EstadoDetalle VARCHAR(255) NULL,
    CHANGE COLUMN hito_actual HitoActual VARCHAR(50) NOT NULL DEFAULT 'APROBACION_PENDIENTE',
    CHANGE COLUMN fecha_creacion FechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHANGE COLUMN fecha_actualizacion FechaActualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHANGE COLUMN fecha_aprobacion FechaAprobacion DATETIME NULL,
    CHANGE COLUMN fecha_eliminacion FechaEliminacion DATETIME NULL,
    CHANGE COLUMN usuario_creacion UsuarioCreacion VARCHAR(100) NULL,
    CHANGE COLUMN usuario_aprobacion UsuarioAprobacion VARCHAR(100) NULL,
    CHANGE COLUMN usuario_eliminacion UsuarioEliminacion VARCHAR(100) NULL,
    CHANGE COLUMN motivo_eliminacion MotivoEliminacion TEXT NULL,
    CHANGE COLUMN razon_social RazonSocial VARCHAR(150) NOT NULL,
    CHANGE COLUMN nombre_fantasia NombreFantasia VARCHAR(70) NULL,
    CHANGE COLUMN domicilio Domicilio VARCHAR(100) NOT NULL,
    CHANGE COLUMN email_principal EmailPrincipal VARCHAR(100) NOT NULL,
    CHANGE COLUMN rut Rut VARCHAR(20) NOT NULL,
    CHANGE COLUMN telefono Telefono VARCHAR(30) NULL,
    CHANGE COLUMN ciudad Ciudad VARCHAR(100) NULL,
    CHANGE COLUMN departamento Departamento VARCHAR(100) NULL,
    CHANGE COLUMN usuario_ef UsuarioEF VARCHAR(50) NOT NULL,
    CHANGE COLUMN clave_usuario_ef ClaveUsuarioEF VARCHAR(100) NOT NULL,
    CHANGE COLUMN licencia Licencia SMALLINT NULL,
    CHANGE COLUMN licencia_texto LicenciaTexto VARCHAR(150) NULL,
    CHANGE COLUMN plan Plan VARCHAR(200) NULL,
    CHANGE COLUMN usuarios Usuarios INT NULL DEFAULT 1,
    CHANGE COLUMN cfe_mensuales CfeMensuales INT NULL,
    CHANGE COLUMN cliente_id_giro ClienteIdGiro INT NOT NULL DEFAULT 0,
    CHANGE COLUMN cliente_id_vendedor ClienteIdVendedor VARCHAR(255) NOT NULL DEFAULT 'admin',
    CHANGE COLUMN cliente_id_fidelizacion ClienteIdFidelizacion INT NOT NULL DEFAULT 0,
    CHANGE COLUMN email_envio_fe EmailEnvioFE VARCHAR(200) NULL,
    CHANGE COLUMN cliente_abonado ClienteAbonado VARCHAR(2) NOT NULL DEFAULT 'NO',
    CHANGE COLUMN cliente_abonado_id_producto ClienteAbonadoIdProducto INT NOT NULL DEFAULT 0,
    CHANGE COLUMN cliente_abonado_importe ClienteAbonadoImporte DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CHANGE COLUMN cliente_abonado_tv ClienteAbonadoTV VARCHAR(10) NOT NULL DEFAULT 'CONTADO',
    CHANGE COLUMN cliente_abonado_moneda ClienteAbonadoMoneda VARCHAR(3) NOT NULL DEFAULT 'UYU',
    CHANGE COLUMN cliente_abonado_periodo ClienteAbonadoPeriodo VARCHAR(10) NOT NULL DEFAULT 'MENSUAL',
    CHANGE COLUMN cliente_abonado_grupo ClienteAbonadoGrupo VARCHAR(20) NULL,
    CHANGE COLUMN cliente_abonado_descuento ClienteAbonadoDescuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CHANGE COLUMN cliente_pn_credito_fiscal ClientePnCreditoFiscal VARCHAR(2) NOT NULL DEFAULT 'NO',
    CHANGE COLUMN cliente_pn_monto ClientePnMonto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    CHANGE COLUMN cliente_id_formapago ClienteIdFormaPago INT NOT NULL DEFAULT 0,
    CHANGE COLUMN cliente_id_medio_pago ClienteIdMedioPago INT NOT NULL DEFAULT 0,
    CHANGE COLUMN nombre_completo_firmante NombreCompletoFirmante VARCHAR(100) NULL,
    CHANGE COLUMN ci_firmante CIFirmante VARCHAR(20) NULL,
    CHANGE COLUMN cliente_adenda ClienteAdenda VARCHAR(500) NULL,
    CHANGE COLUMN suc_cod_sucursal SucCodSucursal VARCHAR(20) NULL,
    CHANGE COLUMN suc_cod_fecha_vigencia SucCodFechaVigencia DATE NULL,
    CHANGE COLUMN alta_especial AltaEspecial VARCHAR(30) NOT NULL DEFAULT 'NO',
    CHANGE COLUMN alta_especial_norma AltaEspecialNorma VARCHAR(255) NULL,
    CHANGE COLUMN alta_es_emisor AltaEsEmisor VARCHAR(2) NOT NULL DEFAULT 'NO',
    CHANGE COLUMN alta_credito_fiscal AltaCreditoFiscal VARCHAR(30) NOT NULL DEFAULT 'NO',
    CHANGE COLUMN alta_certificado_digital AltaCertificadoDigital VARCHAR(30) NULL,
    CHANGE COLUMN carpeta_base CarpetaBase VARCHAR(500) NULL,
    CHANGE COLUMN carpeta_creada CarpetaCreada TINYINT(1) NOT NULL DEFAULT 0,
    CHANGE COLUMN aprobada Aprobada TINYINT(1) NOT NULL DEFAULT 0,
    CHANGE COLUMN empresa_creada EmpresaCreada TINYINT(1) NOT NULL DEFAULT 0,
    CHANGE COLUMN cliente_creado ClienteCreada TINYINT(1) NOT NULL DEFAULT 0,
    CHANGE COLUMN empresa_id_creada EmpresaIdCreada BIGINT UNSIGNED NULL,
    CHANGE COLUMN cliente_id_creado ClienteIdCreado INT NULL,
    CHANGE COLUMN observaciones Observaciones TEXT NULL,
    CHANGE COLUMN notas_admin NotasAdmin TEXT NULL,
    CHANGE COLUMN error_proceso ErrorProceso TEXT NULL;

ALTER TABLE EmpresasNuevasArchivos
    CHANGE COLUMN id Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN nueva_empresa_id NuevaEmpresaId BIGINT UNSIGNED NOT NULL,
    CHANGE COLUMN tipo_archivo TipoArchivo VARCHAR(50) NOT NULL,
    CHANGE COLUMN nombre_original NombreOriginal VARCHAR(255) NOT NULL,
    CHANGE COLUMN nombre_guardado NombreGuardado VARCHAR(255) NOT NULL,
    CHANGE COLUMN ruta_archivo RutaArchivo VARCHAR(500) NOT NULL,
    CHANGE COLUMN extension Extension VARCHAR(20) NULL,
    CHANGE COLUMN mime_type MimeType VARCHAR(100) NULL,
    CHANGE COLUMN tamano_bytes TamanoBytes BIGINT NULL,
    CHANGE COLUMN obligatorio Obligatorio TINYINT(1) NOT NULL DEFAULT 0,
    CHANGE COLUMN activo Activo TINYINT(1) NOT NULL DEFAULT 1,
    CHANGE COLUMN fecha_subida FechaSubida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHANGE COLUMN usuario_subida UsuarioSubida VARCHAR(100) NULL;

ALTER TABLE EmpresasNuevasHistorial
    CHANGE COLUMN id Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN nueva_empresa_id NuevaEmpresaId BIGINT UNSIGNED NOT NULL,
    CHANGE COLUMN evento Evento VARCHAR(50) NOT NULL,
    CHANGE COLUMN estado_anterior EstadoAnterior VARCHAR(30) NULL,
    CHANGE COLUMN estado_nuevo EstadoNuevo VARCHAR(30) NULL,
    CHANGE COLUMN descripcion Descripcion TEXT NULL,
    CHANGE COLUMN usuario_evento UsuarioEvento VARCHAR(100) NULL,
    CHANGE COLUMN fecha_evento FechaEvento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE EmpresasNuevasArchivos
    ADD CONSTRAINT fk_Ena_NuevaEmpresa
        FOREIGN KEY (NuevaEmpresaId)
        REFERENCES EmpresasNuevas(Id)
        ON DELETE CASCADE;

ALTER TABLE EmpresasNuevasHistorial
    ADD CONSTRAINT fk_Enh_NuevaEmpresa
        FOREIGN KEY (NuevaEmpresaId)
        REFERENCES EmpresasNuevas(Id)
        ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
