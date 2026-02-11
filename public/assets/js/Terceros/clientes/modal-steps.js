// JavaScript para la navegación por pasos en el modal de clientes
let currentStep = 1;
const totalSteps = 4;

// Función para mostrar un paso específico
function showStep(step) {
    // Ocultar todos los pasos
    for (let i = 1; i <= totalSteps; i++) {
        const stepContent = document.getElementById(`step-${i}`);
        const stepIndicator = document.querySelector(`[data-step="${i}"]`);

        if (stepContent) {
            if (i === step) {
                stepContent.classList.remove('d-none');
                stepIndicator.classList.add('active');
            } else {
                stepContent.classList.add('d-none');
                stepIndicator.classList.remove('active');
            }
        }
    }

    // Actualizar botones de navegación
    updateNavigationButtons();

    // Actualizar indicador de paso actual
    const currentStepSpan = document.getElementById('current-step');
    if (currentStepSpan) {
        currentStepSpan.textContent = step;
    }

    currentStep = step;
}

// Función para ir al siguiente paso
function nextStep() {
    if (validateCurrentStep() && currentStep < totalSteps) {
        // Marcar el paso actual como completado
        const currentStepIndicator = document.querySelector(`[data-step="${currentStep}"]`);
        if (currentStepIndicator) {
            currentStepIndicator.classList.add('completed');
        }

        showStep(currentStep + 1);
    }
}

// Función para ir al paso anterior
function prevStep() {
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

// Función para validar el paso actual
function validateCurrentStep() {
    let isValid = true;

    // Limpiar errores previos
    clearValidationErrors();

    switch (currentStep) {
        case 1: // Identificación
            isValid = validateIdentificationStep();
            break;
        case 2: // Información Personal
            isValid = validatePersonalInfoStep();
            break;
        case 3: // Contacto
            isValid = validateContactStep();
            break;
        case 4: // Dirección
            isValid = validateAddressStep();
            break;
    }

    return isValid;
}

// Validaciones por paso
function validateIdentificationStep() {
    let isValid = true;

    const tipopersona = document.getElementById('tipopersona_id');
    const tipoidentificacion = document.getElementById('tipoidentificacion_id');
    const identificacion = document.getElementById('identificacion');

    if (!tipopersona.value) {
        showFieldError('tipopersona_id', 'El tipo de persona es requerido');
        isValid = false;
    }

    if (!tipoidentificacion.value) {
        showFieldError('tipoidentificacion_id', 'El tipo de identificación es requerido');
        isValid = false;
    }

    if (!identificacion.value.trim()) {
        showFieldError('identificacion', 'La identificación es requerida');
        isValid = false;
    }

    return isValid;
}

function validatePersonalInfoStep() {
    let isValid = true;
    const tipoPersona = document.getElementById('tipopersona_id').value;

    if (tipoPersona === '1') { // Persona Natural
        const nombres = document.getElementById('nombres');
        const apellidos = document.getElementById('apellidos');

        if (!nombres.value.trim()) {
            showFieldError('nombres', 'Los nombres son requeridos');
            isValid = false;
        }

        if (!apellidos.value.trim()) {
            showFieldError('apellidos', 'Los apellidos son requeridos');
            isValid = false;
        }
    } else { // Persona Jurídica
        const nombreEstablecimiento = document.getElementById('nombre_establecimiento');

        if (!nombreEstablecimiento.value.trim()) {
            showFieldError('nombre_establecimiento', 'El nombre del establecimiento es requerido');
            isValid = false;
        }
    }

    return isValid;
}

function validateContactStep() {
    let isValid = true;

    const vendedorId = document.getElementById('vendedor_id');
    const vendedorHidden = document.getElementById('vendedor_hidden');

    // Validar vendedor solo si no hay uno asignado automáticamente
    if (!vendedorHidden && (!vendedorId || !vendedorId.value)) {
        showFieldError('vendedor_id', 'El vendedor es requerido');
        isValid = false;
    }

    // Validar números de teléfono
    const telefono = document.getElementById('telefono').value.replace(/[^\d]/g, '');
    const celular = document.getElementById('celular').value.replace(/[^\d]/g, '');
    const correo = document.getElementById('correo').value.trim();

    // Validar formato de teléfonos si se proporcionan
    if (telefono && telefono.length !== 10) {
        showFieldError('telefono', 'El teléfono debe tener exactamente 10 dígitos');
        isValid = false;
    }

    if (celular && celular.length !== 10) {
        showFieldError('celular', 'El celular debe tener exactamente 10 dígitos');
        isValid = false;
    }

    // Validar que al menos haya un método de contacto
    if (!telefono && !celular && !correo) {
        showFieldError('telefono', 'Debe proporcionar al menos un método de contacto');
        showFieldError('celular', '');
        showFieldError('correo', '');
        isValid = false;
    }

    // Validar formato de correo si se proporciona
    if (correo && !isValidEmail(correo)) {
        showFieldError('correo', 'Formato de correo inválido');
        isValid = false;
    }

    const correoFe = document.getElementById('correo_fe').value.trim();
    if (correoFe && !isValidEmail(correoFe)) {
        showFieldError('correo_fe', 'Formato de correo inválido');
        isValid = false;
    }

    return isValid;
}

function validateAddressStep() {
    let isValid = true;

    const pais = document.getElementById('pais_id');
    const departamento = document.getElementById('departamento_id');
    const ciudad = document.getElementById('ciudad_id');
    const direccion = document.getElementById('direccion');

    if (!pais.value) {
        showFieldError('pais_id', 'El país es requerido');
        isValid = false;
    }

    if (!departamento.value) {
        showFieldError('departamento_id', 'El departamento es requerido');
        isValid = false;
    }

    if (!ciudad.value) {
        showFieldError('ciudad_id', 'La ciudad es requerida');
        isValid = false;
    }

    if (!direccion.value.trim()) {
        showFieldError('direccion', 'La dirección es requerida');
        isValid = false;
    }

    return isValid;
}

// Funciones auxiliares
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(`error_${fieldId}`);

    if (field) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
    }

    if (errorDiv && message) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('d-none');
    }
}

function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(`error_${fieldId}`);

    if (field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    }

    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.classList.add('d-none');
    }
}

function clearValidationErrors() {
    const errorElements = document.querySelectorAll('[id^="error_"]');
    const invalidFields = document.querySelectorAll('.is-invalid');

    errorElements.forEach(element => {
        element.textContent = '';
        element.classList.add('d-none');
    });

    invalidFields.forEach(field => {
        field.classList.remove('is-invalid');
    });
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevStep');
    const nextBtn = document.getElementById('nextStep');
    const saveBtn = document.getElementById('saveClient');

    if (prevBtn) {
        prevBtn.disabled = currentStep === 1;
    }

    if (nextBtn && saveBtn) {
        if (currentStep === totalSteps) {
            nextBtn.classList.add('d-none');
            saveBtn.classList.remove('d-none');
        } else {
            nextBtn.classList.remove('d-none');
            saveBtn.classList.add('d-none');
        }
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Función para guardar el cliente
function saveClient() {
    if (validateCurrentStep()) {
        // Conectar con la función existente registerCli()
        if (typeof registerCli === 'function') {
            registerCli();
        } else {
            console.error('Función registerCli no encontrada');
        }
    }
}

// Función para resetear el modal
function resetModal() {
    currentStep = 1;
    showStep(1);
    clearValidationErrors();

    // Limpiar campos del formulario
    const form = document.querySelector('#ModalCliente form');
    if (form) {
        form.reset();
    }

    // Remover clases de completado
    document.querySelectorAll('.step.completed').forEach(step => {
        step.classList.remove('completed');
    });
}

// Función para ir directamente a un paso (haciendo clic en el indicador)
function goToStep(step) {
    if (step <= currentStep || step === currentStep + 1) {
        showStep(step);
    }
}

// Validación en tiempo real
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('form-control')) {
        const fieldId = e.target.id;
        if (e.target.value.trim() && e.target.classList.contains('is-invalid')) {
            clearFieldError(fieldId);
        }
    }
});

// Inicializar el primer paso cuando se abra el modal
$(document).ready(function() {
    $('#ModalCliente').on('shown.bs.modal', function() {
        showStep(1);
    });

    // Resetear cuando se cierre el modal
    $('#ModalCliente').on('hidden.bs.modal', function() {
        resetModal();
    });

    // Para Bootstrap 4, también escuchar el evento show
    $('#ModalCliente').on('show.bs.modal', function() {
        setTimeout(() => {
            showStep(1);
        }, 100);
    });

    // Configurar event listeners para los botones de navegación
    $(document).on('click', '#nextStep', function(e) {
        e.preventDefault();
        nextStep();
    });

    $(document).on('click', '#prevStep', function(e) {
        e.preventDefault();
        prevStep();
    });

    $(document).on('click', '#saveClient', function(e) {
        e.preventDefault();
        saveClient();
    });

    // Event listeners para los indicadores de paso
    $(document).on('click', '[data-step]', function(e) {
        e.preventDefault();
        const step = parseInt($(this).attr('data-step'));
        goToStep(step);
    });
});

// Hacer las funciones accesibles globalmente
window.nextStep = nextStep;
window.prevStep = prevStep;
window.saveClient = saveClient;
window.showStep = showStep;
window.resetModal = resetModal;
