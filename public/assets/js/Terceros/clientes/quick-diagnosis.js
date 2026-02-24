// DIAGNÓSTICO RÁPIDO MODAL
console.log('🔍 === DIAGNÓSTICO RÁPIDO MODAL ===');

$(document).ready(function() {
    // Diagnóstico inmediato
    setTimeout(() => {
        console.log('📊 Estado del sistema:');
        console.log('1. jQuery:', typeof $ !== 'undefined' ? '✅' : '❌');
        console.log('2. Bootstrap modal:', typeof $.fn.modal !== 'undefined' ? '✅' : '❌');
        console.log('3. Modal DOM:', document.getElementById('ModalCliente') ? '✅' : '❌');
        console.log('4. Botón regCli:', document.querySelector('[onclick="regCli()"]') ? '✅' : '❌');
        console.log('5. Función regCli:', typeof window.regCli === 'function' ? '✅' : '❌');

        // Test de apertura manual
        window.testModalOpen = function() {
            console.log('🧪 Testing modal open...');
            try {
                $('#ModalCliente').modal('show');
                console.log('✅ Modal open command executed');

                setTimeout(() => {
                    const modal = document.getElementById('ModalCliente');
                    console.log('Modal classes:', modal.className);
                    console.log('Modal style.display:', modal.style.display);
                    console.log('Modal is visible:', modal.offsetWidth > 0 && modal.offsetHeight > 0);
                }, 500);
            } catch (error) {
                console.error('❌ Error:', error);
            }
        };

        console.log('💡 Para testear manualmente ejecuta: testModalOpen()');
    }, 2000);
});
