-- =====================================================
-- SCRIPT SQL PARA TABLAS DE PRODUCTOS EN COTIZACIONES
-- Base de datos: Sistema de Cotizaciones
-- Fecha: Diciembre 2024
-- =====================================================

-- ====================
-- TABLA: ord_cotizacion_productos
-- Descripción: Tabla intermedia entre cotizaciones y productos
-- ====================

CREATE TABLE `ord_cotizacion_productos` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cotizacion_id` bigint(20) UNSIGNED NOT NULL,
    `producto_id` bigint(20) UNSIGNED DEFAULT NULL,
    `nombre` varchar(255) NOT NULL,
    `descripcion` text DEFAULT NULL,
    `codigo` varchar(50) DEFAULT NULL,
    `unidad_medida` varchar(20) NOT NULL,
    `cantidad` decimal(10,3) NOT NULL DEFAULT 1.000,
    `valor_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
    `descuento_porcentaje` decimal(5,2) DEFAULT 0.00,
    `descuento_valor` decimal(12,2) DEFAULT 0.00,
    `valor_total` decimal(12,2) NOT NULL DEFAULT 0.00,
    `observaciones` text DEFAULT NULL,
    `orden` int(11) NOT NULL DEFAULT 0,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cotizacion_producto` (`cotizacion_id`),
    KEY `idx_producto` (`producto_id`),
    KEY `idx_orden` (`orden`),
    KEY `idx_active` (`active`),
    KEY `idx_codigo` (`codigo`),
    KEY `idx_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- TABLA: ord_cotizacion_categorias_productos
-- Descripción: Categorías específicas para productos en cotizaciones
-- ====================

CREATE TABLE `ord_cotizacion_categorias_productos` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` varchar(100) NOT NULL,
    `descripcion` text DEFAULT NULL,
    `icono` varchar(50) DEFAULT NULL,
    `color` varchar(7) DEFAULT '#007bff',
    `orden` int(11) NOT NULL DEFAULT 0,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `nombre` (`nombre`),
    KEY `idx_active` (`active`),
    KEY `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- TABLA: ord_cotizacion_productos_historico
-- Descripción: Historial de cambios en productos de cotizaciones
-- ====================

CREATE TABLE `ord_cotizacion_productos_historico` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cotizacion_producto_id` bigint(20) UNSIGNED NOT NULL,
    `campo_modificado` varchar(50) NOT NULL,
    `valor_anterior` text DEFAULT NULL,
    `valor_nuevo` text DEFAULT NULL,
    `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
    `motivo` varchar(255) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cotizacion_producto` (`cotizacion_producto_id`),
    KEY `idx_usuario` (`usuario_id`),
    KEY `idx_campo` (`campo_modificado`),
    KEY `idx_fecha` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- TABLA: ord_cotizacion_productos_plantillas
-- Descripción: Plantillas de productos predefinidos para cotizaciones
-- ====================

CREATE TABLE `ord_cotizacion_productos_plantillas` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` varchar(100) NOT NULL,
    `descripcion` text DEFAULT NULL,
    `categoria_id` bigint(20) UNSIGNED DEFAULT NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_categoria` (`categoria_id`),
    KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- TABLA: ord_cotizacion_productos_plantillas_items
-- Descripción: Items de las plantillas de productos
-- ====================

CREATE TABLE `ord_cotizacion_productos_plantillas_items` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `plantilla_id` bigint(20) UNSIGNED NOT NULL,
    `producto_id` bigint(20) UNSIGNED DEFAULT NULL,
    `nombre` varchar(255) NOT NULL,
    `descripcion` text DEFAULT NULL,
    `unidad_medida` varchar(20) NOT NULL,
    `cantidad` decimal(10,3) NOT NULL DEFAULT 1.000,
    `valor_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
    `orden` int(11) NOT NULL DEFAULT 0,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_plantilla` (`plantilla_id`),
    KEY `idx_producto` (`producto_id`),
    KEY `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- INSERTAR DATOS INICIALES
-- ====================

-- Categorías de productos por defecto
INSERT INTO `ord_cotizacion_categorias_productos` (`nombre`, `descripcion`, `icono`, `color`, `orden`) VALUES
('Materiales de Construcción', 'Materiales básicos para construcción', 'fas fa-hammer', '#6c757d', 1),
('Acabados', 'Materiales de acabados y terminaciones', 'fas fa-paint-brush', '#28a745', 2),
('Instalaciones Eléctricas', 'Componentes para instalaciones eléctricas', 'fas fa-bolt', '#ffc107', 3),
('Instalaciones Hidráulicas', 'Componentes para instalaciones hidráulicas', 'fas fa-tint', '#007bff', 4),
('Mano de Obra', 'Servicios de mano de obra especializada', 'fas fa-users', '#fd7e14', 5),
('Equipos y Herramientas', 'Alquiler y uso de equipos', 'fas fa-tools', '#6f42c1', 6),
('Otros', 'Productos diversos no categorizados', 'fas fa-box', '#e83e8c', 99);

-- ====================
-- FOREIGN KEYS Y CONSTRAINTS
-- ====================

-- Relación con tabla de cotizaciones (asumiendo que existe)
-- ALTER TABLE `ord_cotizacion_productos`
-- ADD CONSTRAINT `fk_cotizacion_productos_cotizacion`
-- FOREIGN KEY (`cotizacion_id`) REFERENCES `ord_cotizacion`(`id`) ON DELETE CASCADE;

-- Relación con tabla de productos (asumiendo que existe)
-- ALTER TABLE `ord_cotizacion_productos`
-- ADD CONSTRAINT `fk_cotizacion_productos_producto`
-- FOREIGN KEY (`producto_id`) REFERENCES `inv_productos`(`id`) ON DELETE SET NULL;

-- Relación historial con productos de cotización
ALTER TABLE `ord_cotizacion_productos_historico`
ADD CONSTRAINT `fk_historico_cotizacion_producto`
FOREIGN KEY (`cotizacion_producto_id`) REFERENCES `ord_cotizacion_productos`(`id`) ON DELETE CASCADE;

-- Relación plantillas con categorías
ALTER TABLE `ord_cotizacion_productos_plantillas`
ADD CONSTRAINT `fk_plantillas_categoria`
FOREIGN KEY (`categoria_id`) REFERENCES `ord_cotizacion_categorias_productos`(`id`) ON DELETE SET NULL;

-- Relación items de plantilla con plantilla
ALTER TABLE `ord_cotizacion_productos_plantillas_items`
ADD CONSTRAINT `fk_plantilla_items_plantilla`
FOREIGN KEY (`plantilla_id`) REFERENCES `ord_cotizacion_productos_plantillas`(`id`) ON DELETE CASCADE;

-- ====================
-- TRIGGERS PARA CÁLCULOS AUTOMÁTICOS
-- ====================

-- Trigger para calcular valor_total automáticamente al insertar
DELIMITER $$
CREATE TRIGGER `tr_cotizacion_productos_insert_valor_total`
BEFORE INSERT ON `ord_cotizacion_productos`
FOR EACH ROW
BEGIN
    DECLARE subtotal DECIMAL(12,2);
    SET subtotal = NEW.cantidad * NEW.valor_unitario;
    SET NEW.valor_total = subtotal - COALESCE(NEW.descuento_valor, 0);

    -- Si se especifica descuento por porcentaje, calcularlo
    IF NEW.descuento_porcentaje > 0 THEN
        SET NEW.descuento_valor = (subtotal * NEW.descuento_porcentaje) / 100;
        SET NEW.valor_total = subtotal - NEW.descuento_valor;
    END IF;
END$$

-- Trigger para calcular valor_total automáticamente al actualizar
CREATE TRIGGER `tr_cotizacion_productos_update_valor_total`
BEFORE UPDATE ON `ord_cotizacion_productos`
FOR EACH ROW
BEGIN
    DECLARE subtotal DECIMAL(12,2);
    SET subtotal = NEW.cantidad * NEW.valor_unitario;
    SET NEW.valor_total = subtotal - COALESCE(NEW.descuento_valor, 0);

    -- Si se especifica descuento por porcentaje, calcularlo
    IF NEW.descuento_porcentaje > 0 THEN
        SET NEW.descuento_valor = (subtotal * NEW.descuento_porcentaje) / 100;
        SET NEW.valor_total = subtotal - NEW.descuento_valor;
    END IF;
END$$

-- Trigger para guardar historial de cambios
CREATE TRIGGER `tr_cotizacion_productos_historico_update`
AFTER UPDATE ON `ord_cotizacion_productos`
FOR EACH ROW
BEGIN
    -- Registrar cambio de cantidad
    IF OLD.cantidad != NEW.cantidad THEN
        INSERT INTO ord_cotizacion_productos_historico
        (cotizacion_producto_id, campo_modificado, valor_anterior, valor_nuevo, created_at)
        VALUES (NEW.id, 'cantidad', OLD.cantidad, NEW.cantidad, NOW());
    END IF;

    -- Registrar cambio de valor unitario
    IF OLD.valor_unitario != NEW.valor_unitario THEN
        INSERT INTO ord_cotizacion_productos_historico
        (cotizacion_producto_id, campo_modificado, valor_anterior, valor_nuevo, created_at)
        VALUES (NEW.id, 'valor_unitario', OLD.valor_unitario, NEW.valor_unitario, NOW());
    END IF;

    -- Registrar cambio de descuento
    IF OLD.descuento_valor != NEW.descuento_valor THEN
        INSERT INTO ord_cotizacion_productos_historico
        (cotizacion_producto_id, campo_modificado, valor_anterior, valor_nuevo, created_at)
        VALUES (NEW.id, 'descuento_valor', OLD.descuento_valor, NEW.descuento_valor, NOW());
    END IF;
END$$

DELIMITER ;

-- ====================
-- VISTAS ÚTILES
-- ====================

-- Vista para productos activos con información completa
CREATE VIEW `v_cotizacion_productos_activos` AS
SELECT
    cp.*,
    p.categoria as producto_categoria,
    p.marca as producto_marca,
    p.stock_minimo,
    c.num_documento as cotizacion_numero,
    c.proyecto as cotizacion_proyecto,
    c.fecha as cotizacion_fecha,
    (cp.cantidad * cp.valor_unitario) as subtotal_bruto,
    cp.valor_total as subtotal_neto
FROM ord_cotizacion_productos cp
LEFT JOIN inv_productos p ON cp.producto_id = p.id
LEFT JOIN ord_cotizacion c ON cp.cotizacion_id = c.id
WHERE cp.active = 1
ORDER BY cp.cotizacion_id, cp.orden;

-- Vista para totales por cotización
CREATE VIEW `v_cotizacion_totales_productos` AS
SELECT
    cotizacion_id,
    COUNT(*) as total_productos,
    SUM(cantidad) as cantidad_total,
    SUM(cantidad * valor_unitario) as subtotal_bruto,
    SUM(descuento_valor) as descuentos_total,
    SUM(valor_total) as total_neto,
    AVG(valor_unitario) as precio_promedio,
    MAX(valor_unitario) as precio_maximo,
    MIN(valor_unitario) as precio_minimo
FROM ord_cotizacion_productos
WHERE active = 1
GROUP BY cotizacion_id;

-- ====================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- ====================

-- Índices compuestos para consultas frecuentes
CREATE INDEX `idx_cotizacion_activo_orden` ON `ord_cotizacion_productos` (`cotizacion_id`, `active`, `orden`);
CREATE INDEX `idx_producto_activo` ON `ord_cotizacion_productos` (`producto_id`, `active`);
CREATE INDEX `idx_busqueda_productos` ON `ord_cotizacion_productos` (`nombre`, `codigo`, `active`);

-- Índice para búsquedas de texto
ALTER TABLE `ord_cotizacion_productos` ADD FULLTEXT(`nombre`, `descripcion`, `codigo`);

-- ====================
-- COMENTARIOS EN TABLAS
-- ====================

ALTER TABLE `ord_cotizacion_productos` COMMENT = 'Tabla intermedia que relaciona productos con cotizaciones, incluyendo cantidades y precios específicos para cada cotización';
ALTER TABLE `ord_cotizacion_categorias_productos` COMMENT = 'Categorías para organizar productos en cotizaciones';
ALTER TABLE `ord_cotizacion_productos_historico` COMMENT = 'Historial de modificaciones en productos de cotizaciones para auditoría';
ALTER TABLE `ord_cotizacion_productos_plantillas` COMMENT = 'Plantillas predefinidas de conjuntos de productos para cotizaciones';
ALTER TABLE `ord_cotizacion_productos_plantillas_items` COMMENT = 'Items específicos que componen cada plantilla de productos';

-- ====================
-- SCRIPT COMPLETADO
-- ====================

-- NOTAS DE IMPLEMENTACIÓN:
-- 1. Descomenta las foreign keys hacia ord_cotizacion e inv_productos según tu esquema
-- 2. Ajusta los nombres de tablas si son diferentes en tu base de datos
-- 3. Los triggers asumen que valor_total se calcula automáticamente
-- 4. Las vistas proporcionan consultas optimizadas para reportes
-- 5. Los índices mejoran el rendimiento para búsquedas y consultas frecuentes

-- Para usar este script:
-- 1. Ejecuta cada sección en orden
-- 2. Verifica que las foreign keys coincidan con tu esquema
-- 3. Ajusta los datos iniciales según tus necesidades
-- 4. Prueba las vistas y triggers antes del despliegue en producción

SELECT 'SCRIPT EJECUTADO EXITOSAMENTE - TABLAS DE PRODUCTOS EN COTIZACIONES CREADAS' as resultado;
