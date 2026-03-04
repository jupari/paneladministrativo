// JavaScript simple para el modal directo de proveedores (sin pasos)

// Función para abrir el modal directo
function openProveedorDirectModal() {
    console.log('🔵 Abriendo modal directo de proveedores');

    // Resetear modal antes de abrir
    if (typeof resetProveedorDirectModal === 'function') {
        resetProveedorDirectModal();
    }

    // Abrir modal
    $('#ModalProveedorDirect').modal('show');

    console.log('✅ Modal directo abierto');
}

// Función alternativa para el botón "Crear Proveedor"
function regProvDirect() {
    console.log('🆕 regProvDirect() - Abriendo modal directo');
    openProveedorDirectModal();
}

// Función principal para guardar proveedor (versión directa)
function saveProveedorDirect() {
    console.log('💾 Guardando proveedor directamente...');

    // Cambiar estado del botón
    setSaveButtonLoading();

    // Recolectar datos del formulario con IDs únicos (usando campos reales del modal)
    const formData = {
        tercerotipo_id: document.getElementById('direct_tercerotipo_id').value,
        tipopersona_id: document.getElementById('direct_tipopersona_id').value,
        tipoidentificacion_id: document.getElementById('direct_tipoidentificacion_id').value,
        identificacion: document.getElementById('direct_identificacion').value,
        dv: document.getElementById('direct_dv').value,
        nombres: document.getElementById('direct_nombres').value,
        apellidos: document.getElementById('direct_apellidos').value,
        nombre_establecimiento: document.getElementById('direct_nombre_establecimiento').value,
        telefono: document.getElementById('direct_telefono').value,
        celular: document.getElementById('direct_celular').value,
        correo: document.getElementById('direct_correo').value,
        correo_fe: document.getElementById('direct_correo_fe').value,
        vendedor_id: document.getElementById('direct_vendedor_id').value,
        pais_id: document.getElementById('direct_pais_id').value,
        departamento_id: document.getElementById('direct_departamento_id').value,
        ciudad_id: document.getElementById('direct_ciudad_id').value,
        direccion: document.getElementById('direct_direccion').value,
        user_id: document.getElementById('direct_user_id').value,
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    console.log('📦 Datos a enviar:', formData);

    // Limpiar errores previos
    clearAllDirectErrors();

    // Enviar datos via AJAX
    $.ajax({
        url: 'admin.proveedores.store',
        type: 'POST',
        data: formData,
        success: function(response) {
            console.log('✅ Proveedor guardado exitosamente:', response);

            // Mostrar mensaje de éxito
            toastr.success(response.message || 'Proveedor creado exitosamente');

            // Cerrar modal
            $('#ModalProveedorDirect').modal('hide');

            // Recargar tabla
            if (typeof tablaTerceros !== 'undefined' && tablaTerceros.ajax) {
                tablaTerceros.ajax.reload();
            }

            // Resetear botón
            resetSaveButton();
        },
        error: function(xhr, status, error) {
            console.error('❌ Error al guardar proveedor:', {xhr, status, error});

            let errorMessage = 'Error al guardar el proveedor';

            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                // Mostrar errores específicos de validación
                if (xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        if (errors[field] && errors[field][0]) {
                            showDirectError(field, errors[field][0]);
                        }
                    }
                } else {
                    toastr.error(errorMessage);
                }
            } else {
                toastr.error(errorMessage);
            }

            resetSaveButton();
        }
    });
}

// Funciones auxiliares para manejo del botón y errores
function setSaveButtonLoading() {
    const btn = document.getElementById('saveProveedorDirectBtn');
    const spinner = document.getElementById('saveProveedorDirectSpinner');

    if (btn && spinner) {
        btn.disabled = true;
        spinner.classList.remove('d-none');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" id="saveProveedorDirectSpinner"></span> Guardando...';
    }
}

function resetSaveButton() {
    const btn = document.getElementById('saveProveedorDirectBtn');
    const spinner = document.getElementById('saveProveedorDirectSpinner');

    if (btn && spinner) {
        btn.disabled = false;
        spinner.classList.add('d-none');
        btn.innerHTML = '<i class="fas fa-save mr-1"></i>Guardar Proveedor';
    }
}

function clearAllDirectErrors() {
    // Limpiar todos los mensajes de error
    document.querySelectorAll('[id^="error_direct_"]').forEach(element => {
        element.textContent = '';
    });

    // Remover clases de error de los campos
    document.querySelectorAll('[id^="direct_"]').forEach(element => {
        element.classList.remove('is-invalid');
    });
}

function showDirectError(fieldName, message) {
    const errorElement = document.getElementById(`error_direct_${fieldName}`);
    const fieldElement = document.getElementById(`direct_${fieldName}`);

    if (errorElement) {
        errorElement.textContent = message;
    }

    if (fieldElement) {
        fieldElement.classList.add('is-invalid');
    }

    console.warn(`⚠️ Error en ${fieldName}:`, message);
}

// Función para limpiar y resetear el modal
function resetProveedorDirectModal() {
    console.log('🔄 Reseteando modal directo de proveedores');

    // Resetear formulario
    const form = document.getElementById('proveedor-direct-form');
    if (form) {
        form.reset();
    }

    // Limpiar errores
    clearAllDirectErrors();

    // Resetear valores por defecto
    const tercerotipo = document.getElementById('direct_tercerotipo_id');
    const userId = document.getElementById('direct_user_id');

    if (tercerotipo) {
        tercerotipo.value = '2'; // ID por defecto para proveedores
    }

    if (userId && typeof auth !== 'undefined') {
        userId.value = auth.id;
    }

    // Resetear dependencias geográficas
    const departamento = document.getElementById('direct_departamento_id');
    const ciudad = document.getElementById('direct_ciudad_id');

    if (departamento) {
        departamento.innerHTML = '<option value="">Seleccione un departamento...</option>';
    }

    if (ciudad) {
        ciudad.innerHTML = '<option value="">Seleccione una ciudad...</option>';
    }

    // Resetear botón
    resetSaveButton();
}

// Auto cargar dependencias geográficas
$(document).ready(function() {
    console.log('📍 Configurando dependencias geográficas para modal directo...');

    // Configurar evento de cierre del modal
    $('#ModalProveedorDirect').on('hidden.bs.modal', function() {
        resetProveedorDirectModal();
    });

    // Configurar dependencias geográficas
    setupDirectGeographicDependencies();

    console.log('✅ Modal directo configurado correctamente');
});

function setupDirectGeographicDependencies() {
    console.log('🌍 Configurando dependencias geográficas para modal directo...');

    // País -> Departamento (usando IDs únicos del modal directo)
    $('#direct_pais_id').change(function() {
        const paisId = $(this).val();
        console.log('🌍 País seleccionado:', paisId);

        if (paisId) {
            $('#direct_departamento_id').html('<option value="">Cargando...</option>');
            $('#direct_ciudad_id').html('<option value="">Seleccione primero un departamento</option>');

            // Usar datos cargados desde el controlador
            if (typeof dataPaises !== 'undefined') {
                const pais = dataPaises.find(p => p.id == paisId);
                if (pais && pais.departamentos) {
                    let options = '<option value="">Seleccione un departamento...</option>';
                    pais.departamentos.forEach(dept => {
                        options += `<option value="${dept.id}">${dept.nombre}</option>`;
                    });
                    $('#direct_departamento_id').html(options);
                }
            }
        } else {
            $('#direct_departamento_id').html('<option value="">Seleccione un departamento...</option>');
            $('#direct_ciudad_id').html('<option value="">Seleccione una ciudad...</option>');
        }
    });

    // Departamento -> Ciudad (usando IDs únicos del modal directo)
    $('#direct_departamento_id').change(function() {
        const departamentoId = $(this).val();
        console.log('🏛️ Departamento seleccionado:', departamentoId);

        if (departamentoId) {
            $('#direct_ciudad_id').html('<option value="">Cargando...</option>');

            // Buscar ciudades en los datos cargados
            if (typeof dataPaises !== 'undefined') {
                const departamento = dataPaises
                    .flatMap(p => p.departamentos || [])
                    .find(d => d.id == departamentoId);

                if (departamento && departamento.ciudades) {
                    let options = '<option value="">Seleccione una ciudad...</option>';
                    departamento.ciudades.forEach(ciudad => {
                        options += `<option value="${ciudad.id}">${ciudad.nombre}</option>`;
                    });
                    $('#direct_ciudad_id').html(options);
                }
            }
        } else {
            $('#direct_ciudad_id').html('<option value="">Seleccione una ciudad...</option>');
        }
    });

    // Validación de email en tiempo real
    $('#direct_correo, #direct_correo_fe').on('blur', function() {
        const email = $(this).val();
        const fieldId = $(this).attr('id').replace('direct_', '');

        if (email && !isValidDirectEmail(email)) {
            showDirectError(fieldId, 'Ingrese un email válido');
        } else {
            clearFieldDirectError(fieldId);
        }
    });
}

function isValidDirectEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function clearFieldDirectError(fieldId) {
    const errorElement = document.getElementById(`error_direct_${fieldId}`);
    const fieldElement = document.getElementById(`direct_${fieldId}`);

    if (errorElement) {
        errorElement.textContent = '';
    }

    if (fieldElement) {
        fieldElement.classList.remove('is-invalid');
    }
}

console.log('✅ Modal directo de proveedores cargado correctamente');
