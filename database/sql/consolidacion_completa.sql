-- ============================================================================
-- SCRIPT DE CONSOLIDACIÓN COMPLETA - Módulo Producción
-- Base de datos: MySQL 5.7+ / 8.x
-- Fecha: 2026-03-13
--
-- ORDEN DE EJECUCIÓN:
--   PASO 1: Alteraciones estructurales (DDL)
--   PASO 2: Migración de datos (DML)
--   PASO 3: Puente de operarios
--   PASO 4: Limpieza (renombrar tablas legacy)
--
-- ⚠ IMPORTANTE:
--   - Hacer BACKUP COMPLETO de la BD antes de ejecutar
--   - Ejecutar en orden, paso a paso
--   - Si algo falla, corregir antes de continuar
--   - Las tablas legacy se renombran a *_bak (no se borran)
-- ============================================================================

-- ============================================================================
-- PASO 1: ALTERACIONES ESTRUCTURALES (DDL)
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- Fase 1: Ampliar activities para recibir datos de prod_operations
-- ────────────────────────────────────────────────────────────────────────────

ALTER TABLE `activities`
  MODIFY COLUMN `code` VARCHAR(50) NOT NULL,
  ADD COLUMN `legacy_prod_operation_id` BIGINT UNSIGNED NULL AFTER `is_active`;

-- ────────────────────────────────────────────────────────────────────────────
-- Fase 2: Ampliar production_orders para recibir datos de prod_orders
-- ────────────────────────────────────────────────────────────────────────────

-- Agregar columnas legacy
ALTER TABLE `production_orders`
  ADD COLUMN `company_id` BIGINT UNSIGNED NULL AFTER `id`,
  ADD COLUMN `product_id` BIGINT UNSIGNED NULL AFTER `company_id`,
  ADD COLUMN `notes` VARCHAR(255) NULL AFTER `cost_per_unit`,
  ADD COLUMN `created_by` BIGINT UNSIGNED NULL AFTER `notes`,
  ADD COLUMN `legacy_prod_order_id` BIGINT UNSIGNED NULL AFTER `deleted_at`;

-- FK company_id → companies
ALTER TABLE `production_orders`
  ADD CONSTRAINT `production_orders_company_id_foreign`
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL;

-- Reemplazar unique de order_code por unique compuesto (company_id + order_code)
ALTER TABLE `production_orders`
  DROP INDEX `production_orders_order_code_unique`;

ALTER TABLE `production_orders`
  ADD UNIQUE INDEX `production_orders_company_id_order_code_unique` (`company_id`, `order_code`);

-- Cambiar status de ENUM a VARCHAR(20)
ALTER TABLE `production_orders`
  MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'pending';

-- ────────────────────────────────────────────────────────────────────────────
-- Fase 3: Ampliar production_order_activities para recibir datos de prod_order_operations
-- ────────────────────────────────────────────────────────────────────────────

ALTER TABLE `production_order_activities`
  ADD COLUMN `qty_per_unit` DECIMAL(12,4) NULL AFTER `position`,
  ADD COLUMN `required_qty` DECIMAL(12,4) NULL AFTER `qty_per_unit`,
  ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'PENDING' AFTER `required_qty`,
  ADD COLUMN `legacy_prod_order_operation_id` BIGINT UNSIGNED NULL AFTER `updated_at`;

-- ────────────────────────────────────────────────────────────────────────────
-- Fase 4: Ampliar production_operations para recibir datos de prod_worker_logs
-- ────────────────────────────────────────────────────────────────────────────

-- Quitar FK originales (para poder hacer nullable)
ALTER TABLE `production_operations`
  DROP FOREIGN KEY `production_operations_workshop_id_foreign`,
  DROP FOREIGN KEY `production_operations_workshop_operator_id_foreign`,
  DROP FOREIGN KEY `production_operations_user_id_foreign`;

-- Hacer columnas nullable + quantity a DECIMAL
ALTER TABLE `production_operations`
  MODIFY COLUMN `workshop_id` BIGINT UNSIGNED NULL,
  MODIFY COLUMN `workshop_operator_id` BIGINT UNSIGNED NULL,
  MODIFY COLUMN `user_id` BIGINT UNSIGNED NULL,
  MODIFY COLUMN `quantity` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT 0;

-- Re-agregar FK (ahora permiten NULL, con SET NULL on delete)
ALTER TABLE `production_operations`
  ADD CONSTRAINT `production_operations_workshop_id_foreign`
    FOREIGN KEY (`workshop_id`) REFERENCES `workshops`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `production_operations_workshop_operator_id_foreign`
    FOREIGN KEY (`workshop_operator_id`) REFERENCES `workshop_operators`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `production_operations_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- Agregar columnas legacy
ALTER TABLE `production_operations`
  ADD COLUMN `company_id` BIGINT UNSIGNED NULL AFTER `id`,
  ADD COLUMN `employee_id` BIGINT UNSIGNED NULL AFTER `workshop_operator_id`,
  ADD COLUMN `order_operation_id` BIGINT UNSIGNED NULL AFTER `activity_id`,
  ADD COLUMN `notes` VARCHAR(255) NULL AFTER `idempotency_key`,
  ADD COLUMN `created_by` BIGINT UNSIGNED NULL AFTER `notes`,
  ADD COLUMN `legacy_prod_worker_log_id` BIGINT UNSIGNED NULL AFTER `updated_at`;

-- Índice compuesto para queries legacy
ALTER TABLE `production_operations`
  ADD INDEX `po_company_order_oo_idx` (`company_id`, `production_order_id`, `order_operation_id`);

-- ────────────────────────────────────────────────────────────────────────────
-- Fase 6: Puente operarios - employee_id en workshop_operators
-- ────────────────────────────────────────────────────────────────────────────

ALTER TABLE `workshop_operators`
  ADD COLUMN `employee_id` BIGINT UNSIGNED NULL AFTER `is_active`;

ALTER TABLE `workshop_operators`
  ADD CONSTRAINT `workshop_operators_employee_id_foreign`
    FOREIGN KEY (`employee_id`) REFERENCES `empleados`(`id`) ON DELETE SET NULL;

ALTER TABLE `workshop_operators`
  ADD UNIQUE INDEX `wo_workshop_employee_unique` (`workshop_id`, `employee_id`);


-- ============================================================================
-- PASO 2: MIGRACIÓN DE DATOS (DML)
-- Ejecutar DESPUÉS de que todas las alteraciones DDL del PASO 1 hayan pasado
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- Fase 1 DATA: prod_operations → activities
-- ────────────────────────────────────────────────────────────────────────────

-- Insertar operaciones legacy que NO existen en activities (por company_id + code)
INSERT INTO `activities` (
  `company_id`, `code`, `name`, `description`, `unit_price`,
  `is_active`, `legacy_prod_operation_id`, `created_at`, `updated_at`
)
SELECT
  po.`company_id`,
  po.`code`,
  po.`name`,
  po.`description`,
  0,
  po.`is_active`,
  po.`id`,
  COALESCE(po.`created_at`, NOW()),
  NOW()
FROM `prod_operations` po
WHERE NOT EXISTS (
  SELECT 1 FROM `activities` a
  WHERE a.`company_id` = po.`company_id` AND a.`code` = po.`code`
);

-- Marcar legacy_id en activities que ya existían con mismo company_id + code
UPDATE `activities` a
INNER JOIN `prod_operations` po
  ON a.`company_id` = po.`company_id` AND a.`code` = po.`code`
SET a.`legacy_prod_operation_id` = po.`id`
WHERE a.`legacy_prod_operation_id` IS NULL;

-- Re-apuntar prod_order_operations.operation_id al nuevo ID en activities
UPDATE `prod_order_operations` poo
INNER JOIN `activities` a ON a.`legacy_prod_operation_id` = poo.`operation_id`
SET poo.`operation_id` = a.`id`
WHERE a.`legacy_prod_operation_id` IS NOT NULL
  AND a.`id` != poo.`operation_id`;

-- Re-apuntar prod_operation_product_rates.operation_id al nuevo ID en activities
UPDATE `prod_operation_product_rates` r
INNER JOIN `activities` a ON a.`legacy_prod_operation_id` = r.`operation_id`
SET r.`operation_id` = a.`id`
WHERE a.`legacy_prod_operation_id` IS NOT NULL
  AND a.`id` != r.`operation_id`;

-- ────────────────────────────────────────────────────────────────────────────
-- Fase 2 DATA: prod_orders → production_orders
-- ────────────────────────────────────────────────────────────────────────────

-- Insertar cada orden legacy como nuevo registro en production_orders
INSERT INTO `production_orders` (
  `company_id`, `product_id`, `order_code`, `garment_type`,
  `total_units`, `completed_units`, `cost_per_unit`,
  `status`, `start_date`, `deadline`,
  `notes`, `created_by`, `legacy_prod_order_id`,
  `created_at`, `updated_at`
)
SELECT
  po.`company_id`,
  po.`product_id`,
  po.`code`,
  po.`code`,             -- garment_type placeholder (NOT NULL)
  COALESCE(po.`objective_qty`, 0),
  0,
  0,
  COALESCE(po.`status`, 'DRAFT'),
  COALESCE(po.`start_date`, CURDATE()),
  po.`end_date`,
  po.`notes`,
  po.`created_by`,
  po.`id`,
  po.`created_at`,
  po.`updated_at`
FROM `prod_orders` po;

-- Re-apuntar prod_order_operations.order_id
UPDATE `prod_order_operations` poo
INNER JOIN `production_orders` o ON o.`legacy_prod_order_id` = poo.`order_id`
SET poo.`order_id` = o.`id`
WHERE o.`legacy_prod_order_id` IS NOT NULL;

-- Re-apuntar prod_worker_logs.order_id
UPDATE `prod_worker_logs` wl
INNER JOIN `production_orders` o ON o.`legacy_prod_order_id` = wl.`order_id`
SET wl.`order_id` = o.`id`
WHERE o.`legacy_prod_order_id` IS NOT NULL;

-- Re-apuntar prod_production_logs.order_id
UPDATE `prod_production_logs` pl
INNER JOIN `production_orders` o ON o.`legacy_prod_order_id` = pl.`order_id`
SET pl.`order_id` = o.`id`
WHERE o.`legacy_prod_order_id` IS NOT NULL;

-- Re-apuntar prod_worker_settlements.order_id
UPDATE `prod_worker_settlements` ws
INNER JOIN `production_orders` o ON o.`legacy_prod_order_id` = ws.`order_id`
SET ws.`order_id` = o.`id`
WHERE o.`legacy_prod_order_id` IS NOT NULL;

-- ────────────────────────────────────────────────────────────────────────────
-- Fase 3 DATA: prod_order_operations → production_order_activities
-- ────────────────────────────────────────────────────────────────────────────

INSERT INTO `production_order_activities` (
  `production_order_id`, `activity_id`, `position`,
  `qty_per_unit`, `required_qty`, `status`,
  `legacy_prod_order_operation_id`,
  `created_at`, `updated_at`
)
SELECT
  poo.`order_id`,         -- ya re-apuntado a production_orders.id
  poo.`operation_id`,     -- ya re-apuntado a activities.id
  COALESCE(poo.`seq`, 0),
  poo.`qty_per_unit`,
  poo.`required_qty`,
  COALESCE(poo.`status`, 'PENDING'),
  poo.`id`,
  poo.`created_at`,
  poo.`updated_at`
FROM `prod_order_operations` poo;

-- Re-apuntar prod_worker_logs.order_operation_id
UPDATE `prod_worker_logs` wl
INNER JOIN `production_order_activities` poa
  ON poa.`legacy_prod_order_operation_id` = wl.`order_operation_id`
SET wl.`order_operation_id` = poa.`id`
WHERE poa.`legacy_prod_order_operation_id` IS NOT NULL;

-- Re-apuntar prod_production_logs.order_operation_id
UPDATE `prod_production_logs` pl
INNER JOIN `production_order_activities` poa
  ON poa.`legacy_prod_order_operation_id` = pl.`order_operation_id`
SET pl.`order_operation_id` = poa.`id`
WHERE poa.`legacy_prod_order_operation_id` IS NOT NULL;

-- Re-apuntar prod_worker_settlements.order_operation_id
UPDATE `prod_worker_settlements` ws
INNER JOIN `production_order_activities` poa
  ON poa.`legacy_prod_order_operation_id` = ws.`order_operation_id`
SET ws.`order_operation_id` = poa.`id`
WHERE poa.`legacy_prod_order_operation_id` IS NOT NULL;

-- ────────────────────────────────────────────────────────────────────────────
-- Fase 4 DATA: prod_worker_logs → production_operations
-- ────────────────────────────────────────────────────────────────────────────

INSERT INTO `production_operations` (
  `company_id`, `production_order_id`, `activity_id`,
  `order_operation_id`, `employee_id`, `quantity`,
  `registered_at`, `notes`, `created_by`,
  `legacy_prod_worker_log_id`,
  `workshop_id`, `workshop_operator_id`, `user_id`, `idempotency_key`,
  `created_at`, `updated_at`
)
SELECT
  wl.`company_id`,
  wl.`order_id`,              -- ya re-apuntado a production_orders.id
  wl.`operation_id`,          -- ya re-apuntado a activities.id
  wl.`order_operation_id`,    -- ya re-apuntado a production_order_activities.id
  wl.`employee_id`,
  COALESCE(wl.`qty`, 0),
  COALESCE(wl.`worked_at`, NOW()),
  wl.`notes`,
  wl.`created_by`,
  wl.`id`,
  NULL,   -- workshop_id
  NULL,   -- workshop_operator_id
  NULL,   -- user_id
  NULL,   -- idempotency_key
  wl.`created_at`,
  wl.`updated_at`
FROM `prod_worker_logs` wl;


-- ============================================================================
-- PASO 3: VERIFICACIÓN (ejecutar y revisar resultados)
-- ============================================================================

-- Verificar conteos de migración
SELECT 'activities (legacy migradas)' as tabla,
       COUNT(*) as registros
FROM `activities` WHERE `legacy_prod_operation_id` IS NOT NULL
UNION ALL
SELECT 'production_orders (legacy migradas)',
       COUNT(*)
FROM `production_orders` WHERE `legacy_prod_order_id` IS NOT NULL
UNION ALL
SELECT 'production_order_activities (legacy migradas)',
       COUNT(*)
FROM `production_order_activities` WHERE `legacy_prod_order_operation_id` IS NOT NULL
UNION ALL
SELECT 'production_operations (legacy migradas)',
       COUNT(*)
FROM `production_operations` WHERE `legacy_prod_worker_log_id` IS NOT NULL;

-- Comparar con las tablas originales
SELECT 'prod_operations (original)' as tabla, COUNT(*) as registros FROM `prod_operations`
UNION ALL
SELECT 'prod_orders (original)', COUNT(*) FROM `prod_orders`
UNION ALL
SELECT 'prod_order_operations (original)', COUNT(*) FROM `prod_order_operations`
UNION ALL
SELECT 'prod_worker_logs (original)', COUNT(*) FROM `prod_worker_logs`;


-- ============================================================================
-- PASO 4: LIMPIEZA - Renombrar tablas legacy a _bak
-- ⚠ EJECUTAR SOLO DESPUÉS DE VERIFICAR que los conteos del PASO 3 coinciden
-- ============================================================================

RENAME TABLE
  `prod_operations`       TO `prod_operations_bak`,
  `prod_orders`           TO `prod_orders_bak`,
  `prod_order_operations` TO `prod_order_operations_bak`,
  `prod_worker_logs`      TO `prod_worker_logs_bak`;


-- ============================================================================
-- LIMPIEZA FINAL (ejecutar después de verificar que TODO funciona en producción)
-- ============================================================================
-- DROP TABLE IF EXISTS `prod_operations_bak`;
-- DROP TABLE IF EXISTS `prod_orders_bak`;
-- DROP TABLE IF EXISTS `prod_order_operations_bak`;
-- DROP TABLE IF EXISTS `prod_worker_logs_bak`;
