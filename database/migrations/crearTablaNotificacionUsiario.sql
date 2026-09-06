CREATE TABLE notificaciones_usuario (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    prestamo_id INT UNSIGNED NULL,
    solicitud_cobro_id INT UNSIGNED NULL,
    leida TINYINT(1) NOT NULL DEFAULT 0,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    leida_en DATETIME NULL,

    PRIMARY KEY (id),

    INDEX idx_notificaciones_usuario (
        usuario_id,
        leida,
        creado_en
    ),

    INDEX idx_notificaciones_prestamo (
        prestamo_id
    ),

    INDEX idx_notificaciones_solicitud (
        solicitud_cobro_id
    ),

    CONSTRAINT fk_notificaciones_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notificaciones_prestamo
        FOREIGN KEY (prestamo_id)
        REFERENCES prestamos(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_notificaciones_solicitud
        FOREIGN KEY (solicitud_cobro_id)
        REFERENCES solicitudes_cobro(id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;