DROP DATABASE IF EXISTS sisprestamos;
CREATE DATABASE sisprestamos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sisprestamos;

CREATE TABLE roles (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(150) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rol_id TINYINT UNSIGNED NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NULL,
    estado ENUM('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
    ultimo_acceso DATETIME NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NULL DEFAULT NULL,
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    foto VARCHAR(255) NULL,
    nombres VARCHAR(150) NOT NULL,
    dni VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(120) NULL,
    direccion VARCHAR(255) NOT NULL,
    nacionalidad VARCHAR(80) NOT NULL,
    tipo_vivienda VARCHAR(50) NOT NULL,
    situacion_laboral VARCHAR(50) NOT NULL,
    estado_civil VARCHAR(30) NOT NULL,
    direccion_trabajo VARCHAR(255) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    eliminado_en DATETIME NULL,
    INDEX idx_clientes_nombres (nombres),
    INDEX idx_clientes_dni (dni)
) ENGINE=InnoDB;

CREATE TABLE garantes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL UNIQUE,
    foto VARCHAR(255) NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    dni VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    CONSTRAINT fk_garantes_clientes FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB;

CREATE TABLE productos_prestamo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    frecuencia_pago ENUM('diario','semanal','quincenal','mensual') NOT NULL,
    tasa_interes_tipo ENUM('anual','mensual') NOT NULL,
    tasa_interes_valor DECIMAL(8,4) NOT NULL,
    tasa_mora_diaria DECIMAL(8,4) NOT NULL,
    cuotas_maximas SMALLINT UNSIGNED NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE prestamos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    numero_prestamo VARCHAR(30) NOT NULL UNIQUE,
    monto_principal DECIMAL(12,2) NOT NULL,
    tasa_interes_tipo ENUM('anual','mensual') NOT NULL,
    tasa_interes_valor DECIMAL(8,4) NOT NULL,
    tasa_mora_diaria DECIMAL(8,4) NOT NULL,
    plazo_cuotas SMALLINT UNSIGNED NOT NULL,
    frecuencia_pago ENUM('diario','semanal','quincenal','mensual') NOT NULL,
    fecha_otorgamiento DATE NOT NULL,
    fecha_primer_pago DATE NOT NULL,
    total_interes DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_mora DECIMAL(12,2) NOT NULL DEFAULT 0,
    saldo_pendiente DECIMAL(12,2) NOT NULL,
    estado ENUM('vigente','pagado','vencido','moroso','anulado') NOT NULL DEFAULT 'vigente',
    observaciones TEXT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_prestamos_clientes FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    CONSTRAINT fk_prestamos_productos FOREIGN KEY (producto_id) REFERENCES productos_prestamo(id),
    CONSTRAINT fk_prestamos_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_prestamos_estado (estado),
    INDEX idx_prestamos_fecha (fecha_otorgamiento)
) ENGINE=InnoDB;

CREATE TABLE cuotas_prestamo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prestamo_id INT UNSIGNED NOT NULL,
    numero_cuota SMALLINT UNSIGNED NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    capital_programado DECIMAL(12,2) NOT NULL,
    interes_programado DECIMAL(12,2) NOT NULL,
    capital_pagado DECIMAL(12,2) NOT NULL DEFAULT 0,
    interes_pagado DECIMAL(12,2) NOT NULL DEFAULT 0,
    mora_acumulada DECIMAL(12,2) NOT NULL DEFAULT 0,
    mora_pagada DECIMAL(12,2) NOT NULL DEFAULT 0,
    monto_programado DECIMAL(12,2) NOT NULL,
    saldo_cuota DECIMAL(12,2) NOT NULL,
    fecha_ultimo_calculo_mora DATE NULL,
    estado ENUM('pendiente','parcial','pagada','vencida','morosa') NOT NULL DEFAULT 'pendiente',
    UNIQUE KEY uq_cuotas_prestamo_numero (prestamo_id, numero_cuota),
    CONSTRAINT fk_cuotas_prestamos FOREIGN KEY (prestamo_id) REFERENCES prestamos(id),
    INDEX idx_cuotas_estado (estado),
    INDEX idx_cuotas_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB;

CREATE TABLE pagos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prestamo_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    numero_recibo VARCHAR(30) NOT NULL UNIQUE,
    fecha_pago DATETIME NOT NULL,
    monto_recibido DECIMAL(12,2) NOT NULL,
    metodo_pago VARCHAR(30) NOT NULL,
    observacion VARCHAR(255) NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagos_prestamos FOREIGN KEY (prestamo_id) REFERENCES prestamos(id),
    CONSTRAINT fk_pagos_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_pagos_fecha (fecha_pago)
) ENGINE=InnoDB;

CREATE TABLE pago_detalle_cuota (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pago_id BIGINT UNSIGNED NOT NULL,
    cuota_id BIGINT UNSIGNED NOT NULL,
    monto_capital DECIMAL(12,2) NOT NULL DEFAULT 0,
    monto_interes DECIMAL(12,2) NOT NULL DEFAULT 0,
    monto_mora DECIMAL(12,2) NOT NULL DEFAULT 0,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pago_detalle_pagos FOREIGN KEY (pago_id) REFERENCES pagos(id),
    CONSTRAINT fk_pago_detalle_cuotas FOREIGN KEY (cuota_id) REFERENCES cuotas_prestamo(id)
) ENGINE=InnoDB;

CREATE TABLE movimientos_prestamo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prestamo_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    tipo_movimiento VARCHAR(40) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    metadata JSON NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movimientos_prestamos FOREIGN KEY (prestamo_id) REFERENCES prestamos(id),
    CONSTRAINT fk_movimientos_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE notificaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prestamo_id INT UNSIGNED NULL,
    cliente_id INT UNSIGNED NOT NULL,
    canal ENUM('email','sms') NOT NULL,
    destinatario VARCHAR(150) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_programada DATETIME NOT NULL,
    fecha_envio DATETIME NULL,
    estado ENUM('pendiente','enviado','error') NOT NULL DEFAULT 'pendiente',
    error_mensaje VARCHAR(255) NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificaciones_prestamos FOREIGN KEY (prestamo_id) REFERENCES prestamos(id),
    CONSTRAINT fk_notificaciones_clientes FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB;

CREATE TABLE auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    detalle VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditoria_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW vw_dashboard_cartera AS
SELECT
    COALESCE(SUM(monto_principal), 0) AS total_prestado,
    COALESCE(SUM(CASE WHEN estado = 'pagado' THEN total_interes + total_mora ELSE 0 END), 0) AS utilidad_historica,
    SUM(CASE WHEN estado = 'vigente' THEN 1 ELSE 0 END) AS prestamos_vigentes,
    SUM(CASE WHEN estado = 'vencido' THEN 1 ELSE 0 END) AS prestamos_vencidos,
    SUM(CASE WHEN estado = 'moroso' THEN 1 ELSE 0 END) AS prestamos_morosos
FROM prestamos;

DELIMITER $$

CREATE PROCEDURE sp_validar_cliente_para_prestamo(IN p_cliente_id INT)
BEGIN
    SELECT COUNT(*) AS prestamos_bloqueantes
    FROM prestamos
    WHERE cliente_id = p_cliente_id
      AND estado IN ('vigente', 'vencido', 'moroso');
END$$

CREATE PROCEDURE sp_aplicar_mora_prestamo(IN p_prestamo_id INT)
BEGIN
    UPDATE cuotas_prestamo
    SET mora_acumulada = ROUND(
            GREATEST(
                0,
                (capital_programado - capital_pagado + interes_programado - interes_pagado)
                * ((SELECT tasa_mora_diaria FROM prestamos WHERE id = p_prestamo_id) / 100)
                * GREATEST(DATEDIFF(CURDATE(), fecha_vencimiento), 0)
            ),
            2
        ),
        fecha_ultimo_calculo_mora = CURDATE(),
        estado = CASE
            WHEN saldo_cuota <= 0 THEN 'pagada'
            WHEN DATEDIFF(CURDATE(), fecha_vencimiento) > 0 AND mora_acumulada > 0 THEN 'morosa'
            WHEN DATEDIFF(CURDATE(), fecha_vencimiento) > 0 THEN 'vencida'
            ELSE estado
        END
    WHERE prestamo_id = p_prestamo_id
      AND estado <> 'pagada';
END$$

CREATE PROCEDURE sp_actualizar_estado_prestamo(IN p_prestamo_id INT)
BEGIN
    DECLARE v_saldo DECIMAL(12,2);
    DECLARE v_mora DECIMAL(12,2);
    DECLARE v_cuotas_mora INT;

    SELECT
        COALESCE(SUM(saldo_cuota), 0),
        COALESCE(SUM(mora_acumulada - mora_pagada), 0),
        SUM(CASE WHEN estado IN ('vencida', 'morosa') THEN 1 ELSE 0 END)
    INTO v_saldo, v_mora, v_cuotas_mora
    FROM cuotas_prestamo
    WHERE prestamo_id = p_prestamo_id;

    UPDATE prestamos
    SET saldo_pendiente = v_saldo,
        total_mora = v_mora,
        estado = CASE
            WHEN v_saldo <= 0 THEN 'pagado'
            WHEN v_cuotas_mora > 0 THEN 'moroso'
            ELSE 'vigente'
        END
    WHERE id = p_prestamo_id;
END$$

CREATE PROCEDURE sp_generar_recordatorios()
BEGIN
    INSERT INTO notificaciones
    (prestamo_id, cliente_id, canal, destinatario, asunto, mensaje, fecha_programada, estado, creado_en)
    SELECT
        p.id,
        c.id,
        'email',
        COALESCE(c.email, ''),
        'Recordatorio de cuota proxima',
        CONCAT('Estimado/a ', c.nombres, ', su cuota del prestamo ', p.numero_prestamo, ' vence el ', DATE_FORMAT(cp.fecha_vencimiento, '%d/%m/%Y'), '.'),
        NOW(),
        'pendiente',
        NOW()
    FROM cuotas_prestamo cp
    INNER JOIN prestamos p ON p.id = cp.prestamo_id
    INNER JOIN clientes c ON c.id = p.cliente_id
    WHERE cp.estado IN ('pendiente', 'parcial')
      AND DATEDIFF(cp.fecha_vencimiento, CURDATE()) = 3
      AND NOT EXISTS (
          SELECT 1
          FROM notificaciones n
          WHERE n.prestamo_id = p.id
            AND DATE(n.fecha_programada) = CURDATE()
            AND n.asunto = 'Recordatorio de cuota proxima'
      );
END$$

DELIMITER ;

INSERT INTO roles (nombre, descripcion) VALUES
('administrador total', 'Control total del sistema'),
('cobrador', 'Registro de cobros y seguimiento'),
('digitador', 'Registro operativo de clientes y prestamos');

INSERT INTO usuarios (rol_id, nombre_completo, usuario, email, password_hash, telefono, estado) VALUES
(1, 'Administrador General', 'admin', 'admin@sisprestamos.local', '$2y$10$YXI/AwRHfCkB0GwayulIv.fgiqZcEmMk4HnNtpdwXKyVyTgDNkZjC', '999000111', 'activo'),
(2, 'Carlos Cobranzas', 'cobrador', 'cobrador@sisprestamos.local', '$2y$10$YXI/AwRHfCkB0GwayulIv.fgiqZcEmMk4HnNtpdwXKyVyTgDNkZjC', '999222333', 'activo'),
(3, 'Diana Digitacion', 'digitador', 'digitador@sisprestamos.local', '$2y$10$YXI/AwRHfCkB0GwayulIv.fgiqZcEmMk4HnNtpdwXKyVyTgDNkZjC', '999444555', 'activo');

INSERT INTO productos_prestamo (nombre, frecuencia_pago, tasa_interes_tipo, tasa_interes_valor, tasa_mora_diaria, cuotas_maximas, estado) VALUES
('Prestamo mensual basico', 'mensual', 'mensual', 12.5000, 0.8000, 24, 1),
('Prestamo quincenal rapido', 'quincenal', 'mensual', 10.0000, 0.7000, 12, 1),
('Prestamo semanal comercial', 'semanal', 'mensual', 8.5000, 0.5000, 16, 1);

INSERT INTO clientes (codigo, foto, nombres, dni, telefono, email, direccion, nacionalidad, tipo_vivienda, situacion_laboral, estado_civil, direccion_trabajo, estado, creado_en) VALUES
('CLI-20260722001', NULL, 'Juan Carlos Perez Lopez', '12345678', '999111222', 'juan.perez@correo.com', 'Av. Principal 123, Lima', 'Peruana', 'Propia', 'Dependiente', 'Casado', 'Parque Industrial 45', 1, NOW()),
('CLI-20260722002', NULL, 'Maria Elena Ruiz Soto', '87654321', '988777666', 'maria.ruiz@correo.com', 'Jr. Libertad 456, Lima', 'Peruana', 'Alquilada', 'Independiente', 'Soltera', 'Mercado Central Stand 22', 1, NOW());

INSERT INTO garantes (cliente_id, foto, nombre_completo, dni, telefono, direccion) VALUES
(1, NULL, 'Ana Flores Castro', '45678912', '987123456', 'Mz. B Lt. 2, San Juan'),
(2, NULL, 'Pedro Gomez Diaz', '78912345', '976543210', 'Av. Sol 987, Callao');

INSERT INTO prestamos
(cliente_id, producto_id, usuario_id, numero_prestamo, monto_principal, tasa_interes_tipo, tasa_interes_valor, tasa_mora_diaria, plazo_cuotas, frecuencia_pago, fecha_otorgamiento, fecha_primer_pago, total_interes, total_mora, saldo_pendiente, estado, observaciones, creado_en)
VALUES
(1, 1, 1, 'PRE-20260722001', 5000.00, 'mensual', 12.5000, 0.8000, 6, 'mensual', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 749.98, 0.00, 5749.98, 'vigente', 'Prestamo de prueba para cartera inicial.', NOW());

INSERT INTO cuotas_prestamo
(prestamo_id, numero_cuota, fecha_vencimiento, capital_programado, interes_programado, capital_pagado, interes_pagado, mora_acumulada, mora_pagada, monto_programado, saldo_cuota, fecha_ultimo_calculo_mora, estado)
VALUES
(1, 1, DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 728.09, 625.00, 0.00, 0.00, 0.00, 0.00, 1353.09, 1353.09, NULL, 'pendiente'),
(1, 2, DATE_ADD(CURDATE(), INTERVAL 2 MONTH), 819.10, 533.99, 0.00, 0.00, 0.00, 0.00, 1353.09, 1353.09, NULL, 'pendiente'),
(1, 3, DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 921.49, 431.60, 0.00, 0.00, 0.00, 0.00, 1353.09, 1353.09, NULL, 'pendiente'),
(1, 4, DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 1036.67, 316.42, 0.00, 0.00, 0.00, 0.00, 1353.09, 1353.09, NULL, 'pendiente'),
(1, 5, DATE_ADD(CURDATE(), INTERVAL 5 MONTH), 1166.25, 186.84, 0.00, 0.00, 0.00, 0.00, 1353.09, 1353.09, NULL, 'pendiente'),
(1, 6, DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 328.40, 6.13, 0.00, 0.00, 0.00, 0.00, 334.53, 334.53, NULL, 'pendiente');

INSERT INTO movimientos_prestamo (prestamo_id, usuario_id, tipo_movimiento, descripcion, metadata, creado_en) VALUES
(1, 1, 'desembolso', 'Prestamo semilla generado durante la instalacion.', JSON_OBJECT('origen', 'seed'), NOW());
