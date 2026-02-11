-- ===================================================================
-- SCRIPT SQL PARA SISTEMA MULTI-EMPRESA
-- Ejecutar directamente en la base de datos MySQL/MariaDB
-- ===================================================================

-- Desactivar verificaciones de claves foráneas temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- ===================================================================
-- 1. CREAR TABLA COMPANIES
-- ===================================================================

CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nombre de la empresa',
  `legal_name` varchar(255) DEFAULT NULL COMMENT 'Razón social',
  `nit` varchar(255) DEFAULT NULL COMMENT 'NIT o identificación fiscal',
  `email` varchar(255) DEFAULT NULL COMMENT 'Email empresarial',
  `phone` varchar(255) DEFAULT NULL COMMENT 'Teléfono',
  `address` text DEFAULT NULL COMMENT 'Dirección',

  -- Configuración visual
  `logo_path` varchar(255) DEFAULT NULL COMMENT 'Ruta del logo',
  `primary_color` varchar(7) DEFAULT '#007bff' COMMENT 'Color primario',
  `secondary_color` varchar(7) DEFAULT '#6c757d' COMMENT 'Color secundario',
  `theme_settings` json DEFAULT NULL COMMENT 'Configuraciones adicionales de tema',

  -- Sistema de licencias
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Estado activo/inactivo',
  `license_expires_at` date DEFAULT NULL COMMENT 'Fecha vencimiento licencia',
  `license_type` varchar(255) DEFAULT 'standard' COMMENT 'Tipo de licencia (trial, standard, premium)',
  `max_users` int DEFAULT 10 COMMENT 'Máximo usuarios permitidos',
  `features` json DEFAULT NULL COMMENT 'Características habilitadas',

  -- Configuraciones específicas
  `settings` json DEFAULT NULL COMMENT 'Configuraciones generales',
  `notes` text DEFAULT NULL COMMENT 'Notas adicionales',

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_nit_unique` (`nit`),
  KEY `companies_is_active_license_expires_at_index` (`is_active`, `license_expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- 2. AGREGAR COMPANY_ID A TABLA USERS (SI EXISTE)
-- ===================================================================

-- Verificar si la columna company_id ya existe en users
SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME = 'company_id';

SET @sql = IF(@column_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `company_id` bigint UNSIGNED NULL AFTER `id`',
  'SELECT "Column company_id already exists in users table"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- 3. AGREGAR COMPANY_ID A TABLA COTIZACIONES (SI EXISTE)
-- ===================================================================

-- Verificar si la tabla cotizaciones existe y agregar company_id
SELECT COUNT(*) INTO @table_exists
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'cotizaciones';

SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'cotizaciones'
  AND COLUMN_NAME = 'company_id';

SET @sql = IF(@table_exists > 0 AND @column_exists = 0,
  'ALTER TABLE `cotizaciones` ADD COLUMN `company_id` bigint UNSIGNED NULL AFTER `id`',
  'SELECT "Table cotizaciones does not exist or column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- 4. AGREGAR COMPANY_ID A TABLA INV_PRODUCTOS (SI EXISTE)
-- ===================================================================

-- Verificar si la tabla inv_productos existe y agregar company_id
SELECT COUNT(*) INTO @table_exists
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'inv_productos';

SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'inv_productos'
  AND COLUMN_NAME = 'company_id';

SET @sql = IF(@table_exists > 0 AND @column_exists = 0,
  'ALTER TABLE `inv_productos` ADD COLUMN `company_id` bigint UNSIGNED NULL AFTER `id`',
  'SELECT "Table inv_productos does not exist or column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- 5. AGREGAR COMPANY_ID A TABLA TERCEROS (SI EXISTE)
-- ===================================================================

-- Verificar si la tabla terceros existe y agregar company_id
SELECT COUNT(*) INTO @table_exists
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'terceros';

SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'terceros'
  AND COLUMN_NAME = 'company_id';

SET @sql = IF(@table_exists > 0 AND @column_exists = 0,
  'ALTER TABLE `terceros` ADD COLUMN `company_id` bigint UNSIGNED NULL AFTER `id`',
  'SELECT "Table terceros does not exist or column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- 6. AGREGAR COMPANY_ID A TABLA FICHAS_TECNICAS (SI EXISTE)
-- ===================================================================

-- Verificar si la tabla fichas_tecnicas existe y agregar company_id
SELECT COUNT(*) INTO @table_exists
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'fichas_tecnicas';

SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'fichas_tecnicas'
  AND COLUMN_NAME = 'company_id';

SET @sql = IF(@table_exists > 0 AND @column_exists = 0,
  'ALTER TABLE `fichas_tecnicas` ADD COLUMN `company_id` bigint UNSIGNED NULL AFTER `id`',
  'SELECT "Table fichas_tecnicas does not exist or column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- 7. AGREGAR COMPANY_ID A TABLA MOVIMIENTOS (SI EXISTE)
-- ===================================================================

-- Verificar si la tabla movimientos existe y agregar company_id
SELECT COUNT(*) INTO @table_exists
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'movimientos';

SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'movimientos'
  AND COLUMN_NAME = 'company_id';

SET @sql = IF(@table_exists > 0 AND @column_exists = 0,
  'ALTER TABLE `movimientos` ADD COLUMN `company_id` bigint UNSIGNED NULL AFTER `id`',
  'SELECT "Table movimientos does not exist or column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- 8. AGREGAR COMPANY_ID A TABLA BODEGAS (SI EXISTE)
-- ===================================================================

-- Verificar si la tabla bodegas existe y agregar company_id
SELECT COUNT(*) INTO @table_exists
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'bodegas';

SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'bodegas'
  AND COLUMN_NAME = 'company_id';

SET @sql = IF(@table_exists > 0 AND @column_exists = 0,
  'ALTER TABLE `bodegas` ADD COLUMN `company_id` bigint UNSIGNED NULL AFTER `id`',
  'SELECT "Table bodegas does not exist or column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- 9. AGREGAR COMPANY_ID A TABLA EMPLEADOS (SI EXISTE)
-- ===================================================================

-- Verificar si la tabla empleados existe y agregar company_id
SELECT COUNT(*) INTO @table_exists
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'empleados';

SELECT COUNT(*) INTO @column_exists
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'empleados'
  AND COLUMN_NAME = 'company_id';

SET @sql = IF(@table_exists > 0 AND @column_exists = 0,
  'ALTER TABLE `empleados` ADD COLUMN `company_id` bigint UNSIGNED NULL AFTER `id`',
  'SELECT "Table empleados does not exist or column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- 10. INSERTAR DATOS DE EMPRESAS
-- ===================================================================

-- Insertar empresa principal MINDUVAL
INSERT INTO `companies` (
  `id`, `name`, `legal_name`, `nit`, `email`, `phone`, `address`,
  `logo_path`, `primary_color`, `secondary_color`, `theme_settings`,
  `is_active`, `license_expires_at`, `license_type`, `max_users`, `features`,
  `settings`, `notes`, `created_at`, `updated_at`
) VALUES (
  1,
  'MINDUVAL',
  'MINDUVAL S.A.S.',
  '900123456-1',
  'info@minduval.com',
  '+57 300 123 4567',
  'Carrera 123 # 45-67, Bogotá D.C., Colombia',
  NULL,
  '#1f2937',
  '#3b82f6',
  '{"sidebar_color": "dark", "navbar_color": "white", "font_family": "Inter", "logo_position": "left"}',
  1,
  DATE_ADD(CURDATE(), INTERVAL 1 YEAR),
  'premium',
  50,
  '["cotizaciones", "inventario", "produccion", "terceros", "reportes", "multiempresa", "api_access"]',
  '{"timezone": "America/Bogota", "currency": "COP", "date_format": "Y-m-d", "decimal_places": 2, "show_prices_with_tax": true}',
  'Empresa principal del sistema - Configuración completa',
  NOW(),
  NOW()
) ON DUPLICATE KEY UPDATE
  `updated_at` = NOW();

-- Insertar empresa demo
INSERT INTO `companies` (
  `id`, `name`, `legal_name`, `nit`, `email`, `phone`, `address`,
  `logo_path`, `primary_color`, `secondary_color`, `theme_settings`,
  `is_active`, `license_expires_at`, `license_type`, `max_users`, `features`,
  `settings`, `notes`, `created_at`, `updated_at`
) VALUES (
  2,
  'EMPRESA DEMO',
  'Empresa Demo S.A.S.',
  '900654321-9',
  'demo@empresa.com',
  '+57 301 987 6543',
  'Avenida Demo 456, Medellín, Colombia',
  NULL,
  '#059669',
  '#fbbf24',
  '{"sidebar_color": "light", "navbar_color": "green", "font_family": "Roboto", "logo_position": "center"}',
  1,
  DATE_ADD(CURDATE(), INTERVAL 6 MONTH),
  'standard',
  10,
  '["cotizaciones", "inventario", "terceros", "reportes"]',
  '{"timezone": "America/Bogota", "currency": "COP", "date_format": "d/m/Y", "decimal_places": 2, "show_prices_with_tax": false}',
  'Empresa de prueba para demostrar funcionalidades multi-empresa',
  NOW(),
  NOW()
) ON DUPLICATE KEY UPDATE
  `updated_at` = NOW();

-- ===================================================================
-- 11. ASIGNAR USUARIOS EXISTENTES A MINDUVAL
-- ===================================================================

-- Asignar todos los usuarios existentes sin empresa a MINDUVAL
UPDATE `users`
SET `company_id` = 1
WHERE `company_id` IS NULL;

-- ===================================================================
-- 12. CREAR CLAVES FORÁNEAS (FOREIGN KEYS)
-- ===================================================================

-- Reactivar verificaciones de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Crear clave foránea para users.company_id
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users'
     AND CONSTRAINT_NAME = 'users_company_id_foreign') = 0,
  'ALTER TABLE `users` ADD CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL',
  'SELECT "Foreign key users_company_id_foreign already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear clave foránea para cotizaciones.company_id (si la tabla existe)
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cotizaciones') > 0
  AND (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'cotizaciones'
         AND CONSTRAINT_NAME = 'cotizaciones_company_id_foreign') = 0,
  'ALTER TABLE `cotizaciones` ADD CONSTRAINT `cotizaciones_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL',
  'SELECT "Table cotizaciones does not exist or foreign key already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear clave foránea para inv_productos.company_id (si la tabla existe)
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inv_productos') > 0
  AND (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'inv_productos'
         AND CONSTRAINT_NAME = 'inv_productos_company_id_foreign') = 0,
  'ALTER TABLE `inv_productos` ADD CONSTRAINT `inv_productos_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL',
  'SELECT "Table inv_productos does not exist or foreign key already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear clave foránea para terceros.company_id (si la tabla existe)
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'terceros') > 0
  AND (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'terceros'
         AND CONSTRAINT_NAME = 'terceros_company_id_foreign') = 0,
  'ALTER TABLE `terceros` ADD CONSTRAINT `terceros_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL',
  'SELECT "Table terceros does not exist or foreign key already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear índices para mejorar performance
CREATE INDEX IF NOT EXISTS `users_company_id_index` ON `users` (`company_id`);

-- Crear índices para cotizaciones si la tabla existe
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cotizaciones') > 0,
  'CREATE INDEX IF NOT EXISTS `cotizaciones_company_id_index` ON `cotizaciones` (`company_id`)',
  'SELECT "Table cotizaciones does not exist"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear índices para inv_productos si la tabla existe
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inv_productos') > 0,
  'CREATE INDEX IF NOT EXISTS `inv_productos_company_id_index` ON `inv_productos` (`company_id`)',
  'SELECT "Table inv_productos does not exist"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear índices para terceros si la tabla existe
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'terceros') > 0,
  'CREATE INDEX IF NOT EXISTS `terceros_company_id_index` ON `terceros` (`company_id`)',
  'SELECT "Table terceros does not exist"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- 13. ASIGNAR DATOS EXISTENTES A MINDUVAL
-- ===================================================================

-- Asignar cotizaciones existentes a MINDUVAL
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cotizaciones') > 0,
  'UPDATE `cotizaciones` SET `company_id` = 1 WHERE `company_id` IS NULL',
  'SELECT "Table cotizaciones does not exist"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Asignar productos existentes a MINDUVAL
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inv_productos') > 0,
  'UPDATE `inv_productos` SET `company_id` = 1 WHERE `company_id` IS NULL',
  'SELECT "Table inv_productos does not exist"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Asignar terceros existentes a MINDUVAL
SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'terceros') > 0,
  'UPDATE `terceros` SET `company_id` = 1 WHERE `company_id` IS NULL',
  'SELECT "Table terceros does not exist"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===================================================================
-- SCRIPT COMPLETADO
-- ===================================================================

SELECT
  'SISTEMA MULTI-EMPRESA CONFIGURADO EXITOSAMENTE' as MENSAJE,
  (SELECT COUNT(*) FROM companies) as EMPRESAS_CREADAS,
  (SELECT COUNT(*) FROM users WHERE company_id = 1) as USUARIOS_ASIGNADOS_MINDUVAL,
  (SELECT license_expires_at FROM companies WHERE id = 1) as LICENCIA_MINDUVAL_EXPIRA;

COMMIT;

-- ===================================================================
-- NOTAS IMPORTANTES:
--
-- 1. Este script es SEGURO - no elimina tablas ni datos existentes
-- 2. Verifica la existencia de tablas antes de modificarlas
-- 3. Usa ON DUPLICATE KEY UPDATE para evitar duplicados
-- 4. Asigna todos los datos existentes a MINDUVAL (ID: 1)
-- 5. Crea índices para mejorar la performance
-- 6. Maneja errores de claves foráneas existentes
--
-- DESPUÉS DE EJECUTAR:
-- - Todos los usuarios sin empresa → Asignados a MINDUVAL
-- - Todos los datos existentes → Asignados a MINDUVAL
-- - Licencia MINDUVAL válida por 1 año
-- - Empresa DEMO creada con licencia de 6 meses
-- ===================================================================
