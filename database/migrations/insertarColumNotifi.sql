ALTER TABLE notificaciones
ADD COLUMN cuota_id INT UNSIGNED NULL AFTER prestamo_id;

ALTER TABLE notificaciones
MODIFY cuota_id BIGINT(20) UNSIGNED NOT NULL;

ALTER TABLE notificaciones
ADD CONSTRAINT fk_notificaciones_cuotas
FOREIGN KEY (cuota_id) REFERENCES cuotas_prestamo(id);