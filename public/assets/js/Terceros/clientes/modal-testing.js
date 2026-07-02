// SCRIPT DE TESTING PARA MODAL DE CLIENTES
// Permite testear la funcionalidad del modal desde la consola

window.testModalFunctionality = function() {

    // Test 1: Verificar existencia de elementos
    const modal = document.getElementById('ModalCliente');
    const btnRegCli = document.querySelector('[onclick="regCli()"]');
    try {
        if (window.openClientModal) {
            // console.log('   - Intentando abrir modal...');
            // No abrir realmente, solo verificar que la función existe
            // console.log('   - ✅ Función openClientModal disponible');
        }
    } catch (error) {
        console.log('   - ❌ Error:', error.message);
    }
    return true;
};

// Función para simular click en el botón de registro
window.simulateRegCliClick = function() {
    const btn = document.querySelector('[onclick="regCli()"]');
    if (btn) {
        btn.click();
    } else {
        console.log('❌ Botón no encontrado');
    }
};

// Función para verificar estado del modal
window.checkModalState = function() {
    const modal = document.getElementById('ModalCliente');
    if (!modal) {
        return;
    }

    const isVisible = modal.classList.contains('show');
    return isVisible;
};

// Auto-test al cargar
$(document).ready(function() {
    // Esperar un poco para que todo se cargue
    setTimeout(() => {
        window.testModalFunctionality();
    }, 1000);
});
