// PARCHE DE COMPATIBILIDAD BOOTSTRAP 4.6 - SIMPLIFICADO
// Soluciona problemas de apertura del modal

$(document).ready(function() {

    // Función simple para verificar modal
    window.debugModalSimple = function() {
        // console.log('🔍 Debug Modal Simple:');
        // console.log('   - Modal existe:', !!document.getElementById('ModalCliente'));
        // console.log('   - jQuery disponible:', typeof $ !== 'undefined');
        // console.log('   - Bootstrap modal:', typeof $.fn.modal !== 'undefined');
        // console.log('   - regCli función:', typeof window.regCli === 'function');
    };

    // Interceptar eventos del modal para limpieza adicional
    $('#ModalCliente').on('show.bs.modal', function(e) {
        // console.log('🔄 Modal abriéndose - verificando limpieza...');

        // Si no es modo edición, limpiar todo
        const isEditMode = $(this).data('edit-mode');
        if (!isEditMode) {
            // console.log('🧹 Modo creación detectado - ejecutando limpieza adicional');
            if (window.limpiarTodoElModal) {
                setTimeout(() => {
                    window.limpiarTodoElModal();
                }, 100);
            }
        }
    });

    // Configurar eventos cuando se muestre el modal
    $('#ModalCliente').on('shown.bs.modal', function(e) {
        // console.log('✅ Modal mostrado - configurando eventos');

        // Configurar evento para tipo de persona
        $('#tipopersona_id').off('change.bootstrap').on('change.bootstrap', function() {
            // console.log('🔄 Cambio en tipo de persona detectado');
            if (typeof actualizarValidaciones === 'function') {
                setTimeout(() => {
                    actualizarValidaciones();
                }, 100);
            }
        });

        // Ejecutar validaciones iniciales
        if (typeof actualizarValidaciones === 'function') {
            setTimeout(() => {
                actualizarValidaciones();
            }, 300);
        }
    });

    // Test automático
    setTimeout(() => {
        window.debugModalSimple();
    }, 1000);
});
