/**
 * Script de prueba para verificar la corrección de elementos DOM
 */

console.log('🧪 Test DOM Fix cargado');

// Función para probar actualizarValidaciones sin errores
window.testActualizarValidaciones = function() {
    console.log('🧪 === TEST ACTUALIZAR VALIDACIONES ===');

    try {
        // Simular llamada a actualizarValidaciones
        if (typeof actualizarValidaciones === 'function') {
            console.log('Ejecutando actualizarValidaciones...');
            actualizarValidaciones();
            console.log('✅ actualizarValidaciones ejecutada sin errores');
        } else {
            console.log('❌ función actualizarValidaciones no disponible');
        }
    } catch (error) {
        console.error('❌ Error en actualizarValidaciones:', error);
    }

    console.log('🧪 === FIN TEST ===');
};

// Auto-ejecutar test cuando el modal se abra
$(document).on('shown.bs.modal', '#ModalCliente', function() {
    setTimeout(() => {
        window.testActualizarValidaciones();
    }, 1500);
});
