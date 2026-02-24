// Script de debugging para botones de navegación
console.log('=== DEBUGGING BOTONES DE NAVEGACIÓN ===');

$(document).ready(function() {
    // Debug cuando se abre el modal
    $('#ModalCliente').on('shown.bs.modal', function() {
        console.log('Modal abierto - verificando botones');

        setTimeout(() => {
            const nextBtn = document.getElementById('next-step-btn');
            const prevBtn = document.getElementById('prev-step-btn');

            console.log('Botón siguiente encontrado:', nextBtn);
            console.log('Botón anterior encontrado:', prevBtn);

            // Simplificar debug - no usar getEventListeners
            console.log('Los botones están listos para usar');

            // Verificar si los botones son realmente clickeables
            setTimeout(() => {
                const nextBtn = document.getElementById('next-step-btn');
                const prevBtn = document.getElementById('prev-step-btn');

                if (nextBtn) {
                    console.log('VERIFICACIÓN NEXT BUTTON:');
                    console.log('- Visible:', nextBtn.offsetParent !== null);
                    console.log('- Estilo display:', window.getComputedStyle(nextBtn).display);
                    console.log('- Estilo pointer-events:', window.getComputedStyle(nextBtn).pointerEvents);
                    console.log('- Posición Z-index:', window.getComputedStyle(nextBtn).zIndex);
                    console.log('- Disabled:', nextBtn.disabled);
                    console.log('- Rect:', nextBtn.getBoundingClientRect());
                }

                if (prevBtn) {
                    console.log('VERIFICACIÓN PREV BUTTON:');
                    console.log('- Visible:', prevBtn.offsetParent !== null);
                    console.log('- Estilo display:', window.getComputedStyle(prevBtn).display);
                    console.log('- Estilo pointer-events:', window.getComputedStyle(prevBtn).pointerEvents);
                    console.log('- Posición Z-index:', window.getComputedStyle(prevBtn).zIndex);
                    console.log('- Disabled:', prevBtn.disabled);
                    console.log('- Rect:', prevBtn.getBoundingClientRect());
                }

                // PROBAR CLICK PROGRAMÁTICO después de 2 segundos
                setTimeout(() => {
                    console.log('🔥 PROBANDO CLICK PROGRAMÁTICO EN NEXT BUTTON 🔥');
                    if (nextBtn) {
                        nextBtn.click();
                    }
                }, 2000);
            }, 1000);
        }, 500);
    });

    // Debug clicks en cualquier botón del modal - AGRESIVO
    $('#ModalCliente').on('click', 'button', function(e) {
        console.log('=== CLICK DETECTADO ===');
        console.log('Botón ID:', this.id);
        console.log('Clases:', this.className);

        // Si es un botón de navegación, DETENER TODO
        if (this.id === 'next-step-btn' || this.id === 'prev-step-btn') {
            console.log('BOTÓN DE NAVEGACIÓN - DETENIENDO EVENTO');
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            // Manejar la navegación
            if (this.id === 'next-step-btn') {
                console.log('Ejecutando NEXT STEP');
                if (window.handleNextStep) {
                    window.handleNextStep(e);
                } else if (window.clienteModalSteps) {
                    window.clienteModalSteps.nextStep(e);
                }
            } else if (this.id === 'prev-step-btn') {
                console.log('Ejecutando PREV STEP');
                if (window.handlePrevStep) {
                    window.handlePrevStep(e);
                } else if (window.clienteModalSteps) {
                    window.clienteModalSteps.prevStep(e);
                }
            }

            // IMPORTANTE: Retornar false para detener completamente la propagación
            return false;
        }

        console.log('Click normal en:', this.id);
    });

    // Event listeners DIRECTOS para los botones
    $(document).on('click', '#next-step-btn', function(e) {
        console.log('💥 CLICK DIRECTO EN NEXT-STEP-BTN 💥');
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (window.clienteModalSteps) {
            console.log('Ejecutando nextStep directamente');
            window.clienteModalSteps.nextStep(e);
        }
        return false;
    });

    $(document).on('click', '#prev-step-btn', function(e) {
        console.log('💥 CLICK DIRECTO EN PREV-STEP-BTN 💥');
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (window.clienteModalSteps) {
            console.log('Ejecutando prevStep directamente');
            window.clienteModalSteps.prevStep(e);
        }
        return false;
    });

    // Detectar cuando el modal se va a cerrar
    $('#ModalCliente').on('hide.bs.modal', function(e) {
        console.log('🚨 MODAL SE VA A CERRAR 🚨');
        console.log('Evento que lo causó:', e);
        console.log('Target:', e.target);
        console.log('Tipo de evento:', e.type);

        // Para debugging, temporalmente prevenir el cierre
        // e.preventDefault();
        // console.log('Cierre del modal PREVENIDO para debugging');
        // return false;
    });
});
