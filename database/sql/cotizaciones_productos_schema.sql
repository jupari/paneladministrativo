-- =============================================
-- Scripts SQL para Sistema de Cotizaciones - Módulo de Productos
-- Fecha: 2026-01-20
-- Descripción: Tablas necesarias para manejar productos en cotizaciones
-- =============================================

-- =============================================
-- 1. Tabla: estados_cotizacion
-- Descripción: Estados del workflow de cotizaciones
-- =============================================
CREATE TABLE IF NOT EXISTS `estados_cotizacion` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `estado` varchar(50) NOT NULL UNIQUE,
    `descripcion` varchar(255) NULL,
    `color` varchar(20) NOT NULL DEFAULT '#6c757d',
    `active` tinyint(1) NOT NULL DEFAULT '1',
    `orden` int(11) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `estados_cotizacion_estado_unique` (`estado`),
    KEY `estados_cotizacion_active_orden` (`active`, `orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar estados por defecto
INSERT IGNORE INTO `estados_cotizacion` (`estado`, `descripcion`, `color`, `orden`, `created_at`, `updated_at`) VALUES
('Borrador', 'Cotización en proceso de elaboración', '#6c757d', 1, NOW(), NOW()),
('En Proceso', 'Cotización siendo revisada internamente', '#ffc107', 2, NOW(), NOW()),
('Enviado', 'Cotización enviada al cliente', '#17a2b8', 3, NOW(), NOW()),
('Aprobado', 'Cotización aprobada por el cliente', '#28a745', 4, NOW(), NOW()),
('Rechazado', 'Cotización rechazada por el cliente', '#dc3545', 5, NOW(), NOW()),
('Vencido', 'Cotización vencida por tiempo', '#6f42c1', 6, NOW(), NOW()),
('Terminado', 'Cotización finalizada y convertida', '#20c997', 7, NOW(), NOW());

-- =============================================
-- 2. Tabla: ord_cotizacion
-- Descripción: Tabla principal de cotizaciones
-- =============================================
CREATE TABLE IF NOT EXISTS `ord_cotizacion` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `num_documento` varchar(50) NOT NULL UNIQUE,
    `fecha` date NOT NULL,
    `tipo` varchar(20) NOT NULL DEFAULT 'Standard',
    `proyecto` varchar(255) NULL,
    `autorizacion_id` bigint(20) unsigned NULL,
    `doc_origen` varchar(100) NULL,
    `version` int(11) NOT NULL DEFAULT '1',
    `tercero_id` bigint(20) unsigned NOT NULL,
    `tercero_sucursal_id` bigint(20) unsigned NULL,
    `tercero_contacto_id` bigint(20) unsigned NULL,
    `observacion` text NULL,
    `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
    `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
    `total` decimal(12,2) NOT NULL DEFAULT '0.00',
    `total_impuesto` decimal(12,2) NOT NULL DEFAULT '0.00',
    `estado_id` bigint(20) unsigned NOT NULL DEFAULT '1',
    `user_id` bigint(20) unsigned NOT NULL,
    `vendedor_id` bigint(20) unsigned NULL,
    `fecha_vencimiento` date NULL,
    `fecha_envio` datetime NULL,
    `fecha_respuesta` datetime NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ord_cotizacion_num_documento_unique` (`num_documento`),
    KEY `ord_cotizacion_estado_fecha` (`estado_id`, `fecha`),
    KEY `ord_cotizacion_tercero_fecha` (`tercero_id`, `fecha`),
    KEY `ord_cotizacion_vendedor_fecha` (`vendedor_id`, `fecha`),
    KEY `ord_cotizacion_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. Tabla: ord_cotizacion_productos
-- Descripción: Productos asociados a cotizaciones
-- =============================================
CREATE TABLE IF NOT EXISTS `ord_cotizacion_productos` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `cotizacion_id` bigint(20) unsigned NOT NULL,
    `producto_id` bigint(20) unsigned NULL,
    `nombre` varchar(255) NOT NULL,
    `descripcion` text NULL,
    `codigo` varchar(50) NULL,
    `unidad_medida` varchar(20) NOT NULL DEFAULT 'UND',
    `cantidad` decimal(10,3) NOT NULL DEFAULT '1.000',
    `valor_unitario` decimal(12,2) NOT NULL DEFAULT '0.00',
    `descuento_porcentaje` decimal(5,2) NOT NULL DEFAULT '0.00',
    `descuento_valor` decimal(12,2) NOT NULL DEFAULT '0.00',
    `valor_total` decimal(12,2) NOT NULL DEFAULT '0.00',
    `observaciones` text NULL,
    `orden` int(11) NOT NULL DEFAULT '1',
    `active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ord_cotizacion_productos_cotizacion_orden` (`cotizacion_id`, `orden`),
    KEY `ord_cotizacion_productos_cotizacion_active` (`cotizacion_id`, `active`),
    KEY `ord_cotizacion_productos_producto_id` (`producto_id`),
    CONSTRAINT `ord_cotizacion_productos_cotizacion_id_foreign`
        FOREIGN KEY (`cotizacion_id`) REFERENCES `ord_cotizacion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. Tabla: ord_cotizaciones_conceptos
-- Descripción: Conceptos adicionales (impuestos, descuentos, etc.)
-- =============================================
CREATE TABLE IF NOT EXISTS `ord_cotizaciones_conceptos` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `cotizacion_id` bigint(20) unsigned NOT NULL,
    `concepto_id` bigint(20) unsigned NULL,
    `nombre` varchar(255) NOT NULL,
    `tipo` enum('PORCENTAJE', 'VALOR_FIJO', 'IMPUESTO') NOT NULL DEFAULT 'PORCENTAJE',
    `porcentaje` decimal(5,2) NOT NULL DEFAULT '0.00',
    `valor` decimal(12,2) NOT NULL DEFAULT '0.00',
    `base_calculo` decimal(12,2) NOT NULL DEFAULT '0.00',
    `activo` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ord_cotizaciones_conceptos_cotizacion_id` (`cotizacion_id`),
    KEY `ord_cotizaciones_conceptos_concepto_id` (`concepto_id`),
    CONSTRAINT `ord_cotizaciones_conceptos_cotizacion_id_foreign`
        FOREIGN KEY (`cotizacion_id`) REFERENCES `ord_cotizacion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. Tabla: ord_cotizacion_sub_items
-- Descripción: Sub-items detallados de productos
-- =============================================
CREATE TABLE IF NOT EXISTS `ord_cotizacion_sub_items` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `cotizacion_item_id` bigint(20) unsigned NOT NULL,
    `nombre` varchar(255) NOT NULL,
    `descripcion` text NULL,
    `cantidad` decimal(10,3) NOT NULL DEFAULT '1.000',
    `unidad_medida` varchar(20) NOT NULL DEFAULT 'UND',
    `valor_unitario` decimal(12,2) NOT NULL DEFAULT '0.00',
    `valor_total` decimal(12,2) NOT NULL DEFAULT '0.00',
    `orden` int(11) NOT NULL DEFAULT '1',
    `active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ord_cotizacion_sub_items_item_orden` (`cotizacion_item_id`, `orden`),
    KEY `ord_cotizacion_sub_items_item_active` (`cotizacion_item_id`, `active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 6. Tabla: ord_cotizacion_observaciones
-- Descripción: Observaciones de cotizaciones
-- =============================================
CREATE TABLE IF NOT EXISTS `ord_cotizacion_observaciones` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `cotizacion_id` bigint(20) unsigned NOT NULL,
    `tipo` enum('GENERAL', 'TECNICA', 'COMERCIAL', 'LEGAL') NOT NULL DEFAULT 'GENERAL',
    `titulo` varchar(255) NULL,
    `observacion` text NOT NULL,
    `orden` int(11) NOT NULL DEFAULT '1',
    `active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ord_cotizacion_observaciones_cotizacion_orden` (`cotizacion_id`, `orden`),
    CONSTRAINT `ord_cotizacion_observaciones_cotizacion_id_foreign`
        FOREIGN KEY (`cotizacion_id`) REFERENCES `ord_cotizacion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 7. Tabla: ord_cotizacion_condiciones_comerciales
-- Descripción: Condiciones comerciales de cotizaciones
-- =============================================
CREATE TABLE IF NOT EXISTS `ord_cotizacion_condiciones_comerciales` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `cotizacion_id` bigint(20) unsigned NOT NULL,
    `tipo` enum('ENTREGA', 'PAGO', 'GARANTIA', 'VALIDEZ', 'OTRAS') NOT NULL DEFAULT 'OTRAS',
    `titulo` varchar(255) NOT NULL,
    `descripcion` text NOT NULL,
    `orden` int(11) NOT NULL DEFAULT '1',
    `active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ord_cotizacion_condiciones_cotizacion_orden` (`cotizacion_id`, `orden`),
    CONSTRAINT `ord_cotizacion_condiciones_cotizacion_id_foreign`
        FOREIGN KEY (`cotizacion_id`) REFERENCES `ord_cotizacion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 8. Tabla base de productos (si no existe)
-- Descripción: Catálogo de productos disponibles
-- =============================================
CREATE TABLE IF NOT EXISTS `inv_productos` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `tipo_producto` varchar(50) NOT NULL DEFAULT 'PRODUCTO',
    `codigo` varchar(50) NULL UNIQUE,
    `nombre` varchar(255) NOT NULL,
    `descripcion` text NULL,
    `unidad_medida` varchar(20) NOT NULL DEFAULT 'UND',
    `stock_minimo` decimal(10,3) NOT NULL DEFAULT '0.000',
    `marca` varchar(100) NULL,
    `categoria` varchar(100) NULL,
    `subcategoria` varchar(100) NULL,
    `precio` decimal(12,2) NOT NULL DEFAULT '0.00',
    `active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `inv_productos_active_categoria` (`active`, `categoria`),
    KEY `inv_productos_codigo` (`codigo`),
    KEY `inv_productos_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ÍNDICES ADICIONALES Y OPTIMIZACIONES
-- =============================================

-- Índices para mejorar rendimiento en consultas frecuentes
CREATE INDEX IF NOT EXISTS `idx_cotizacion_fecha_estado` ON `ord_cotizacion` (`fecha`, `estado_id`);
CREATE INDEX IF NOT EXISTS `idx_cotizacion_tercero_estado` ON `ord_cotizacion` (`tercero_id`, `estado_id`);
CREATE INDEX IF NOT EXISTS `idx_cotizacion_productos_valores` ON `ord_cotizacion_productos` (`cotizacion_id`, `active`, `valor_total`);

-- =============================================
-- TRIGGERS PARA CÁLCULOS AUTOMÁTICOS
-- =============================================

-- Trigger para calcular valor_total automáticamente en productos
DELIMITER $$

DROP TRIGGER IF EXISTS `tr_cotizacion_productos_calculate_total`$$

CREATE TRIGGER `tr_cotizacion_productos_calculate_total`
    BEFORE INSERT ON `ord_cotizacion_productos`
    FOR EACH ROW
BEGIN
    DECLARE subtotal DECIMAL(12,2);
    DECLARE descuento DECIMAL(12,2);

    SET subtotal = NEW.cantidad * NEW.valor_unitario;
    SET descuento = NEW.descuento_valor + (subtotal * (NEW.descuento_porcentaje / 100));
    SET NEW.valor_total = subtotal - descuento;
END$$

DROP TRIGGER IF EXISTS `tr_cotizacion_productos_update_total`$$

CREATE TRIGGER `tr_cotizacion_productos_update_total`
    BEFORE UPDATE ON `ord_cotizacion_productos`
    FOR EACH ROW
BEGIN
    DECLARE subtotal DECIMAL(12,2);
    DECLARE descuento DECIMAL(12,2);

    SET subtotal = NEW.cantidad * NEW.valor_unitario;
    SET descuento = NEW.descuento_valor + (subtotal * (NEW.descuento_porcentaje / 100));
    SET NEW.valor_total = subtotal - descuento;
END$$

DELIMITER ;

-- =============================================
-- VISTAS ÚTILES
-- =============================================

-- Vista para obtener resumen de cotizaciones con totales
CREATE OR REPLACE VIEW `v_cotizaciones_resumen` AS
SELECT
    c.id,
    c.num_documento,
    c.fecha,
    c.proyecto,
    c.tipo,
    c.subtotal,
    c.descuento,
    c.total_impuesto,
    c.total,
    e.estado,
    e.color as estado_color,
    CONCAT(IFNULL(t.nombres, ''), ' ', IFNULL(t.apellidos, '')) as cliente_nombre,
    IFNULL(t.nombre_establecimiento, CONCAT(IFNULL(t.nombres, ''), ' ', IFNULL(t.apellidos, ''))) as cliente_display,
    ts.nombre_sucursal,
    tc.nombres as contacto_nombres,
    v.nombre_completo as vendedor,
    u.name as usuario_creador,
    COUNT(cp.id) as total_productos,
    SUM(CASE WHEN cp.active = 1 THEN cp.valor_total ELSE 0 END) as valor_productos_activos,
    c.created_at,
    c.updated_at
FROM ord_cotizacion c
LEFT JOIN estados_cotizacion e ON c.estado_id = e.id
LEFT JOIN terceros t ON c.tercero_id = t.id
LEFT JOIN terceros_sucursales ts ON c.tercero_sucursal_id = ts.id
LEFT JOIN terceros_contactos tc ON c.tercero_contacto_id = tc.id
LEFT JOIN vendedores v ON c.vendedor_id = v.id
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN ord_cotizacion_productos cp ON c.id = cp.cotizacion_id
GROUP BY c.id, c.num_documento, c.fecha, c.proyecto, c.tipo, c.subtotal,
         c.descuento, c.total_impuesto, c.total, e.estado, e.color,
         t.nombres, t.apellidos, t.nombre_establecimiento,
         ts.nombre_sucursal, tc.nombres, v.nombre_completo, u.name,
         c.created_at, c.updated_at;

-- =============================================
-- FIN DEL SCRIPT
-- =============================================