/**
 * Sistema de navegación por pasos para modal de clientes
 * Versión mejorada con UX avanzado
 */

class ClienteModalSteps {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        this.validationRules = {};
        this.contactos = [];
        this.sucursales = [];

        // Propiedades de control de modo
        this.editMode = false;
        this.allowFreeNavigation = false;

        this.initializeSteps();
        this.setupValidation();
        this.setupEventListeners();
        this.setupToastSystem();
        this.checkDependencies();
    }

    checkDependencies() {
        const dependencies = {
            'jQuery': typeof $ !== 'undefined',
            'Bootstrap': typeof $.fn.modal !== 'undefined',
            'Toastr': typeof toastr !== 'undefined'
        };

        const missing = Object.entries(dependencies)
            .filter(([name, available]) => !available)
            .map(([name]) => name);

        if (missing.length > 0) {
            console.warn('⚠️ Dependencias faltantes para modal de clientes:', missing);
        } else {
            console.log('✅ Todas las dependencias están disponibles');
        }
    }

    initializeSteps() {
        this.updateStepDisplay();
        this.updateProgress();
        this.updateNavigationButtons();
    }

    updateStepDisplay() {

        // Ocultar todos los pasos
        for (let i = 1; i <= this.totalSteps; i++) {
            const stepContent = document.getElementById(`step-${i}`);
            if (stepContent) {
                stepContent.classList.add('d-none');
                stepContent.classList.remove('fade-in');
            } else {
                console.error(`Elemento step-${i} no encontrado en el DOM`);
            }
        }

        // Mostrar paso actual
        const currentStepContent = document.getElementById(`step-${this.currentStep}`);
        if (currentStepContent) {
            currentStepContent.classList.remove('d-none');
            setTimeout(() => {
                currentStepContent.classList.add('fade-in');
            }, 10);
        } else {
            console.error(`Elemento step-${this.currentStep} no encontrado en el DOM`);
        }

        // Actualizar indicadores de pasos
        document.querySelectorAll('.step-item').forEach((item, index) => {
            item.classList.remove('active', 'completed');
            const stepNumber = index + 1;

            if (stepNumber < this.currentStep) {
                item.classList.add('completed');
            } else if (stepNumber === this.currentStep) {
                item.classList.add('active');
            }
        });

        // Actualizar título del modal
        this.updateModalTitle();
    }

    updateModalTitle() {
        const titles = {
            1: { title: 'Datos Básicos', subtitle: 'Información fundamental del cliente' },
            2: { title: 'Información de Contacto', subtitle: 'Datos de comunicación y ubicación' },
            3: { title: 'Contactos', subtitle: 'Personas de contacto adicionales' },
            4: { title: 'Sucursales', subtitle: 'Ubicaciones y puntos de atención' }
        };

        const titleElement = document.getElementById('modal-title-text');
        const subtitleElement = document.getElementById('modal-subtitle');

        if (titleElement && titles[this.currentStep]) {
            titleElement.textContent = titles[this.currentStep].title;
        }

        if (subtitleElement && titles[this.currentStep]) {
            subtitleElement.textContent = titles[this.currentStep].subtitle;
        }
    }

    updateProgress() {
        const progress = (this.currentStep / this.totalSteps) * 100;
        const progressBar = document.getElementById('modal-progress');
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
            progressBar.setAttribute('aria-valuenow', progress);
        }
    }

    updateNavigationButtons() {
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const finishBtn = document.getElementById('finish-btn');

        if (prevBtn) {
            prevBtn.classList.toggle('d-none', this.currentStep === 1);
        }

        if (nextBtn && finishBtn) {
            if (this.currentStep === this.totalSteps) {
                nextBtn.classList.add('d-none');
                finishBtn.classList.remove('d-none');
            } else {
                nextBtn.classList.remove('d-none');
                finishBtn.classList.add('d-none');
            }
        }
    }

    ensureNavigationButtons() {
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');

        if (!prevBtn || !nextBtn) {

            // Intentar buscar en el footer
            const footer = document.querySelector('#ModalCliente .modal-footer');
            if (footer) {
                const allButtons = footer.querySelectorAll('button');
                allButtons.forEach((btn, index) => {
                    console.log(`Botón ${index}: id="${btn.id}" onclick="${btn.getAttribute('onclick')}" classes="${btn.className}"`);
                });

                // Si los botones no existen, intentar recrearlos
                this.recreateNavigationButtons(footer);
            }
        } else {
            // FORZAR que los botones sean visibles removiendo d-none
            prevBtn.classList.remove('d-none');
            nextBtn.classList.remove('d-none');
            console.log('🔧 Forzando visibilidad de botones (removiendo d-none)');

            // Asegurar que los botones tengan los event handlers correctos
            if (prevBtn && !prevBtn.onclick) {
                prevBtn.setAttribute('onclick', 'prevStepHandler(event)');
            }

            if (nextBtn && !nextBtn.onclick) {
                nextBtn.setAttribute('onclick', 'nextStepHandler(event)');
            }

            // Luego aplicar la lógica normal de visibilidad según el paso
            setTimeout(() => {
                this.updateNavigationButtons();
            }, 50);
        }
    }

    recreateNavigationButtons(footer) {
        // Buscar el contenedor de navegación de pasos
        let stepNav = footer.querySelector('.step-navigation');
        if (!stepNav) {
            // Si no existe, buscar el div derecho del footer
            const rightDiv = footer.querySelector('div:last-child');
            if (rightDiv) {
                stepNav = rightDiv;
                stepNav.classList.add('step-navigation');
            } else {
                // Crear contenedor si no existe
                stepNav = document.createElement('div');
                stepNav.className = 'step-navigation';
                footer.appendChild(stepNav);
            }
        }

        // Recrear botón anterior si no existe
        if (!document.getElementById('prev-btn')) {
            const prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'btn btn-outline-primary d-none';
            prevBtn.id = 'prev-btn';
            prevBtn.setAttribute('onclick', 'prevStepHandler(event)');
            prevBtn.innerHTML = '<i class="fas fa-chevron-left mr-1"></i>Anterior';
            stepNav.appendChild(prevBtn);
        }

        // Recrear botón siguiente si no existe
        if (!document.getElementById('next-btn')) {
            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'btn btn-primary';
            nextBtn.id = 'next-btn';
            nextBtn.setAttribute('onclick', 'nextStepHandler(event)');
            nextBtn.innerHTML = 'Siguiente<i class="fas fa-chevron-right ml-1"></i>';
            stepNav.appendChild(nextBtn);
        }

        // Recrear botón finalizar si no existe
        if (!document.getElementById('finish-btn')) {
            const finishBtn = document.createElement('button');
            finishBtn.type = 'button';
            finishBtn.className = 'btn btn-success d-none';
            finishBtn.id = 'finish-btn';
            finishBtn.setAttribute('onclick', 'registerCliWithFeedback()');
            finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
            stepNav.appendChild(finishBtn);
        }
    }

    setupValidation() {
        // Validación en tiempo real
        this.validationRules = {
            1: ['tipopersona_id', 'tipoidentificacion_id', 'identificacion', 'nombres', 'apellidos'],
            2: ['correo', 'ciudad_id', 'direccion', 'telefono', 'celular', 'correo_fe'],
            3: [], // Opcional
            4: []  // Opcional
        };

        // Configurar validación de campos
        this.setupFieldValidation();
    }

    setupFieldValidation() {
        // Validación de identificación
        const identificacionField = document.getElementById('identificacion');
        if (identificacionField) {
            identificacionField.addEventListener('input', (e) => {
                this.validateField(e.target);
                this.autoCalculateDV(e.target.value);
            });
        }

        // Validación de emails
        ['correo', 'correo_fe', 'contacto_correo', 'sucursal_correo'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', (e) => this.validateEmail(e.target));
            }
        });

        // Validación de teléfonos
        ['telefono', 'celular', 'contacto_telefono', 'contacto_celular', 'sucursal_telefono', 'sucursal_celular'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', (e) => this.validatePhone(e.target));
            }
        });

        // Validación de campos requeridos
        document.querySelectorAll('input[required], select[required]').forEach(field => {
            field.addEventListener('blur', (e) => this.validateField(e.target));
            field.addEventListener('input', (e) => this.clearFieldError(e.target));
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldId = field.id;
        let isValid = true;
        let message = '';

        // Validación básica de campos requeridos
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'Este campo es requerido';
        }

        // Validaciones específicas
        if (isValid && value && fieldId === 'identificacion') {
            if (!/^[0-9]+$/.test(value)) {
                isValid = false;
                message = 'Solo se permiten números';
            }
        }

        if (isValid && value && (fieldId === 'correo' || fieldId === 'correo_fe')) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Formato de email inválido';
            }
        }

        if (isValid && value && (fieldId === 'telefono' || fieldId === 'celular')) {
            if (!/^[0-9]{10}$/.test(value)) {
                isValid = false;
                message = `El ${fieldId === 'telefono' ? 'teléfono' : 'celular'} debe tener 10 dígitos numéricos`;
            }
        }

        this.setFieldValidation(field, isValid, message);
        return isValid;
    }

    validateEmail(field) {
        const value = field.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isValid = !value || emailRegex.test(value);

        this.setFieldValidation(field, isValid, isValid ? '' : 'Formato de email inválido');
        return isValid;
    }

    validatePhone(field) {
        const value = field.value.trim();
        // Limpiar formato automáticamente
        if (value) {
            const cleanValue = value.replace(/[^\d]/g, '');
            field.value = cleanValue;
        }
        return true;
    }

    setFieldValidation(field, isValid, message) {
        const errorElement = document.getElementById(`error_${field.id}`);

        field.classList.remove('is-valid', 'is-invalid');

        if (isValid) {
            field.classList.add('is-valid');
        } else {
            field.classList.add('is-invalid');
        }

        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = message ? 'block' : 'none';
        }
    }

    clearFieldError(field) {
        const errorElement = document.getElementById(`error_${field.id}`);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
        field.classList.remove('is-invalid');
    }

    clearValidationErrors() {
        // Remover TODAS las clases de validación de todos los campos
        document.querySelectorAll('.is-valid, .is-invalid, .was-validated').forEach(field => {
            field.classList.remove('is-valid', 'is-invalid', 'was-validated');
        });

        // Remover clases de validación del formulario completo
        const form = document.querySelector('#ModalCliente form');
        if (form) {
            form.classList.remove('was-validated');
        }

        // Limpiar TODOS los mensajes de error posibles
        document.querySelectorAll('.invalid-feedback, .error-message, .text-danger, .alert-danger').forEach(error => {
            error.textContent = '';
            error.style.display = 'none';
            error.innerHTML = '';
        });

        // Limpiar TODOS los mensajes de éxito posibles
        document.querySelectorAll('.valid-feedback, .success-message, .text-success, .alert-success').forEach(success => {
            success.textContent = '';
            success.style.display = 'none';
            success.innerHTML = '';
        });

        // Limpiar indicadores de pasos completados y validación
        document.querySelectorAll('.step-completed, .step.completed, .step-validated').forEach(step => {
            step.classList.remove('step-completed', 'completed', 'step-validated');
        });

        // Limpiar todos los spans de error específicos por campo
        document.querySelectorAll('[id^="error_"]').forEach(errorSpan => {
            errorSpan.textContent = '';
            errorSpan.style.display = 'none';
        });

        // Remover atributos aria de validación
        document.querySelectorAll('[aria-invalid]').forEach(field => {
            field.removeAttribute('aria-invalid');
        });

        // Remover borders personalizados de error
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.style.borderColor = '';
            field.style.boxShadow = '';
        });

        // NUEVA: Limpiar clases específicas de Bootstrap y validación personalizada
        document.querySelectorAll('.border-success, .border-danger, .bg-success, .bg-danger').forEach(elem => {
            elem.classList.remove('border-success', 'border-danger', 'bg-success', 'bg-danger');
        });

        // NUEVA: Forzar reset visual de todos los inputs
        document.querySelectorAll('#ModalCliente input, #ModalCliente select, #ModalCliente textarea').forEach(field => {
            // Remover todas las clases posibles de validación
            field.className = field.className.replace(/\b(is-valid|is-invalid|was-validated|border-success|border-danger|text-success|text-danger)\b/g, '');

            // Restaurar clases base necesarias
            if (field.tagName === 'INPUT' || field.tagName === 'SELECT' || field.tagName === 'TEXTAREA') {
                if (!field.classList.contains('form-control')) {
                    field.classList.add('form-control');
                }
            }
        });
    }

    autoCalculateDV(identificacion) {
        // Auto-cálculo del DV para NIT
        const tipoId = document.getElementById('tipoidentificacion_id')?.value;
        if (tipoId === '6' && identificacion.length >= 7) { // Asumiendo que 6 es NIT
            const dv = this.calculateDV(identificacion);
            const dvField = document.getElementById('dv');
            if (dvField) {
                dvField.value = dv;
            }
        }
    }

    calculateDV(nit) {
        const factors = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        let sum = 0;

        for (let i = 0; i < nit.length; i++) {
            sum += parseInt(nit[nit.length - 1 - i]) * factors[i];
        }

        const remainder = sum % 11;
        return remainder < 2 ? remainder : 11 - remainder;
    }

    validateCurrentStep() {
        return this.validateStep(this.currentStep);
    }

    validateLoadedData() {
        // Función para validar y marcar como válidos los campos que ya tienen datos (modo edición)
        // Validar campos de cada paso que tengan datos
        for (let step = 1; step <= this.totalSteps; step++) {
            const stepFields = this.validationRules[step] || [];
            stepFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && field.value && field.value.trim() !== '') {
                    // Si el campo tiene datos, marcarlo como válido
                    this.setFieldValidation(field, true, '');
                }
            });
        }

        // También validar campos no requeridos que puedan tener datos
        ['correo', 'correo_fe', 'telefono', 'celular'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && field.value && field.value.trim() !== '') {
                this.setFieldValidation(field, true, '');
            }
        });

        // Forzar revalidación de todos los pasos para modo edición
        for (let step = 1; step <= this.totalSteps; step++) {
            const isStepValid = this.validateStep(step);
        }
    }

    validateStep(stepNumber) {
        // Función para validar un paso específico
        const stepFields = this.validationRules[stepNumber] || [];
        let isValid = true;

        stepFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                const fieldValid = this.validateField(field);
                if (!fieldValid) {
                    isValid = false;
                }
            }
        });

        return isValid;
    }

    nextStep(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (this.validateCurrentStep()) {
            if (this.currentStep < this.totalSteps) {
                const previousStep = this.currentStep;
                this.currentStep++;
                this.updateStepDisplay();
                this.updateProgress();
                this.updateNavigationButtons();
                this.showToast('success', '¡Paso completado!', `Paso ${previousStep} validado correctamente`);
            } else {
                console.log('Ya está en el último paso');
            }
        } else {
            this.showToast('error', 'Campos requeridos', 'Complete todos los campos obligatorios para continuar');
        }
    }

    prevStep(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStepDisplay();
            this.updateProgress();
            this.updateNavigationButtons();
        }
    }

    setupEventListeners() {
        // Los botones next-btn y prev-btn tienen onclick="nextStepHandler(event)" / prevStepHandler(event)
        // directamente en el HTML del modal — NO se agregan listeners adicionales aquí para
        // evitar que nextStep() se llame más de una vez por click.

        // Navegación por teclado
        document.addEventListener('keydown', (e) => {
            if (document.getElementById('ModalCliente').classList.contains('show')) {
                if (e.key === 'ArrowLeft' && e.ctrlKey) {
                    e.preventDefault();
                    this.prevStep();
                } else if (e.key === 'ArrowRight' && e.ctrlKey) {
                    e.preventDefault();
                    this.nextStep();
                }
            }
        });

        // Eventos para contactos y sucursales
        this.setupContactoEvents();
        this.setupSucursalEvents();

        // Auto-save draft
        // this.setupAutoSave();
    }

    setupContactoEvents() {
        const addBtn = document.getElementById('addContactoBtn');
        const cancelBtn = document.getElementById('cancelContactoBtn');
        const form = document.getElementById('contacto-form');

        if (addBtn) {
            addBtn.addEventListener('click', () => {
                form?.classList.remove('d-none');
                addBtn.style.display = 'none';
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                form?.classList.add('d-none');
                addBtn && (addBtn.style.display = 'block');
                this.clearContactoForm();
            });
        }
    }

    setupSucursalEvents() {
        const addBtn = document.getElementById('addSucursalBtn');
        const cancelBtn = document.getElementById('cancelSucursalBtn');
        const form = document.getElementById('sucursal-form');

        if (addBtn) {
            addBtn.addEventListener('click', () => {
                form?.classList.remove('d-none');
                addBtn.style.display = 'none';
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                form?.classList.add('d-none');
                addBtn && (addBtn.style.display = 'block');
                this.clearSucursalForm();
            });
        }
    }

    clearContactoForm() {
        ['contacto_nombres', 'contacto_apellidos', 'contacto_correo', 'contacto_cargo',
         'contacto_celular', 'contacto_telefono', 'contacto_ext'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) field.value = '';
        });
    }

    clearSucursalForm() {
        ['sucursal_nombre_sucursal', 'sucursal_persona_contacto', 'sucursal_correo',
         'sucursal_telefono', 'sucursal_celular', 'sucursal_direccion'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) field.value = '';
        });
    }

    setupToastSystem() {
        // Crear contenedor de toasts si no existe
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed';
            container.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            document.body.appendChild(container);
        }
    }

    showToast(type, title, message, duration = 5000) {
        // Primero intentar con toastr si está disponible
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: duration,
                extendedTimeOut: 1000,
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "slideDown",
                hideMethod: "slideUp"
            };

            switch(type) {
                case 'success':
                    toastr.success(message, title);
                    break;
                case 'error':
                    toastr.error(message, title);
                    break;
                case 'warning':
                    toastr.warning(message, title);
                    break;
                case 'info':
                default:
                    toastr.info(message, title);
                    break;
            }
            return;
        }

        // Si no hay toastr, usar sistema de toasts personalizado
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            // Crear contenedor si no existe
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                width: 350px;
            `;
            document.body.appendChild(container);
        }

        const toastId = 'toast-' + Date.now();
        const iconClasses = {
            success: 'fas fa-check-circle text-success',
            error: 'fas fa-exclamation-circle text-danger',
            warning: 'fas fa-exclamation-triangle text-warning',
            info: 'fas fa-info-circle text-info'
        };

        const toastHTML = `
            <div class="toast mb-2" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true" style="width: 100%;">
                <div class="toast-header">
                    <i class="${iconClasses[type]} mr-2"></i>
                    <strong class="mr-auto">${title}</strong>
                    <button type="button" class="ml-2 mb-1 close" onclick="document.getElementById('${toastId}').remove()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

        const container = document.getElementById('toast-container');
        container.insertAdjacentHTML('beforeend', toastHTML);

        const toastElement = document.getElementById(toastId);
        if (toastElement) {
            // Usar Bootstrap toast si está disponible
            if (typeof $ !== 'undefined' && typeof $.fn.toast === 'function') {
                $(toastElement).toast({ delay: duration }).toast('show');
            } else {
                // Fallback manual
                toastElement.classList.add('show');
                setTimeout(() => {
                    if (toastElement.parentElement) {
                        toastElement.remove();
                    }
                }, duration);
            }
        }
    }

    // Método para restablecer el modal
    reset(preserveData = false) {
        this.currentStep = 1;
        this.contactos = [];
        this.sucursales = [];
        this.updateStepDisplay();
        this.updateProgress();
        this.updateNavigationButtons();

        if (!preserveData) {
            // Limpiar formulario solo si no se quieren preservar los datos
            const form = document.getElementById('cliente-form');
            if (form) {
                form.reset();
            }

            // Limpiar validaciones
            document.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
                field.classList.remove('is-valid', 'is-invalid');
            });

            document.querySelectorAll('[id^="error_"]').forEach(error => {
                error.textContent = '';
            });
        } else {

        }

        // Limpiar atributos de listeners para permitir nueva configuración
        const nextBtn = document.getElementById('next-step-btn');
        const prevBtn = document.getElementById('prev-step-btn');
        if (nextBtn) nextBtn.removeAttribute('data-listener-added');
        if (prevBtn) prevBtn.removeAttribute('data-listener-added');
    }

    // Método para ir a un paso específico (si está permitido)
    goToStep(stepNumber) {
        if (stepNumber >= 1 && stepNumber <= this.totalSteps) {
            // Validar pasos previos
            let canProceed = true;
            for (let i = 1; i < stepNumber; i++) {
                this.currentStep = i;
                if (!this.validateCurrentStep()) {
                    canProceed = false;
                    break;
                }
            }

            if (canProceed) {
                this.currentStep = stepNumber;
                this.updateStepDisplay();
                this.updateProgress();
                this.updateNavigationButtons();
            } else {
                this.showToast('error', 'No se puede avanzar', 'Complete los pasos anteriores antes de continuar');
            }
        }
    }
}

// FUNCIONES GLOBALES PARA NAVEGACIÓN (Definidas después de la clase)
window.nextStepHandler = function(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    console.log('🔄 nextStepHandler llamada');
    if (window.clienteModalSteps) {
        // Siempre avanzar paso a paso, validando el paso actual, tanto en edición como en creación
        window.clienteModalSteps.nextStep(event);
    }
};

window.prevStepHandler = function(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    if (window.clienteModalSteps) {
        // Navegación libre para retroceder (sin validación)
        if (window.clienteModalSteps.currentStep > 1) {
            window.clienteModalSteps.currentStep--;
            window.clienteModalSteps.updateStepDisplay();
            window.clienteModalSteps.updateProgress();
            window.clienteModalSteps.updateNavigationButtons();
        }
    }
};

// Funciones de debugging
window.debugSteps = function() {
    console.log('🔍 === DEBUG PASOS ===');
    if (window.clienteModalSteps) {

        for (let i = 1; i <= window.clienteModalSteps.totalSteps; i++) {
            const stepElement = document.getElementById(`step-${i}`);
            if (stepElement) {
                console.log(`  - Visible: ${!stepElement.classList.contains('d-none')}`);
                console.log(`  - Clases: ${stepElement.className}`);
            }
        }
    } else {
        console.log('❌ clienteModalSteps no disponible');
    }
};

window.forceStep = function(stepNumber) {
    if (window.clienteModalSteps && stepNumber >= 1 && stepNumber <= window.clienteModalSteps.totalSteps) {
        window.clienteModalSteps.currentStep = stepNumber;
        window.clienteModalSteps.updateStepDisplay();
        window.clienteModalSteps.updateProgress();
        window.clienteModalSteps.updateNavigationButtons();
    } else {
        console.error('❌ No se puede forzar el paso:', stepNumber);
    }
};

// Inicializar cuando el modal se abra
let clienteModalSteps = null;

$(document).ready(function() {
    // Guardar funciones originales antes de sobrescribirlas (solo si no son nuestras)
    if (typeof window.registerContacto === 'function') {
        // Solo guardar si la función no es la que nosotros definimos
        const funcStr = window.registerContacto.toString();
        if (!funcStr.includes('registerContacto llamada')) {
            window.originalRegisterContacto = window.registerContacto;
        } else {
            console.log('La función registerContacto ya es nuestra, no la guardamos');
        }
    }

    if (typeof window.registerSucursal === 'function') {
        // Solo guardar si la función no es la que nosotros definimos
        const funcStr = window.registerSucursal.toString();
        if (!funcStr.includes('registerSucursal llamada')) {
            window.originalRegisterSucursal = window.registerSucursal;
        } else {
            console.log('La función registerSucursal ya es nuestra, no la guardamos');
        }
    }

    // Inicializar cuando se abra el modal
    $('#ModalCliente').on('shown.bs.modal', function () {

        // LIMPIEZA INMEDIATA para evitar validaciones residuales
        document.querySelectorAll('.is-valid, .is-invalid, .was-validated').forEach(field => {
            field.classList.remove('is-valid', 'is-invalid', 'was-validated');
        });
        document.querySelectorAll('[id^="error_"]').forEach(errorSpan => {
            errorSpan.textContent = '';
        });

        // Esperar un momento para que el DOM esté completamente renderizado
        setTimeout(() => {
            // SIEMPRE crear nueva instancia para evitar corrupción
            clienteModalSteps = new ClienteModalSteps();
            window.clienteModalSteps = clienteModalSteps;

            // LIMPIEZA COMPLETA con la nueva instancia
            clienteModalSteps.clearValidationErrors();

            // Asegurar que los botones siempre estén disponibles
            clienteModalSteps.ensureNavigationButtons();

            // Función para verificar si es modo edición (revisión tardía)
            const checkIfEditMode = () => {
                return (document.getElementById('id')?.value &&
                       document.getElementById('id').value.trim() !== '') ||
                      (document.getElementById('identificacion')?.value &&
                       document.getElementById('identificacion').value.trim() !== '') ||
                      (document.getElementById('nombres')?.value &&
                       document.getElementById('nombres').value.trim() !== '');
            };

            // Dar tiempo para que se carguen los datos antes de verificar modo
            setTimeout(() => {
                const isEditMode = checkIfEditMode();

                if (isEditMode) {
                    // En modo edición, permitir navegación libre entre pasos
                    clienteModalSteps.editMode = true;
                    clienteModalSteps.allowFreeNavigation = true;
                    clienteModalSteps.currentStep = 1;

                    // Forzar actualización de validaciones para mostrar/ocultar campos
                    setTimeout(() => {
                        if (typeof actualizarValidaciones === 'function') {
                            actualizarValidaciones();
                        }
                    }, 50);

                    // Validar campos que ya tienen datos
                    setTimeout(() => {
                        clienteModalSteps.validateLoadedData();
                    }, 200);
                } else {
                   // En modo nuevo, empezar en paso 1 con validación normal
                    clienteModalSteps.editMode = false;
                    clienteModalSteps.allowFreeNavigation = false;
                    clienteModalSteps.currentStep = 1;

                    // También ejecutar actualizarValidaciones en modo creación
                    setTimeout(() => {
                        if (typeof actualizarValidaciones === 'function') {
                            actualizarValidaciones();
                        }
                    }, 50);
                }
                // Siempre actualizar la interfaz
                clienteModalSteps.updateStepDisplay();
                clienteModalSteps.updateProgress();
                clienteModalSteps.updateNavigationButtons();
            }, 300); // Dar más tiempo para que se carguen los datos

        }, 200);
    });

    // Limpiar cuando se cierre el modal - pero sin resetear la variable global
    $('#ModalCliente').on('hidden.bs.modal', function () {
        // Restaurar título para el próximo uso (creación)
        $('#modal-title-text').text('Registrar Cliente');
        // NO resetear clienteModalSteps para evitar pérdida de referencia
        // Solo limpiar validaciones visuales
        if (clienteModalSteps) {
            clienteModalSteps.clearValidationErrors();
        }
    });
});
// Función mejorada para guardar cliente con UX mejorado
window.registerCliWithFeedback = function() {
    const finishBtn = document.getElementById('finish-btn');

    // Mostrar estado de carga
    if (finishBtn) {
        finishBtn.disabled = true;
        finishBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Guardando...';
    }

    // Mostrar toast de proceso
    if (window.clienteModalSteps) {
        window.clienteModalSteps.showToast('info', 'Procesando...', 'Guardando información del cliente', 3000);
    }

    // DETECTAR MODO DE EDICIÓN
    const isEditMode = $('#ModalCliente').data('edit-mode');
    const clienteId = $('#id').val();

    // PREPARAR tercerotipo_id Y user_id según el modo
    if (!isEditMode) {
        // En modo CREACIÓN: asegurar que tercerotipo_id = tipopersona_id
        const tipopersona_val = $('#tipopersona_id').val();
        $('#tercerotipo_id').val(tipopersona_val);

        // INTENTAR REPARAR user_id si está vacío
        let current_user_id = $('#user_id').val();

        if (!current_user_id || current_user_id === '' || current_user_id === 'undefined') {

            // Buscar valor desde atributo value del input
            const userInputElement = document.getElementById('user_id');
            if (userInputElement) {
                const attrValue = userInputElement.getAttribute('value');

                if (attrValue && attrValue !== '') {
                    $('#user_id').val(attrValue);
                    current_user_id = attrValue;
                }
            }
        }

        // Última verificación
        if (!current_user_id || current_user_id === '' || current_user_id === 'undefined') {
            console.error('❌ ERROR: user_id no se pudo configurar');
            console.error('❌ Valor final:', current_user_id);

            if (window.clienteModalSteps) {
                window.clienteModalSteps.showToast('error', 'Error de configuración', 'Usuario no identificado. Revise la consola para más detalles.');
            }

            // Restaurar botón
            if (finishBtn) {
                finishBtn.disabled = false;
                finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
            }
            return;
        }
    } else {
        // En modo EDICIÓN: mantener el user_id original (ya viene cargado desde el controlador)
        console.log('📝 MODO EDICIÓN - Manteniendo user_id original:', $('#user_id').val());
    }

    // VALIDAR que los campos obligatorios no estén vacíos
    const tercerotipo_val = $('#tercerotipo_id').val();
    const user_val = $('#user_id').val();

    if (!tercerotipo_val || tercerotipo_val === '') {
        if (window.clienteModalSteps) {
            window.clienteModalSteps.showToast('error', 'Error', 'Debe seleccionar un tipo de persona');
        }
        // Restaurar botón
        if (finishBtn) {
            finishBtn.disabled = false;
            finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
        }
        return;
    }

    if (!user_val || user_val === '') {
        if (window.clienteModalSteps) {
            window.clienteModalSteps.showToast('error', 'Error', 'Campo user_id no configurado correctamente');
        }
        // Restaurar botón
        if (finishBtn) {
            finishBtn.disabled = false;
            finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
        }
        return;
    }

    // Verificar si la función original existe
    if (typeof window.registerCli !== 'function') {
        if (window.clienteModalSteps) {
            window.clienteModalSteps.showToast('error', 'Error del sistema', 'Función de registro no disponible');
        }
        // Restaurar botón
        if (finishBtn) {
            finishBtn.disabled = false;
            finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
        }
        return;
    }

    // INTERCEPTAR LA RESPUESTA DE ÉXITO para mejorar UX
    const originalAjax = $.ajax;
    $.ajax = function(options) {
        const originalSuccess = options.success;
        const originalError = options.error;

        options.success = function(response) {
            // Mostrar toast de éxito
            if (window.clienteModalSteps) {
                window.clienteModalSteps.showToast('success', '¡Éxito!', response.message || 'Cliente guardado correctamente');
            }

            // Cerrar modal después de un momento
            setTimeout(() => {
                $('#ModalCliente').modal('hide');
            }, 1500);

            // Restaurar función AJAX original
            $.ajax = originalAjax;

            // Ejecutar callback original si existe
            if (originalSuccess) {
                originalSuccess.call(this, response);
            }

            // Restaurar botón
            if (finishBtn) {
                finishBtn.disabled = false;
                finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
            }
        };

        options.error = function(xhr, status, error) {
            // Restaurar función AJAX original
            $.ajax = originalAjax;
            // Manejar errores con UX mejorado
            if (xhr.status === 422) {
                if (window.clienteModalSteps) {
                    window.clienteModalSteps.showToast('error', 'Errores de validación', 'Por favor revise los campos marcados.');
                    // Ir al primer paso con errores
                    if (window.clienteModalSteps.goToStep) {
                        window.clienteModalSteps.goToStep(1);
                    }
                }
            } else if (xhr.status === 403) {
                if (window.clienteModalSteps) {
                    window.clienteModalSteps.showToast('error', 'Permisos insuficientes', 'No tiene permisos para realizar esta acción');
                }
            } else {
                if (window.clienteModalSteps) {
                    window.clienteModalSteps.showToast('error', 'Error del servidor', 'Ocurrió un error inesperado. Por favor intente de nuevo.');
                }
            }

            // Ejecutar callback de error original si existe
            if (originalError) {
                originalError.call(this, xhr, status, error);
            }

            // Restaurar botón
            if (finishBtn) {
                finishBtn.disabled = false;
                finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
            }
        };

        return originalAjax.call(this, options);
    };
    // Ejecutar función original con mejoras aplicadas
    window.registerCli();
};

// Funciones globales para navegación de pasos (definidas sin window. para compatibilidad directa)
function nextStepHandler(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (window.clienteModalSteps) {
        window.clienteModalSteps.nextStep(event);
    } else {
        console.error('window.clienteModalSteps no disponible');
    }
}

function prevStepHandler(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (window.clienteModalSteps) {
        window.clienteModalSteps.prevStep(event);
    } else {
        console.error('window.clienteModalSteps no disponible');
    }
}

// También definir con window para compatibilidad
window.nextStepHandler = nextStepHandler;
window.prevStepHandler = prevStepHandler;

// Funciones globales para navegación de pasos (LEGACY - mantener por compatibilidad)
window.handleNextStep = function(event) {
    event.preventDefault();
    event.stopPropagation();

    if (window.clienteModalSteps) {
        window.clienteModalSteps.nextStep(event);
    } else {
        console.error('window.clienteModalSteps no disponible');
    }
};

window.handlePrevStep = function(event) {
    event.preventDefault();
    event.stopPropagation();

    if (window.clienteModalSteps) {
        window.clienteModalSteps.prevStep(event);
    } else {
        console.error('window.clienteModalSteps no disponible');
    }
};

// El segundo bloque ready fue eliminado: duplicaba el handler shown.bs.modal
// lo que causaba doble avance de pasos (1→3 en lugar de 1→2).
// La inicialización queda únicamente en el primer $(document).ready de este archivo.

// Función resetModal para compatibilidad con clientes.js
window.resetModal = function() {
    // Restaurar título para modo creación
    $('#modal-title-text').text('Registrar Cliente');
    // Limpiar formulario PRIMERO - pero preservar campos críticos
    const form = document.querySelector('#ModalCliente form');
    if (form) {
        // PRESERVAR valores críticos antes del reset
        const criticalValues = {
            user_id: $('#user_id').val(),
            tercerotipo_id: $('#tercerotipo_id').val()
        };
        form.reset();
        form.classList.remove('was-validated');

        // RESTAURAR valores críticos después del reset
        $('#user_id').val(criticalValues.user_id);
        $('#tercerotipo_id').val(criticalValues.tercerotipo_id);
    }

    // Limpiar todas las validaciones visuales MANUALMENTE Y AGRESIVAMENTE
    document.querySelectorAll('#ModalCliente .is-valid, #ModalCliente .is-invalid, #ModalCliente .was-validated, #ModalCliente .border-success, #ModalCliente .border-danger').forEach(field => {
        field.classList.remove('is-valid', 'is-invalid', 'was-validated', 'border-success', 'border-danger');
    });

    document.querySelectorAll('#ModalCliente [id^="error_"]').forEach(errorSpan => {
        errorSpan.textContent = '';
        errorSpan.style.display = 'none';
        errorSpan.innerHTML = '';
    });

    // PRESERVAR valores críticos antes de limpiar todos los inputs
    const criticalValuesBeforeCleaning = {
        user_id: $('#user_id').val(),
        tercerotipo_id: $('#tercerotipo_id').val()
    };

    // Limpiar todos los inputs específicamente
    document.querySelectorAll('#ModalCliente input, #ModalCliente select, #ModalCliente textarea').forEach(field => {
        // NO LIMPIAR campos críticos ni configuraciones importantes
        if (!['tipopersona_id', 'tipoidentificacion_id', 'vendedor_id', 'user_id', 'tercerotipo_id'].includes(field.id)) {
            field.value = '';
        }

        // Restaurar clases originales solamente
        field.className = 'form-control';
        field.style.borderColor = '';
        field.style.boxShadow = '';
        field.removeAttribute('aria-invalid');
    });

    // RESTAURAR valores críticos después de limpiar
    $('#user_id').val(criticalValuesBeforeCleaning.user_id);
    $('#tercerotipo_id').val(criticalValuesBeforeCleaning.tercerotipo_id);

    window.clienteModalSteps = new ClienteModalSteps();

    // Configurar para modo creación
    window.clienteModalSteps.editMode = false;
    window.clienteModalSteps.allowFreeNavigation = false;
    window.clienteModalSteps.currentStep = 1;

    // Asegurar botones disponibles
    window.clienteModalSteps.ensureNavigationButtons();

    // Actualizar interfaz
    window.clienteModalSteps.updateStepDisplay();
    window.clienteModalSteps.updateProgress();
    window.clienteModalSteps.updateNavigationButtons();

    // SEGUNDA LIMPIEZA con el método de la instancia
    window.clienteModalSteps.clearValidationErrors();

    // TERCERA LIMPIEZA específica para campos problemáticos
    setTimeout(() => {
        document.querySelectorAll('#ModalCliente input, #ModalCliente select, #ModalCliente textarea').forEach(field => {
            field.classList.remove('is-valid', 'is-invalid', 'border-success', 'border-danger');
            field.style.borderColor = '';
            field.style.boxShadow = '';
        });

        // Limpieza adicional de elementos específicos
        document.querySelectorAll('#ModalCliente .valid-feedback, #ModalCliente .invalid-feedback').forEach(feedback => {
            feedback.style.display = 'none';
            feedback.textContent = '';
        });
    }, 100);

    // CUARTA LIMPIEZA tardía adicional
    setTimeout(() => {
        window.clienteModalSteps.clearValidationErrors();
    }, 300);
};
