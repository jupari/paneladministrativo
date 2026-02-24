/**
 * Sistema de protección y configuración para documento de cotización
 * Maneja CSRF, elementos faltantes, protección de errores y notificaciones
 */

window.documentoProtection = {

    /**
     * Inicializar todas las protecciones
     */
    init: function() {
        this.setupCSRF();
        this.createMissingElements();
        this.protectNullAccess();
        this.protectAjax();
        this.initToastr();
    },

    /**
     * Configurar token CSRF globalmente
     */
    setupCSRF: function() {
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.csrfToken = token.getAttribute('content');

            // Configurar para jQuery si está disponible
            if (window.$ && $.ajaxSetup) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken
                    }
                });
            }
            console.log('Token CSRF configurado globalmente');
        } else {
            console.error('Token CSRF no encontrado');
        }
    },

    /**
     * Crear elementos faltantes que documento.js necesita
     */
    createMissingElements: function() {
        const requiredElements = [
            'botonesAgregarProductos',
            'agregarProductos',
            'cliente_id',
            'item_subitem',
            'item_cantidad',
            'item_valor_unitario',
            'btn_crear_subitem',
            'accordionCotizacionDetails',
            'item_nombre',
            'btn_agregar_item',
            'btn_limpiar_item'
        ];

        requiredElements.forEach(id => {
            if (!document.getElementById(id)) {
                console.warn(`Elemento ${id} no encontrado, creando placeholder`);

                let element;
                // Crear el tipo de elemento adecuado según el ID
                if (id.includes('btn_') || id === 'agregarProductos') {
                    element = document.createElement('button');
                    element.className = 'btn btn-secondary d-none';
                } else if (id.includes('item_') && !id.includes('btn_')) {
                    if (id === 'item_subitem') {
                        element = document.createElement('select');
                        element.className = 'form-control d-none';
                    } else {
                        element = document.createElement('input');
                        element.className = 'form-control d-none';
                        element.type = 'text';
                    }
                } else {
                    element = document.createElement('div');
                    element.className = 'd-none';
                }

                element.id = id;
                element.style.display = 'none';
                document.body.appendChild(element);
            }
        });

        // Asegurar que existe el toast-container
        if (!document.getElementById('toast-container')) {
            const toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container';
            toastContainer.setAttribute('aria-live', 'polite');
            toastContainer.setAttribute('aria-atomic', 'true');
            toastContainer.style.position = 'fixed';
            toastContainer.style.top = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
            console.log('Toast container creado');
        }

        // Verificar elementos select específicos y agregar método appendChild si no existe
        const selectElements = ['item_subitem'];
        selectElements.forEach(id => {
            const element = document.getElementById(id);
            if (element && !element.appendChild) {
                element.appendChild = function(child) {
                    return this.add ? this.add(child) : Element.prototype.appendChild.call(this, child);
                };
            }
        });

        // Proteger document.createDocumentFragment
        if (!document.createDocumentFragment) {
            document.createDocumentFragment = function() {
                return document.createElement('div');
            };
        }
    },

    /**
     * Proteger acceso a elementos null
     */
    protectNullAccess: function() {
        // Crear función segura para obtener elementos
        window.safeGetElement = function(id) {
            const element = document.getElementById(id);
            if (!element) {
                console.warn(`Elemento ${id} no encontrado, creando mock`);
                return {
                    id: id,
                    value: '',
                    textContent: '',
                    innerHTML: '',
                    style: {},
                    classList: {
                        add: () => {},
                        remove: () => {},
                        contains: () => false
                    },
                    addEventListener: () => {},
                    removeEventListener: () => {},
                    click: () => {},
                    focus: () => {},
                    blur: () => {},
                    parentNode: null,
                    children: [],
                    childNodes: [],
                    querySelector: () => null,
                    querySelectorAll: () => [],
                    getElementsByTagName: () => [],
                    getElementsByClassName: () => []
                };
            }
            return element;
        };
    },

    /**
     * Protección para llamadas AJAX
     */
    protectAjax: function() {
        // Interceptar fetch para manejar respuestas HTML inesperadas
        const originalFetch = window.fetch;
        window.fetch = function(url, options) {
            return originalFetch(url, options)
                .then(response => {
                    if (response.ok) {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return response;
                        } else if (contentType && contentType.includes('text/html')) {
                            console.error(`Respuesta HTML inesperada para ${url}`);
                            return {
                                json: () => Promise.resolve({
                                    success: false,
                                    data: [],
                                    message: 'Error: Respuesta HTML inesperada del servidor'
                                })
                            };
                        }
                    }
                    return response;
                })
                .catch(error => {
                    console.error('Error en fetch:', error);
                    return {
                        json: () => Promise.resolve({
                            success: false,
                            data: [],
                            message: 'Error de conexión'
                        })
                    };
                });
        };
    },

    /**
     * Inicializar Toastr y sistema de notificaciones
     */
    initToastr: function() {
        // Verificar si toastr está disponible
        if (typeof toastr !== 'undefined') {
            // Configurar toastr
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            console.log('Toastr configurado correctamente');
        } else {
            console.warn('Toastr no está disponible, creando funciones mock');
            // Crear funciones mock si toastr no está disponible
            window.toastr = {
                success: function(message, title) {
                    console.log('SUCCESS:', title || 'Éxito', message);
                    this.showBootstrapAlert(message, 'success');
                },
                error: function(message, title) {
                    console.log('ERROR:', title || 'Error', message);
                    this.showBootstrapAlert(message, 'danger');
                },
                warning: function(message, title) {
                    console.log('WARNING:', title || 'Advertencia', message);
                    this.showBootstrapAlert(message, 'warning');
                },
                info: function(message, title) {
                    console.log('INFO:', title || 'Información', message);
                    this.showBootstrapAlert(message, 'info');
                },
                showBootstrapAlert: function(message, type) {
                    const alertHtml = `
                        <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                            ${message}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `;
                    document.body.insertAdjacentHTML('beforeend', alertHtml);

                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        const alert = document.querySelector('.alert:last-of-type');
                        if (alert) alert.remove();
                    }, 5000);
                }
            };
        }

        // Crear funciones globales para compatibilidad
        window.showSuccessToast = function(message, title = 'Éxito') {
            toastr.success(message, title);
        };

        window.showErrorToast = function(message, title = 'Error') {
            toastr.error(message, title);
        };

        window.showWarningToast = function(message, title = 'Advertencia') {
            toastr.warning(message, title);
        };

        window.showInfoToast = function(message, title = 'Información') {
            toastr.info(message, title);
        };
    }
};

// NOTA: La inicialización ahora es manejada por documento-coordinator.js
// No se ejecuta automáticamente para evitar conflictos de timing