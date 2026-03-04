/**
 * Sistema de navegación por pasos para modal de proveedores
 * Adaptado del sistema de clientes
 */

class ProveedorModalSteps {
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

        console.log('✅ Nueva instancia ProveedorModalSteps creada');
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
            console.warn('⚠️ Dependencias faltantes para modal de proveedores:', missing);
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
        console.log(`Mostrando paso ${this.currentStep} de ${this.totalSteps}`);

        // Ocultar todos los pasos
        for (let i = 1; i <= this.totalSteps; i++) {
            const stepContent = document.getElementById(`step-${i}`);
            if (stepContent) {
                stepContent.classList.add('d-none');
                stepContent.classList.remove('fade-in');
                console.log(`Paso step-${i} oculto`);
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
            console.log(`Paso step-${this.currentStep} mostrado`);
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
            1: { title: 'Datos Básicos', subtitle: 'Información fundamental del proveedor' },
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
        console.log('🔧 Asegurando que los botones de navegación estén disponibles');

        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');

        if (!prevBtn || !nextBtn) {
            console.log('❌ Botones de navegación no encontrados en DOM');
            console.log('prev-btn:', !!prevBtn, 'next-btn:', !!nextBtn);

            const footer = document.querySelector('#ModalProveedor .modal-footer');
            if (footer) {
                console.log('✅ Footer encontrado, verificando botones...');
                this.recreateNavigationButtons(footer);
            }
        } else {
            console.log('✅ Botones de navegación encontrados correctamente');

            // FORZAR que los botones sean visibles removiendo d-none
            prevBtn.classList.remove('d-none');
            nextBtn.classList.remove('d-none');
            console.log('🔧 Forzando visibilidad de botones (removiendo d-none)');

            // Asegurar que los botones tengan los event handlers correctos
            if (prevBtn && !prevBtn.onclick) {
                prevBtn.setAttribute('onclick', 'prevStepHandler(event)');
                console.log('✅ onclick asignado a prevBtn');
            }

            if (nextBtn && !nextBtn.onclick) {
                nextBtn.setAttribute('onclick', 'nextStepHandler(event)');
                console.log('✅ onclick asignado a nextBtn');
            }

            // Luego aplicar la lógica normal de visibilidad según el paso
            setTimeout(() => {
                this.updateNavigationButtons();
            }, 50);

            console.log('✅ Estado final botones:');
            console.log('   - prev-btn visible:', !prevBtn.classList.contains('d-none'));
            console.log('   - next-btn visible:', !nextBtn.classList.contains('d-none'));
        }
    }

    recreateNavigationButtons(footer) {
        console.log('🚑 Recreando botones de navegación que faltan...');

        let stepNav = footer.querySelector('.step-navigation');
        if (!stepNav) {
            const rightDiv = footer.querySelector('div:last-child');
            if (rightDiv) {
                stepNav = rightDiv;
                stepNav.classList.add('step-navigation');
            } else {
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
            console.log('✅ Botón prev-btn recreado');
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
            console.log('✅ Botón next-btn recreado');
        }

        // Recrear botón finalizar si no existe
        if (!document.getElementById('finish-btn')) {
            const finishBtn = document.createElement('button');
            finishBtn.type = 'button';
            finishBtn.className = 'btn btn-success d-none';
            finishBtn.id = 'finish-btn';
            finishBtn.setAttribute('onclick', 'registerProvWithFeedback()');
            finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
            stepNav.appendChild(finishBtn);
            console.log('✅ Botón finish-btn recreado');
        }

        console.log('🚑 Recreación de botones completada');
    }

    setupValidation() {
        // Validación en tiempo real
        this.validationRules = {
            1: ['tipopersona_id', 'tipoidentificacion_id', 'identificacion', 'nombres', 'apellidos'],
            2: ['ciudad_id', 'direccion'],
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
        if (!field) return false;

        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');

        if (isRequired && !value) {
            this.showFieldError(field.id, 'Este campo es requerido');
            return false;
        }

        if (value && field.type === 'email' && !this.isValidEmail(value)) {
            this.showFieldError(field.id, 'Formato de email inválido');
            return false;
        }

        this.clearFieldError(field.id);
        return true;
    }

    validateEmail(field) {
        const email = field.value.trim();
        if (email && !this.isValidEmail(email)) {
            this.showFieldError(field.id, 'Formato de email inválido');
            return false;
        }
        this.clearFieldError(field.id);
        return true;
    }

    validatePhone(field) {
        const phone = field.value.replace(/[^\d]/g, '');
        if (phone && phone.length !== 10) {
            this.showFieldError(field.id, 'Debe tener exactamente 10 dígitos');
            return false;
        }
        this.clearFieldError(field.id);
        return true;
    }

    autoCalculateDV(identificacion) {
        // Solo para NIT (si el tipo de identificación es NIT)
        const tipoIdSelect = document.getElementById('tipoidentificacion_id');
        if (!tipoIdSelect || tipoIdSelect.value !== '3') return; // 3 = NIT

        const nit = identificacion.replace(/[^\d]/g, '');
        if (nit.length >= 7) {
            const dv = this.calculateDV(nit);
            const dvField = document.getElementById('dv');
            if (dvField) {
                dvField.value = dv;
            }
        }
    }

    calculateDV(nit) {
        const weights = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        let sum = 0;

        for (let i = 0; i < nit.length && i < weights.length; i++) {
            sum += parseInt(nit[nit.length - 1 - i]) * weights[i];
        }

        const remainder = sum % 11;
        return remainder > 1 ? 11 - remainder : remainder;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorSpan = document.getElementById(`error_${fieldId}`);

        if (field) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }

        if (errorSpan && message) {
            errorSpan.textContent = message;
            errorSpan.style.display = 'block';
        }
    }

    clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        const errorSpan = document.getElementById(`error_${fieldId}`);

        if (field) {
            field.classList.remove('is-invalid');
            if (field.value.trim()) {
                field.classList.add('is-valid');
            }
        }

        if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.style.display = 'none';
        }
    }

    clearValidationErrors() {
        console.log('🧹 Limpiando errores de validación...');

        // Limpiar todos los mensajes de error
        document.querySelectorAll('#ModalProveedor [id^="error_"]').forEach(errorSpan => {
            errorSpan.textContent = '';
            errorSpan.style.display = 'none';
        });

        // Limpiar todas las clases de validación
        document.querySelectorAll('#ModalProveedor .is-valid, #ModalProveedor .is-invalid').forEach(field => {
            field.classList.remove('is-valid', 'is-invalid');
        });

        console.log('✅ Errores de validación limpiados');
    }

    validateCurrentStep() {
        let isValid = true;

        switch (this.currentStep) {
            case 1: // Datos Básicos
                isValid = this.validateBasicDataStep();
                break;
            case 2: // Información de Contacto
                isValid = this.validateContactInfoStep();
                break;
            case 3: // Contactos (opcional)
                isValid = true;
                break;
            case 4: // Sucursales (opcional)
                isValid = true;
                break;
        }

        return isValid;
    }

    validateBasicDataStep() {
        let isValid = true;
        const requiredFields = ['tipopersona_id', 'tipoidentificacion_id', 'identificacion', 'nombres', 'apellidos'];

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && field.hasAttribute('required') && !field.value.trim()) {
                this.showFieldError(fieldId, 'Este campo es requerido');
                isValid = false;
            }
        });

        return isValid;
    }

    validateContactInfoStep() {
        let isValid = true;
        const requiredFields = ['ciudad_id', 'direccion'];

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && field.hasAttribute('required') && !field.value.trim()) {
                this.showFieldError(fieldId, 'Este campo es requerido');
                isValid = false;
            }
        });

        return isValid;
    }

    nextStep() {
        console.log('🔍 nextStep - iniciado');
        console.log('🔍 nextStep - paso actual:', this.currentStep);

        // MODO RELAJADO: Permitir navegación libre para testing
        // Solo validar estrictamente en el paso final
        const allowFreeNavigation = true; // Cambiar a false para validación estricta

        if (allowFreeNavigation) {
            console.log('⚙️ Modo navegación libre activado - saltando validación');

            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.updateStepDisplay();
                this.updateProgress();
                this.updateNavigationButtons();
                console.log(`✅ Navegado al paso ${this.currentStep}`);
                this.showToast('success', 'Navegación', `Paso ${this.currentStep} de ${this.totalSteps}`, 2000);
            } else {
                console.log('ℹ️ Ya estás en el último paso');
                this.showToast('info', 'Navegación', 'Ya estás en el último paso', 2000);
            }
        } else {
            // Validación estricta (modo producción)
            console.log('🔍 nextStep - validando paso actual...');
            const isValid = this.validateCurrentStep();
            console.log('🔍 nextStep - resultado validación:', isValid);

            if (isValid && this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.updateStepDisplay();
                this.updateProgress();
                this.updateNavigationButtons();
                console.log(`✅ Navegado al paso ${this.currentStep}`);
            } else if (!isValid) {
                console.log('⚠️ Validación fallida - mostrando toast');
                try {
                    this.showToast('warning', 'Validación', 'Por favor complete los campos requeridos');
                } catch (e) {
                    console.error('❌ Error en showToast:', e);
                    alert('Por favor complete los campos requeridos');
                }
            } else {
                console.log('ℹ️ Ya estás en el último paso');
            }
        }

        console.log('🔍 nextStep - terminado');
    }

    prevStep() {
        console.log('🔍 prevStep - iniciado');
        console.log('🔍 prevStep - paso actual:', this.currentStep);

        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStepDisplay();
            this.updateProgress();
            this.updateNavigationButtons();
            console.log(`✅ Navegado al paso ${this.currentStep}`);
            this.showToast('info', 'Navegación', `Paso ${this.currentStep} de ${this.totalSteps}`, 2000);
        } else {
            console.log('ℹ️ Ya estás en el primer paso');
            this.showToast('info', 'Navegación', 'Ya estás en el primer paso', 2000);
        }

        console.log('🔍 prevStep - terminado');
    }

    setupToastSystem() {
        // Configurar toastr si está disponible
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right',
                timeOut: 5000,
                extendedTimeOut: 2000
            };
        }
    }

    showToast(type, title, message, duration = 5000) {
        console.log(`📢 Toast: ${type.toUpperCase()}: ${title} - ${message}`);

        if (typeof toastr !== 'undefined') {
            // Configuración simplificada para evitar errores
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: duration,
                extendedTimeOut: 2000
            };

            try {
                toastr[type](message, title);
                console.log(`✅ Toast ${type} mostrado exitosamente`);
            } catch (e) {
                console.error('❌ Error en toastr:', e);
                // Fallback: mostrar alerta simple
                alert(`${title}: ${message}`);
            }
        } else {
            console.log(`⚠️ Toastr no disponible - usando console.log`);
            console.log(`${type.toUpperCase()}: ${title} - ${message}`);
            // Fallback: mostrar alerta simple
            alert(`${title}: ${message}`);
        }
    }

    setupEventListeners() {
        // Event listeners para botones de contactos y sucursales
        $('#addContactoBtn').off('click').on('click', () => {
            this.toggleContactoForm();
        });

        $('#cancelContactoBtn').off('click').on('click', () => {
            this.hideContactoForm();
        });

        $('#addSucursalBtn').off('click').on('click', () => {
            this.toggleSucursalForm();
        });

        $('#cancelSucursalBtn').off('click').on('click', () => {
            this.hideSucursalForm();
        });
    }

    toggleContactoForm() {
        const form = document.getElementById('contacto-form');
        const btn = document.getElementById('addContactoBtn');

        if (form && btn) {
            const isHidden = form.classList.contains('d-none');

            if (isHidden) {
                form.classList.remove('d-none');
                btn.style.display = 'none';
            } else {
                form.classList.add('d-none');
                btn.style.display = 'block';
            }
        }
    }

    hideContactoForm() {
        const form = document.getElementById('contacto-form');
        const btn = document.getElementById('addContactoBtn');

        if (form && btn) {
            form.classList.add('d-none');
            btn.style.display = 'block';

            // Limpiar formulario
            form.querySelectorAll('input').forEach(input => input.value = '');
        }
    }

    toggleSucursalForm() {
        const form = document.getElementById('sucursal-form');
        const btn = document.getElementById('addSucursalBtn');

        if (form && btn) {
            const isHidden = form.classList.contains('d-none');

            if (isHidden) {
                form.classList.remove('d-none');
                btn.style.display = 'none';
            } else {
                form.classList.add('d-none');
                btn.style.display = 'block';
            }
        }
    }

    hideSucursalForm() {
        const form = document.getElementById('sucursal-form');
        const btn = document.getElementById('addSucursalBtn');

        if (form && btn) {
            form.classList.add('d-none');
            btn.style.display = 'block';

            // Limpiar formulario
            form.querySelectorAll('input, select, textarea').forEach(field => field.value = '');
        }
    }
}

// FUNCIONES GLOBALES PARA NAVEGACIÓN
window.nextStepHandler = function(event) {
    console.log('🔄 nextStepHandler llamada - INICIO');

    // PRIMER NIVEL: Prevenir cualquier comportamiento default
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    try {
        // DEBUG: Verificar que window.proveedorModalSteps existe
        console.log('📊 Estado de window.proveedorModalSteps:', !!window.proveedorModalSteps);

        if (!window.proveedorModalSteps) {
            console.error('❌ window.proveedorModalSteps NO EXISTE - Creando instancia ahora');
            try {
                window.proveedorModalSteps = new ProveedorModalSteps();
                console.log('✅ Instancia creada exitosamente');
            } catch (e) {
                console.error('❌ Error creando instancia:', e);
                return false;
            }
        }

        console.log('📊 Paso actual:', window.proveedorModalSteps.currentStep);

        if (window.proveedorModalSteps) {
            // En modo edición, permitir navegación sin validación estricta
            const isEdit = (document.getElementById('id')?.value &&
                           document.getElementById('id').value.trim() !== '') ||
                          (document.getElementById('identificacion')?.value &&
                           document.getElementById('identificacion').value.trim() !== '');

            if (isEdit) {
                console.log('Modo edición - navegación libre');
                if (window.proveedorModalSteps.currentStep < window.proveedorModalSteps.totalSteps) {
                    window.proveedorModalSteps.currentStep++;
                    window.proveedorModalSteps.updateStepDisplay();
                    window.proveedorModalSteps.updateProgress();
                    window.proveedorModalSteps.updateNavigationButtons();
                }
            } else {
                // Modo normal con validación
                console.log('🔍 Llamando nextStep');
                window.proveedorModalSteps.nextStep();
            }
        }

        console.log('🔄 nextStepHandler llamada - FIN');
    } catch (error) {
        console.error('❌ Error en nextStepHandler:', error);
    }

    return false; // Asegurar que no se propague
};

window.prevStepHandler = function(event) {
    console.log('🔄 prevStepHandler llamada - INICIO');

    // PRIMER NIVEL: Prevenir cualquier comportamiento default
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    try {
        // DEBUG: Verificar que window.proveedorModalSteps existe
        console.log('📊 Estado de window.proveedorModalSteps:', !!window.proveedorModalSteps);

        if (!window.proveedorModalSteps) {
            console.error('❌ window.proveedorModalSteps NO EXISTE - Creando instancia ahora');
            try {
                window.proveedorModalSteps = new ProveedorModalSteps();
                console.log('✅ Instancia creada exitosamente');
            } catch (e) {
                console.error('❌ Error creando instancia:', e);
                return false;
            }
        }

        console.log('📊 Paso actual:', window.proveedorModalSteps.currentStep);

        if (window.proveedorModalSteps) {
            // Navegación libre para retroceder (sin validación)
            if (window.proveedorModalSteps.currentStep > 1) {
                window.proveedorModalSteps.currentStep--;
                window.proveedorModalSteps.updateStepDisplay();
                window.proveedorModalSteps.updateProgress();
                window.proveedorModalSteps.updateNavigationButtons();
                console.log('✅ Navegado al paso:', window.proveedorModalSteps.currentStep);
            } else {
                console.log('⚠️ Ya estás en el primer paso');
            }
        }

        console.log('🔄 prevStepHandler llamada - FIN');
    } catch (error) {
        console.error('❌ Error en prevStepHandler:', error);
    }

    return false; // Asegurar que no se propague
};
window.registerProvWithFeedback = function() {
    console.log('🚀 Iniciando registro de proveedor con feedback mejorado...');

    const finishBtn = document.getElementById('finish-btn');

    // Mostrar estado de carga
    if (finishBtn) {
        finishBtn.disabled = true;
        finishBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Guardando...';
        console.log('✅ Botón actualizado a estado de carga');
    }

    // Mostrar toast de proceso
    if (window.proveedorModalSteps) {
        window.proveedorModalSteps.showToast('info', 'Procesando...', 'Guardando información del proveedor', 3000);
        console.log('✅ Toast de procesamiento mostrado');
    }

    // Validación final
    if (!window.proveedorModalSteps.validateCurrentStep()) {
        if (window.proveedorModalSteps) {
            window.proveedorModalSteps.showToast('error', 'Error de validación', 'Por favor complete todos los campos requeridos');
        }

        // Restaurar botón
        if (finishBtn) {
            finishBtn.disabled = false;
            finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
        }
        return;
    }

    try {
        // Llamar a la función de registro de proveedores existente
        if (typeof registerProv === 'function') {
            // Configurar callback para manejar éxito/error
            const originalToastrSuccess = toastr.success;
            const originalToastrError = toastr.error;

            // Interceptar éxito
            toastr.success = function(message, title) {
                // Restaurar función original
                toastr.success = originalToastrSuccess;
                toastr.error = originalToastrError;

                // Mostrar mensaje de éxito
                originalToastrSuccess.call(toastr, message, title);

                if (window.proveedorModalSteps) {
                    window.proveedorModalSteps.showToast('success', 'Éxito', 'Proveedor guardado correctamente');
                }

                // Cerrar modal después de un momento
                setTimeout(() => {
                    $('#ModalProveedor').modal('hide');
                }, 1500);

                // Restaurar botón
                if (finishBtn) {
                    finishBtn.disabled = false;
                    finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
                }
            };

            // Interceptar error
            toastr.error = function(message, title) {
                // Restaurar función original
                toastr.success = originalToastrSuccess;
                toastr.error = originalToastrError;

                // Mostrar mensaje de error
                originalToastrError.call(toastr, message, title);

                if (window.proveedorModalSteps) {
                    window.proveedorModalSteps.showToast('error', 'Error', 'Error al guardar proveedor');
                }

                // Restaurar botón
                if (finishBtn) {
                    finishBtn.disabled = false;
                    finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
                }
            };

            // Llamar a la función de registro de proveedores
            registerProv();

        } else {
            console.error('❌ Función registerProv no encontrada');

            if (window.proveedorModalSteps) {
                window.proveedorModalSteps.showToast('error', 'Error del sistema', 'Función de registro no disponible');
            }

            // Restaurar botón
            if (finishBtn) {
                finishBtn.disabled = false;
                finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
            }
        }
    } catch (error) {
        console.error('❌ Error al llamar registerProv:', error);

        if (window.proveedorModalSteps) {
            window.proveedorModalSteps.showToast('error', 'Error del sistema', 'Error interno al guardar');
        }

        // Restaurar botón
        if (finishBtn) {
            finishBtn.disabled = false;
            finishBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Finalizar y Guardar';
        }
    }
};

// Función resetModal para compatibilidad
window.resetModal = function() {
    console.log('🔄 resetModal llamada - reiniciando modal para modo CREACIÓN');

    // Limpiar formulario preservando campos críticos
    const form = document.querySelector('#ModalProveedor form');
    if (form) {
        const criticalValues = {
            user_id: $('#user_id').val(),
            tercerotipo_id: $('#tercerotipo_id').val()
        };
        console.log('💾 Preservando valores críticos antes del reset:', criticalValues);

        form.reset();
        form.classList.remove('was-validated');
        console.log('✅ Formulario reseteado y clases removidas');

        // Restaurar valores críticos
        $('#user_id').val(criticalValues.user_id);
        $('#tercerotipo_id').val(criticalValues.tercerotipo_id);
        console.log('✅ Valores críticos restaurados después del reset');
    }

    // Crear nueva instancia limpia
    console.log('🆕 Creando nueva instancia ProveedorModalSteps (resetModal)');
    window.proveedorModalSteps = new ProveedorModalSteps();

    // Configurar para modo creación
    window.proveedorModalSteps.editMode = false;
    window.proveedorModalSteps.allowFreeNavigation = false;
    window.proveedorModalSteps.currentStep = 1;

    // Asegurar botones disponibles
    window.proveedorModalSteps.ensureNavigationButtons();

    // Actualizar interfaz
    window.proveedorModalSteps.updateStepDisplay();
    window.proveedorModalSteps.updateProgress();
    window.proveedorModalSteps.updateNavigationButtons();

    // Limpiar validaciones
    window.proveedorModalSteps.clearValidationErrors();
};

// ========================================
// FUNCIONES GLOBALES ULTRA SIMPLES PARA BOTONES
// ========================================

// Función global simple para SIGUIENTE
window.goToNextStep = function(event) {
    console.log('🟢 goToNextStep() EJECUTADA - INICIO');

    // BLOQUEAR TODO EVENTO
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    try {
        // Asegurar que existe la instancia
        if (!window.proveedorModalSteps) {
            console.log('📝 Creando instancia de ProveedorModalSteps...');
            window.proveedorModalSteps = new ProveedorModalSteps();
        }

        // Navegación simple y directa
        if (window.proveedorModalSteps.currentStep < 4) {
            window.proveedorModalSteps.currentStep++;
            console.log('⬆️ Avanzando al paso:', window.proveedorModalSteps.currentStep);

            // Actualizar interfaz
            window.proveedorModalSteps.updateStepDisplay();
            window.proveedorModalSteps.updateProgress();
            window.proveedorModalSteps.updateNavigationButtons();

            console.log('✅ Navegación exitosa al paso:', window.proveedorModalSteps.currentStep);
        } else {
            console.log('🛑 Ya estás en el último paso');
        }

    } catch (error) {
        console.error('❌ Error en goToNextStep:', error);
    }

    console.log('🟢 goToNextStep() EJECUTADA - FIN');
    return false; // CRÍTICO: Prevenir cualquier acción adicional
};

// Función global simple para ANTERIOR
window.goToPrevStep = function(event) {
    console.log('🔵 goToPrevStep() EJECUTADA - INICIO');

    // BLOQUEAR TODO EVENTO
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    try {
        // Asegurar que existe la instancia
        if (!window.proveedorModalSteps) {
            console.log('📝 Creando instancia de ProveedorModalSteps...');
            window.proveedorModalSteps = new ProveedorModalSteps();
        }

        // Navegación simple y directa
        if (window.proveedorModalSteps.currentStep > 1) {
            window.proveedorModalSteps.currentStep--;
            console.log('⬇️ Retrocediendo al paso:', window.proveedorModalSteps.currentStep);

            // Actualizar interfaz
            window.proveedorModalSteps.updateStepDisplay();
            window.proveedorModalSteps.updateProgress();
            window.proveedorModalSteps.updateNavigationButtons();

            console.log('✅ Navegación exitosa al paso:', window.proveedorModalSteps.currentStep);
        } else {
            console.log('🛑 Ya estás en el primer paso');
        }

    } catch (error) {
        console.error('❌ Error en goToPrevStep:', error);
    }

    console.log('🔵 goToPrevStep() EJECUTADA - FIN');
    return false; // CRÍTICO: Prevenir cualquier acción adicional
};

// Función global para FINALIZAR
window.finishProveedorSetup = function(event) {
    console.log('🟡 finishProveedorSetup() EJECUTADA');

    // BLOQUEAR TODO EVENTO
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    try {
        if (typeof window.registerProvWithFeedback === 'function') {
            window.registerProvWithFeedback();
        } else {
            console.log('❌ registerProvWithFeedback no disponible');
        }
    } catch (error) {
        console.error('❌ Error en finishProveedorSetup:', error);
    }

    return false; // CRÍTICO: Prevenir cualquier acción adicional
};

// FUNCIONES DE DEBUG ACTUALIZADAS
window.testButtons = function() {
    console.log('🧪 TEST: Verificando estado de botones');
    console.log('🧪 window.goToNextStep exists:', typeof window.goToNextStep);
    console.log('🧪 window.goToPrevStep exists:', typeof window.goToPrevStep);
    console.log('🧪 window.proveedorModalSteps exists:', !!window.proveedorModalSteps);

    // Verificar que los botones existan en el DOM
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');

    console.log('🧪 next-btn DOM element:', !!nextBtn);
    console.log('🧪 prev-btn DOM element:', !!prevBtn);

    if (nextBtn) {
        console.log('🧪 next-btn onclick:', nextBtn.getAttribute('onclick'));
        console.log('🧪 next-btn classList:', nextBtn.classList.toString());
        console.log('🧪 next-btn disabled:', nextBtn.disabled);
    }

    if (prevBtn) {
        console.log('🧪 prev-btn onclick:', prevBtn.getAttribute('onclick'));
        console.log('🧪 prev-btn classList:', prevBtn.classList.toString());
        console.log('🧪 prev-btn disabled:', prevBtn.disabled);
    }

    if (window.proveedorModalSteps) {
        console.log('🧪 currentStep:', window.proveedorModalSteps.currentStep);
        console.log('🧪 totalSteps:', window.proveedorModalSteps.totalSteps);
    }
};

window.forceTest = function() {
    console.log('🚨 FORCE TEST: Probando nueva navegación directa');
    console.log('🚨 Ejecutando window.goToNextStep() directamente...');
    window.goToNextStep();
};

// Inicializar cuando DOM esté listo
$(document).ready(function() {
    console.log('📋 ProveedorModalSteps cargado');

    // Asegurar que las funciones estén en el scope global
    window.nextStepHandler = window.nextStepHandler;
    window.prevStepHandler = window.prevStepHandler;

    console.log('✅ window.nextStepHandler:', typeof window.nextStepHandler);
    console.log('✅ window.prevStepHandler:', typeof window.prevStepHandler);

    // CONFIGURAR EVENT LISTENERS ROBUSTOS
    function setupButtonListeners() {
        console.log('🔧 Configurando event listeners para botones...');

        // Remover listeners existentes para evitar duplicados
        const nextBtn = document.getElementById('next-btn');
        const prevBtn = document.getElementById('prev-btn');
        const finishBtn = document.getElementById('finish-btn');

        if (nextBtn) {
            // Eliminar listeners anteriores
            const newNextBtn = nextBtn.cloneNode(true);
            nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);

            newNextBtn.addEventListener('click', function(e) {
                console.log('🔴 EVENT LISTENER: NEXT clickeado');
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                if (typeof window.nextStepHandler === 'function') {
                    window.nextStepHandler(e);
                } else {
                    console.log('⚡ Usando navegación simple');
                    window.simpleNext(e);
                }
                return false;
            }, true); // Use capture phase
            console.log('✅ Event listener agregado a next-btn');
        }

        if (prevBtn) {
            // Eliminar listeners anteriores
            const newPrevBtn = prevBtn.cloneNode(true);
            prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);

            newPrevBtn.addEventListener('click', function(e) {
                console.log('🔴 EVENT LISTENER: PREV clickeado');
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                if (typeof window.prevStepHandler === 'function') {
                    window.prevStepHandler(e);
                } else {
                    console.log('⚡ Usando navegación simple');
                    window.simplePrev(e);
                }
                return false;
            }, true); // Use capture phase
            console.log('✅ Event listener agregado a prev-btn');
        }

        if (finishBtn) {
            // Eliminar listeners anteriores
            const newFinishBtn = finishBtn.cloneNode(true);
            finishBtn.parentNode.replaceChild(newFinishBtn, finishBtn);

            newFinishBtn.addEventListener('click', function(e) {
                console.log('🔴 EVENT LISTENER: FINISH clickeado');
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                if (typeof window.registerProvWithFeedback === 'function') {
                    window.registerProvWithFeedback();
                } else {
                    console.log('❌ registerProvWithFeedback no disponible');
                }
                return false;
            }, true);
            console.log('✅ Event listener agregado a finish-btn');
        }
    }

    // PROBAR CON AMBOS IDs DE MODAL (#ModalProveedor y #modal)
    $('#ModalProveedor, #modal').on('shown.bs.modal', function() {
        console.log('🔄 Modal de proveedor mostrado - inicializando sistema de pasos');
        console.log('🔄 Modal ID:', $(this).attr('id'));

        if (!window.proveedorModalSteps) {
            console.log('✅ Creando instancia de ProveedorModalSteps');
            window.proveedorModalSteps = new ProveedorModalSteps();
        } else {
            console.log('✅ Reutilizando instancia existente de ProveedorModalSteps');
            // Reinicializar al paso 1
            window.proveedorModalSteps.currentStep = 1;
            window.proveedorModalSteps.updateStepDisplay();
            window.proveedorModalSteps.updateProgress();
            window.proveedorModalSteps.updateNavigationButtons();
        }

        // Setup event listeners cada vez que se abra el modal
        setTimeout(function() {
            setupButtonListeners();

            // DEBUG: Verificar que las funciones estén accesibles
            console.log('🧪 Post-inicialización check:');
            window.testButtons();
        }, 100);
    });

    // Limpiar cuando el modal se cierre
    $('#ModalProveedor, #modal').on('hidden.bs.modal', function() {
        console.log('🚪 Modal de proveedor cerrado');
        if (window.proveedorModalSteps) {
            window.proveedorModalSteps.clearValidationErrors();
        }
    });
});

console.log('✅ Sistema de navegación por pasos para proveedores cargado correctamente');
