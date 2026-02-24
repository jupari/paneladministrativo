// SCRIPT DE TESTING PARA MODAL DE CLIENTES
// Permite testear la funcionalidad del modal desde la consola

window.testModalFunctionality = function() {
    console.log('🧪 === TESTING MODAL FUNCTIONALITY ===');

    // Test 1: Verificar existencia de elementos
    console.log('1. Verificando elementos del DOM:');
    const modal = document.getElementById('ModalCliente');
    const btnRegCli = document.querySelector('[onclick="regCli()"]');

    console.log('   - Modal ModalCliente:', !!modal ? '✅ Existe' : '❌ No encontrado');
    console.log('   - Botón regCli():', !!btnRegCli ? '✅ Existe' : '❌ No encontrado');
    console.log('   - Bootstrap modal:', typeof $.fn.modal !== 'undefined' ? '✅ Disponible' : '❌ No disponible');

    // Test 2: Verificar funciones
    console.log('2. Verificando funciones:');
    console.log('   - regCli():', typeof window.regCli === 'function' ? '✅ Disponible' : '❌ No disponible');
    console.log('   - upCli():', typeof window.upCli === 'function' ? '✅ Disponible' : '❌ No disponible');
    console.log('   - openClientModal():', typeof window.openClientModal === 'function' ? '✅ Disponible' : '❌ No disponible');
    console.log('   - ClienteModalSteps:', typeof window.ClienteModalSteps === 'function' ? '✅ Disponible' : '❌ No disponible');

    // Test 3: Verificar compatibilidad Bootstrap
    console.log('3. Verificando compatibilidad Bootstrap:');
    console.log('   - jQuery versión:', $.fn.jquery);
    console.log('   - Bootstrap versión:', $().tooltip ? '✅ Bootstrap cargado' : '❌ Bootstrap no detectado');

    // Test 4: Test de apertura del modal
    console.log('4. Testing apertura del modal:');
    try {
        if (window.openClientModal) {
            console.log('   - Intentando abrir modal...');
            // No abrir realmente, solo verificar que la función existe
            console.log('   - ✅ Función openClientModal disponible');
        }
    } catch (error) {
        console.log('   - ❌ Error:', error.message);
    }

    console.log('🎯 === TEST COMPLETADO ===');
    return true;
};

// Función para simular click en el botón de registro
window.simulateRegCliClick = function() {
    console.log('🖱️ Simulando click en botón de registro...');
    const btn = document.querySelector('[onclick="regCli()"]');
    if (btn) {
        btn.click();
        console.log('✅ Click simulado');
    } else {
        console.log('❌ Botón no encontrado');
    }
};

// Función para verificar estado del modal
window.checkModalState = function() {
    const modal = document.getElementById('ModalCliente');
    if (!modal) {
        console.log('❌ Modal no encontrado');
        return;
    }

    console.log('📊 Estado del modal:');
    console.log('   - Visible:', modal.style.display !== 'none');
    console.log('   - Clase show:', modal.classList.contains('show'));
    console.log('   - Clase fade:', modal.classList.contains('fade'));
    console.log('   - Clase modal:', modal.classList.contains('modal'));
    console.log('   - Z-index:', modal.style.zIndex);
    console.log('   - Backdrop:', document.querySelector('.modal-backdrop') ? 'Presente' : 'Ausente');
};

// Auto-test al cargar
$(document).ready(function() {
    // Esperar un poco para que todo se cargue
    setTimeout(() => {
        console.log('🚀 Auto-testing modal functionality...');
        window.testModalFunctionality();
    }, 1000);
});

console.log('🧪 Modal Testing Script cargado');
