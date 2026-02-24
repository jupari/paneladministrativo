/**
 * Coordinador de Carga para Documentos de Cotización
 * Maneja la inicialización ordenada de todos los módulos del sistema
 */

// Estado del coordinador
const CotizacionCoordinator = {
    // Estados de carga
    states: {
        DOM_LOADED: 'dom_loaded',
        CONFIG_LOADED: 'config_loaded',
        PROTECTION_READY: 'protection_ready',
        BASE_READY: 'base_ready',
        PROGRESSIVE_READY: 'progressive_ready',
        STICKY_READY: 'sticky_ready',
        MAIN_READY: 'main_ready',
        FULLY_INITIALIZED: 'fully_initialized'
    },

    // Estado actual
    currentState: null,

    // Configuración del servidor
    serverConfig: null,

    // Flags de inicialización
    initialized: {
        protection: false,
        base: false,
        progressive: false,
        sticky: false,
        main: false
    },

    // Callbacks de módulos
    callbacks: {
        onProtectionReady: [],
        onBaseReady: [],
        onProgressiveReady: [],
        onStickyReady: [],
        onMainReady: [],
        onFullyInitialized: []
    },

    /**
     * Inicializar el coordinador
     */
    init: function() {
        console.log('🚀 Coordinador de Cotización - Iniciando...');

        // Esperar a que el DOM esté completamente cargado
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.onDOMReady());
        } else {
            this.onDOMReady();
        }
    },

    /**
     * DOM está listo - iniciar secuencia de carga
     */
    onDOMReady: function() {
        console.log('✅ DOM completamente cargado');
        this.currentState = this.states.DOM_LOADED;

        // Esperar un momento para que todo se estabilice
        setTimeout(() => {
            this.startInitializationSequence();
        }, 100);
    },

    /**
     * Configurar datos del servidor
     */
    setServerConfig: function(config) {
        console.log('📥 Configuración del servidor recibida:', config);
        this.serverConfig = config;
        this.currentState = this.states.CONFIG_LOADED;

        // Si ya iniciamos la secuencia, aplicar configuración
        if (this.initialized.base) {
            this.applyServerConfiguration();
        }
    },

    /**
     * Iniciar secuencia ordenada de inicialización
     */
    startInitializationSequence: function() {
        console.log('🔄 Iniciando secuencia de inicialización...');

        // Paso 1: Sistema de protección (crítico)
        this.initializeProtection()
            .then(() => this.initializeBase())
            .then(() => this.initializeProgressive())
            .then(() => this.initializeSticky())
            .then(() => this.initializeMain())
            .then(() => this.finalizeInitialization())
            .catch((error) => {
                console.error('❌ Error en secuencia de inicialización:', error);
                // Intentar continuar con inicialización básica
                this.fallbackInitialization();
            });
    },

    /**
     * Paso 1: Inicializar sistema de protección
     */
    initializeProtection: function() {
        return new Promise((resolve, reject) => {
            console.log('🛡️  Inicializando sistema de protección...');

            try {
                if (window.documentoProtection && window.documentoProtection.init) {
                    window.documentoProtection.init();
                    this.initialized.protection = true;
                    this.currentState = this.states.PROTECTION_READY;
                    console.log('✅ Sistema de protección listo');

                    // Ejecutar callbacks
                    this.executeCallbacks('onProtectionReady');
                    resolve();
                } else {
                    console.warn('⚠️  Sistema de protección no encontrado, continuando...');
                    resolve();
                }
            } catch (error) {
                console.error('❌ Error al inicializar protección:', error);
                // Continuar a pesar del error
                resolve();
            }
        });
    },

    /**
     * Paso 2: Inicializar sistema base
     */
    initializeBase: function() {
        return new Promise((resolve, reject) => {
            console.log('🏗️  Inicializando sistema base...');

            try {
                // Aplicar configuración del servidor si está disponible
                if (this.serverConfig) {
                    this.applyServerConfiguration();
                }

                // Inicializar características principales del documento
                setTimeout(async () => {
                    try {
                        if (typeof initializeCoreFeatures === 'function') {
                            await initializeCoreFeatures();
                        }

                        this.initialized.base = true;
                        this.currentState = this.states.BASE_READY;
                        console.log('✅ Sistema base listo');

                        // Ejecutar callbacks
                        this.executeCallbacks('onBaseReady');
                        resolve();
                    } catch (error) {
                        console.error('❌ Error al inicializar características principales:', error);
                        // Continuar con el resto del flujo
                        resolve();
                    }
                }, 100);
            } catch (error) {
                console.error('❌ Error al inicializar base:', error);
                reject(error);
            }
        });
    },

    /**
     * Paso 3: Inicializar sistema progresivo
     */
    initializeProgressive: function() {
        return new Promise((resolve, reject) => {
            console.log('📊 Inicializando sistema progresivo...');

            try {
                // Esperar un momento para que los elementos estén listos
                setTimeout(() => {
                    if (typeof inicializarSistemaProgresivo === 'function') {
                        inicializarSistemaProgresivo();
                    }

                    if (typeof configurarEventosSistemaProgresivo === 'function') {
                        configurarEventosSistemaProgresivo();
                    }

                    this.initialized.progressive = true;
                    this.currentState = this.states.PROGRESSIVE_READY;
                    console.log('✅ Sistema progresivo listo');

                    // Ejecutar callbacks
                    this.executeCallbacks('onProgressiveReady');
                    resolve();
                }, 200);
            } catch (error) {
                console.error('❌ Error al inicializar sistema progresivo:', error);
                // Continuar a pesar del error
                resolve();
            }
        });
    },

    /**
     * Paso 4: Inicializar sistema sticky
     */
    initializeSticky: function() {
        return new Promise((resolve, reject) => {
            console.log('📌 Inicializando sistema sticky...');

            try {
                setTimeout(() => {
                    if (typeof iniciarActualizacionSticky === 'function') {
                        iniciarActualizacionSticky();
                    }

                    if (typeof actualizarStickyAhora === 'function') {
                        actualizarStickyAhora();
                    }

                    this.initialized.sticky = true;
                    this.currentState = this.states.STICKY_READY;
                    console.log('✅ Sistema sticky listo');

                    // Ejecutar callbacks
                    this.executeCallbacks('onStickyReady');
                    resolve();
                }, 300);
            } catch (error) {
                console.error('❌ Error al inicializar sistema sticky:', error);
                // Continuar a pesar del error
                resolve();
            }
        });
    },

    /**
     * Paso 5: Inicializar sistema principal
     */
    initializeMain: function() {
        return new Promise((resolve, reject) => {
            console.log('🎯 Inicializando sistema principal...');

            try {
                setTimeout(async () => {
                    try {
                        // Configurar event listeners básicos
                        if (typeof configurarEventListeners === 'function') {
                            configurarEventListeners();
                        }

                        // Inicializar productos y salarios
                        if (typeof initProductosYSalarios === 'function') {
                            initProductosYSalarios();
                        }

                        // Solo cargar productos guardados si es edición o visualización
                        const isEditing = this.serverConfig && (this.serverConfig.variable === 'editar' || this.serverConfig.variable === 'ver');
                        const cotizacionId = document.getElementById('id')?.value;

                        if (isEditing && cotizacionId) {
                            console.log('🔄 Cargando productos guardados para cotización existente...');
                            if (typeof cargarProductosGuardados === 'function') {
                                await cargarProductosGuardados();
                            }
                        } else {
                            console.log('🆕 Cotización nueva - omitiendo carga de productos');
                        }

                        // Actualizar totales
                        if (typeof actualizarTotalesCompletos === 'function') {
                            await actualizarTotalesCompletos();
                        }

                        // Si es modo edición, hacer actualizaciones adicionales
                        if (this.serverConfig && this.serverConfig.variable === 'editar') {
                            if (typeof mostrarEstadoTotalesEnPantalla === 'function') {
                                mostrarEstadoTotalesEnPantalla();
                            }
                        }

                        this.initialized.main = true;
                        this.currentState = this.states.MAIN_READY;
                        console.log('✅ Sistema principal listo');

                        // Ejecutar callbacks
                        this.executeCallbacks('onMainReady');
                        resolve();
                    } catch (error) {
                        console.error('❌ Error específico en sistema principal:', error);
                        // Continuar con la inicialización a pesar del error
                        this.initialized.main = true;
                        resolve();
                    }
                }, 500);
            } catch (error) {
                console.error('❌ Error al inicializar sistema principal:', error);
                reject(error);
            }
        });
    },

    /**
     * Finalizar inicialización
     */
    finalizeInitialization: function() {
        return new Promise((resolve) => {
            console.log('🎉 Finalizando inicialización...');

            setTimeout(() => {
                this.currentState = this.states.FULLY_INITIALIZED;
                console.log('✅ Sistema completamente inicializado');

                // Ocultar skeleton si existe
                if (typeof hideSkeleton === 'function') {
                    hideSkeleton();
                }

                // Actualizar progreso inicial solo si no es una cotización nueva
                setTimeout(() => {
                    if (typeof actualizarProgresoCompletion === 'function') {
                        // Solo actualizar automáticamente si hay una cotización existente
                        const cotizacionId = document.getElementById('id')?.value;
                        const isNewCotizacion = !cotizacionId || cotizacionId === '' || cotizacionId === 'null';

                        if (!isNewCotizacion) {
                            actualizarProgresoCompletion();
                        } else {
                            // En cotizaciones nuevas, establecer progreso en 0
                            console.log('🔄 Estableciendo progreso inicial en 0% para cotización nueva');
                            const progressBar = document.getElementById('cotization-progress');
                            const progressText = document.getElementById('progress-text');
                            const statusBadge = document.getElementById('status-badge');

                            if (progressBar) progressBar.style.width = '0%';
                            if (progressText) progressText.textContent = '0% completado';
                            if (statusBadge) {
                                statusBadge.className = 'badge bg-light text-dark';
                                statusBadge.textContent = 'Sin iniciar';
                            }
                        }
                    }
                }, 100);


                resolve();
            }, 200);
        });
    },

    /**
     * Aplicar configuración del servidor
     */
    applyServerConfiguration: function() {
        if (!this.serverConfig) return;

        console.log('⚙️  Aplicando configuración del servidor...');

        try {
            if (window.cotizacionApp && window.cotizacionApp.inicializarConfiguracion) {
                window.cotizacionApp.inicializarConfiguracion(this.serverConfig);
            } else if (typeof inicializarConfiguracion === 'function') {
                inicializarConfiguracion(this.serverConfig);
            }
            console.log('✅ Configuración aplicada exitosamente');
        } catch (error) {
            console.error('❌ Error al aplicar configuración:', error);
        }
    },

    /**
     * Configurar actualizaciones periódicas
     */
    setupPeriodicUpdates: function() {
        console.log('⏰ Configurando actualizaciones periódicas...');

        // Actualización sticky cada 30 segundos
        setInterval(() => {
            if (typeof actualizarStickyAhora === 'function') {
                actualizarStickyAhora();
            }
        }, 30000);

        console.log('✅ Actualizaciones periódicas configuradas');
    },

    /**
     * Inicialización de respaldo en caso de error
     */
    fallbackInitialization: function() {
        console.warn('⚠️  Ejecutando inicialización de respaldo...');

        try {
            // Protección básica
            if (window.documentoProtection && window.documentoProtection.init) {
                window.documentoProtection.init();
            }

            // Configuración básica
            if (this.serverConfig) {
                this.applyServerConfiguration();
            }

            // Funciones principales
            setTimeout(() => {
                if (typeof initProductosYSalarios === 'function') {
                    initProductosYSalarios();
                }
                if (typeof hideSkeleton === 'function') {
                    hideSkeleton();
                }
            }, 1000);

            console.log('✅ Inicialización de respaldo completada');
        } catch (error) {
            console.error('❌ Error en inicialización de respaldo:', error);
        }
    },

    /**
     * Ejecutar callbacks de un tipo específico
     */
    executeCallbacks: function(type) {
        if (this.callbacks[type]) {
            this.callbacks[type].forEach(callback => {
                try {
                    callback();
                } catch (error) {
                    console.error(`Error ejecutando callback ${type}:`, error);
                }
            });
        }
    },

    /**
     * Registrar callback para un evento específico
     */
    onEvent: function(eventType, callback) {
        if (this.callbacks[eventType]) {
            this.callbacks[eventType].push(callback);
        }
    },

    /**
     * Verificar si un módulo está inicializado
     */
    isReady: function(module) {
        return this.initialized[module] || false;
    },

    /**
     * Verificar si todo está inicializado
     */
    isFullyReady: function() {
        return this.currentState === this.states.FULLY_INITIALIZED;
    },

    /**
     * Forzar reinicialización
     */
    reinitialize: function() {
        console.log('🔄 Forzando reinicialización...');
        this.initialized = {
            protection: false,
            base: false,
            progressive: false,
            sticky: false,
            main: false
        };
        this.startInitializationSequence();
    },

    /**
     * Diagnóstico del estado del sistema
     */
    diagnose: function() {
        console.log('🔍 === DIAGNÓSTICO DEL COORDINADOR ===');
        console.log('Estado actual:', this.currentState);
        console.log('Módulos inicializados:', this.initialized);
        console.log('Configuración del servidor:', this.serverConfig ? 'Cargada' : 'No disponible');

        // Verificar disponibilidad de funciones críticas
        const funciones = [
            'inicializarConfiguracion',
            'inicializarSistemaProgresivo',
            'initProductosYSalarios',
            'cargarProductosGuardados',
            'actualizarTotalesCompletos',
            'initializeCoreFeatures',
            'configurarEventListeners'
        ];

        console.log('Funciones disponibles:');
        funciones.forEach(func => {
            console.log(`  ${func}:`, typeof window[func] === 'function' ? '✅' : '❌');
        });

        // Verificar elementos DOM críticos
        const elementos = [
            'cliente_id',
            'agregarCotizacion',
            'cotizacionForm',
            'main-content'
        ];

        console.log('Elementos DOM críticos:');
        elementos.forEach(id => {
            const element = document.getElementById(id);
            console.log(`  ${id}:`, element ? '✅' : '❌');
        });

        console.log('=== FIN DIAGNÓSTICO ===');
    }
};

// Exponer globalmente
window.CotizacionCoordinator = CotizacionCoordinator;

// Auto-inicialización
CotizacionCoordinator.init();

// Funciones de utilidad para otros módulos
window.whenCotizacionReady = function(callback) {
    if (CotizacionCoordinator.isFullyReady()) {
        callback();
    } else {
        CotizacionCoordinator.onEvent('onFullyInitialized', callback);
    }
};

window.whenModuleReady = function(module, callback) {
    if (CotizacionCoordinator.isReady(module)) {
        callback();
    } else {
        const eventName = `on${module.charAt(0).toUpperCase() + module.slice(1)}Ready`;
        if (CotizacionCoordinator.callbacks[eventName]) {
            CotizacionCoordinator.onEvent(eventName, callback);
        }
    }
};

// Función de debug para desarrolladores
window.debugCotizacion = function() {
    if (window.CotizacionCoordinator) {
        window.CotizacionCoordinator.diagnose();
    } else {
        console.error('❌ Coordinador no disponible');
    }
};

// Función para forzar reinicialización en caso de problemas
window.reiniciarCotizacion = function() {
    if (window.CotizacionCoordinator) {
        window.CotizacionCoordinator.reinitialize();
    } else {
        console.error('❌ Coordinador no disponible');
    }
};

// ========================================
// FUNCIONALIDAD PDF
// ========================================

// Variables globales para PDF
let cotizacionIdForPdf = null;

// Funciones para PDF
window.previewPdf = async function () {
    const cotizacionId = cotizacionIdForPdf || document.getElementById('id')?.value;

    if (!cotizacionId) {
        Swal.fire({
            title: 'Cotización no guardada',
            text: 'Debe guardar la cotización antes de generar el PDF',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    Swal.fire({
        title: 'Generando vista previa...',
        text: 'Por favor espere mientras se genera el documento',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });

    const previewUrl = `/admin/admin.cotizaciones.preview/${cotizacionId}`;

    try {
        // 👇 ping previo para validar que el backend responde
        const response = await fetch(previewUrl, { method: 'HEAD' });

        if (!response.ok) {
            throw new Error('No se pudo generar el PDF');
        }

        window.open(previewUrl, '_blank');
        Swal.close();

    } catch (error) {
        Swal.fire({
            title: 'Error al generar PDF',
            text: 'No fue posible generar la vista previa. Intente nuevamente.',
            icon: 'error'
        });
    }
};

window.downloadPdf = function() {
    // Intentar obtener el ID de la cotización actual
    const cotizacionId = cotizacionIdForPdf || document.getElementById('id')?.value;

    if (!cotizacionId) {
        Swal.fire({
            title: 'Cotización no guardada',
            text: 'Debe guardar la cotización antes de generar el PDF',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // Mostrar loading
    Swal.fire({
        title: 'Generando PDF...',
        text: 'Por favor espere mientras se descarga el documento',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Descargar PDF
    const downloadUrl = `/admin/admin.cotizaciones.pdf/${cotizacionId}`;
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Cerrar el loading después de un breve delay
    setTimeout(() => {
        Swal.close();
        // Mostrar mensaje de éxito
        Swal.fire({
            title: '¡Descarga iniciada!',
            text: 'El archivo PDF se está descargando',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }, 1000);
};

// Función para mostrar los botones de PDF después de guardar
window.mostrarBotonesPdf = function(cotizacionId) {
    cotizacionIdForPdf = cotizacionId;
    const pdfButtons = document.getElementById('pdf-buttons');
    if (pdfButtons) {
        pdfButtons.style.display = 'block';
    }
};

// Función para ocultar los botones de PDF
window.ocultarBotonesPdf = function() {
    cotizacionIdForPdf = null;
    const pdfButtons = document.getElementById('pdf-buttons');
    if (pdfButtons) {
        pdfButtons.style.display = 'none';
    }
};

// Inicializar funcionalidad PDF al cargar el documento
CotizacionCoordinator.callbacks.onFullyInitialized.push(function() {
    // Si estamos editando una cotización existente
    const cotizacionId = document.getElementById('id')?.value;
    if (cotizacionId && cotizacionId !== '') {
        mostrarBotonesPdf(cotizacionId);
    } else {
        // En modo creación, mostrar los botones pero estarán deshabilitados hasta guardar
        const pdfButtons = document.getElementById('pdf-buttons');
        if (pdfButtons) {
            pdfButtons.style.display = 'block';
        }
    }
});

console.log('📋 Coordinador de Cotización cargado y listo');
console.log('💡 Funciones de debug disponibles: debugCotizacion(), reiniciarCotizacion()');
console.log('📄 Funciones de PDF disponibles: previewPdf(), downloadPdf(), mostrarBotonesPdf(), ocultarBotonesPdf()');

// ========================================
// FUNCIÓN GLOBAL PARA INICIALIZACIÓN DESDE SERVIDOR
// ========================================

/**
 * Función global para ser llamada desde la plantilla Blade
 * Maneja la configuración del servidor y la inicialización
 */
window.initializeCotizacionWithServerConfig = function(config) {
    console.log('🌐 Inicializando cotización con configuración del servidor:', config);

    // Usar el coordinador para manejar la configuración
    if (window.CotizacionCoordinator) {
        window.CotizacionCoordinator.setServerConfig(config);
    } else {
        // Fallback si el coordinador aún no está disponible
        console.warn('⚠️ Coordinador no disponible, reintentando en 500ms...');
        setTimeout(() => {
            if (window.CotizacionCoordinator) {
                window.CotizacionCoordinator.setServerConfig(config);
            } else {
                console.warn('⚠️ Coordinador no disponible después del reintento, usando inicialización legacy');
                // Inicialización legacy como respaldo
                if (window.cotizacionApp && window.cotizacionApp.inicializarConfiguracion) {
                    window.cotizacionApp.inicializarConfiguracion(config);
                }
            }
        }, 500);
    }
};
