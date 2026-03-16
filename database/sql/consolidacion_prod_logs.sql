-- ============================================================
-- CONSOLIDACIÓN:  prod_production_logs  →  production_operations
-- Ejecutar en orden: Paso 1 → 2 → 3 → 4
-- ============================================================

-- ============================================================
-- PASO 1 · DDL — Agregar columnas nuevas a production_operations
-- ============================================================
ALTER TABLE production_operations
    ADD COLUMN work_date  DATE           NULL           AFTER registered_at,
    ADD COLUMN shift      VARCHAR(10)    NULL           AFTER work_date,
    ADD COLUMN rejected_qty DECIMAL(12,4) NOT NULL DEFAULT 0 AFTER quantity;


-- ============================================================
-- PASO 2 · DML — Migrar datos históricos de prod_production_logs
-- Para cada fila de prod_production_logs se inserta una nueva
-- fila en production_operations con los campos mapeados.
-- ============================================================
INSERT INTO production_operations
    (company_id, production_order_id, activity_id, order_operation_id,
     employee_id, quantity, rejected_qty, work_date, shift,
     notes, created_by, registered_at, created_at, updated_at)
SELECT
    l.company_id,
    l.order_id                                           AS production_order_id,
    oo.activity_id                                       AS activity_id,
    l.order_operation_id,
    l.employee_id,
    l.qty                                                AS quantity,
    COALESCE(l.rejected_qty, 0)                          AS rejected_qty,
    l.work_date,
    l.shift,
    l.notes,
    l.created_by,
    COALESCE(l.created_at, NOW())                        AS registered_at,
    l.created_at,
    l.updated_at
FROM prod_production_logs l
JOIN production_order_activities oo ON oo.id = l.order_operation_id;


-- ============================================================
-- PASO 3 · VERIFICACIÓN — Comparar totales
-- Ejecutar y verificar que ambos totales coincidan antes de
-- continuar con el Paso 4.
-- ============================================================
SELECT 'prod_production_logs' AS tabla,
       COUNT(*) AS filas,
       ROUND(SUM(qty),2) AS sum_qty,
       ROUND(SUM(rejected_qty),2) AS sum_rejected
FROM prod_production_logs

UNION ALL

-- Solo filas migradas (las que tienen employee_id, sin workshop_operator_id)
SELECT 'production_operations (migradas)' AS tabla,
       COUNT(*) AS filas,
       ROUND(SUM(quantity),2) AS sum_qty,
       ROUND(SUM(rejected_qty),2) AS sum_rejected
FROM production_operations
WHERE employee_id IS NOT NULL
  AND workshop_operator_id IS NULL
  AND work_date IS NOT NULL;


-- ============================================================
-- PASO 4 · CLEANUP — Renombrar tabla legacy (solo si Paso 3 OK)
-- ============================================================
RENAME TABLE prod_production_logs TO prod_production_logs_bak;
