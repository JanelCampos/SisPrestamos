# Manual de Instalacion en XAMPP

## 1. Requisitos previos
- XAMPP con Apache y MySQL activos.
- PHP 7.4 o superior.
- MySQL 5.7 o superior.
- Composer instalado en el sistema para dependencias opcionales de PDF, correo y exportaciones.

## 2. Ubicacion del proyecto
- Copia la carpeta `SisPrestamos` dentro de `c:\xampp\htdocs\`.
- La URL base esperada es `http://localhost/SisPrestamos/public`.

## 3. Crear la base de datos
1. Abre `http://localhost/phpmyadmin`.
2. Ingresa a la pestaña `Importar`.
3. Selecciona el archivo `database/schema.sql`.
4. Ejecuta la importacion completa.

El archivo SQL crea:
- La base de datos `sisprestamos`.
- Tablas, relaciones, vista y procedimientos almacenados.
- Roles, usuarios iniciales, productos de prestamo y datos de prueba.

## 4. Configurar la aplicacion
Edita el archivo `app/Config.php` y verifica:
- `app.base_url`: debe coincidir con la URL donde publicas el sistema.
- `database.host`, `database.dbname`, `database.username`, `database.password`.
- `notifications.mail` y `notifications.sms` si quieres habilitar envios reales.

## 5. Instalar dependencias opcionales
Desde la carpeta del proyecto ejecuta:

```bash
composer install
```

Estas dependencias habilitan:
- Exportaciones y comprobantes PDF.
- Integracion de correo SMTP.
- Exportaciones enriquecidas a Excel.

Si no instalas Composer, el sistema seguira funcionando con exportacion CSV/HTML basica.

## 6. Permisos y carpetas
Verifica que Apache pueda escribir en:
- `storage/uploads/clientes`
- `storage/uploads/garantes`
- `storage/exports`
- `storage/logs`

## 7. Credenciales iniciales
- Usuario: `admin`
- Contrasena: `Admin123*`

Usuarios de prueba adicionales:
- `cobrador`
- `digitador`

Ambos usan la misma contrasena inicial `Admin123*`.

## 8. Acceso al sistema
Abre:

```text
http://localhost/SisPrestamos/public
```

## 9. Tareas programadas recomendadas
Para recordatorios y actualizacion de mora:
- Programa la ejecucion diaria de los procedimientos `sp_generar_recordatorios` y `sp_aplicar_mora_prestamo`.
- Puedes hacerlo desde el Programador de tareas de Windows o un script PHP/SQL externo.

## 10. Verificaciones finales
- Inicia sesion con el usuario administrador.
- Revisa el dashboard y valida que existan KPIs y graficos.
- Ingresa al modulo de clientes y verifica la apertura de Google Maps desde la direccion.
- Registra un prestamo nuevo y valida la simulacion del cronograma.
- Registra un pago para comprobar el recalculo de saldo y estado.
- Exporta reportes en Excel y PDF.
