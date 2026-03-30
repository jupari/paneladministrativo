/**
 * Sistema Progresivo de Pasos y Sincronización para Cotizaciones
 * Maneja la lógica del flujo de pasos, sincronización de resumen y controles de navegación
 */

// Variables globales del sistema progresivo
let cotizacionGuardada = false;
let pasoActual = 0;
let totalPasos = 6; // Total de pasos en el sistema progresivo (nuevo flujo)

/**
 * Inicializar el sistema progresivo
 */
function inicializarSistemaProgresivo() {
    console.log('Sistema progresivo deshabilitado - todas las secciones visibles');

    try {
        // Obtener el ID de cotización de la URL
        const urlParams = new URLSearchParams(window.location.search);
        const cotizacionIdFromUrl = urlParams.get('id');

        // También verificar el input hidden si existe
        const $cotizacionIdInput = $('input[name="id"]');
        const cotizacionId = cotizacionIdFromUrl || ($cotizacionIdInput.length ? $cotizacionIdInput.val() : null);

        const $clienteSelect = $('#cliente_id');
        const clienteId = $clienteSelect.length ? $clienteSelect.val() : null;

        console.log('Verificando estado:', { cotizacionId, clienteId });

        if (cotizacionId && cotizacionId !== '') {
            // Es edición - mostrar todas las secciones
            cotizacionGuardada = true;
            mostrarTodasLasSecciones();
            mostrarEstadoGuardado('Editando cotización existente. Todas las secciones están disponibles.', 'edit');
            console.log('Modo edición activado');
        } else {
            // Es nueva cotización - sistema progresivo
            cotizacionGuardada = false;
            pasoActual = 0;
            ocultarTodasLasSecciones();
            console.log('Modo nueva cotización activado');
        }
    } catch (error) {
        console.error('Error al verificar estado de cotización:', error);
        // En caso de error, mostrar todas las secciones por seguridad
        mostrarTodasLasSecciones();
    }

    // Configurar listeners para actualización reactiva del progreso
    configurarListenersProgreso();

    // Actualización inicial del progreso después de la configuración
    setTimeout(() => {
        if (typeof actualizarProgresoCompletion === 'function') {
            actualizarProgresoCompletion();
            console.log('Actualización inicial del progreso ejecutada');
        }
    }, 200);
}

/**
 * Configurar listeners para actualización reactiva del progreso
 */
function configurarListenersProgreso() {
    // Listener para cambios en el cliente
    $(document).on('change', '#cliente_id', function() {
        console.log('Cliente cambiado:', $(this).val());
        setTimeout(() => {
            if (typeof actualizarProgresoCompletion === 'function') {
                actualizarProgresoCompletion();
            }
        }, 100);
    });

    // Listener para cambios en el estado
    $(document).on('change', '#estado_id', function() {
        const estadoSelect = document.getElementById('estado_id');
        const estadoValue = $(this).val();
        let estadoTexto = '';

        if (estadoSelect && estadoValue) {
            const selectedOption = estadoSelect.querySelector(`option[value="${estadoValue}"]`);
            if (selectedOption) {
                estadoTexto = selectedOption.textContent;
            }
        }

        console.log('Estado cambiado:', estadoTexto || 'Sin selección');

        setTimeout(() => {
            if (typeof actualizarProgresoCompletion === 'function') {
                actualizarProgresoCompletion();
            }
        }, 100);
    });

    // Listener para cuando se agreguen productos (usando MutationObserver)
    const targetNode = document.getElementById('tbodyProductosGuardados');
    if (targetNode) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Verificar si se agregó una fila con data-producto-id
                    const hasProductRow = Array.from(mutation.addedNodes).some(node =>
                        node.nodeType === Node.ELEMENT_NODE &&
                        node.hasAttribute &&
                        node.hasAttribute('data-producto-id')
                    );

                    if (hasProductRow) {
                        console.log('Producto agregado, actualizando progreso');
                        setTimeout(() => {
                            if (typeof actualizarProgresoCompletion === 'function') {
                                actualizarProgresoCompletion();
                            }
                        }, 200);
                    }
                }
            });
        });

        observer.observe(targetNode, {
            childList: true,
            subtree: true
        });

        console.log('Observer configurado para tabla de productos');
    }
}

/**
 * Mostrar estado guardado
 */
function mostrarEstadoGuardado(mensaje, tipo = 'success') {
    const estadoElement = document.getElementById('estadoGuardado');
    const mensajeElement = document.getElementById('mensajeEstado');
    const iconoElement = document.getElementById('iconoEstado');

    if (estadoElement && mensajeElement && iconoElement) {
        estadoElement.classList.remove('d-none');
        mensajeElement.textContent = mensaje;

        if (tipo === 'edit') {
            iconoElement.className = 'fas fa-edit text-primary mr-2';
            estadoElement.className = 'alert alert-info';
        } else {
            iconoElement.className = 'fas fa-check-circle text-success mr-2';
            estadoElement.className = 'alert alert-success';
        }
    }
}

/**
 * Configurar eventos del sistema progresivo
 */
function configurarEventosSistemaProgresivo() {
    if (!cotizacionGuardada) {
        const btnGuardar = document.getElementById('agregarCotizacion');
        if (btnGuardar) {
            btnGuardar.addEventListener('click', function(e) {
                setTimeout(() => {
                    const $clienteSelect = $('#cliente_id');
                    const clienteId = $clienteSelect.length ? $clienteSelect.val() : null;

                    if (clienteId && clienteId !== '') {
                        cotizacionGuardada = true;
                        habilitarSistemaProgresivo();
                    }
                }, 2000);
            });
        }
    }
}

/**
 * Habilitar sistema progresivo tras guardado
 */
function habilitarSistemaProgresivo() {
    mostrarEstadoGuardado('Información básica guardada exitosamente. Complete los siguientes pasos.');
    // Siempre mostrar el paso de items como primer paso después de guardar la cabecera
    pasoActual = 1;
    mostrarPaso(pasoActual);
    configurarNavegacion();
}

/**
 * Ocultar todas las secciones
 */
function ocultarTodasLasSecciones() {
    $('.section-step').each(function() {
        console.log('paso--->', $(this).attr('id'));
        //Section logic here if needed
        $(this).addClass('d-none').removeClass('completed');
    });

}
/*
Mostrar todas las secciones
 */
function mostrarTodasLasSecciones() {
    try {
        $('.section-step').each(function(index) {
            $(this).removeClass('d-none').addClass('completed');

            const $statusIcon = $(this).find('.step-status i');
            if ($statusIcon.length) {
                $statusIcon.attr('class', 'fas fa-check-circle text-success');
            }
        });

        pasoActual = totalPasos;
        actualizarProgreso();
    } catch (error) {
        console.error('Error en mostrarTodasLasSecciones:', error);
    }
}

/**
 * Mostrar paso específico
 */
function mostrarPaso(numeroPaso) {
    if (typeof totalPasos === 'undefined' || numeroPaso < 1 || numeroPaso > totalPasos) return;

    pasoActual = numeroPaso;
    ocultarTodasLasSecciones();

    const seccionActual = document.getElementById(`paso-${getPasoNombre(numeroPaso)}`);
    if (seccionActual) {
        seccionActual.classList.remove('d-none');
        seccionActual.classList.add('slide-in');

        const statusIcon = seccionActual.querySelector('.step-status i');
        if (statusIcon) {
            statusIcon.className = 'fas fa-edit text-info';
        }
    }

    actualizarProgreso();
    actualizarBotonesNavegacion();
}

/**
 * Obtener nombre del paso por número
 */
function getPasoNombre(numero) {
    // Nuevo orden de pasos:
    // 1: items, 2: productos, 3: impuestos, 4: observaciones, 5: condiciones
    const nombres = {
        1: 'items',
        2: 'productos',
        3: 'impuestos',
        4: 'observaciones',
        5: 'condiciones',
        6: 'condiciones' // Para evitar errores si se llama al último paso
    };
    return nombres[numero];
}

/**
 * Configurar navegación entre pasos
 */
function configurarNavegacion() {
    const botonesNav = document.getElementById('botonesNavegacion');
    if (botonesNav) {
        botonesNav.style.display = 'flex';
    }

    const btnSiguiente = document.getElementById('btnSiguiente');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnOmitir = document.getElementById('btnOmitir');

    if (btnSiguiente) {
        btnSiguiente.addEventListener('click', function() {
            if (pasoActual < totalPasos) {
                mostrarPaso(pasoActual + 1);
            }
        });
    }

    if (btnAnterior) {
        btnAnterior.addEventListener('click', function() {
            if (pasoActual > 1) {
                mostrarPaso(pasoActual - 1);
            }
        });
    }

    if (btnOmitir) {
        btnOmitir.addEventListener('click', function() {
            if (pasoActual < totalPasos) {
                mostrarPaso(pasoActual + 1);
            }
        });
    }
}

/**
 * Actualizar barra de progreso
 */
function actualizarProgreso() {
    try {
        const barraProgreso = document.getElementById('barraProgreso');
        const pasoActualElement = document.getElementById('pasoActual');

        if (barraProgreso && typeof totalPasos !== 'undefined' && totalPasos > 0) {
            const porcentaje = (pasoActual / totalPasos) * 100;
            barraProgreso.style.width = porcentaje + '%';
        }

        if (pasoActualElement && typeof totalPasos !== 'undefined') {
            pasoActualElement.textContent = `Paso ${pasoActual} de ${totalPasos}`;
        }
    } catch (error) {
        console.error('Error en actualizarProgreso:', error);
    }
}

/**
 * Actualizar estado de botones de navegación
 */
function actualizarBotonesNavegacion() {
    const btnAnterior = document.getElementById('btnAnterior');
    const btnSiguiente = document.getElementById('btnSiguiente');
    const btnOmitir = document.getElementById('btnOmitir');

    if (btnAnterior) {
        btnAnterior.disabled = (pasoActual === 1);
    }

    if (btnSiguiente) {
        if (pasoActual === totalPasos) {
            btnSiguiente.textContent = 'Finalizar';
            if (btnOmitir) btnOmitir.style.display = 'none';
        } else {
            btnSiguiente.innerHTML = 'Siguiente<i class="fas fa-chevron-right ml-2"></i>';
            if (btnOmitir) btnOmitir.style.display = 'inline-block';
        }
    }
}

/**
 * Funciones para mejorar UX del resumen financiero
 */
function scrollToResumenDetallado() {
    const resumen = document.getElementById('resumen-totales-cotizacion');
    if (resumen) {
        resumen.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
}

function toggleResumenDetalle() {
    const content = document.getElementById('resumen-detalle-content');
    const button = document.getElementById('btn-toggle-resumen');

    if (content && button) {
        const icon = button.querySelector('i');

        if (content.style.display === 'none') {
            content.style.display = 'block';
            if (icon) icon.className = 'fas fa-eye';
            button.title = 'Ocultar detalle';
        } else {
            content.style.display = 'none';
            if (icon) icon.className = 'fas fa-eye-slash';
            button.title = 'Mostrar detalle';
        }
    }
}

/**
 * Actualizar resumen sticky
 */
function actualizarResumenSticky(totales) {

    if (totales) {
        const stickySubtotal = document.getElementById('sticky-subtotal');
        const stickyDescuentos = document.getElementById('sticky-descuentos');
        const stickyImpuestos = document.getElementById('sticky-impuestos');
        const stickyTotal = document.getElementById('sticky-total');

        if (stickySubtotal) stickySubtotal.textContent = formatCurrency(totales.subtotal || 0);
        if (stickyDescuentos) stickyDescuentos.textContent = formatCurrency(totales.descuento || 0);
        if (stickyImpuestos) stickyImpuestos.textContent = formatCurrency(totales.impuestos || totales.total_impuesto || 0);
        if (stickyTotal) stickyTotal.textContent = formatCurrency(totales.total || 0);

        actualizarProgresoCompletion();
        console.log('Resumen sticky actualizado:', totales);
    }
}

/**
 * Sincronizar desde los campos del DOM
 */
function sincronizarResumenStickyDesdeDOM() {
    try {
        // Campos ocultos como fallback
        const hiddenSubtotal = document.getElementById('subtotal');
        const hiddenDescuento = document.getElementById('descuento');
        const hiddenImpuesto = document.getElementById('total_impuesto');
        const hiddenTotal = document.getElementById('total');

        const finalSubtotal = hiddenSubtotal ? parseFloat(hiddenSubtotal.value) || 0 : 0;
        const finalDescuento =hiddenDescuento ? parseFloat(hiddenDescuento.value) || 0 : 0;
        const finalImpuesto = hiddenImpuesto ? parseFloat(hiddenImpuesto.value) || 0 : 0;
        const finalTotal = hiddenTotal ? parseFloat(hiddenTotal.value) || 0 : 0;


        // Actualizar sticky
        const stickySubtotal = document.getElementById('sticky-subtotal');
        const stickyDescuentos = document.getElementById('sticky-descuentos');
        const stickyImpuestos = document.getElementById('sticky-impuestos');
        const stickyTotal = document.getElementById('sticky-total');

        if (stickySubtotal) stickySubtotal.textContent = formatCurrency(finalSubtotal);
        if (stickyDescuentos) stickyDescuentos.textContent = formatCurrency(finalDescuento);
        if (stickyImpuestos) stickyImpuestos.textContent = formatCurrency(finalImpuesto);
        if (stickyTotal) stickyTotal.textContent = formatCurrency(finalTotal);

        const totales = {
            subtotal: finalSubtotal,
            descuento: finalDescuento,
            impuestos: finalImpuesto,
            total: finalTotal
        };

        actualizarResumenSticky(totales);
        console.log('Sincronización automática completada:', totales);
    } catch (error) {
        console.error('Error en sincronización sticky:', error);
    }
}

/**
 * Actualizar progreso de completitud
 */
function actualizarProgresoCompletion() {
    try {
        const clienteValue = $('#cliente_id').val();
        const estadoValue = $('#estado_id').val();

        // Contar solo productos reales (filas con data-producto-id)
        const productos = $('#tbodyProductosGuardados tr[data-producto-id]').length;

        let progreso = 0;

        // Verificar que sea una cotización existente
        const cotizacionId = document.getElementById('id')?.value;
        const isNewCotizacion = !cotizacionId || cotizacionId === '' || cotizacionId === 'null';

        // Verificar que el cliente sea válido (no vacío, no null, no undefined, no "0")
        const tieneClienteValido = clienteValue &&
                                 clienteValue !== '' &&
                                 clienteValue !== '0' &&
                                 clienteValue !== 'null' &&
                                 clienteValue !== 'undefined';

        // Obtener el nombre del estado actual
        const estadoSelect = document.getElementById('estado_id');
        let estadoTexto = '';
        if (estadoSelect && estadoValue) {
            const selectedOption = estadoSelect.querySelector(`option[value="${estadoValue}"]`);
            if (selectedOption) {
                estadoTexto = selectedOption.textContent.toLowerCase();
            }
        }

        // Si el estado indica finalización (terminado, anulado, finalizado, etc.), progreso automático al 100%
        const estadosFinales = ['terminado', 'anulado', 'finalizado', 'completado', 'cerrado', 'cancelado'];
        const esEstadoFinal = estadosFinales.some(estado => estadoTexto.includes(estado));

        if (esEstadoFinal) {
            progreso = 100;
        } else {
            // Lógica normal de progreso para otros estados
            // Para cotizaciones nuevas, ser muy estricto
            if (isNewCotizacion) {
                // Solo contar progreso si realmente hay datos válidos
                if (tieneClienteValido) {
                    progreso += 30;
                }
                if (productos > 0) {
                    progreso += 45;
                }
            } else {
                // En cotizaciones existentes, usar la lógica normal
                if (tieneClienteValido) {
                    progreso += 30;
                }
                if (productos > 0) {
                    progreso += 45;
                }
            }

            progreso = Math.min(progreso, 100);
        }

        const progressBar = document.getElementById('cotization-progress');
        const progressText = document.getElementById('progress-text');
        const statusBadge = document.getElementById('status-badge');
        const cotizationStatus = document.getElementById('cotization-status');

        if (progressBar) progressBar.style.width = progreso + '%';
        if (progressText) progressText.textContent = progreso + '% completado';

        if (statusBadge) {
            // Primero verificar si hay un estado seleccionado
            if (!estadoValue || estadoValue === '' || estadoTexto === 'seleccione un estado' || estadoTexto === '') {
                // Si no hay estado seleccionado, mostrar estado por defecto
                statusBadge.className = 'badge bg-secondary';
                statusBadge.textContent = 'Borrador';
            } else if (progreso === 100) {
                // Texto específico según el estado para progreso 100%
                const estadosFinales = ['terminado', 'anulado', 'finalizado', 'completado', 'cerrado', 'cancelado'];

                if (estadoTexto.includes('terminado') || estadoTexto.includes('completado') || estadoTexto.includes('finalizado')) {
                    statusBadge.className = 'badge bg-success';
                    statusBadge.textContent = 'Terminada';
                } else if (estadoTexto.includes('anulado') || estadoTexto.includes('cancelado')) {
                    statusBadge.className = 'badge bg-danger';
                    statusBadge.textContent = 'Anulada';
                } else if (estadoTexto.includes('cerrado')) {
                    statusBadge.className = 'badge bg-secondary';
                    statusBadge.textContent = 'Cerrada';
                } else {
                    statusBadge.className = 'badge bg-success';
                    statusBadge.textContent = 'Completada';
                }
            } else {
                // Para estados válidos seleccionados, usar el texto del estado directamente
                if (estadoTexto.includes('borrador')) {
                    statusBadge.className = 'badge bg-secondary';
                    statusBadge.textContent = 'Borrador';
                } else if (estadoTexto.includes('proceso')) {
                    statusBadge.className = 'badge bg-warning';
                    statusBadge.textContent = 'En Proceso';
                } else if (estadoTexto.includes('enviado') || estadoTexto.includes('enviad')) {
                    statusBadge.className = 'badge bg-info';
                    statusBadge.textContent = 'Enviada';
                } else if (estadoTexto.includes('aprobado') || estadoTexto.includes('aprobad')) {
                    statusBadge.className = 'badge bg-success';
                    statusBadge.textContent = 'Aprobada';
                } else if (estadoTexto.includes('rechazado') || estadoTexto.includes('rechazad')) {
                    statusBadge.className = 'badge bg-danger';
                    statusBadge.textContent = 'Rechazada';
                } else if (estadoTexto.includes('vencido') || estadoTexto.includes('vencid')) {
                    statusBadge.className = 'badge bg-dark';
                    statusBadge.textContent = 'Vencida';
                } else {
                    // Fallback basado en progreso
                    if (progreso >= 75) {
                        statusBadge.className = 'badge bg-info';
                        statusBadge.textContent = 'Avanzada';
                    } else if (progreso > 30) {
                        statusBadge.className = 'badge bg-warning';
                        statusBadge.textContent = 'En Progreso';
                    } else if (progreso > 0) {
                        statusBadge.className = 'badge bg-secondary';
                        statusBadge.textContent = 'Iniciando';
                    } else {
                        statusBadge.className = 'badge bg-light text-dark';
                        statusBadge.textContent = 'Sin iniciar';
                    }
                }
            }
        }

        // Actualizar también el elemento cotization-status
        if (cotizationStatus) {
            let statusText = 'En progreso'; // Valor por defecto

            if (!estadoValue || estadoValue === '' || estadoTexto === 'seleccione un estado' || estadoTexto === '') {
                statusText = 'Borrador';
            } else if (progreso === 100) {
                if (estadoTexto.includes('terminado') || estadoTexto.includes('completado') || estadoTexto.includes('finalizado')) {
                    statusText = 'Terminada';
                } else if (estadoTexto.includes('anulado') || estadoTexto.includes('cancelado')) {
                    statusText = 'Anulada';
                } else if (estadoTexto.includes('cerrado')) {
                    statusText = 'Cerrada';
                } else {
                    statusText = 'Completada';
                }
            } else {
                // Usar el texto del estado directamente
                if (estadoTexto.includes('borrador')) {
                    statusText = 'Borrador';
                } else if (estadoTexto.includes('proceso')) {
                    statusText = 'En Proceso';
                } else if (estadoTexto.includes('enviado') || estadoTexto.includes('enviad')) {
                    statusText = 'Enviada';
                } else if (estadoTexto.includes('aprobado') || estadoTexto.includes('aprobad')) {
                    statusText = 'Aprobada';
                } else if (estadoTexto.includes('rechazado') || estadoTexto.includes('rechazad')) {
                    statusText = 'Rechazada';
                } else if (estadoTexto.includes('vencido') || estadoTexto.includes('vencid')) {
                    statusText = 'Vencida';
                } else if (estadoTexto.includes('terminado')) {
                    statusText = 'Terminada';
                } else if (estadoTexto.includes('anulado')) {
                    statusText = 'Anulada';
                } else {
                    // Mantener valor por defecto o basado en progreso
                    statusText = 'En progreso';
                }
            }

            cotizationStatus.textContent = statusText;
        }

        console.log('Progreso actualizado:', {
            clienteValue: clienteValue || 'vacío',
            estadoValue: estadoValue || 'vacío',
            estadoTexto: estadoTexto || 'vacío',
            tieneClienteValido,
            productos,
            progreso: progreso + '%',
            isNewCotizacion,
            cotizacionId: cotizacionId || 'sin ID',
            esEstadoFinal: estadosFinales.some(estado => estadoTexto.includes(estado))
        });

    } catch (error) {
        console.error('Error en actualización de progreso:', error);
    }
}

 /* Formatear número como moneda colombiana
 * @param {number} amount - Cantidad a formatear
 * @returns {string} - Texto formateado
 */

// === FUNCIONES DE DEBUG ===

/**
 * Función de debug para verificar el estado del progreso
 * Usar en consola: debugProgreso()
 */
function debugProgreso() {
    const clienteValue = $('#cliente_id').val();
    const estadoValue = $('#estado_id').val();
    const productos = $('#tbodyProductosGuardados tr[data-producto-id]').length;
    const cotizacionId = document.getElementById('id')?.value;
    const isNewCotizacion = !cotizacionId || cotizacionId === '' || cotizacionId === 'null';

    const tieneClienteValido = clienteValue &&
                             clienteValue !== '' &&
                             clienteValue !== '0' &&
                             clienteValue !== 'null' &&
                             clienteValue !== 'undefined';

    // Obtener el nombre del estado actual
    const estadoSelect = document.getElementById('estado_id');
    let estadoTexto = '';
    if (estadoSelect && estadoValue) {
        const selectedOption = estadoSelect.querySelector(`option[value="${estadoValue}"]`);
        if (selectedOption) {
            estadoTexto = selectedOption.textContent.toLowerCase();
        }
    }

    const estadosFinales = ['terminado', 'anulado', 'finalizado', 'completado', 'cerrado', 'cancelado'];
    const esEstadoFinal = estadosFinales.some(estado => estadoTexto.includes(estado));
    const progresoNormal = (tieneClienteValido ? 30 : 0) + (productos > 0 ? 45 : 0);
    const progresoFinal = esEstadoFinal ? 100 : progresoNormal;

    console.table({
        'Cliente Value': clienteValue || 'VACÍO',
        'Cliente Válido': tieneClienteValido ? 'SÍ' : 'NO',
        'Estado Value': estadoValue || 'VACÍO',
        'Estado Texto': estadoTexto || 'VACÍO',
        'Es Estado Final': esEstadoFinal ? 'SÍ (100%)' : 'NO',
        'Productos': productos,
        'Cotización ID': cotizacionId || 'VACÍO',
        'Es Nueva': isNewCotizacion ? 'SÍ' : 'NO',
        'Progreso Cliente': tieneClienteValido ? '30%' : '0%',
        'Progreso Productos': productos > 0 ? '45%' : '0%',
        'Progreso Normal': progresoNormal + '%',
        'Progreso Final': progresoFinal + '%'
    });

    // Forzar actualización del progreso
    if (typeof actualizarProgresoCompletion === 'function') {
        actualizarProgresoCompletion();
    }
}

/**
 * Función para resetear el progreso manualmente
 * Usar en consola: resetProgreso()
 */
function resetProgreso() {
    const progressBar = document.getElementById('cotization-progress');
    const progressText = document.getElementById('progress-text');
    const statusBadge = document.getElementById('status-badge');

    if (progressBar) progressBar.style.width = '0%';
    if (progressText) progressText.textContent = '0% completado';
    if (statusBadge) {
        statusBadge.className = 'badge bg-light text-dark';
        statusBadge.textContent = 'Sin iniciar';
    }

    console.log('Progreso reseteado a 0%');
}

/**
 * Función para probar estados terminales
 * Usar en consola: probarEstadoTerminal('terminado') o probarEstadoTerminal('anulado')
 */
function probarEstadoTerminal(estado) {
    const estadoSelect = document.getElementById('estado_id');
    if (!estadoSelect) {
        console.error('No se encontró el select de estados');
        return;
    }

    const opcionEstado = Array.from(estadoSelect.options).find(option =>
        option.textContent.toLowerCase().includes(estado.toLowerCase())
    );

    if (opcionEstado) {
        estadoSelect.value = opcionEstado.value;
        console.log(`Estado cambiado a: ${opcionEstado.textContent}`);

        // Disparar evento change
        estadoSelect.dispatchEvent(new Event('change'));

        // Forzar actualización del progreso
        setTimeout(() => {
            if (typeof actualizarProgresoCompletion === 'function') {
                actualizarProgresoCompletion();
            }
        }, 100);

        console.log('El progreso debería estar en 100%');
    } else {
        console.error(`No se encontró el estado: ${estado}`);
        console.log('Estados disponibles:', Array.from(estadoSelect.options).map(o => o.textContent));
    }
}

// Hacer funciones de debug globalmente disponibles
if (typeof window !== 'undefined') {
    window.debugProgreso = debugProgreso;
    window.resetProgreso = resetProgreso;
    window.probarEstadoTerminal = probarEstadoTerminal;
}
function formatCurrency(amount) {
    const num = Number(amount) || 0;
    // Para números grandes sin decimales, no usar fractions
    if (num >= 1000 && num % 1 === 0) {
        return '$' + num.toLocaleString('es-CO');
    }
    // Para números pequeños o con decimales, usar 2 decimales
    return '$' + num.toLocaleString('es-CO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Inicializar resumen sticky y observadores
 */
function inicializarResumenSticky() {
    console.log('🚀 Inicializando resumen sticky...');

    setTimeout(() => {
        console.log('🔄 Primera sincronización sticky');
        sincronizarResumenStickyDesdeDOM();
    }, 1000);

    setTimeout(() => {
        console.log('🔄 Segunda sincronización sticky');
        sincronizarResumenStickyDesdeDOM();
    }, 3000);

    interceptarActualizacionesTotales();
    configurarTriggersCotizacion();

    // Sincronización periódica inteligente
    let syncInterval = setInterval(() => {
        // Incrementar contador siempre que se ejecute el intervalo
        window.sync_count = (window.sync_count || 0) + 1;

        console.log(`🔄 Sync automático #${window.sync_count}`);
        sincronizarResumenStickyDesdeDOM();

        // Detener después de 8 intentos para evitar consumo excesivo de recursos
        if (window.sync_count >= 3) {
            console.log('✅ Sincronización sticky completada, deteniendo monitoreo automático');
            clearInterval(syncInterval);
        }
    }, 12000); // Reducido a 12 segundos para mayor responsividad

    console.log('✅ Resumen sticky inicializado');
}

/**
 * Interceptar funciones de actualización de totales
 */
function interceptarActualizacionesTotales() {
    const funcionesTotales = [
        'actualizarTotalesCompletos',
        'actualizarTotalesEnVista',
        'actualizarTotalesRenovadosOptimizado',
        'actualizarTotales',
        'actualizarTotalesEnVistaRenovada',
        'actualizarTotalesManualmente'
    ];

    funcionesTotales.forEach(nombreFuncion => {
        const funcionOriginal = window[nombreFuncion];
        if (funcionOriginal && typeof funcionOriginal === 'function') {
            window[nombreFuncion] = function(...args) {
                const resultado = funcionOriginal.apply(this, args);

                setTimeout(() => {
                    console.log(`💰 Sincronizando sticky después de ${nombreFuncion}`);
                    sincronizarResumenStickyDesdeDOM();
                }, 200);

                return resultado;
            };
            console.log(`✅ Función ${nombreFuncion} interceptada correctamente`);
        }
    });

    // Observar cambios en elementos de totales
    const observarCamposTotales = () => {
        const camposObservables = [
            'display-subtotal-valor',
            'display-descuento-valor',
            'display-impuesto-valor',
            'display-total-valor'
        ];

        camposObservables.forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento) {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach(mutation => {
                        if (mutation.type === 'childList' || mutation.type === 'characterData') {
                            setTimeout(() => {
                                console.log(`🔄 Cambio detectado en ${id}, sincronizando sticky`);
                                sincronizarResumenStickyDesdeDOM();
                            }, 100);
                        }
                    });
                });

                observer.observe(elemento, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });

                console.log(`👀 Observer configurado para ${id}`);
            }
        });
    };

    setTimeout(observarCamposTotales, 1500);
}

/**
 * Configurar triggers de cotización
 */
function configurarTriggersCotizacion() {
    const originalCargarDocumento = window.cargarDocumento;
    if (originalCargarDocumento) {
        window.cargarDocumento = function(...args) {
            const resultado = originalCargarDocumento.apply(this, args);

            setTimeout(() => {
                console.log('📄 Documento cargado, sincronizando sticky y progreso');
                sincronizarResumenStickyDesdeDOM();

                // Actualizar progreso después de cargar cotización
                if (typeof actualizarProgresoCompletion === 'function') {
                    actualizarProgresoCompletion();
                }
            }, 2000);

            return resultado;
        };
        console.log('✅ cargarDocumento interceptado');
    }

    window.forzarSyncSticky = function() {
        console.log('🔧 SYNC MANUAL FORZADO');
        sincronizarResumenStickyDesdeDOM();
    };
}

// Funciones de debug globales
window.debugStickyResumen = function() {
    console.log('🔍 DEBUG DEL RESUMEN STICKY');
    console.log('===========================');

    const elementos = {
        sticky: ['sticky-subtotal', 'sticky-descuento', 'sticky-impuesto', 'sticky-total'],
        display: ['display-subtotal-valor', 'display-descuento-valor', 'display-impuesto-valor', 'display-total-valor'],
        hidden: ['subtotal', 'descuento', 'total_impuesto', 'total']
    };

    Object.keys(elementos).forEach(tipo => {
        console.log(`📋 ELEMENTOS ${tipo.toUpperCase()}:`);
        elementos[tipo].forEach(id => {
            const elemento = document.getElementById(id);
            const valor = elemento ? (tipo === 'hidden' ? elemento.value : elemento.textContent) : 'NO ENCONTRADO';
            console.log(`   ${id}:`, valor);
        });
    });

    const cotizacionIdInput = document.getElementById('id');
    console.log('📋 ID COTIZACIÓN:');
    console.log('   Input#id:', cotizacionIdInput ? cotizacionIdInput.value : 'NO ENCONTRADO');

    console.log('🔄 Ejecutando sincronización manual...');
    sincronizarResumenStickyDesdeDOM();
};

window.forzarActualizacionCompleta = function() {
    console.log('🚀 FORZANDO ACTUALIZACIÓN COMPLETA');

    if (typeof window.actualizarTotalesCompletos === 'function') {
        window.actualizarTotalesCompletos();
    }

    setTimeout(() => {
        sincronizarResumenStickyDesdeDOM();
    }, 1000);

    setTimeout(() => {
        window.debugStickyResumen();
    }, 2000);
};

// NOTA: La auto-inicialización ha sido deshabilitada
// Ahora es manejada por documento-coordinator.js para garantizar el orden correcto
// Las funciones están disponibles para ser llamadas en el momento apropiado
