CREATE TABLE solicitudes_cobro (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    prestamo_id INT UNSIGNED NOT NULL,
    usuario_solicitante_id INT UNSIGNED NOT NULL,

    monto_recibido DECIMAL(12,2) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    fecha_pago DATETIME NOT NULL,
    observacion TEXT NULL,

    estado ENUM(
        'pendiente',
        'aprobada',
        'rechazada',
        'utilizada',
        'cancelada'
    ) NOT NULL DEFAULT 'pendiente',

    usuario_aprobador_id INT UNSIGNED NULL,

    aprobado_en DATETIME NULL,
    rechazado_en DATETIME NULL,
    utilizado_en DATETIME NULL,

    motivo_rechazo TEXT NULL,

    pago_id BIGINT UNSIGNED NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_solicitud_prestamo (prestamo_id),
    INDEX idx_solicitud_solicitante (usuario_solicitante_id),
    INDEX idx_solicitud_aprobador (usuario_aprobador_id),
    INDEX idx_solicitud_estado (estado),
    INDEX idx_solicitud_pago (pago_id),

    CONSTRAINT fk_solicitud_prestamo
        FOREIGN KEY (prestamo_id)
        REFERENCES prestamos(id),

    CONSTRAINT fk_solicitud_solicitante
        FOREIGN KEY (usuario_solicitante_id)
        REFERENCES usuarios(id),

    CONSTRAINT fk_solicitud_aprobador
        FOREIGN KEY (usuario_aprobador_id)
        REFERENCES usuarios(id),

    CONSTRAINT fk_solicitud_pago
        FOREIGN KEY (pago_id)
        REFERENCES pagos(id)
);