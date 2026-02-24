/**
 * ===============================================
 * COTIZACIÓN PRODUCTOS MANAGER
 * Gestión de productos en cotizaciones
 * ===============================================
 */

class CotizacionProductosManager {
    constructor() {
        this.cotizacionId = null;
        this.productos = [];
        this.productosSeleccionados = new Set();
        this.config = {
            endpoints: {
                buscarProductos: '/admin/admin.cotizaciones.productos.buscar',
                agregarProducto: '/admin/admin.cotizaciones.productos.agregar',
                actualizarProducto: '/admin/admin.cotizaciones.productos.actualizar',
                eliminarProducto: '/admin/admin.cotizaciones.productos.eliminar',
                reordenarProductos: '/admin/admin.cotizaciones.productos.reordenar',
                obtenerTotales: '/admin/admin.cotizaciones/{cotizacionId}/totales',
                aplicarDescuentoGlobal: '/admin/admin.cotizaciones.productos.descuento-global'
            },
            selectors: {
                modalProductos: '#modalProductos',
                tablaProductos: '#tablaProductos tbody',
                btnAgregarProducto: '#btnAgregarProducto',
                btnBuscarProductos: '#btnBuscarProductos',
                inputBusqueda: '#inputBusquedaProductos',
                formProducto: '#formProducto',
                totalSubtotal: '#totalSubtotal',
                totalDescuentos: '#totalDescuentos',
                totalNeto: '#totalNeto'
            }
        };
        
        this.init();
    }

    /**
     * Inicializar el manager
     */
    init() {
        this.bindEvents();
        this.initSortable();
        console.log('CotizacionProductosManager inicializado');
    }

    /**
     * Configurar cotización activa
     */
    setCotizacion(cotizacionId) {
        this.cotizacionId = cotizacionId;
        this.cargarProductosCotizacion();
    }

    /**
     * Enlazar eventos
     */
    bindEvents() {
        // Botón agregar producto
        $(document).on('click', this.config.selectors.btnAgregarProducto, () => {
            this.abrirModalProductos();
        });

        // Búsqueda de productos
        $(document).on('input', this.config.selectors.inputBusqueda, 
            this.debounce((e) => this.buscarProductos(e.target.value), 300)
        );

        // Envío de formulario de producto
        $(document).on('submit', this.config.selectors.formProducto, (e) => {
            e.preventDefault();
            this.procesarFormularioProducto(e.target);
        });

        // Acciones de tabla
        $(document).on('click', '.btn-editar-producto', (e) => {
            const id = $(e.target).data('id');
            this.editarProducto(id);
        });

        $(document).on('click', '.btn-eliminar-producto', (e) => {
            const id = $(e.target).data('id');
            this.eliminarProducto(id);
        });

        // Cambios en cantidad y precio
        $(document).on('change', '.input-cantidad, .input-precio', (e) => {
            this.actualizarProductoEnLinea(e.target);
        });

        // Descuento global
        $(document).on('click', '#btnDescuentoGlobal', () => {
            this.mostrarModalDescuentoGlobal();
        });
    }

    /**
     * Inicializar tabla sortable para reordenar
     */
    initSortable() {
        if (typeof $.fn.sortable !== 'undefined') {
            $(this.config.selectors.tablaProductos).sortable({
                handle: '.handle-orden',
                axis: 'y',
                helper: 'clone',
                update: (event, ui) => {
                    this.guardarNuevoOrden();
                }
            });
        }
    }

    /**
     * Cargar productos de la cotización
     */
    async cargarProductosCotizacion() {
        if (!this.cotizacionId) return;

        try {
            this.mostrarLoader();
            
            const response = await fetch(`/admin/admin.cotizaciones/${this.cotizacionId}/elementos`);
            const data = await response.json();

            if (data.success) {
                this.productos = data.data.filter(item => item.tipo === 'Producto');
                this.renderTablaProductos();
                this.actualizarTotales();
            } else {
                this.mostrarError('Error al cargar productos: ' + data.message);
            }
        } catch (error) {
            this.mostrarError('Error de conexión: ' + error.message);
        } finally {
            this.ocultarLoader();
        }
    }

    /**
     * Buscar productos disponibles
     */
    async buscarProductos(termino) {
        if (termino.length < 2) {
            $('#resultadosBusqueda').empty();
            return;
        }

        try {
            const response = await fetch(`${this.config.endpoints.buscarProductos}?q=${encodeURIComponent(termino)}&limite=10`);
            const data = await response.json();

            if (data.success) {
                this.renderResultadosBusqueda(data.data);
            }
        } catch (error) {
            console.error('Error en búsqueda:', error);
        }
    }

    /**
     * Renderizar resultados de búsqueda
     */
    renderResultadosBusqueda(productos) {
        const container = $('#resultadosBusqueda');
        container.empty();

        if (productos.length === 0) {
            container.append('<div class="alert alert-info">No se encontraron productos</div>');
            return;
        }

        productos.forEach(producto => {
            const item = $(`
                <div class="list-group-item list-group-item-action producto-resultado" data-producto='${JSON.stringify(producto)}'>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${producto.nombre}</h6>
                            <small class="text-muted">${producto.codigo} - ${producto.categoria}</small>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-primary">$${this.formatearPrecio(producto.precio)}</span>
                            <small class="text-muted d-block">${producto.unidad}</small>
                        </div>
                    </div>
                </div>
            `);

            item.on('click', () => {
                this.seleccionarProducto(producto);
            });

            container.append(item);
        });
    }

    /**
     * Seleccionar producto de búsqueda
     */
    seleccionarProducto(producto) {
        $('#producto_id').val(producto.id);
        $('#nombre').val(producto.nombre);
        $('#descripcion').val(producto.descripcion || '');
        $('#codigo').val(producto.codigo || '');
        $('#unidad_medida').val(producto.unidad || 'Unidad');
        $('#valor_unitario').val(producto.precio);
        $('#cantidad').val(1).focus();
        
        $('#resultadosBusqueda').empty();
        $(this.config.selectors.inputBusqueda).val('');
    }

    /**
     * Procesar formulario de producto
     */
    async procesarFormularioProducto(form) {
        const formData = new FormData(form);
        const datos = Object.fromEntries(formData);
        datos.cotizacion_id = this.cotizacionId;

        try {
            this.mostrarLoader();

            const response = await fetch(this.config.endpoints.agregarProducto, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(datos)
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarExito('Producto agregado correctamente');
                $(this.config.selectors.modalProductos).modal('hide');
                this.limpiarFormulario(form);
                this.cargarProductosCotizacion();
            } else {
                this.mostrarError('Error al agregar producto: ' + result.message);
            }
        } catch (error) {
            this.mostrarError('Error de conexión: ' + error.message);
        } finally {
            this.ocultarLoader();
        }
    }

    /**
     * Actualizar producto en línea
     */
    async actualizarProductoEnLinea(input) {
        const row = $(input).closest('tr');
        const id = row.data('id');
        const campo = $(input).data('campo');
        const valor = $(input).val();

        const datos = { [campo]: valor };

        try {
            const response = await fetch(`${this.config.endpoints.actualizarProducto}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(datos)
            });

            const result = await response.json();

            if (result.success) {
                // Actualizar valores calculados en la fila
                this.actualizarFilaProducto(row, result.data);
                this.actualizarTotales();
                this.mostrarExito('Producto actualizado');
            } else {
                this.mostrarError('Error al actualizar: ' + result.message);
                // Revertir valor
                $(input).val($(input).data('original-value'));
            }
        } catch (error) {
            this.mostrarError('Error de conexión: ' + error.message);
            $(input).val($(input).data('original-value'));
        }
    }

    /**
     * Eliminar producto
     */
    async eliminarProducto(id) {
        if (!confirm('¿Está seguro de eliminar este producto?')) return;

        try {
            const response = await fetch(`${this.config.endpoints.eliminarProducto}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarExito('Producto eliminado correctamente');
                this.cargarProductosCotizacion();
            } else {
                this.mostrarError('Error al eliminar: ' + result.message);
            }
        } catch (error) {
            this.mostrarError('Error de conexión: ' + error.message);
        }
    }

    /**
     * Renderizar tabla de productos
     */
    renderTablaProductos() {
        const tbody = $(this.config.selectors.tablaProductos);
        tbody.empty();

        this.productos.forEach((producto, index) => {
            const row = $(`
                <tr data-id="${producto.id}" data-orden="${index + 1}">
                    <td class="text-center">
                        <span class="handle-orden" style="cursor: move;">⋮⋮</span>
                        ${index + 1}
                    </td>
                    <td>
                        <strong>${producto.nombre || producto.descripcion}</strong>
                        ${producto.codigo ? `<br><small class="text-muted">Código: ${producto.codigo}</small>` : ''}
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm input-cantidad" 
                               value="${producto.cantidad}" step="0.01" min="0.01" 
                               data-campo="cantidad" data-original-value="${producto.cantidad}">
                    </td>
                    <td class="text-center">${producto.unidad_medida || 'Unidad'}</td>
                    <td class="text-right">
                        <input type="number" class="form-control form-control-sm input-precio" 
                               value="${producto.precio_unitario || producto.valor_unitario}" step="0.01" min="0" 
                               data-campo="valor_unitario" data-original-value="${producto.precio_unitario || producto.valor_unitario}">
                    </td>
                    <td class="text-right">
                        <span class="producto-subtotal">$${this.formatearPrecio(producto.costo_total || producto.valor_total)}</span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary btn-editar-producto" data-id="${producto.id}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-eliminar-producto" data-id="${producto.id}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
            
            tbody.append(row);
        });

        if (this.productos.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-box fa-2x mb-2"></i>
                        <p>No hay productos agregados</p>
                        <button type="button" class="btn btn-primary btn-sm" id="btnAgregarPrimerProducto">
                            <i class="fas fa-plus"></i> Agregar primer producto
                        </button>
                    </td>
                </tr>
            `);
        }
    }

    /**
     * Actualizar totales
     */
    async actualizarTotales() {
        if (!this.cotizacionId) return;

        try {
            const response = await fetch(this.config.endpoints.obtenerTotales.replace('{cotizacionId}', this.cotizacionId));
            const data = await response.json();

            if (data.success) {
                const totales = data.data;
                $(this.config.selectors.totalSubtotal).text('$' + this.formatearPrecio(totales.subtotal));
                $(this.config.selectors.totalDescuentos).text('$' + this.formatearPrecio(totales.descuento_total));
                $(this.config.selectors.totalNeto).text('$' + this.formatearPrecio(totales.total));
            }
        } catch (error) {
            console.error('Error al actualizar totales:', error);
        }
    }

    /**
     * Abrir modal de productos
     */
    abrirModalProductos() {
        this.limpiarFormulario(this.config.selectors.formProducto);
        $(this.config.selectors.modalProductos).modal('show');
        setTimeout(() => {
            $(this.config.selectors.inputBusqueda).focus();
        }, 500);
    }

    /**
     * Limpiar formulario
     */
    limpiarFormulario(form) {
        if (typeof form === 'string') form = $(form)[0];
        form.reset();
        $('#resultadosBusqueda').empty();
        $('#producto_id').val('');
    }

    /**
     * Utilidades
     */
    formatearPrecio(precio) {
        return parseFloat(precio || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    mostrarLoader() {
        $('#loader').show();
    }

    ocultarLoader() {
        $('#loader').hide();
    }

    mostrarExito(mensaje) {
        this.mostrarToast(mensaje, 'success');
    }

    mostrarError(mensaje) {
        this.mostrarToast(mensaje, 'error');
    }

    mostrarToast(mensaje, tipo) {
        // Implementar según tu sistema de notificaciones
        if (typeof toastr !== 'undefined') {
            toastr[tipo](mensaje);
        } else {
            alert(mensaje);
        }
    }
}

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    window.cotizacionProductosManager = new CotizacionProductosManager();
    
    // Si hay un ID de cotización en la página, configurarlo
    const cotizacionId = $('meta[name="cotizacion-id"]').attr('content') || 
                        $('#cotizacion_id').val() || 
                        window.cotizacionId;
    
    if (cotizacionId) {
        window.cotizacionProductosManager.setCotizacion(cotizacionId);
    }
});