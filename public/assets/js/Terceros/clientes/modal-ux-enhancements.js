// Mejoras adicionales de UX/UI para el modal de clientes

// Función para mostrar notificaciones toast
function showToast(message, type = 'info', duration = 3000) {
    // Crear el toast si no existe el contenedor
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Crear el toast
    const toastId = 'toast-' + Date.now();
    const iconClass = {
        'success': 'fas fa-check-circle text-success',
        'error': 'fas fa-times-circle text-danger',
        'warning': 'fas fa-exclamation-triangle text-warning',
        'info': 'fas fa-info-circle text-info'
    };

    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="${iconClass[type]} mr-2"></i>
                <strong class="mr-auto">Información</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    // Mostrar el toast usando Bootstrap
    $(`#${toastId}`).toast({
        delay: duration
    }).toast('show');

    // Remover el toast después de que se oculte
    $(`#${toastId}`).on('hidden.bs.toast', function() {
        this.remove();
    });
}

// Función para confirmar acciones importantes
function confirmAction(message, callback, title = 'Confirmar Acción') {
    const confirmModal = `
        <div class="modal fade" id="confirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle mr-2"></i>${title}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ${message}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmBtn">
                            <i class="fas fa-check mr-1"></i>Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal anterior si existe
    $('#confirmModal').remove();

    // Agregar nuevo modal
    $('body').append(confirmModal);

    // Configurar eventos
    $('#confirmBtn').on('click', function() {
        $('#confirmModal').modal('hide');
        callback();
    });

    // Mostrar modal
    $('#confirmModal').modal('show');
}

// Mejorar la validación en tiempo real con efectos visuales
function enhanceFieldValidation() {
    // Validación en tiempo real para campos requeridos
    $('#ModalCliente input, #ModalCliente select, #ModalCliente textarea').on('blur', function() {
        const field = $(this);
        const fieldId = field.attr('id');
        const value = field.val().trim();

        // Limpiar estados previos
        field.removeClass('is-invalid is-valid');

        // Validaciones específicas
        if (fieldId === 'correo' || fieldId === 'correo_fe') {
            if (value && !isValidEmail(value)) {
                field.addClass('is-invalid');
                showFieldError(fieldId, 'Formato de correo inválido');
            } else if (value) {
                field.addClass('is-valid');
                clearFieldError(fieldId);
            }
        }

        // Validación de identificación numérica
        if (fieldId === 'identificacion') {
            if (value && !/^[0-9]+$/.test(value)) {
                field.addClass('is-invalid');
                showFieldError(fieldId, 'La identificación debe contener solo números');
            } else if (value) {
                field.addClass('is-valid');
                clearFieldError(fieldId);
            }
        }

        // Validación de teléfonos
        if (fieldId === 'telefono' || fieldId === 'celular') {
            if (value && !/^[0-9+\-\s\(\)]+$/.test(value)) {
                field.addClass('is-invalid');
                showFieldError(fieldId, 'Formato de teléfono inválido');
            } else if (value) {
                field.addClass('is-valid');
                clearFieldError(fieldId);
            }
        }
    });

    // Auto-formato para campos específicos
    $('#identificacion').on('input', function() {
        // Solo permitir números
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $('#dv').on('input', function() {
        // Solo permitir un carácter
        this.value = this.value.slice(0, 1);
    });

    $('#telefono, #celular').on('input', function() {
        // Formatear número de teléfono para visualización
        let value = this.value.replace(/[^\d]/g, '');
        if (value.length >= 10) {
            value = value.substring(0, 10); // Limitar a 10 dígitos
        }
        if (value.length >= 7) {
            this.value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            this.value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
        } else {
            this.value = value;
        }

        // Almacenar el valor limpio en un atributo data para el envío
        $(this).attr('data-clean-value', value);
    });
}

// Función para mostrar indicadores de progreso
function updateProgressIndicator() {
    const totalFields = $('#ModalCliente input[required], #ModalCliente select[required]').length;
    const filledFields = $('#ModalCliente input[required], #ModalCliente select[required]').filter(function() {
        return $(this).val().trim() !== '';
    }).length;

    const progress = Math.round((filledFields / totalFields) * 100);

    // Actualizar barra de progreso si existe
    const progressBar = $('.modal-progress-bar');
    if (progressBar.length === 0) {
        // Crear barra de progreso
        const progressHTML = `
            <div class="modal-progress bg-light p-2 border-bottom">
                <small class="text-muted">Progreso del formulario</small>
                <div class="progress mt-1" style="height: 5px;">
                    <div class="progress-bar modal-progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        `;
        $('.modal-body').prepend(progressHTML);
    }

    $('.modal-progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
}

// Función para auto-guardar borrador (localStorage)
function autosaveDraft() {
    const formData = {};
    $('#ModalCliente input, #ModalCliente select, #ModalCliente textarea').each(function() {
        const field = $(this);
        if (field.attr('type') !== 'hidden') {
            formData[field.attr('id')] = field.val();
        }
    });

    localStorage.setItem('clientDraft', JSON.stringify(formData));

    // Mostrar indicador de auto-guardado
    if ($('.auto-save-indicator').length === 0) {
        $('.modal-header').append('<small class="auto-save-indicator text-light ml-2"><i class="fas fa-cloud mr-1"></i>Borrador guardado</small>');
        setTimeout(() => {
            $('.auto-save-indicator').fadeOut(2000, function() {
                $(this).remove();
            });
        }, 1000);
    }
}

// Función para cargar borrador guardado
function loadDraft() {
    const draft = localStorage.getItem('clientDraft');
    if (draft && confirm('¿Desea cargar el borrador guardado anteriormente?')) {
        const formData = JSON.parse(draft);
        Object.keys(formData).forEach(fieldId => {
            $(`#${fieldId}`).val(formData[fieldId]);
        });
        showToast('Borrador cargado exitosamente', 'success');
    }
}

// Función para limpiar borrador
function clearDraft() {
    localStorage.removeItem('clientDraft');
}

// Inicializar mejoras de UX
$(document).ready(function() {
    // Mejorar validación de campos
    enhanceFieldValidation();

    // Auto-guardar cada 30 segundos
    let autosaveInterval;

    $('#ModalCliente').on('shown.bs.modal', function() {
        // Cargar borrador si existe
        loadDraft();

        // Iniciar auto-guardado
        autosaveInterval = setInterval(autosaveDraft, 30000);
    });

    $('#ModalCliente').on('hidden.bs.modal', function() {
        // Detener auto-guardado
        if (autosaveInterval) {
            clearInterval(autosaveInterval);
        }
    });

    // Actualizar progreso cuando cambie cualquier campo
    $('#ModalCliente').on('input change', 'input, select, textarea', function() {
        updateProgressIndicator();
    });

    // Confirmar cierre si hay cambios
    $('#ModalCliente .close, #ModalCliente [data-dismiss="modal"]').on('click', function(e) {
        const hasChanges = $('#ModalCliente input, #ModalCliente select, #ModalCliente textarea').filter(function() {
            return $(this).val().trim() !== '' && $(this).attr('type') !== 'hidden';
        }).length > 0;

        if (hasChanges) {
            e.preventDefault();
            confirmAction(
                '¿Está seguro de que desea cerrar? Los cambios no guardados se perderán.',
                function() {
                    clearDraft();
                    $('#ModalCliente').modal('hide');
                },
                'Confirmar Cierre'
            );
        } else {
            clearDraft();
        }
    });

    // Prevenir cierre accidental con Escape
    $('#ModalCliente').on('keydown', function(e) {
        if (e.key === 'Escape') {
            const hasChanges = $('#ModalCliente input, #ModalCliente select, #ModalCliente textarea').filter(function() {
                return $(this).val().trim() !== '' && $(this).attr('type') !== 'hidden';
            }).length > 0;

            if (hasChanges) {
                e.preventDefault();
                confirmAction(
                    '¿Está seguro de que desea cerrar? Los cambios no guardados se perderán.',
                    function() {
                        clearDraft();
                        $('#ModalCliente').modal('hide');
                    },
                    'Confirmar Cierre'
                );
            }
        }
    });

    // Mejorar accesibilidad con navegación por teclado
    $('#ModalCliente').on('keydown', 'input, select, textarea', function(e) {
        if (e.key === 'Enter' && !e.shiftKey && this.tagName !== 'TEXTAREA') {
            e.preventDefault();
            const currentIndex = $('#ModalCliente input:visible, #ModalCliente select:visible, #ModalCliente textarea:visible').index(this);
            const nextField = $('#ModalCliente input:visible, #ModalCliente select:visible, #ModalCliente textarea:visible').eq(currentIndex + 1);

            if (nextField.length) {
                nextField.focus();
            } else {
                // Si no hay más campos, ir al siguiente paso
                if (typeof nextStep === 'function') {
                    nextStep();
                }
            }
        }
    });
});

// Exportar funciones para uso global
window.showToast = showToast;
window.confirmAction = confirmAction;
