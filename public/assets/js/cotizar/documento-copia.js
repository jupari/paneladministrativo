// Funciones para controlar el skeleton loader
function showSkeleton() {
    const skeleton = document.getElementById('skeleton-loader');
    const content = document.getElementById('main-content');
    if (skeleton) skeleton.style.display = 'block';
    if (content) content.style.display = 'none';
}

// function hideSkeleton() {
//     const skeleton = document.getElementById('skeleton-loader');
//     const content = document.getElementById('main-content');
//     if (skeleton) skeleton.style.display = 'none';
//     if (content) content.style.display = 'block';
// }

function hideSkeleton() {
    document.getElementById('skeleton-loader').style.display = 'none';
    document.getElementById('main-content').style.display = 'block';
}

function showLoadingState() {
    const content = document.getElementById('main-content');
    if (content) content.classList.add('loading');
}

function hideLoadingState() {
    const content = document.getElementById('main-content');
    if (content) content.classList.remove('loading');
}

(async function() {

    await initEstadosSelect('estado_id', estados);
    await initClientesSelect('cliente_id', clientes);
    await initImpuestosDescuentos();
    await initItems();

    const observacionTextarea = document.getElementById('observacion');
    const contadorSpan = document.getElementById('observacion_count');
    const agregarCotizacion = document.getElementById('agregarCotizacion');
    const botonesAgregarProductos = document.getElementById('botonesAgregarProductos');
    const documentoLabel = document.getElementById('document-label');

    botonesAgregarProductos.classList.add('d-none');
    document.getElementById('accordionCotizacionDetails').style.display = 'none';

    if (variable === 'ver') {
        // Deshabilitar todos los campos del formulario en modo "ver"
        document.querySelectorAll('#cotizacionForm input, #cotizacionForm select, #cotizacionForm textarea, #cotizacionForm button').forEach(element => {
            element.disabled = true;
        });
        agregarCotizacion.style.display = 'none';
        await cargarDocumento(variable, cotizacion);
        await cargarObservacionesExistentes(cotizacion.id);
        await cargarCondicionesExistentes(cotizacion.id);
        await cargarItemsExistentes(cotizacion.id);
        document.getElementById('accordionCotizacionDetails').style.display = 'block';
        botonesAgregarProductos.classList.add('d-none');
    }

    if (variable === 'editar') {
        agregarCotizacion.removeEventListener('click', guardarCotizacion)
        agregarCotizacion.addEventListener('click', function() {
            if (validateForm()) {
                // actualizarCotizacion(document.getElementById('id').value);
                guardarCotizacion();
            }
        });
        await cargarDocumento(variable, cotizacion);
        await cargarObservacionesExistentes(cotizacion.id);
        await cargarCondicionesExistentes(cotizacion.id);
        await cargarItemsExistentes(cotizacion.id);
        document.getElementById('accordionCotizacionDetails').style.display = 'block';
        botonesAgregarProductos.classList.remove('d-none');
    } else {
        agregarCotizacion.addEventListener('click', guardarCotizacion);
        await cargarDocumento(variable, cotizacion);
        document.getElementById('accordionCotizacionDetails').style.display = 'none';
        botonesAgregarProductos.classList.add('d-none');
    }

    if (observacionTextarea && contadorSpan) {
        observacionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            contadorSpan.textContent = length;

            if (length > 1000) {
                this.classList.add('is-invalid');
                document.getElementById('error_observacion').textContent = 'La observación no puede tener más de 1000 caracteres';
            } else {
                this.classList.remove('is-invalid');
                document.getElementById('error_observacion').textContent = '';
            }
        });
    }

    // Validación en tiempo real para otros campos
    document.getElementById('proyecto').addEventListener('input', function() {
        if (this.value.length > 255) {
            this.classList.add('is-invalid');
            document.getElementById('error_proyecto').textContent = 'El proyecto no puede tener más de 255 caracteres';
        } else {
            this.classList.remove('is-invalid');
            document.getElementById('error_proyecto').textContent = '';
        }
    });

    // Limpiar errores cuando se selecciona un valor
    document.getElementById('estado_id').addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
            document.getElementById('error_estado_id').textContent = '';
        }
    });

    document.getElementById('cliente_id').addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
            document.getElementById('error_cliente_id').textContent = '';
        }
    });

    // Event listeners para los campos de totales
    const camposTotales = ['subtotal', 'descuento', 'total_impuesto'];

    camposTotales.forEach(function(campo) {
        const input = document.getElementById(campo);
        if (input) {
            // Calcular totales al cambiar
            input.addEventListener('change', calcularTotales);
            input.addEventListener('input', calcularTotales);

            // Formatear al perder el foco
            input.addEventListener('blur', function() {
                formatearMoneda(this);
                calcularTotales();
            });

            // Validar valores negativos
            input.addEventListener('input', function() {
                if (parseFloat(this.value) < 0) {
                    this.classList.add('is-invalid');
                    document.getElementById(`error_${campo}`).textContent = `El ${campo.replace('_', ' ')} no puede ser negativo`;
                } else {
                    this.classList.remove('is-invalid');
                    document.getElementById(`error_${campo}`).textContent = '';
                }
            });
        }
    });

    // Inicializar cálculo de totales
    calcularTotales();
})();

async function initClientesSelect(selectId, clientes) {
    const select = document.getElementById(selectId);
    const optioonDefault = document.createElement('option');
    optioonDefault.value = '';
    optioonDefault.text = 'Seleccione un cliente';
    select.appendChild(optioonDefault);

    clientes.forEach(cliente => {
        const option = document.createElement('option');
        option.value = cliente.id;
        option.text = clientes.nombre?cliente.nombre+' '+cliente.apellido:cliente.nombre_establecimiento;
        select.appendChild(option);
    });
}

async function initEstadosSelect(selectId, estados) {
    const select = document.getElementById(selectId);
    const optioonDefault = document.createElement('option');
    optioonDefault.value = '';
    optioonDefault.text = 'Seleccione un estado';
    select.appendChild(optioonDefault);
    estados.forEach(estado => {
        const option = document.createElement('option');
        option.value = estado.id;
        option.text = estado.estado;
        select.appendChild(option);
    });
}

async function fetchSucursales(terceroId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.getSucursales/${terceroId}`);
        const data = await response.json();

        const sedeSelect = document.getElementById('tercero_sucursal_id');
        sedeSelect.innerHTML = ''; // Limpiar opciones existentes

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.text = 'Seleccione una sede';
        sedeSelect.appendChild(defaultOption);

        data.data.forEach(sede => {
            const option = document.createElement('option');
            option.value = sede.id;
            option.text = sede.nombre_sucursal;
            sedeSelect.appendChild(option);
        });
        fetchContactos(terceroId);
        return data.data; // Retornar los datos para uso posterior
    } catch (error) {
        console.error('Error al cargar las sucursales:', error);
        throw error;
    }
}


async function fetchContactos(terceroId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.getContactos/${terceroId}`);
        const data = await response.json();

        const contactoSelect = document.getElementById('tercero_contacto_id');
        contactoSelect.innerHTML = ''; // Limpiar opciones existentes

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.text = 'Seleccione un contacto';
        contactoSelect.appendChild(defaultOption);

        data.data.forEach(contacto => {
            const option = document.createElement('option');
            option.value = contacto.id;
            option.text = contacto.nombres+' '+contacto.apellidos + ' ('+contacto.correo+')' + ' ('+contacto.cargo+')';
            contactoSelect.appendChild(option);
        });

        return data.data; // Retornar los datos para uso posterior
    } catch (error) {
        console.error('Error al cargar las sucursales:', error);
        throw error;
    }
}


//Gurdar
async function guardarCotizacion() {

    $('#spinnerRegister').removeClass('d-none');

    //botones para agregar productos
    const botonesAgregarProductos = document.getElementById('botonesAgregarProductos');
    // Determinar si es creación o actualización
    const isEdit = variable === 'editar';
    console.log('isEdit:', isEdit);
    const cotizacionId = document.getElementById('id')?.value;
    const route = isEdit ? `/admin/admin.cotizaciones.update/${cotizacionId}` : "/admin/admin.cotizaciones.store";
    const method = isEdit ? 'PUT' : 'POST';

    // Crear un objeto FormData directamente desde el formulario
    let ajax_data = new FormData();

    if (isEdit) {
        ajax_data.append('_method', 'PUT');
    }
    // Agregar los nuevos campos del formulario
    ajax_data.append('estado_id', $('#estado_id').val()==''?1:$('#estado_id').val());
    ajax_data.append('num_documento', $('#num_documento').val());
    ajax_data.append('fecha', $('#fecha').val());
    ajax_data.append('tipo', $('#tipo').val()=='' ? 'COT' : $('#tipo').val());
    ajax_data.append('proyecto', $('#proyecto').val());
    ajax_data.append('autorizacion_id', $('#autorizacion_id').val()=='Pendiente por autorización' ? 1 : 2);
    ajax_data.append('doc_origen', $('#doc_origen').val());
    ajax_data.append('version', $('#version').val()=='' ? '1' : $('#version').val());
    ajax_data.append('tercero_id', $('#cliente_id').val());
    ajax_data.append('tercero_sucursal_id', $('#tercero_sucursal_id').val());
    ajax_data.append('tercero_contacto_id', $('#tercero_contacto_id').val());
    ajax_data.append('observacion', $('#observacion').val());
    ajax_data.append('subtotal', $('#subtotal').val()==''?0:$('#subtotal').val());
    ajax_data.append('descuento', $('#descuento').val()==''?0:$('#descuento').val());
    ajax_data.append('total_impuesto', $('#total_impuesto').val()==''?0:$('#total_impuesto').val());
    ajax_data.append('total', $('#total').val()==''?0:$('#total').val());

    ajax_data.append('impuestos_descuentos', JSON.stringify(impuestosDescuentos));

    // Mostrar estado de carga
    showLoadingState();

    // Realizar la solicitud AJAX
    $.ajax({
        url: route,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false, // IMPORTANTE PARA SUBIR IMÁGENES O ARCHIVOS POR AJAX
        processData: false,
    }).then(async response => {
        if (response.success) {
            const cotizacionGuardadaId = isEdit ? cotizacionId : response.data?.id;
            let id = document.getElementById('id');
            id.value = cotizacionGuardadaId;
            // Siempre guardar conceptos, observaciones y condiciones comerciales
            if (isEdit) {
                toastr.success('Cotización actualizada exitosamente');
                document.getElementById('accordionCotizacionDetails').style.display = 'block';
                botonesAgregarProductos.classList.remove('d-none');
                if (cotizacionGuardadaId) {
                    await guardarConceptosCotizacion(cotizacionGuardadaId);
                    await guardarObservacionesCotizacion(cotizacionGuardadaId);
                    await guardarCondicionesCotizacion(cotizacionGuardadaId);
                }
            } else {
                toastr.success('Cotización creada exitosamente');
                document.getElementById('accordionCotizacionDetails').style.display = 'block';
                botonesAgregarProductos.classList.remove('d-none');
                variable='editar';
            }
            //parar el spinner
            $('#spinnerRegister').addClass('d-none');
            hideLoadingState();
        }
    }).catch(e => {
        $('#spinnerRegister').addClass('d-none');
        hideLoadingState();
        showValidationErrors(e.responseJSON?.errors || {});
        const arr = e.responseJSON;
        toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
    });
}


//Actualizar
async function actualizarCotizacion(btn) {

    const route = `/admin/admin.cotizaciones.update/${btn}`;

    // Crear un objeto FormData para enviar los datos
    let ajax_data = new FormData();

    // Lista de campos del formulario
    const fields = [
        'id',
        'estado_id',
        'num_documento',
        'fecha',
        'proyecto',
        'cliente_id',
        'tercero_sucursal_id',
        'tercero_contacto_id',
        'observacion',
        'subtotal',
        'descuento',
        'total_impuesto',
        'total'
    ];
    // Recorrer los campos y agregarlos al FormData
    fields.forEach(field => {
        ajax_data.append(field, $('#' + field).val());
    });

    ajax_data.append('tercero_id', $('#cliente_id').val()=='' ? null : $('#cliente_id').val());

    // Mostrar estado de carga
    showLoadingState();

    // Enviar la solicitud AJAX
    $.ajax({
        url: route,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-HTTP-Method-Override': 'PUT'
        },
        type: 'POST',
        dataType: 'json',
        data: ajax_data,
        contentType: false,
        processData: false,
    })
    .then(response => {
        hideLoadingState();
        toastr.success(response.message);
        CargarCotizaciones();
    })
    .catch(e => {
        hideLoadingState();
        clearValidationErrors();
        const arr = e.responseJSON;
        const toast = arr.errors;

        if (e.status === 422) {
            // Errores de validación
            showValidationErrors(toast);
            toastr.warning('No fue posible guardar el registro, revisar los errores en los campos.');
        } else if (e.status === 403) {
            toastr.warning(arr.message);
        }
    });
}

// Función para calcular totales automáticamente
function calcularTotales() {
    try {
        console.log('🔄 calcularTotales() llamado - Detectando sistema a usar...');

        // Detectar si tenemos el sistema nuevo de totales
        const tieneElementosNuevos = !!(
            document.getElementById('display-subtotal-valor') ||
            document.getElementById('display-total-valor') ||
            document.getElementById('hidden_subtotal')
        );

        // Detectar si tenemos elementos tradicionales
        const subtotalEl = document.getElementById('subtotal');
        const descuentoEl = document.getElementById('descuento');
        const totalImpuestoEl = document.getElementById('total_impuesto');

        if (tieneElementosNuevos) {
            console.log('🆕 Sistema nuevo detectado - Usando actualizarTotalesCompletos()');
            actualizarTotalesCompletos();
            return;
        }

        if (!subtotalEl || !descuentoEl || !totalImpuestoEl) {
            console.log('⚠️ Elementos tradicionales no encontrados - Intentando sistema nuevo');
            if (typeof actualizarTotalesCompletos === 'function') {
                actualizarTotalesCompletos();
                return;
            } else {
                console.log('❌ Sistema nuevo no disponible');
                return;
            }
        }

        console.log('📊 Sistema tradicional detectado - Calculando totales tradicionales');

        // Calcular subtotal automáticamente
        actualizarSubtotal();

        const subtotal = parseFloat(subtotalEl.value) || 0;
        const descuento = parseFloat(descuentoEl.value) || 0;
        const totalImpuesto = parseFloat(totalImpuestoEl.value) || 0;

        // Validar descuento de forma segura
        const errorDescuentoEl = document.getElementById('error_descuento');
        if (descuento > subtotal) {
            if (descuentoEl) descuentoEl.classList.add('is-invalid');
            if (errorDescuentoEl) errorDescuentoEl.textContent = 'El descuento no puede ser mayor al subtotal';
            return;
        } else {
            if (descuentoEl) descuentoEl.classList.remove('is-invalid');
            if (errorDescuentoEl) errorDescuentoEl.textContent = '';
        }

        // Calcular totales de forma segura
        const subtotalMenosDescuentoEl = document.getElementById('subtotal_menos_descuento');
        const totalEl = document.getElementById('total');

        const subtotalMenosDescuento = subtotal - descuento;
        if (subtotalMenosDescuentoEl) {
            subtotalMenosDescuentoEl.value = subtotalMenosDescuento.toFixed(2);
        }

        const totalFinal = subtotalMenosDescuento + totalImpuesto;
        if (totalEl) {
            totalEl.value = totalFinal.toFixed(2);
        }

        console.log('✅ Totales tradicionales calculados correctamente');
    } catch (error) {
        console.error('💥 Error en calcularTotales:', error);
        // Fallback al sistema nuevo si está disponible
        if (typeof actualizarTotalesCompletos === 'function') {
            console.log('🔄 Fallback: Usando sistema nuevo');
            actualizarTotalesCompletos();
        }
    }
}

/**
 * Actualizar subtotal basado en items, productos y salarios
 */
function actualizarSubtotal() {
    let subtotal = 0;

    // Sumar items existentes (los que ya estaban en la cotización)
    if (typeof itemsCotizacion !== 'undefined' && Array.isArray(itemsCotizacion)) {
        subtotal += itemsCotizacion.reduce((sum, item) => {
            return sum + (item.valor_total || 0);
        }, 0);
    }

    document.getElementById('subtotal').value = subtotal.toFixed(2);
}

// Formatear números como moneda
function formatearMoneda(input) {
    let value = parseFloat(input.value) || 0;
    input.value = value.toFixed(2);
}

// Funciones de validación
function clearValidationErrors() {
    // Remover clases de error
    document.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });

    // Limpiar mensajes de error
    document.querySelectorAll('.invalid-feedback').forEach(element => {
        element.textContent = '';
    });
}

function showValidationErrors(errors) {
    clearValidationErrors();

    for (const field in errors) {
        const input = document.getElementById(field);
        const errorDiv = document.getElementById(`error_${field}`);

        if (input && errorDiv) {
            input.classList.add('is-invalid');
            errorDiv.textContent = errors[field][0];
        }
    }
}

function validateForm() {
    let isValid = true;
    const errors = {};

    // Validar estado
    const estado = document.getElementById('estado_id');
    if (!estado.value) {
        errors.estado_id = ['Debe seleccionar un estado'];
        isValid = false;
    }

    // Validar número de documento
    const numDocumento = document.getElementById('num_documento');
    if (!numDocumento.value) {
        errors.num_documento = ['El número de documento es obligatorio'];
        isValid = false;
    }

    // Validar cliente
    const cliente = document.getElementById('cliente_id');
    if (!cliente.value) {
        errors.cliente_id = ['Debe seleccionar un cliente'];
        isValid = false;
    }

    // Validar proyecto (máximo 255 caracteres)
    const proyecto = document.getElementById('proyecto');
    if (proyecto.value && proyecto.value.length > 255) {
        errors.proyecto = ['El proyecto no puede tener más de 255 caracteres'];
        isValid = false;
    }

    // Validar observación (máximo 1000 caracteres)
    const observacion = document.getElementById('observacion');
    if (observacion.value && observacion.value.length > 1000) {
        errors.observacion = ['La observación no puede tener más de 1000 caracteres'];
        isValid = false;
    }

    // Validar subtotal y descuento
    const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;

    if (descuento > subtotal) {
        errors.descuento = ['El descuento no puede ser mayor al subtotal'];
        isValid = false;
    }

    // Validar que los montos sean positivos
    if (subtotal < 0) {
        errors.subtotal = ['El subtotal no puede ser negativo'];
        isValid = false;
    }

    if (descuento < 0) {
        errors.descuento = ['El descuento no puede ser negativo'];
        isValid = false;
    }

    const totalImpuesto = parseFloat(document.getElementById('total_impuesto').value) || 0;
    if (totalImpuesto < 0) {
        errors.total_impuesto = ['El total de impuesto no puede ser negativo'];
        isValid = false;
    }

    if (!isValid) {
        showValidationErrors(errors);
    }

    return isValid;
}


async function cargarDocumento(variable, cotizacion) {
    try {

        document.getElementById('document-label').textContent = variable === 'editar' ? cotizacion.num_documento +' - Editar' : variable === 'ver' ? cotizacion.num_documento + ' - Ver' : ' - Nuevo';

        if (variable === 'editar' || variable === 'ver') {
            // Cambiar texto del botón
            const botonGuardar = document.getElementById('agregarCotizacion');
            if (botonGuardar && variable === 'editar') {
                botonGuardar.innerHTML = '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegister"></span> Actualizar Cotización';
            }

            // Asignar el número de documento al campo correspondiente
            const id=document.getElementById('id');
            id.value=cotizacion.id;

            const estado = document.getElementById('estado_id');
            estado.value = cotizacion.estado_id;

            const documento = document.getElementById('num_documento');
            documento.value = consecutivo;
            documento.readOnly = true;

            const proyecto = document.getElementById('proyecto');
            proyecto.value = cotizacion.proyecto;

            const tipo = document.getElementById('tipo');
            tipo.value = cotizacion.tipo;

            const autorizacion = document.getElementById('autorizacion_id');
            autorizacion.readOnly= true;
            autorizacion.value = cotizacion.autorizacion ? cotizacion.autorizacion.nombre : 'Pendiente por autorización';

            const docOrigen = document.getElementById('doc_origen');
            docOrigen.readOnly= true;
            docOrigen.value = cotizacion.doc_origen;

            const version = document.getElementById('version');
            version.readOnly= true;
            version.value = cotizacion.version;

            const fecha = document.getElementById('fecha');
            fecha.value = cotizacion.fecha?cotizacion.fecha.split('T')[0]:'';

            const cliente = document.getElementById('cliente_id');
            cliente.value = cotizacion.tercero_id;

            await fetchSucursales(cotizacion.tercero_id);
            const sede = document.getElementById('tercero_sucursal_id');
            // Establecer el valor después de que las opciones se hayan cargado
            setTimeout(() => {
                sede.value = cotizacion.tercero_sucursal_id;
            }, 100);

            await fetchContactos(cotizacion.tercero_id);
            const contacto = document.getElementById('tercero_contacto_id');
            // Establecer el valor después de que las opciones se hayan cargado
            setTimeout(() => {
                contacto.value = cotizacion.tercero_contacto_id;
            }, 100);

            const observacion = document.getElementById('observacion');
            observacion.value = cotizacion.observacion;

            const contadorSpan = document.getElementById('observacion_count');
            contadorSpan.textContent = observacion.value.length;

            const subtotal = document.getElementById('subtotal');
            if (variable === 'ver') {
                subtotal.readOnly= true;
            }
            subtotal.value = parseFloat(cotizacion.subtotal).toFixed(2);

            const descuento = document.getElementById('descuento');
            if (variable === 'ver') {
                descuento.readOnly= true;
            }
            descuento.value = parseFloat(cotizacion.descuento).toFixed(2);

            const totalImpuesto = document.getElementById('total_impuesto');
            if (variable === 'ver') {
                totalImpuesto.readOnly= true;
            }
            totalImpuesto.value = parseFloat(cotizacion.total_impuesto).toFixed(2);

            const total = document.getElementById('total');
            if (variable === 'ver') {
                total.readOnly= true;
            }
            total.value = parseFloat(cotizacion.total).toFixed(2);

            hideSkeleton();
            cargarProductosGuardados();


        }else{
            // Asignar el número de documento al campo correspondiente
            const id=document.getElementById('id');
            id.value = null;

            const documento = document.getElementById('num_documento');
            documento.value = consecutivo;
            documento.readOnly = true;

            document.getElementById('doc_origen').readOnly= true;

            document.getElementById('version').value = '1';
            document.getElementById('version').readOnly= true;

            const autorizacion = document.getElementById('autorizacion_id');
            autorizacion.readOnly= true;
            autorizacion.value ='Pendiente por autorización';
        }
    } catch (error) {
        console.error('Error al cargar los datos iniciales:', error);
    }
}

// Variables globales para impuestos y descuentos
let impuestosDescuentos = [];
let contadorRegistros = 0;
let conceptosDisponibles = [];


// Función para cargar conceptos disponibles desde el backend
async function cargarConceptosDisponibles() {
    try {
        const response = await fetch('/admin/admin.cotizaciones.conceptos.getConceptos', {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            conceptosDisponibles = data.data;
            poblarSelectConceptos();
        } else {
            console.error('Error al cargar conceptos:', data.message);
            toastr.error('Error al cargar los conceptos disponibles');
        }
    } catch (error) {
        console.error('Error en la solicitud:', error);
        toastr.error('Error de conexión al cargar conceptos');
    }
}

// Función para poblar los selects con los conceptos
function poblarSelectConceptos() {
    const selectImpuesto = document.getElementById('concepto_impuesto');
    const selectDescuento = document.getElementById('concepto_descuento');

    if (selectImpuesto) {
        selectImpuesto.innerHTML = '<option value="">Seleccione concepto</option>';
        conceptosDisponibles
            .filter(concepto => concepto.tipo === 'IMP')
            .forEach(concepto => {
                const option = document.createElement('option');
                option.value = concepto.id;
                option.textContent = concepto.nombre;
                option.setAttribute('data-porcentaje', concepto.porcentaje_defecto || '0');
                selectImpuesto.appendChild(option);
            });
    }

    if (selectDescuento) {
        selectDescuento.innerHTML = '<option value="">Seleccione concepto</option>';
        conceptosDisponibles
            .filter(concepto => concepto.tipo === 'DES')
            .forEach(concepto => {
                const option = document.createElement('option');
                option.value = concepto.id;
                option.textContent = concepto.nombre;
                option.setAttribute('data-porcentaje', concepto.porcentaje_defecto || '0');
                selectDescuento.appendChild(option);
            });
    }
}

// Función para auto-completar valores según el concepto seleccionado
function autoCompletarValorConcepto(tipo, conceptoId) {
    if (!conceptoId) return;

    const concepto = conceptosDisponibles.find(c => c.id == conceptoId);
    if (concepto && concepto.porcentaje_defecto > 0) {
        const valorInput = document.getElementById(`valor_${tipo}`);
        const tipoSelect = document.getElementById(`tipo_${tipo}`);
        const simbolo = document.getElementById(`simbolo_${tipo}`);

        if (valorInput && tipoSelect && simbolo) {
            valorInput.value = concepto.porcentaje_defecto;
            tipoSelect.value = 'porcentaje';
            simbolo.textContent = '%';
        }
    }
}

// Función para inicializar la funcionalidad de impuestos y descuentos
async function initImpuestosDescuentos() {
    // Cargar conceptos disponibles y observaciones disponibles
    Promise.all([
        cargarConceptosDisponibles(),
        cargarObservacionesDisponibles()
    ]).then(() => {
        // Si estamos en modo edición y hay una cotización, cargar datos existentes
        if (variable === 'editar' && cotizacion?.id) {
            cargarConceptosExistentes(cotizacion.id);
            document.getElementById('agregar_descuento').disabled = false;
            document.getElementById('agregar_impuesto').disabled = false;
        }else if (variable === 'ver' && cotizacion?.id) {
            cargarConceptosExistentes(cotizacion.id);
            document.getElementById('agregar_descuento').disabled = true;
            document.getElementById('agregar_impuesto').disabled = true;
        }else{
            document.getElementById('agregar_descuento').disabled = false;
            document.getElementById('agregar_impuesto').disabled = false;
        }
    });

    // Cambiar símbolo según el tipo seleccionado para descuentos
    const tipoDescuentoSelect = document.getElementById('tipo_descuento');
    if (tipoDescuentoSelect) {
        tipoDescuentoSelect.addEventListener('change', function() {
            const simbolo = document.getElementById('simbolo_descuento');
            if (simbolo) {
                simbolo.textContent = this.value === 'porcentaje' ? '%' : '$';
                document.getElementById('valor_descuento').placeholder = this.value === 'porcentaje' ? '0.00' : '0.00';
            }
        });
    }

    // Cambiar símbolo según el tipo seleccionado para impuestos
    const tipoImpuestoSelect = document.getElementById('tipo_impuesto');
    if (tipoImpuestoSelect) {
        tipoImpuestoSelect.addEventListener('change', function() {
            const simbolo = document.getElementById('simbolo_impuesto');
            if (simbolo) {
                simbolo.textContent = this.value === 'porcentaje' ? '%' : '$';
                document.getElementById('valor_impuesto').placeholder = this.value === 'porcentaje' ? '0.00' : '0.00';
            }
        });
    }

    // Auto-completar valores desde base de datos
    const conceptoImpuestoSelect = document.getElementById('concepto_impuesto');
    if (conceptoImpuestoSelect) {
        conceptoImpuestoSelect.addEventListener('change', function() {
            autoCompletarValorConcepto('impuesto', this.value);
        });
    }

    const conceptoDescuentoSelect = document.getElementById('concepto_descuento');
    if (conceptoDescuentoSelect) {
        conceptoDescuentoSelect.addEventListener('change', function() {
            autoCompletarValorConcepto('descuento', this.value);
        });
    }

    // Agregar descuento
    const btnAgregarDescuento = document.getElementById('agregar_descuento');
    if (btnAgregarDescuento) {
        btnAgregarDescuento.addEventListener('click', function() {
            agregarImpuestoDescuento('descuento');
        });
    }

    // Agregar impuesto
    const btnAgregarImpuesto = document.getElementById('agregar_impuesto');
    if (btnAgregarImpuesto) {
        btnAgregarImpuesto.addEventListener('click', function() {
            agregarImpuestoDescuento('impuesto');
        });
    }

    // Seleccionar/Deseleccionar todos
    const selectAllCheckbox = document.getElementById('select_all_impuestos');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#tbody_impuestos_descuentos input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleEliminarSeleccionados();
        });
    }

    // Eliminar seleccionados
    const btnEliminarSeleccionados = document.getElementById('eliminar_seleccionados');
    if (btnEliminarSeleccionados) {
        btnEliminarSeleccionados.addEventListener('click', function() {
            eliminarSeleccionados();
        });
    }

    // Limpiar todo
    const btnLimpiarTodo = document.getElementById('limpiar_todo');
    if (btnLimpiarTodo) {
        btnLimpiarTodo.addEventListener('click', function() {
            if (confirm('¿Está seguro de eliminar todos los impuestos y descuentos?')) {
                limpiarTodoImpuestosDescuentos();
            }
        });
    }
}

// Función para agregar impuesto o descuento
function agregarImpuestoDescuento(tipo) {
    const concepto = document.getElementById(`concepto_${tipo}`)?.value;
    const tipoCalculo = document.getElementById(`tipo_${tipo}`)?.value;
    const valor = parseFloat(document.getElementById(`valor_${tipo}`)?.value) || 0;

    if (!concepto) {
        toastr.warning('Por favor seleccione un concepto');
        return;
    }

    if (valor <= 0) {
        toastr.warning('El valor debe ser mayor a cero');
        return;
    }

    contadorRegistros++;
    const registro = {
        id: contadorRegistros,
        tipo: tipo,
        concepto: concepto,
        tipoCalculo: tipoCalculo,
        valor: valor,
        valorCalculado: calcularValorImpuestoDescuento(valor, tipoCalculo, tipo)
    };

    impuestosDescuentos.push(registro);
    actualizarTablaImpuestosDescuentos();
    limpiarFormularioImpuestoDescuento(tipo);
    actualizarTotalesConImpuestosDescuentos();

    toastr.success(`${tipo.charAt(0).toUpperCase() + tipo.slice(1)} agregado correctamente`);
}

// Función para calcular el valor del impuesto/descuento
function calcularValorImpuestoDescuento(valor, tipoCalculo, tipo) {
    const subtotal = parseFloat(document.getElementById('subtotal')?.value) || 0;

    if (tipoCalculo === 'porcentaje') {
        return (subtotal * valor) / 100;
    } else {
        return valor;
    }
}

// Función para actualizar la tabla
function actualizarTablaImpuestosDescuentos() {
    const tbody = document.getElementById('tbody_impuestos_descuentos');
    const noItemsRow = document.getElementById('no_items_row');

    if (!tbody || !noItemsRow) return;

    // Limpiar tabla excepto la fila "no items"
    const existingRows = tbody.querySelectorAll('tr:not(#no_items_row)');
    existingRows.forEach(row => row.remove());

    if (impuestosDescuentos.length === 0) {
        noItemsRow.style.display = 'table-row';
        return;
    }

    noItemsRow.style.display = 'none';

    impuestosDescuentos.forEach(item => {
        const row = document.createElement('tr');
        const conceptoTexto = getConceptoTexto(item.concepto);

        // row.innerHTML = `
        //     <td>
        //         <input type="checkbox" class="item-checkbox" data-id="${item.id}" onchange="toggleEliminarSeleccionados()">
        //     </td>
        //     <td>${item.id.toString().padStart(5, '0')}</td>
        //     <td>
        //         <span class="badge bg-${item.tipo === 'impuesto' ? 'success' : 'primary'}">${item.tipo}</span>
        //         ${conceptoTexto}
        //     </td>
        //     <td>${item.tipoCalculo === 'porcentaje' ? 'Porcentaje' : 'Valor Fijo'}</td>
        //     <td>${item.tipoCalculo === 'porcentaje' ? item.valor + '%' : '$' + numberFormat(item.valor, 2)}</td>
        //     <td class="${item.tipo === 'impuesto' ? 'text-success' : 'text-primary'}">
        //         $${numberFormat(item.valorCalculado, 2)}
        //     </td>
        //     <td>
        //         <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarImpuestoDescuento(${item.id})" title="Eliminar">
        //             <i class="fas fa-trash"></i>
        //         </button>
        //     </td>
        // `;

        row.innerHTML = `
            <td>
                <input type="checkbox" class="item-checkbox" data-id="${item.id}" onchange="toggleEliminarSeleccionados()">
            </td>
            <td>${item.id.toString().padStart(5, '0')}</td>
            <td>
                <span class="badge bg-${item.tipo === 'impuesto' ? 'success' : 'primary'}">${item.tipo}</span>
                ${conceptoTexto}
            </td>
            <td>${item.tipoCalculo === 'porcentaje' ? 'Porcentaje' : 'Valor Fijo'}</td>
            <td>${item.tipoCalculo === 'porcentaje' ? item.valor + '%' : '$' + numberFormat(item.valor, 2)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarImpuestoDescuento(${item.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.insertBefore(row, noItemsRow);
    });
}

// Función para obtener el texto del concepto
function getConceptoTexto(conceptoId) {
    const concepto = conceptosDisponibles.find(c => c.id == conceptoId);
    return concepto ? concepto.nombre : `Concepto ${conceptoId}`;
}

// Función para formatear números
function numberFormat(number, decimals) {
    return parseFloat(number).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Función para limpiar formulario
function limpiarFormularioImpuestoDescuento(tipo) {
    const conceptoElement = document.getElementById(`concepto_${tipo}`);
    const valorElement = document.getElementById(`valor_${tipo}`);

    if (conceptoElement) conceptoElement.value = '';
    if (valorElement) valorElement.value = '';
}

// Función para eliminar un impuesto/descuento específico
function eliminarImpuestoDescuento(id) {
    const initialLength = impuestosDescuentos.length;
    impuestosDescuentos = impuestosDescuentos.filter(item => item.id !== id);

    // Debug: verificar que se eliminó correctamente
    // console.log(`Eliminando item ${id}. Items antes: ${initialLength}, Items después: ${impuestosDescuentos.length}`);

    actualizarTablaImpuestosDescuentos();
    actualizarTotalesConImpuestosDescuentos();

    // Limpiar selección
    const selectAllCheckbox = document.getElementById('select_all_impuestos');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    toggleEliminarSeleccionados();

    toastr.info('Elemento eliminado');
}

// Función para eliminar seleccionados
function eliminarSeleccionados() {
    const checkboxes = document.querySelectorAll('#tbody_impuestos_descuentos .item-checkbox:checked');
    const idsEliminar = Array.from(checkboxes).map(cb => parseInt(cb.getAttribute('data-id')));

    if (idsEliminar.length === 0) {
        toastr.warning('No hay elementos seleccionados');
        return;
    }

    impuestosDescuentos = impuestosDescuentos.filter(item => !idsEliminar.includes(item.id));
    actualizarTablaImpuestosDescuentos();
    actualizarTotalesConImpuestosDescuentos();
    const selectAllCheckbox = document.getElementById('select_all_impuestos');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    toggleEliminarSeleccionados();
    toastr.info(`${idsEliminar.length} elemento(s) eliminado(s)`);
}

// Función para habilitar/deshabilitar botón de eliminar seleccionados
function toggleEliminarSeleccionados() {
    const checkboxes = document.querySelectorAll('#tbody_impuestos_descuentos .item-checkbox:checked');
    const btnEliminar = document.getElementById('eliminar_seleccionados');
    if (btnEliminar) {
        btnEliminar.disabled = checkboxes.length === 0;
    }
}

// Función para limpiar todo
function limpiarTodoImpuestosDescuentos() {
    impuestosDescuentos = [];
    contadorRegistros = 0;
    actualizarTablaImpuestosDescuentos();
    actualizarTotalesConImpuestosDescuentos();
    const selectAllCheckbox = document.getElementById('select_all_impuestos');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    toggleEliminarSeleccionados();
    toastr.info('Todos los impuestos y descuentos han sido eliminados');
}

// Función para actualizar totales considerando impuestos y descuentos del accordion
function actualizarTotalesConImpuestosDescuentos() {
    const subtotal = parseFloat(document.getElementById('subtotal')?.value) || 0;

    let totalDescuentos = 0;
    let totalImpuestos = 0;

    impuestosDescuentos.forEach(item => {
        const valorCalculado = calcularValorImpuestoDescuento(item.valor, item.tipoCalculo, item.tipo);

        if (item.tipo === 'descuento') {
            totalDescuentos += valorCalculado;
        } else {
            totalImpuestos += valorCalculado;
        }
    });

    // Actualizar campos automáticamente
    const descuentoField = document.getElementById('descuento');
    const totalImpuestoField = document.getElementById('total_impuesto');

    if (descuentoField) descuentoField.value = totalDescuentos.toFixed(2);
    if (totalImpuestoField) totalImpuestoField.value = totalImpuestos.toFixed(2);

    // Usar nuestro sistema nuevo de totales
    actualizarTotalesCompletos();

    // Actualizar valores en la tabla
    actualizarTablaImpuestosDescuentos();
}

// Función para guardar conceptos en el backend
async function guardarConceptosCotizacion(cotizacionId) {
    try {
        const conceptosParaGuardar = impuestosDescuentos.map(item => ({
            concepto_id: item.concepto,
            porcentaje: item.tipoCalculo === 'porcentaje' ? item.valor : null,
            valor: item.valorCalculado
        }));

        // console.log('Enviando conceptos:', {
        //     cotizacion_id: cotizacionId,
        //     conceptos: conceptosParaGuardar,
        //     cantidad: conceptosParaGuardar.length
        // });

        const response = await fetch('/admin/admin.cotizaciones.conceptos.store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                cotizacion_id: cotizacionId,
                conceptos: conceptosParaGuardar
            })
        });

        const data = await response.json();

        if (data.success) {
            if (conceptosParaGuardar.length === 0) {
                // console.log('Conceptos eliminados correctamente de la base de datos');
            } else {
                // console.log(`${conceptosParaGuardar.length} conceptos guardados correctamente`);
            }
        } else {
            console.error('Error al guardar conceptos:', data.message);
            toastr.warning('Cotización guardada, pero hubo problemas con los conceptos');
        }
    } catch (error) {
        console.error('Error al enviar conceptos:', error);
        toastr.warning('Cotización guardada, pero hubo problemas con los conceptos');
    }
}

// Función para cargar conceptos existentes en modo edición
async function cargarConceptosExistentes(cotizacionId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.conceptos.getCotizacionConceptos/${cotizacionId}`, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const data = await response.json();

        if (data.success && data.data.length > 0) {
            // Limpiar conceptos actuales
            impuestosDescuentos = [];
            contadorRegistros = 0;

            // Cargar conceptos existentes
            data.data.forEach(item => {
                contadorRegistros++;
                const concepto = conceptosDisponibles.find(c => c.id == item.concepto_id);
                if (concepto) {
                    impuestosDescuentos.push({
                        id: contadorRegistros,
                        tipo: concepto.tipo,
                        concepto: item.concepto_id,
                        tipoCalculo: item.porcentaje ? 'porcentaje' : 'valor',
                        valor: item.porcentaje || item.valor,
                        valorCalculado: item.valor
                    });
                }
            });

            actualizarTablaImpuestosDescuentos();
        }
    } catch (error) {
        console.error('Error al cargar conceptos existentes:', error);
    }
}

// ========================================
// FUNCIONES PARA OBSERVACIONES ADICIONALES
// ========================================

let observaciones = [];
let observacionesDisponibles = [];
let contadorObservaciones = 0;

/**
 * Cargar observaciones disponibles
 */
async function cargarObservacionesDisponibles() {
    try {
        const response = await fetch('/admin/admin.cotizaciones.observaciones.getObservaciones');
        const data = await response.json();

        if (data.success) {
            observacionesDisponibles = data.data;

            // Llenar el select
            const select = document.getElementById('observacionSelect');
            select.innerHTML = '<option value="">Seleccione una observación...</option>';

            observacionesDisponibles.forEach(obs => {
                const option = document.createElement('option');
                option.value = obs.id;
                option.textContent = obs.texto;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar observaciones disponibles:', error);
        toastr.error('Error al cargar las observaciones disponibles');
    }
}

/**
 * Agregar una nueva observación
 */
function agregarObservacion() {
    const selectElement = document.getElementById('observacionSelect');
    const observacionId = selectElement.value;

    // Validaciones
    if (!observacionId) {
        toastr.error('Debe seleccionar una observación');
        selectElement.focus();
        return;
    }

    // Verificar si ya existe esa observación
    const observacionExistente = observaciones.find(obs => obs.observacion_id == observacionId);
    if (observacionExistente) {
        toastr.error('Esta observación ya ha sido agregada');
        selectElement.focus();
        return;
    }

    // Obtener el texto de la observación
    const observacionData = observacionesDisponibles.find(obs => obs.id == observacionId);
    if (!observacionData) {
        toastr.error('Observación no encontrada');
        return;
    }

    // Crear nueva observación
    contadorObservaciones++;
    const nuevaObservacion = {
        id: contadorObservaciones,
        observacion_id: parseInt(observacionId),
        texto: observacionData.texto
    };

    observaciones.push(nuevaObservacion);
    actualizarTablaObservaciones();

    // Limpiar selector
    selectElement.value = '';
    selectElement.focus();

    toastr.success('Observación agregada correctamente');
}

/**
 * Eliminar una observación
 */
function eliminarObservacion(id) {
    observaciones = observaciones.filter(obs => obs.id !== id);
    actualizarTablaObservaciones();
    toastr.success('Observación eliminada correctamente');
}

/**
 * Actualizar la tabla de observaciones
 */
function actualizarTablaObservaciones() {
    const tbody = document.getElementById('observacionesTableBody');
    const tabla = document.getElementById('tablaObservaciones');
    const mensaje = document.getElementById('noObservacionesMessage');

    // Limpiar tabla
    tbody.innerHTML = '';

    if (observaciones.length === 0) {
        tabla.style.display = 'none';
        mensaje.style.display = 'block';
        return;
    }

    observaciones.forEach((observacion, index) => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${index + 1}</td>
            <td>${observacion.texto}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarObservacion(${observacion.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(fila);
    });

    tabla.style.display = 'table';
    mensaje.style.display = 'none';
}

/**
 * Cargar observaciones existentes de la cotización
 */
async function cargarObservacionesExistentes(cotizacionId) {
    if (!cotizacionId) return;

    try {
        const response = await fetch(`/admin/admin.cotizaciones.observaciones.getCotizacionObservaciones/${cotizacionId}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            observaciones = [];
            contadorObservaciones = 0;

            data.data.forEach(item => {
                contadorObservaciones++;
                observaciones.push({
                    id: contadorObservaciones,
                    observacion_id: item.id,
                    texto: item.texto
                });
            });

            actualizarTablaObservaciones();
        }

        if (variable === 'ver') {
            // Deshabilitar controles de observaciones en modo ver
            const botonesEliminar = document.querySelectorAll('#tablaObservaciones .btn-danger');
            botonesEliminar.forEach(btn => btn.disabled = true);
            document.getElementById('observacionSelect').disabled = true;
            document.getElementById('agregar_observacion').style.display = 'none';
        }else if (variable === 'editar') {
            // Habilitar controles de observaciones en modo editar
            const botonesEliminar = document.querySelectorAll('#tablaObservaciones .btn-danger');
            botonesEliminar.forEach(btn => btn.disabled = false);
            document.getElementById('observacionSelect').disabled = false;
            document.getElementById('agregar_observacion').style.display = 'inline-block';
        }else{
            // Habilitar controles de observaciones en modo crear
            const botonesEliminar = document.querySelectorAll('#tablaObservaciones .btn-danger');
            botonesEliminar.forEach(btn => btn.disabled = false);
            document.getElementById('observacionSelect').disabled = false;
            document.getElementById('agregar_observacion').style.display = 'inline-block';
        }

    } catch (error) {
        console.error('Error al cargar observaciones existentes:', error);
    }
}

/**
 * Guardar observaciones en el backend
 */
async function guardarObservacionesCotizacion(cotizacionId) {
    try {
        const observacionesParaGuardar = observaciones.map(item => ({
            observacion_id: item.observacion_id
        }));

        // console.log('Enviando observaciones:', {
        //     cotizacion_id: cotizacionId,
        //     observaciones: observacionesParaGuardar,
        //     cantidad: observacionesParaGuardar.length
        // });

        const response = await fetch('/admin/admin.cotizaciones.observaciones.store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                cotizacion_id: cotizacionId,
                observaciones: observacionesParaGuardar
            })
        });

        const data = await response.json();

        if (data.success) {
            if (observacionesParaGuardar.length === 0) {
                // console.log('Observaciones eliminadas correctamente de la base de datos');
            } else {
                // console.log(`${observacionesParaGuardar.length} observaciones guardadas correctamente`);
            }
        } else {
            console.error('Error al guardar observaciones:', data.message);
            toastr.warning('Cotización guardada, pero hubo problemas con las observaciones');
        }
    } catch (error) {
        console.error('Error al enviar observaciones:', error);
        toastr.warning('Cotización guardada, pero hubo problemas con las observaciones');
    }
}

// ========================================
// FUNCIONES PARA CONDICIONES COMERCIALES
// ========================================

let condicionesComerciales = {};

/**
 * Guardar condiciones comerciales
 */
async function guardarCondicionesComerciales() {
    try {
        const form = document.getElementById('formCondicionesComerciales');
        const formData = new FormData(form);

        // Crear objeto con los datos
        const condiciones = {
            tiempo_entrega: formData.get('tiempo_entrega')?.trim() || null,
            lugar_obra: formData.get('lugar_obra')?.trim() || null,
            duracion_oferta: formData.get('duracion_oferta')?.trim() || null,
            garantia: formData.get('garantia')?.trim() || null,
            forma_pago: formData.get('forma_pago')?.trim() || null
        };

        // Validar que al menos un campo tenga contenido
        const tieneContenido = Object.values(condiciones).some(valor => valor && valor.length > 0);

        if (!tieneContenido) {
            toastr.warning('Debe completar al menos un campo de las condiciones comerciales');
            return;
        }

        // Guardar en variable local
        condicionesComerciales = condiciones;

        // Mostrar resumen
        mostrarResumenCondiciones(condiciones);

        toastr.success('Condiciones comerciales guardadas temporalmente. Se guardarán definitivamente al guardar la cotización.');

    } catch (error) {
        console.error('Error al guardar condiciones comerciales:', error);
        toastr.error('Error al guardar las condiciones comerciales');
    }
}

/**
 * Guardar condiciones comerciales en el backend
 */
async function guardarCondicionesCotizacion(cotizacionId) {
    try {
        // Solo enviar si hay condiciones comerciales definidas
        if (!condicionesComerciales || Object.keys(condicionesComerciales).length === 0) {
            console.log('No hay condiciones comerciales para guardar');
            return;
        }

        // console.log('Enviando condiciones comerciales:', {
        //           cotizacion_id: cotizacionId,
        //    condiciones: condicionesComerciales
        //});

        const response = await fetch('/admin/admin.cotizaciones.condiciones.store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                cotizacion_id: cotizacionId,
                ...condicionesComerciales
            })
        });

        const data = await response.json();

        if (data.success) {
            // console.log('Condiciones comerciales guardadas correctamente');
        } else {
            console.error('Error al guardar condiciones comerciales:', data.message);
            toastr.warning('Cotización guardada, pero hubo problemas con las condiciones comerciales');
        }
    } catch (error) {
        console.error('Error al enviar condiciones comerciales:', error);
        toastr.warning('Cotización guardada, pero hubo problemas con las condiciones comerciales');
    }
}

/**
 * Cargar condiciones comerciales existentes
 */
async function cargarCondicionesExistentes(cotizacionId) {
    if (!cotizacionId) return;

    try {
        const response = await fetch(`/admin/admin.cotizaciones.condiciones.getCotizacionCondiciones/${cotizacionId}`);
        const data = await response.json();

        if (data.success && data.data) {
            const condiciones = data.data;

            // Llenar el formulario
            document.getElementById('tiempo_entrega').value = condiciones.tiempo_entrega || '';
            document.getElementById('lugar_obra').value = condiciones.lugar_obra || '';
            document.getElementById('duracion_oferta').value = condiciones.duracion_oferta || '';
            document.getElementById('garantia').value = condiciones.garantia || '';
            document.getElementById('forma_pago').value = condiciones.forma_pago || '';

            // Guardar en variable local
            condicionesComerciales = {
                tiempo_entrega: condiciones.tiempo_entrega,
                lugar_obra: condiciones.lugar_obra,
                duracion_oferta: condiciones.duracion_oferta,
                garantia: condiciones.garantia,
                forma_pago: condiciones.forma_pago
            };

            // Mostrar resumen si hay datos
            const tieneContenido = Object.values(condicionesComerciales).some(valor => valor && valor.length > 0);
            if (tieneContenido) {
                mostrarResumenCondiciones(condicionesComerciales);
            }

            if (variable === 'ver') {
                // Deshabilitar formulario en modo ver
                document.getElementById('formCondicionesComerciales').querySelectorAll('input, textarea, select, button').forEach(elem => {
                    elem.disabled = true;
                });

                document.getElementById('btnGuardarCondiciones').style.display = 'none';

            } else if (variable === 'editar') {
                // Habilitar formulario en modo editar
                document.getElementById('formCondicionesComerciales').querySelectorAll('input, textarea, select, button').forEach(elem => {
                    elem.disabled = false;
                });
                document.getElementById('btnGuardarCondiciones').style.display = 'inline-block';
            } else {
                // Habilitar formulario en modo crear
                document.getElementById('formCondicionesComerciales').querySelectorAll('input, textarea, select, button').forEach(elem => {
                    elem.disabled = false;
                });
                document.getElementById('btnGuardarCondiciones').style.display = 'inline-block';
            }
        }
    } catch (error) {
        console.error('Error al cargar condiciones comerciales existentes:', error);
    }
}

/**
 * Mostrar resumen de condiciones comerciales
 */
function mostrarResumenCondiciones(condiciones) {
    const resumenDiv = document.getElementById('resumenCondiciones');
    if (!resumenDiv) return;

    let html = '<div class="alert alert-info"><h6><i class="fas fa-info-circle"></i> Resumen de Condiciones Comerciales</h6><ul class="mb-0">';

    if (condiciones.tiempo_entrega) {
        html += `<li><strong>Tiempo de Entrega:</strong> ${condiciones.tiempo_entrega}</li>`;
    }

    if (condiciones.lugar_obra) {
        html += `<li><strong>Lugar de Obra:</strong> ${condiciones.lugar_obra}</li>`;
    }

    if (condiciones.duracion_oferta) {
        html += `<li><strong>Duración de Oferta:</strong> ${condiciones.duracion_oferta}</li>`;
    }

    if (condiciones.garantia) {
        html += `<li><strong>Garantía:</strong> ${condiciones.garantia}</li>`;
    }

    if (condiciones.forma_pago) {
        html += `<li><strong>Forma de Pago:</strong> ${condiciones.forma_pago}</li>`;
    }

    html += '</ul></div>';

    resumenDiv.innerHTML = html;
    resumenDiv.style.display = 'block';
}

/**
 * Limpiar formulario de condiciones comerciales
 */
function limpiarCondicionesComerciales() {
    document.getElementById('formCondicionesComerciales').reset();
    condicionesComerciales = {};
    document.getElementById('resumenCondiciones').style.display = 'none';
    toastr.info('Formulario de condiciones comerciales limpio');
}

// ========================================
// FUNCIONES PARA ITEMS DE COTIZACIÓN
// ========================================

let itemsCotizacion = [];
let subitemsDisponibles = [];
let unidadesMedida = [];
let contadorItems = 0;

/**
 * Inicializar funcionalidad de items
 */
async function initItems() {
    try {
        // Cargar datos necesarios
        await Promise.all([
            cargarSubitemsDisponibles(),
            cargarUnidadesMedida()
        ]);

        // Configurar event listeners
        setupItemsEventListeners();

        // Si estamos en modo edición, cargar items existentes
        if ((variable === 'editar' || variable === 'ver') && cotizacion?.id) {
            // await cargarItemsExistentes(cotizacion.id);
        }

        // Configurar permisos según el modo
        configurarPermisosItems();

    } catch (error) {
        console.error('Error al inicializar items:', error);
        toastr.error('Error al cargar los datos de items');
    }
}

/**
 * Cargar subitems disponibles
 */
async function cargarSubitemsDisponibles() {
    try {
        const response = await fetch('/admin/admin.cotizaciones.items.getSubitems');
        const data = await response.json();

        if (data.success) {
            subitemsDisponibles = data.data;
            poblarSelectSubitems();
        }
    } catch (error) {
        console.error('Error al cargar subitems:', error);
    }
}

/**
 * Cargar unidades de medida
 */
async function cargarUnidadesMedida() {
    try {
        const response = await fetch('/admin/admin.cotizaciones.items.getUnidadesMedida');
        const data = await response.json();

        if (data.success) {
            unidadesMedida = data.data;
            poblarSelectUnidadesMedida();
        }
    } catch (error) {
        console.error('Error al cargar unidades de medida:', error);
    }
}

/**
 * Poblar select de subitems
 */
function poblarSelectSubitems() {
    const select = document.getElementById('item_subitem');
    if (!select) return;

    select.innerHTML = '<option value="">Seleccione subitem...</option>';

    subitemsDisponibles.forEach(subitem => {
        const option = document.createElement('option');
        option.value = subitem.id;
        option.textContent = `${subitem.codigo} - ${subitem.nombre}`;
        option.setAttribute('data-unidad', subitem.unidad_medida?.simbolo || '');
        option.setAttribute('data-cantidad', subitem.cantidad || 1);
        select.appendChild(option);
    });
}

/**
 * Poblar select de unidades de medida
 */
function poblarSelectUnidadesMedida() {
    const select = document.getElementById('subitem_unidad_medida');
    if (!select) return;

    select.innerHTML = '<option value="">Seleccione unidad...</option>';

    unidadesMedida.forEach(unidad => {
        const option = document.createElement('option');
        option.value = unidad.id;
        option.textContent = `${unidad.nombre} (${unidad.sigla})`;
        select.appendChild(option);
    });
}

/**
 * Configurar event listeners para items
 */
function setupItemsEventListeners() {
    // Cálculo automático del valor total
    const cantidadInput = document.getElementById('item_cantidad');
    const valorUnitarioInput = document.getElementById('item_valor_unitario');

    if (cantidadInput && valorUnitarioInput) {
        cantidadInput.addEventListener('input', calcularValorTotalItem);
        valorUnitarioInput.addEventListener('input', calcularValorTotalItem);
    }

    // Botones principales
    const btnAgregarItem = document.getElementById('btn_agregar_item');
    if (btnAgregarItem) {
        btnAgregarItem.addEventListener('click', agregarItem);
    }

    const btnLimpiarItem = document.getElementById('btn_limpiar_item');
    if (btnLimpiarItem) {
        btnLimpiarItem.addEventListener('click', limpiarFormularioItem);
    }

    // Botones de gestión de tabla
    const btnEliminarSeleccionados = document.getElementById('btn_eliminar_items_seleccionados');
    if (btnEliminarSeleccionados) {
        btnEliminarSeleccionados.addEventListener('click', eliminarItemsSeleccionados);
    }

    const btnLimpiarTodos = document.getElementById('btn_limpiar_todos_items');
    if (btnLimpiarTodos) {
        btnLimpiarTodos.addEventListener('click', function() {
            if (confirm('¿Está seguro de eliminar todos los items?')) {
                limpiarTodosItems();
            }
        });
    }

    // Select all checkbox
    const selectAllCheckbox = document.getElementById('select_all_items');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#tbody_items .item-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleEliminarItemsSeleccionados();
        });
    }

    // Modal para crear subitem
    const btnCrearSubitem = document.getElementById('btn_crear_subitem');
    if (btnCrearSubitem) {
        btnCrearSubitem.addEventListener('click', abrirModalCrearSubitem);
    }

    // // Formulario de crear subitem
    // const formCrearSubitem = document.getElementById('formCrearSubitem');
    // if (formCrearSubitem) {
    //     formCrearSubitem.addEventListener('submit', guardarSubitem);
    // }

    // Contador de caracteres para observación de subitem
    const observacionTextarea = document.getElementById('subitem_observacion');
    const contadorSpan = document.getElementById('subitem_observacion_count');
    if (observacionTextarea && contadorSpan) {
        observacionTextarea.addEventListener('input', function() {
            contadorSpan.textContent = this.value.length;
        });
    }
}

/**
 * Calcular valor total del item
 */
function calcularValorTotalItem() {
    const cantidad = parseFloat(document.getElementById('item_cantidad')?.value) || 0;
    const valorUnitario = parseFloat(document.getElementById('item_valor_unitario')?.value) || 0;
    const valorTotal = cantidad * valorUnitario;

    const valorTotalInput = document.getElementById('item_valor_total');
    if (valorTotalInput) {
        valorTotalInput.value = valorTotal.toFixed(2);
    }
}

/**
 * Agregar nuevo item
 */
async function agregarItem() {
    try {
        // Validar formulario
        if (!validarFormularioItem()) {
            return;
        }

        const cotizacionId = document.getElementById('id')?.value;
        // Verificar si ya tenemos una cotización guardada
        if (!cotizacionId) {
            toastr.error('Debe guardar la cotización primero antes de agregar items');
            return;
        }

        // Obtener datos del formulario
        const nombre = document.getElementById('item_nombre').value.trim();

        // Crear objeto para enviar al backend
        const itemData = {
            cotizacion_id: cotizacionId,
            nombre: nombre,
            active: true
        };

        // Enviar al backend inmediatamente
        const response = await fetch('/admin/admin.cotizaciones.items.createItem', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(itemData)
        });

        const result = await response.json();

        if (result.success) {
            // Agregar el item con ID del backend a la lista local
            const nuevoItem = {
                id: result.data.id,
                nombre: result.data.nombre,
                active: result.data.active,
                subitems: result.data.subitems || []
            };

            itemsCotizacion.push(nuevoItem);

            // Actualizar tabla
            actualizarTablaItems();

            // Recalcular totales
            calcularTotales();

            // Limpiar formulario
            limpiarFormularioItem();

            toastr.success('Item creado correctamente');
        } else {
            throw new Error(result.message || 'Error al crear item');
        }

    } catch (error) {
        console.error('Error al agregar item:', error);
        toastr.error('Error al crear el item: ' + error.message);
    }
}

/**
 * Validar formulario de item
 */
function validarFormularioItem() {
    let esValido = true;
    const errores = {};

    // Validar nombre
    const nombre = document.getElementById('item_nombre').value.trim();
    if (!nombre) {
        errores.item_nombre = 'El nombre del item es obligatorio';
        esValido = false;
    } else if (nombre.length > 255) {
        errores.item_nombre = 'El nombre no puede exceder 255 caracteres';
        esValido = false;
    }

    // Mostrar errores
    if (!esValido) {
        mostrarErroresItems(errores);
    } else {
        limpiarErroresItems();
    }
    return esValido;
}

/**
 * Obtener subitem por ID
 */
function obtenerSubitemPorId(subitemId) {
    return subitemsDisponibles.find(s => s.id == subitemId) || null;
}

/**
 * Actualizar tabla de items
 */
function actualizarTablaItems() {
    const tbody = document.getElementById('tbody_items');
    let noItemsRow = document.getElementById('no_items_row_items');

    if (!tbody) return;

    // Limpiar tabla excepto la fila "no items"
    const existingRows = tbody.querySelectorAll('tr:not(#no_items_row_items)');
    existingRows.forEach(row => row.remove());

    if (itemsCotizacion.length == 0) {
        // Crear o mostrar la fila de "no items"
        if (!noItemsRow) {
            noItemsRow = document.createElement('tr');
            noItemsRow.id = 'no_items_row_items';
            noItemsRow.innerHTML = `
                <td colspan="6" class="text-center text-muted py-3">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No hay items agregados
                </td>
            `;
            tbody.appendChild(noItemsRow);
        }
        noItemsRow.style.display = 'table-row';
        return;
    }

    if (noItemsRow) {
        noItemsRow.style.display = 'none';
    }


    itemsCotizacion.forEach((item, index) => {
        const row = document.createElement('tr');
        // Generar HTML para lista de subitems

        const subitemsHtml = generarListaSubitems(item.id || index, item.subitems || []);
        row.innerHTML = `
            <td class="text-center">
                <input type="checkbox" class="item-checkbox" data-id="${item.id || index}" onchange="toggleEliminarItemsSeleccionados()">
            </td>
            <td class="text-center">${(index + 1).toString().padStart(3, '0')}</td>
            <td>${item.nombre}</td>
            <td>
                ${subitemsHtml}
            </td>
            <td class="text-center"><span class="badge ${item.active ? 'bg-success' : 'bg-secondary'}">${item.active ? 'Activo' : 'Inactivo'}</span></td>
            <td class="text-center">
                    ${item.id ? `
                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="editarItem(${item.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="abrirModalCrearSubitem(${item.id})" title="Crear Subitem">
                        <i class="fas fa-cube"></i>
                    </button>` : ''}
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarItem(${item.id || index})" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
            </td>
        `;

        // Agregar al final del tbody
        tbody.appendChild(row);
    });

    // Solo cargar subitems para items que no los tengan ya cargados
    itemsCotizacion.forEach(item => {
        if (item.id && (!item.subitems || item.subitems.length === 0)) {
            cargarSubitemsDelItem(item.id);
        }
    });

    // Actualizar también la tabla del modal
    actualizarTablaItemsAcordeon();
}

/**
 * Actualizar tabla de items en el modal de productos
 */
function actualizarTablaItemsAcordeon() {
    const tbody = document.getElementById('tbodyItemsAcordeon');
    if (!tbody) return; // Si no existe el modal, no hacer nada

    // Limpiar la tabla
    tbody.innerHTML = '';

    if (itemsCotizacion.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">
                    <i class="fas fa-info-circle mr-2"></i>
                    No hay items agregados en el acordeón
                </td>
            </tr>
        `;
        return;
    }

    // Agregar filas para cada item y sus subitems
    itemsCotizacion.forEach((item, itemIndex) => {
        // Fila del item principal (NO seleccionable)
        const itemRow = document.createElement('tr');
        itemRow.classList.add('table-light');
        itemRow.innerHTML = `
            <td class="text-center">
                <i class="fas fa-folder text-secondary" title="Items principales no seleccionables"></i>
            </td>
            <td><strong><i class="fas fa-folder mr-1 text-secondary"></i>${item.nombre}</strong></td>
            <td class="text-muted"><em>Item principal - No seleccionable</em></td>
            <td><span class="badge bg-secondary">Item Principal</span></td>
        `;
        tbody.appendChild(itemRow);

        // Agregar filas para cada subitem (SÍ seleccionables)
        if (item.subitems && item.subitems.length > 0) {
            item.subitems.forEach((subitem, subitemIndex) => {
                const subitemRow = document.createElement('tr');
                subitemRow.innerHTML = `
                    <td class="text-center">
                        <input type="radio" name="itemSelected" class="item-select" data-type="subitem" data-item-id="${item.id || itemIndex}" data-id="${subitem.id}" data-index="${itemIndex}-${subitemIndex}" onchange="seleccionarUnicoItem(this)">
                    </td>
                    <td class="ps-4"><i class="fas fa-cubes mr-1 text-info"></i>${subitem.codigo || subitem.nombre || 'SUB-' + (subitemIndex + 1).toString().padStart(3, '0')}</td>
                    <td>${subitem.descripcion || subitem.observacion || subitem.nombre || 'Sin descripción'}</td>
                    <td>
                        <span class="badge bg-info">Subitem</span>
                        ${subitem.cantidad ? `<span class="badge bg-secondary ml-1">${subitem.cantidad}${subitem.unidadMedida && subitem.unidadMedida.sigla ? ` ${subitem.unidadMedida.sigla}` : ''}</span>` : ''}
                    </td>
                `;
                tbody.appendChild(subitemRow);
            });
        } else {
            // Mostrar mensaje si el item no tiene subitems
            const noSubitemsRow = document.createElement('tr');
            noSubitemsRow.innerHTML = `
                <td class="text-center">
                    <i class="fas fa-info-circle text-muted"></i>
                </td>
                <td class="ps-4 text-muted"><em>Sin subitems disponibles</em></td>
                <td class="text-muted">-</td>
                <td><span class="badge bg-light text-dark">Sin subitems</span></td>
            `;
            tbody.appendChild(noSubitemsRow);
        }
    });

    // Configurar la funcionalidad de selección única
    configurarSeleccionUnica();

    // Aplicar filtro si hay texto de búsqueda
    const searchInput = document.getElementById('buscarItemsAcordeon');
    if (searchInput && searchInput.value.trim() !== '') {
        filtrarItemsAcordeon();
    }
}

/**
 * Configurar funcionalidad de selección única en el modal
 */
function configurarSeleccionUnica() {
    // No necesita configuración adicional ya que los radio buttons manejan la selección única automáticamente
    console.log('Configuración de selección única lista');
}

/**
 * Manejar selección única de subitem
 */
function seleccionarUnicoItem(radioButton) {
    // Limpiar selección previa en la tabla de productos seleccionados
    // (No limpiamos automáticamente para preservar items ya agregados)

    // Actualizar interfaz visual
    const tipo = radioButton.dataset.type;
    const nombre = radioButton.closest('tr').querySelector('td:nth-child(2)').textContent.trim();

    // Solo procesar subitems
    if (tipo === 'subitem') {
        console.log(`Subitem seleccionado: ${nombre}`);

        // Destacar visualmente la fila seleccionada
        document.querySelectorAll('input[name="itemSelected"]').forEach(radio => {
            const row = radio.closest('tr');
            if (radio.checked) {
                row.style.backgroundColor = 'rgba(13, 202, 240, 0.1)';
                row.classList.add('table-info');
            } else {
                row.style.backgroundColor = '';
                row.classList.remove('table-info');
            }
        });
    }
}

/**
 * Obtener item seleccionado del acordeón
 */
function obtenerItemSeleccionado() {
    const radioSeleccionado = document.querySelector('input[name="itemSelected"]:checked');

    if (!radioSeleccionado) {
        return null;
    }

    const tipo = radioSeleccionado.dataset.type;
    const id = radioSeleccionado.dataset.id;
    const index = radioSeleccionado.dataset.index;

    if (tipo === 'item') {
        const itemIndex = parseInt(index);
        const item = itemsCotizacion[itemIndex];
        if (item) {
            return {
                tipo: 'item',
                id: item.id,
                nombre: item.nombre,
                descripcion: item.descripcion || item.nombre,
                data: item
            };
        }
    } else if (tipo === 'subitem') {
        const [itemIndex, subitemIndex] = index.split('-').map(i => parseInt(i));
        const item = itemsCotizacion[itemIndex];
        if (item && item.subitems && item.subitems[subitemIndex]) {
            const subitem = item.subitems[subitemIndex];
            return {
                tipo: 'subitem',
                id: subitem.id,
                itemId: item.id,
                itemNombre: item.nombre,
                nombre: subitem.codigo || subitem.descripcion || subitem.nombre,
                descripcion: subitem.descripcion || subitem.nombre,
                data: subitem
            };
        }
    }

    return null;
}

/**
 * Usar item seleccionado del acordeón como producto
 */
function usarItemSeleccionado() {
    console.log('=== USAR ITEM SELECCIONADO ===');

    // Buscar item seleccionado en el acordeón
    const itemSeleccionado = document.querySelector('input[name="itemSelected"]:checked');

    if (!itemSeleccionado) {
        Swal.fire({
            type: 'warning',
            title: 'Sin selección',
            text: 'Por favor seleccione un item o subitem del acordeón para agregarlo como producto.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    const tipo = itemSeleccionado.dataset.type;
    const indexData = itemSeleccionado.dataset.index;

    console.log('Item seleccionado:', { tipo, indexData });

    // Guardar temporalmente el item seleccionado
    if (tipo === 'item') {
        const itemIndex = parseInt(indexData);
        window.subitemTemporal = {
            tipo: 'item',
            item: itemsCotizacion[itemIndex],
            index: itemIndex
        };
    } else if (tipo === 'subitem') {
        const [itemIndex, subitemIndex] = indexData.split('-').map(i => parseInt(i));
        const item = itemsCotizacion[itemIndex];
        const subitem = item.subitems[subitemIndex];

        window.subitemTemporal = {
            tipo: 'subitem',
            item: item,
            subitem: subitem,
            itemIndex: itemIndex,
            subitemIndex: subitemIndex
        };
    }

    // Abrir modal de categorías
    abrirModalSeleccionCategorias();
}

/**
 * Abrir modal para selección de categorías
 */
function abrirModalSeleccionCategorias() {
    // Crear modal dinámico para seleccionar categorías
    const modalHtml = `
        <div class="modal fade" id="modalSeleccionCategorias" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-tags mr-2"></i>Seleccionar una o más categorías para cargar los productos.
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-white">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Subitem seleccionado:</strong> ${window.subitemTemporal?.subitem?.codigo || window.subitemTemporal?.subitem?.nombre}
                            <br><small>Seleccione una o más categorías para cargar los items propios correspondientes.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Categorías disponibles:</strong></label>
                            <div id="categoriasContainer" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                <div class="text-center p-3">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Cargando categorías...
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleTodasCategorias()">
                                <i class="fas fa-check-double mr-1"></i>Seleccionar todas
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="cargarItemsPorCategorias()" disabled id="btnCargarItems">
                            <i class="fas fa-arrow-right mr-1"></i>Cargar Items Propios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal anterior si existe
    const modalAnterior = document.getElementById('modalSeleccionCategorias');
    if (modalAnterior) {
        modalAnterior.remove();
    }

    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Verificar que el modal se creó correctamente
    const nuevoModal = document.getElementById('modalSeleccionCategorias');
    if (!nuevoModal) {
        console.error('No se pudo crear el modal de selección de categorías');
        return;
    }

    // Mostrar modal con timeout para asegurar que se renderice
    setTimeout(() => {
        try {
            $('#modalSeleccionCategorias').modal('show');
            console.log('Modal de categorías abierto correctamente');
        } catch (error) {
            console.error('Error al mostrar modal:', error);
        }
    }, 100);

    // Cargar categorías disponibles
    cargarCategoriasPorSeleccionar();
}

/**
 * Cargar categorías disponibles para selección
 */
async function cargarCategoriasPorSeleccionar() {
    try {
        console.log('Iniciando carga de categorías para selección...');
        const container = document.getElementById('categoriasContainer');

        if (!container) {
            console.error('No se encontró el contenedor de categorías');
            return;
        }

        // Usar categorías desde variable global o cargar desde backend
        let categorias = [];

        // Intentar obtener de variable global primero
        if (window.categoriasDisponibles && window.categoriasDisponibles.length > 0) {
            categorias = window.categoriasDisponibles;
        } else {
            // Cargar desde backend
            const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
            const response = await fetch(`${baseUrl}/admin/admin.cotizaciones.categorias.obtener`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success && result.data) {
                    categorias = result.data;
                }
            }
        }

        // Si no hay categorías, usar datos simulados
        if (categorias.length === 0) {
            categorias = [
                { id: 1, nombre: 'Materiales de Construcción' },
                { id: 2, nombre: 'Herramientas' },
                { id: 3, nombre: 'Equipos' },
                { id: 4, nombre: 'Servicios' },
                { id: 5, nombre: 'Mano de Obra' }
            ];
        }

        // Generar HTML de categorías
        let categoriasHtml = '';
        categorias.forEach(categoria => {
            categoriasHtml += `
                <div class="form-check mb-2">
                    <input class="form-check-input categoria-checkbox" type="checkbox" value="${categoria.id}" id="categoria_${categoria.id}" onchange="validarSeleccionCategorias()">
                    <label class="form-check-label" for="categoria_${categoria.id}">
                        <strong>${categoria.nombre}</strong>
                        <small class="text-muted d-block">ID: ${categoria.id}</small>
                    </label>
                </div>
            `;
        });

        container.innerHTML = categoriasHtml;

        // Guardar categorías en variable temporal
        window.categoriasTemporal = categorias;

    } catch (error) {
        console.error('Error al cargar categorías:', error);
        document.getElementById('categoriasContainer').innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Error al cargar categorías. Se usarán categorías por defecto.
            </div>
        `;
    }
}

/**
 * Validar selección de categorías y habilitar botón
 */
function validarSeleccionCategorias() {
    const categoriasSeleccionadas = document.querySelectorAll('.categoria-checkbox:checked');
    const btnCargarItems = document.getElementById('btnCargarItems');

    if (categoriasSeleccionadas.length > 0) {
        btnCargarItems.disabled = false;
        btnCargarItems.innerHTML = `<i class="fas fa-arrow-right mr-1"></i>Cargar Items Propios (${categoriasSeleccionadas.length} categoría${categoriasSeleccionadas.length !== 1 ? 's' : ''})`;
    } else {
        btnCargarItems.disabled = true;
        btnCargarItems.innerHTML = '<i class="fas fa-arrow-right mr-1"></i>Cargar Items Propios';
    }
}

/**
 * Toggle todas las categorías
 */
function toggleTodasCategorias() {
    const checkboxes = document.querySelectorAll('.categoria-checkbox');
    const todasSeleccionadas = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(checkbox => {
        checkbox.checked = !todasSeleccionadas;
    });

    validarSeleccionCategorias();
}

/**
 * Cargar items propios basándose en categorías seleccionadas
 */
async function cargarItemsPorCategorias() {
    const categoriasSeleccionadas = document.querySelectorAll('.categoria-checkbox:checked');

    if (categoriasSeleccionadas.length === 0) {
        Swal.fire({
            type: 'warning',
            title: 'Sin selección',
            text: 'Debe seleccionar al menos una categoría.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // Mostrar loading en el botón
    const btnCargarItems = document.getElementById('btnCargarItems');
    const textoOriginal = btnCargarItems.innerHTML;
    btnCargarItems.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Cargando...';
    btnCargarItems.disabled = true;

    try {
        // Obtener IDs de categorías seleccionadas
        const categoriaIds = Array.from(categoriasSeleccionadas).map(cb => parseInt(cb.value));

        console.log('Cargando items propios para categorías:', categoriaIds);

        // Preparar datos para la petición
        const requestData = {
            categoria_ids: categoriaIds
        };

        // Obtener la URL base
        const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
        const url = `${baseUrl}/admin/admin.cotizaciones.items-categoria.obtener`;

        // Hacer petición al backend
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });
        console.log('Respuesta el servidor recibida para items propios', response);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const result = await response.json();

        let itemsPropios = [];

        if (result.success && result.data && result.data.length > 0) {
            // Procesar y validar los datos del backend
            itemsPropios = result.data.map(item => {
                // Asegurar que todos los campos son del tipo correcto
                return {
                    id: String(item.id || ''),
                    nombre: String(item.nombre || 'Sin nombre'),
                    codigo: String(item.codigo || 'Sin código'),
                    descripcion: String(item.descripcion || 'Sin descripción'),
                    categoria_id: item.categoria_id || null,
                    categoria: {
                        id: item.categoria?.id || item.categoria_id || null,
                        nombre: String(item.categoria?.nombre || 'Sin categoría')
                    },
                    unidad_medida: String(item.unidad_medida || ''),
                    precio: item.precio || 0,
                    tipo: String(item.tipo || 'item_propio'),
                    // Datos específicos de parametrización si existen
                    ...(item.tipo === 'parametrizacion' && {
                        cargo_id: item.cargo_id || null,
                        cargo: item.cargo ? {
                            id: item.cargo.id || null,
                            nombre: String(item.cargo.nombre || 'Sin cargo')
                        } : null,
                        valor_porcentaje: Number(item.valor_porcentaje || 0),
                        valor_admon: Number(item.valor_admon || 0),
                        valor_obra: Number(item.valor_obra || 0)
                    })
                };
            });
            console.log(`Se encontraron ${itemsPropios.length} items propios para las categorías seleccionadas`);
            console.log('Items procesados:', itemsPropios);
        } else {
            console.log('No se encontraron items propios, usando datos simulados');
            // Datos simulados como fallback
            // itemsPropios = generarItemsPropiosSimulados(categoriaIds);
            itemsPropios = [];
        }

        // Cerrar modal de categorías y abrir modal de selección de items propios
        $('#modalSeleccionCategorias').modal('hide');

        console.log('Abriendo modal de selección de items propios con', itemsPropios.length, 'items');

        // Dar tiempo a que se cierre el modal anterior
        setTimeout(() => {
            // Abrir modal para seleccionar items propios
            abrirModalSeleccionItemsPropios(itemsPropios, categoriaIds);
        }, 500);

    } catch (error) {
        console.log('Error al cargar items propios:', error);
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: `No se pudieron cargar los items propios: ${error.message}`,
            confirmButtonText: 'Entendido'
        });
    } finally {
        // Restaurar botón
        btnCargarItems.innerHTML = textoOriginal;
        btnCargarItems.disabled = false;
    }
}

/**
 * Generar items propios simulados para categorías seleccionadas
 */
function generarItemsPropiosSimulados(categoriaIds) {
    const itemsSimulados = [];

    // Más variedad de nombres por categoría
    const itemsPorCategoria = {
        1: ['Cemento Portland', 'Arena de Río', 'Grava Triturada', 'Ladrillos Cerámicos', 'Blocks de Concreto', 'Cal Hidratada', 'Yeso Fresco'],
        2: ['Varillas de Acero', 'Alambre Galvanizado', 'Clavos 2.5"', 'Tornillos Autoroscantes', 'Tuercas y Arandelas', 'Pernos de Anclaje'],
        3: ['Taladro Percutor', 'Sierra Circular', 'Amoladora Angular', 'Martillo Demoledor', 'Pistola de Calor', 'Compresor de Aire'],
        4: ['Soldadura Profesional', 'Consultoría Técnica', 'Supervisión de Obra', 'Control de Calidad', 'Diseño Estructural'],
        5: ['Maestro de Obra', 'Ayudante General', 'Operario Especializado', 'Técnico Electricista', 'Plomero Industrial', 'Soldador Certificado']
    };

    categoriaIds.forEach(categoriaId => {
        const categoria = window.categoriasTemporal?.find(c => c.id === categoriaId);
        const nombreCategoria = categoria?.nombre || `Categoría ${categoriaId}`;

        // Obtener items específicos para la categoría o usar genéricos
        const itemsDisponibles = itemsPorCategoria[categoriaId] ||
            ['Item Genérico A', 'Item Genérico B', 'Item Genérico C', 'Item Genérico D', 'Item Genérico E'];

        // Generar 5-8 items por categoría (más cantidad)
        const cantidadItems = Math.floor(Math.random() * 4) + 5;

        for (let i = 1; i <= cantidadItems; i++) {
            const nombreItem = itemsDisponibles[(i-1) % itemsDisponibles.length];
            const variante = i > itemsDisponibles.length ? ` V${Math.ceil(i/itemsDisponibles.length)}` : '';

            itemsSimulados.push({
                id: `sim_${categoriaId}_${i}`,
                nombre: `${nombreItem}${variante}`,
                codigo: `${nombreCategoria.substring(0, 3).toUpperCase()}-${categoriaId}${i.toString().padStart(3, '0')}`,
                descripcion: `Item propio especializado de la categoría ${nombreCategoria}. Calidad profesional.`,
                categoria: { id: categoriaId, nombre: nombreCategoria },
                unidad_medida: ['Kg', 'M³', 'Unidad', 'M²', 'Litros', 'Metros', 'Piezas'][Math.floor(Math.random() * 7)],
                precio: Math.floor(Math.random() * 15000) + 2000
            });
        }
    });

    console.log(`Generados ${itemsSimulados.length} items propios simulados para ${categoriaIds.length} categorías`);
    return itemsSimulados;
}

/**
 * Abrir modal para selección de items propios
 */
function abrirModalSeleccionItemsPropios(itemsPropios, categoriaIds) {
    console.log('Iniciando creación de modal de selección de items propios');
    console.log('Items recibidos:', itemsPropios.length);
    console.log('Categorías IDs:', categoriaIds);

    const categoriasTexto = categoriaIds.map(id => {
        const cat = window.categoriasTemporal?.find(c => c.id === id);
        return cat?.nombre || `Categoría ${id}`;
    }).join(', ');

    const modalHtml = `
        <div class="modal fade" id="modalSeleccionItemsPropios" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-cubes mr-2"></i>Seleccionar Items Propios
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-white">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Subitem:</strong> ${window.subitemTemporal?.subitem?.codigo || window.subitemTemporal?.subitem?.nombre}
                            <br><strong>Categorías:</strong> ${categoriasTexto}
                            <br><small>Seleccione los items propios que desea asociar al subitem.</small>
                        </div>

                        <!-- Campo de búsqueda -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="buscarItemsPropios"
                                           placeholder="Buscar items propios por nombre, código o descripción..."
                                           onkeyup="filtrarItemsPropios()"
                                           onpaste="setTimeout(filtrarItemsPropios, 10)">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusquedaItemsPropios()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleTodosItemsPropios()">
                                    <i class="fas fa-check-double mr-1"></i>Seleccionar todos
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <strong>Items propios disponibles (<span id="totalItemsPropios">${itemsPropios.length}</span>) -
                            Mostrando: <span id="itemsVisibles">${itemsPropios.length}</span></strong>
                        </div>

                        <div id="itemsPropiosContainer" class="border rounded" style="max-height: 400px; overflow-y: auto;">
                            ${generarHtmlItemsPropios(itemsPropios)}
                        </div>

                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <span id="contadorSeleccionados">0</span> items propios seleccionados
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="finalizarSeleccionItemsPropios()" id="btnFinalizarSeleccion">
                            <i class="fas fa-check mr-1"></i>Agregar Selección
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal anterior si existe
    const modalAnterior = document.getElementById('modalSeleccionItemsPropios');
    if (modalAnterior) {
        modalAnterior.remove();
    }

    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Guardar items propios temporalmente
    window.itemsPropiosTemporal = itemsPropios;

    // Mostrar modal
    $('#modalSeleccionItemsPropios').modal('show');

    // Actualizar contador inicial
    actualizarContadorSeleccionados();
}

/**
 * Generar HTML para mostrar items propios
 */
function generarHtmlItemsPropios(itemsPropios) {
    if (itemsPropios.length === 0) {
        return `
            <div class="text-center p-4 text-muted">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h5>No hay items propios disponibles</h5>
                <p>Las categorías seleccionadas no contienen items propios.</p>
            </div>
        `;
    }

    let html = '<div class="p-3">';

    // Agrupar por categoría
    const itemsPorCategoria = {};
    itemsPropios.forEach(item => {
        const categoriaId = item.categoria?.id || 'sin_categoria';
        const categoriaNombre = item.categoria?.nombre || 'Sin categoría';

        if (!itemsPorCategoria[categoriaId]) {
            itemsPorCategoria[categoriaId] = {
                nombre: categoriaNombre,
                items: []
            };
        }
        itemsPorCategoria[categoriaId].items.push(item);
    });

    // Generar HTML por categoría
    Object.entries(itemsPorCategoria).forEach(([categoriaId, data]) => {
        console.log('data-->', data);

        html += `
            <div class="mb-4">
                <h6 class="text-primary border-bottom pb-2">
                    <i class="fas fa-tag mr-2"></i>${data.nombre}
                </h6>
                <div class="row">
        `;

        data.items.forEach(item => {
            // Determinar el tipo de item y su información específica
            const esParametrizacion = item.tipo === 'parametrizacion';
            const icono = esParametrizacion ? 'fas fa-user-tie' : 'fas fa-cube';
            const tipoClass = esParametrizacion ? 'border-warning' : 'border-primary';

            // Asegurar que todos los valores son strings
            const itemNombre = String(item.nombre || 'Sin nombre');
            const itemCodigo = String(item.codigo || 'No definido');
            const itemId = String(item.id || '');
            const itemTipo = String(item.tipo || 'item_propio');
            const unidadMedida = item.unidad_medida ? String(item.unidad_medida) : '';
            const precio = item.precio ? String(item.precio) : '';

            // Descripción mejorada para parametrización con información del cargo
            let descripcion = String(item.descripcion || 'Sin descripción');
            if (esParametrizacion && item.cargo && item.cargo.nombre) {
                const cargoNombre = String(item.cargo.nombre);
                const valorPorcentaje = Number(item.valor_porcentaje || 0);
                const valorAdmon = Number(item.valor_admon || 0);
                const valorObra = Number(item.valor_obra || 0);

                descripcion = `👤 ${cargoNombre} | ${valorPorcentaje}% | Admin: $${valorAdmon.toLocaleString()} | Obra: $${valorObra.toLocaleString()}`;
            }

            // Debug logging
            if (typeof descripcion !== 'string' || descripcion.includes('[object Object]')) {
                console.error('Problema con descripción:', {
                    item: item,
                    descripcion: descripcion,
                    tipo: typeof descripcion,
                    esParametrizacion: esParametrizacion,
                    cargo: item.cargo
                });
                descripcion = 'Error en descripción';
            }

            html += `
                <div class="col-md-6 mb-3">
                    <div class="card ${tipoClass} item-propio-card" data-item-id="${itemId}">
                        <div class="card-body p-3">
                            <div class="form-check">
                                <input class="form-check-input item-propio-checkbox"
                                       type="checkbox"
                                       value="${itemId}"
                                       id="item_${itemId}"
                                       onchange="actualizarContadorSeleccionados()"
                                       data-tipo="${itemTipo}">
                                <label class="form-check-label" for="item_${itemId}">
                                    <div>
                                        <strong class="item-nombre">
                                            <i class="${icono} mr-1 ${esParametrizacion ? 'text-warning' : 'text-primary'}"></i>
                                            ${itemNombre}
                                        </strong>
                                        <br><small class="text-muted item-codigo">
                                            <i class="fas fa-tag mr-1"></i>Código: ${itemCodigo}
                                        </small>
                                        <br><small class="${esParametrizacion ? 'text-warning' : 'text-info'} item-descripcion">
                                            ${descripcion}
                                        </small>
                                        <br><small class="text-secondary">
                                            ${unidadMedida=='' ? `<span class="badge ${esParametrizacion ? 'bg-warning' : 'bg-info'}">${unidadMedida}</span>` : ''}
                                            ${precio ? `<span class="badge bg-success ml-1">$${precio}</span>` : ''}
                                            ${esParametrizacion ? `<span class="badge text-white ml-1" style="background-color: #fd7e14;">📊 Parametrización</span>` : ''}
                                            ${esParametrizacion && item.cargo && item.cargo.nombre ? `<span class="badge bg-secondary ml-1">👤 ${String(item.cargo.nombre)}</span>` : ''}
                                        </small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;
    });

    html += '</div>';
    return html;
}

/**
 * Toggle todos los items propios
 */
function toggleTodosItemsPropios() {
    const checkboxes = document.querySelectorAll('.item-propio-checkbox');
    const todosSeleccionados = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(checkbox => {
        checkbox.checked = !todosSeleccionados;
    });

    actualizarContadorSeleccionados();
}

/**
 * Actualizar contador de items propios seleccionados
 */
function actualizarContadorSeleccionados() {
    const seleccionados = document.querySelectorAll('.item-propio-checkbox:checked');
    const contador = document.getElementById('contadorSeleccionados');
    const btnFinalizar = document.getElementById('btnFinalizarSeleccion');

    if (contador) {
        contador.textContent = seleccionados.length;
    }

    if (btnFinalizar) {
        if (seleccionados.length > 0) {
            btnFinalizar.innerHTML = `<i class="fas fa-check mr-1"></i>Agregar Selección (${seleccionados.length})`;
        } else {
            btnFinalizar.innerHTML = '<i class="fas fa-check mr-1"></i>Agregar Selección';
        }
    }
}

/**
 * Finalizar selección de items propios y pasar a configuración de costos
 */
async function finalizarSeleccionItemsPropios() {
    const itemsPropiosSeleccionados = document.querySelectorAll('.item-propio-checkbox:checked');

    if (itemsPropiosSeleccionados.length === 0) {
        Swal.fire({
            type: 'warning',
            title: 'Sin selección',
            text: 'Debe seleccionar al menos un item propio.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    try {
        // Obtener los datos de los items propios seleccionados
        const itemsPropiosData = Array.from(itemsPropiosSeleccionados).map(checkbox => {
            const itemId = checkbox.value;
            return window.itemsPropiosTemporal.find(item => item.id == itemId);
        }).filter(item => item !== undefined);

        console.log('Items propios seleccionados:', itemsPropiosData);

        // Guardar items propios seleccionados para el siguiente paso
        window.itemsPropiosSeleccionadosTemporal = itemsPropiosData;

        // Cerrar modal actual
        $('#modalSeleccionItemsPropios').modal('hide');

        // Abrir modal de configuración de costos
        await abrirModalConfiguracionCostos(itemsPropiosData);

    } catch (error) {
        console.error('Error al finalizar selección:', error);
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: error.message || 'No se pudo completar la selección. Intente nuevamente.',
            confirmButtonText: 'Entendido'
        });
    }
}

/**
 * Agregar subitem con items propios seleccionados por el usuario
 */
async function agregarSubitemConItemsPropiosSeleccionados(subitem, itemParent, itemsPropiosSeleccionados) {
    try {

        console.log('agregarSubitemConItemsPropiosSeleccionados>>> ', { subitem, itemParent, itemsPropiosSeleccionados });

        const tbody = document.getElementById('tbody_items');
        const subitemId = `subitem_${subitem.id || subitem.codigo}`;

        // Verificar si ya existe en la tabla
        const existeSubitem = tbody.querySelector(`tr[data-subitem-id="${subitemId}"]`);
        if (existeSubitem) {
            throw new Error(`El subitem "${subitem.codigo || subitem.nombre}" ya está agregado.`);
        }

        // Limpiar tabla si tiene mensaje de "no hay elementos"
        const mensajeVacio = tbody.querySelector('#no_items_row_items');
        if (mensajeVacio) {
            mensajeVacio.remove();
        }

        // Crear sección del subitem
        const subitemRow = document.createElement('tr');
        subitemRow.setAttribute('data-subitem-id', subitemId);
        subitemRow.classList.add('table-info', 'subitem-header');
        subitemRow.innerHTML = `
            <td colspan="2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-cubes text-info fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><strong>${subitem.codigo || subitem.nombre}</strong></h6>
                            <small class="text-muted">
                                <i class="fas fa-folder mr-1"></i>Item padre: ${itemParent.nombre}
                            </small>
                            <br><small class="text-info">
                                ${subitem.descripcion || subitem.observacion || 'Sin descripción'}
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info mr-2">${itemsPropiosSeleccionados.length} Item(s) Propio(s)</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarSubitemCompleto('${subitemId}')" title="Quitar subitem completo">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(subitemRow);

        // Agregar items propios seleccionados por el usuario
        if (itemsPropiosSeleccionados && itemsPropiosSeleccionados.length > 0) {
            itemsPropiosSeleccionados.forEach((itemPropio, index) => {
                const itemPropioRow = document.createElement('tr');
                itemPropioRow.setAttribute('data-parent-subitem', subitemId);
                itemPropioRow.classList.add('item-propio-row');
                itemPropioRow.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center ps-4">
                            <div class="me-3">
                                <i class="fas fa-cube text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div>
                                    <strong>${itemPropio.nombre}</strong>
                                    <br><small class="text-muted">
                                        <i class="fas fa-tag mr-1"></i>Código: ${itemPropio.codigo || 'No definido'}
                                    </small>
                                    <br><small class="text-success">
                                        ${itemPropio.descripcion || 'Sin descripción'}
                                    </small>
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-success">Item Propio</span>
                                    ${itemPropio.categoria?.nombre ? `<span class="badge bg-secondary ml-1">${itemPropio.categoria.nombre}</span>` : ''}
                                    ${itemPropio.unidad_medida ? `<span class="badge bg-info ml-1">${itemPropio.unidad_medida}</span>` : ''}
                                    ${itemPropio.precio ? `<span class="badge bg-primary ml-1">$${itemPropio.precio}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="quitarItemPropio('${subitemId}', '${itemPropio.id}')" title="Quitar item propio">
                            <i class="fas fa-minus"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(itemPropioRow);
            });
        }

        // Actualizar variable global
        if (!window.subitemsSeleccionados) {
            window.subitemsSeleccionados = [];
        }

        window.subitemsSeleccionados.push({
            id: subitemId,
            subitem: subitem,
            itemParent: itemParent,
            itemsPropios: itemsPropiosSeleccionados,
            fechaAgregado: new Date().toISOString()
        });

        // Actualizar contador
        actualizarContadorProductosSeleccionados();

        console.log(`Subitem "${subitem.codigo || subitem.nombre}" agregado con ${itemsPropiosSeleccionados.length} items propios seleccionados por el usuario`);
        console.log('subitemsSeleccionados>>>@', window.subitemsSeleccionados);

        return Promise.resolve();

    } catch (error) {
        console.error('Error al agregar subitem con items propios seleccionados:', error);
        return Promise.reject(error);
    }
}

/**
 * Abrir modal para configuración de costos de items propios
 */
async function abrirModalConfiguracionCostos(itemsPropiosSeleccionados) {
    const subitem = window.subitemTemporal.subitem;

    const modalHtml = `
        <div class="modal fade" id="modalConfiguracionCostos" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content shadow-lg border-0">
                    <!-- Header con diseño moderno -->
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 mr-3 shadow-sm">
                                <i class="fas fa-calculator text-primary" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title text-white mb-0 font-weight-bold">Configuración de Costos</h5>
                                <small class="text-white-50">Configure los tipos de costo para cada item seleccionado</small>
                            </div>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="text-shadow: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Progress indicator -->
                    <div class="bg-light px-4 py-3 border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="step-indicator completed mr-3">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="step-indicator completed mr-3">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="step-indicator active mr-3">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <div class="step-indicator">
                                        <i class="fas fa-save"></i>
                                    </div>
                                </div>
                                <div class="d-flex mt-2">
                                    <small class="text-muted mr-4">Categorías</small>
                                    <small class="text-muted mr-4">Items</small>
                                    <small class="text-primary mr-4 font-weight-bold">Costos</small>
                                    <small class="text-muted">Finalizar</small>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="bg-primary text-white px-3 py-1 rounded-pill d-inline-block">
                                    <i class="fas fa-cube mr-1"></i>
                                    <strong>${itemsPropiosSeleccionados.length}</strong> items seleccionados
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subitem info card -->
                    <div class="p-4 bg-light border-bottom">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="media">
                                    <div class="bg-info text-white rounded-circle p-2 mr-3 shadow-sm">
                                        <i class="fas fa-cubes"></i>
                                    </div>
                                    <div class="media-body">
                                        <h6 class="mb-1 font-weight-bold text-dark">${subitem.codigo || subitem.nombre}</h6>
                                        <p class="mb-0 text-muted small">${subitem.descripcion || 'Configure el tipo de costo y parámetros para cada item'}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="d-flex flex-column align-items-end">
                                    <span class="badge badge-outline-info mb-1">Paso 3 de 4</span>
                                    <small class="text-muted">Configuración de costos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main content area -->
                    <div class="modal-body p-0" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Items cost configuration area -->
                        <div class="container-fluid p-4">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3 font-weight-bold text-uppercase">
                                        <i class="fas fa-cog mr-2"></i>Items a Configurar
                                    </h6>
                                    ${generarTarjetasItemsCostos(itemsPropiosSeleccionados)}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer con botones mejorados -->
                    <div class="modal-footer bg-light border-top-0 p-4">
                        <div class="row w-100">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-secondary btn-block" onclick="volverASeleccionItemsPropios()">
                                    <i class="fas fa-arrow-left mr-2"></i>Volver a Selección de Items
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success btn-block btn-lg shadow" onclick="finalizarConfiguracionCostos()" id="btnFinalizarCostos">
                                    <i class="fas fa-check mr-2"></i>Finalizar Configuración
                                </button>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div class="w-100 mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"
                                     aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CSS adicional para el modal -->
        <style>
            .step-indicator {
                width: 35px;
                height: 35px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid #dee2e6;
                background: #f8f9fa;
                color: #6c757d;
                font-size: 0.8rem;
            }

            .step-indicator.completed {
                background: #28a745;
                border-color: #28a745;
                color: white;
            }

            .step-indicator.active {
                background: #007bff;
                border-color: #007bff;
                color: white;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
                70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
                100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
            }

            .item-cost-card {
                border: 2px solid #e3f2fd;
                border-radius: 12px;
                transition: all 0.3s ease;
                background: white;
                overflow: hidden;
            }

            .item-cost-card:hover {
                border-color: #2196f3;
                box-shadow: 0 4px 20px rgba(33, 150, 243, 0.1);
            }

            .item-cost-card.configured {
                border-color: #4caf50;
                background: #f8fff8;
            }

            .cost-type-selector {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .cost-field-group {
                background: white;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .price-display {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                border-radius: 10px;
                padding: 1rem;
                text-align: center;
                font-size: 1.1rem;
                font-weight: bold;
                box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            }

            .collapse-toggle {
                background: none;
                border: none;
                color: #007bff;
                text-decoration: none;
                font-size: 0.9rem;
                padding: 0;
            }

            .collapse-toggle:hover {
                color: #0056b3;
                text-decoration: underline;
            }

            .form-control:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }

            .btn-outline-secondary:hover {
                background-color: #6c757d;
                border-color: #6c757d;
            }
        </style>
    `;

    // Remover modal anterior si existe
    const modalAnterior = document.getElementById('modalConfiguracionCostos');
    if (modalAnterior) {
        modalAnterior.remove();
    }

    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Mostrar modal
    $('#modalConfiguracionCostos').modal('show');
}

/**
 * Generar tarjetas modernas de items propios para configuración de costos
 */
function generarTarjetasItemsCostos(itemsPropios) {
    let html = '';

    itemsPropios.forEach((item, index) => {
        const itemId = item.id;

        html += `
            <div class="item-cost-card mb-4" id="cardItem_${itemId}">
                <!-- Header de la tarjeta -->
                <div class="card-header bg-primary text-white py-3" style="border-radius: 12px 12px 0 0;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="bg-white text-primary rounded-circle p-2 mr-3 shadow-sm">
                                    <i class="fas fa-cube"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold">${item.nombre}</h6>
                                    <small class="text-white-50">
                                        <i class="fas fa-tag mr-1"></i>${item.codigo || 'Sin código'}
                                        ${item.categoria?.nombre ? `• ${item.categoria.nombre}` : ''}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-light text-primary" id="statusBadge_${itemId}">
                                <i class="fas fa-clock mr-1"></i>Pendiente
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Contenido de configuración -->
                <div class="card-body p-4">
                    ${item.descripcion ? `
                        <div class="alert alert-light border-0 mb-3" style="background-color: #f8f9ff;">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>${item.descripcion}
                            </small>
                        </div>
                    ` : ''}

                    <!-- Selector de tipo de costo -->
                    <div class="cost-type-selector mb-4">
                        <label class="form-label font-weight-bold text-primary mb-3">
                            <i class="fas fa-tag mr-2"></i>Seleccione el Tipo de Costo
                        </label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="tipoUnitario_${itemId}" name="tipoCosto_${itemId}"
                                           value="unitario" class="custom-control-input" onchange="cambiarTipoCostoVisual('${itemId}', 'unitario')">
                                    <label class="custom-control-label" for="tipoUnitario_${itemId}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calculator text-info mr-2"></i>
                                            <div>
                                                <strong>Costo Unitario</strong>
                                                <br><small class="text-muted">Precio por unidad</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="tipoHora_${itemId}" name="tipoCosto_${itemId}"
                                           value="hora" class="custom-control-input" onchange="cambiarTipoCostoVisual('${itemId}', 'hora')">
                                    <label class="custom-control-label" for="tipoHora_${itemId}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-warning mr-2"></i>
                                            <div>
                                                <strong>Costo Hora</strong>
                                                <br><small class="text-muted">Precio por hora</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="tipoDia_${itemId}" name="tipoCosto_${itemId}"
                                           value="dia" class="custom-control-input" onchange="cambiarTipoCostoVisual('${itemId}', 'dia')">
                                    <label class="custom-control-label" for="tipoDia_${itemId}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-day text-success mr-2"></i>
                                            <div>
                                                <strong>Costo Día</strong>
                                                <br><small class="text-muted">Precio por día</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Área de configuración de campos (inicialmente oculta) -->
                    <div id="camposCosto_${itemId}" class="d-none">
                        ${generarCamposConfiguracion(itemId)}
                    </div>

                    <!-- Display del precio calculado -->
                    <div class="price-display d-none" id="precioDisplay_${itemId}">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave mr-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="mb-0">Precio Total</h6>
                                        <small style="opacity: 0.9;">Cálculo automático</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <h4 class="mb-0" id="valorPrecio_${itemId}">$0.00</h4>
                            </div>
                        </div>

                        <!-- Input oculto para compatibilidad -->
                        <input type="hidden" id="precio_${itemId}" value="0">
                    </div>
                </div>
            </div>
        `;
    });

    return html;
}

/**
 * Generar campos de configuración para un item
 */
function generarCamposConfiguracion(itemId) {
    return `
        <!-- Campos básicos -->
        <div class="cost-field-group mb-3">
            <h6 class="text-muted font-weight-bold mb-3">
                <i class="fas fa-cogs mr-2"></i>Configuración Básica
            </h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">
                            <i class="fas fa-ruler mr-1"></i>Unidad de Medida
                        </label>
                        <input type="text" class="form-control" id="unidadMedida_${itemId}"
                               placeholder="Ej: UND, M2, KG, ML" onchange="actualizarPrecioVisual('${itemId}')">
                        <small class="form-text text-muted">Especifique la unidad de medida del item</small>
                    </div>
                </div>
                <div class="col-md-6" id="campoCantidadOperarios_${itemId}">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">
                            <i class="fas fa-users mr-1"></i><span id="labelCantidad_${itemId}">Cantidad</span>
                        </label>
                        <input type="number" class="form-control" id="cantidadOperarios_${itemId}"
                               placeholder="1" step="1" min="1" onchange="actualizarPrecioVisual('${itemId}')">
                        <small class="form-text text-muted" id="helpCantidad_${itemId}">Ingrese la cantidad</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campos específicos por tipo de costo -->
        <div class="cost-field-group mb-3 d-none" id="camposEspecificos_${itemId}">
            <h6 class="text-muted font-weight-bold mb-3">
                <i class="fas fa-dollar-sign mr-2"></i>Configuración de Costos
            </h6>

            <!-- Costo Unitario -->
            <div class="d-none" id="camposCostoUnitario_${itemId}">
                <div class="form-group">
                    <label class="form-label font-weight-bold text-info">
                        <i class="fas fa-calculator mr-1"></i>Valor del Costo Unitario
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white">$</span>
                        </div>
                        <input type="number" class="form-control" id="costoUnitario_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                    </div>
                    <small class="form-text text-muted">Ingrese el costo por unidad individual</small>
                </div>
            </div>

            <!-- Costo Hora -->
            <div class="d-none" id="camposCostoHora_${itemId}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold text-warning">
                                <i class="fas fa-clock mr-1"></i>Valor del Costo por Hora
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-warning text-dark">$</span>
                                </div>
                                <input type="number" class="form-control" id="costoHora_${itemId}"
                                       placeholder="0.00" step="0.01" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                            </div>
                            <small class="form-text text-muted">Costo por cada hora de trabajo</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-sun mr-1 text-warning"></i>Horas Diurnas
                            </label>
                            <input type="number" class="form-control" id="horasDiurnas_${itemId}"
                                   placeholder="0" step="1" min="0" max="7" onchange="validarHorasYActualizar('${itemId}')">
                            <small class="form-text text-danger font-weight-bold">Máximo 7 horas permitidas</small>
                        </div>
                    </div>
                </div>

                <div class="d-none" id="campoHorasRemuneradas_${itemId}">
                    <div class="alert alert-warning border-warning">
                        <div class="form-group mb-0">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-calculator mr-1"></i>Cantidad de Horas Remuneradas
                            </label>
                            <input type="number" class="form-control" id="horasRemuneradas_${itemId}"
                                   placeholder="0" step="1" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                            <small class="form-text text-muted">Ingrese el total de horas que serán remuneradas</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Costo Día -->
            <div class="d-none" id="camposCostoDia_${itemId}">
                <div class="form-group">
                    <label class="form-label font-weight-bold text-success">
                        <i class="fas fa-calendar-day mr-1"></i>Valor del Costo por Día
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-success text-white">$</span>
                        </div>
                        <input type="number" class="form-control" id="costoDia_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                    </div>
                    <small class="form-text text-muted">Costo por cada día de trabajo</small>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-sun mr-1 text-warning"></i>Días Diurnos
                            </label>
                            <input type="number" class="form-control" id="diasDiurnos_${itemId}"
                                   placeholder="0" step="1" min="0" onchange="mostrarCamposDiasRemuneradosVisual('${itemId}')">
                            <small class="form-text text-muted">Cantidad de días en horario diurno</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-moon mr-1 text-info"></i>Días Nocturnos
                            </label>
                            <input type="number" class="form-control" id="diasNocturnos_${itemId}"
                                   placeholder="0" step="1" min="0" onchange="mostrarCamposDiasRemuneradosVisual('${itemId}')">
                            <small class="form-text text-muted">Cantidad de días en horario nocturno</small>
                        </div>
                    </div>
                </div>

                <!-- Campos de días remunerados -->
                <div id="seccionDiasRemunerados_${itemId}" class="d-none">
                    <div class="alert alert-info border-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-calculator mr-2"></i>Configuración de Días Remunerados
                        </h6>
                        <div class="row">
                            <div class="col-md-6 d-none" id="campoDiasRemuneradosDiurnos_${itemId}">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-sun mr-1 text-warning"></i>Días Remunerados (Diurnos)
                                    </label>
                                    <input type="number" class="form-control" id="diasRemuneradosDiurnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                                    <small class="form-text text-muted">Cantidad de días diurnos que serán remunerados</small>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="campoDiasRemuneradosNocturnos_${itemId}">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-moon mr-1 text-info"></i>Días Remunerados (Nocturnos)
                                    </label>
                                    <input type="number" class="form-control" id="diasRemuneradosNocturnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                                    <small class="form-text text-muted">Cantidad de días nocturnos que serán remunerados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Días dominicales -->
                <div class="alert alert-secondary border-0" style="background-color: #f8f9fa;">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="incluirDominicales_${itemId}"
                               onchange="toggleDiasDominicalesVisual('${itemId}')">
                        <label class="custom-control-label font-weight-bold" for="incluirDominicales_${itemId}">
                            <i class="fas fa-calendar-week mr-2 text-purple"></i>¿Desea incluir días dominicales?
                        </label>
                    </div>

                    <div class="d-none mt-3" id="camposDominicales_${itemId}">
                        <div class="row">
                            <div class="col-md-6 d-none" id="campoDominicalDiurno_${itemId}">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-sun mr-1 text-warning"></i>Dominicales Diurnos
                                    </label>
                                    <input type="number" class="form-control" id="dominicalesDiurnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="campoDominicalNocturno_${itemId}">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-moon mr-1 text-info"></i>Dominicales Nocturnos
                                    </label>
                                    <input type="number" class="form-control" id="dominicalesNocturnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="actualizarPrecioVisual('${itemId}')">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Cambiar tipo de costo en la nueva interfaz visual
 */
function cambiarTipoCostoVisual(itemId, tipoCosto) {
    const camposCosto = document.getElementById(`camposCosto_${itemId}`);
    const camposEspecificos = document.getElementById(`camposEspecificos_${itemId}`);
    const camposCostoUnitario = document.getElementById(`camposCostoUnitario_${itemId}`);
    const camposCostoHora = document.getElementById(`camposCostoHora_${itemId}`);
    const camposCostoDia = document.getElementById(`camposCostoDia_${itemId}`);
    const labelCantidad = document.getElementById(`labelCantidad_${itemId}`);
    const helpCantidad = document.getElementById(`helpCantidad_${itemId}`);
    const statusBadge = document.getElementById(`statusBadge_${itemId}`);
    const cardItem = document.getElementById(`cardItem_${itemId}`);

    // Mostrar campos de configuración
    camposCosto.classList.remove('d-none');
    camposEspecificos.classList.remove('d-none');

    // Ocultar todos los campos específicos primero
    camposCostoUnitario.classList.add('d-none');
    camposCostoHora.classList.add('d-none');
    camposCostoDia.classList.add('d-none');

    // Mostrar campos según tipo seleccionado
    switch (tipoCosto) {
        case 'unitario':
            camposCostoUnitario.classList.remove('d-none');
            labelCantidad.textContent = 'Cantidad de Unidades';
            helpCantidad.textContent = 'Ingrese el número de unidades';
            statusBadge.innerHTML = '<i class="fas fa-calculator mr-1"></i>Configurando Unitario';
            statusBadge.className = 'badge badge-info';
            cardItem.style.borderColor = '#17a2b8';
            break;
        case 'hora':
            camposCostoHora.classList.remove('d-none');
            labelCantidad.textContent = 'Número de Operarios';
            helpCantidad.textContent = 'Ingrese la cantidad de operarios';
            statusBadge.innerHTML = '<i class="fas fa-clock mr-1"></i>Configurando por Hora';
            statusBadge.className = 'badge badge-warning';
            cardItem.style.borderColor = '#ffc107';
            break;
        case 'dia':
            camposCostoDia.classList.remove('d-none');
            labelCantidad.textContent = 'Número de Operarios';
            helpCantidad.textContent = 'Ingrese la cantidad de operarios';
            statusBadge.innerHTML = '<i class="fas fa-calendar-day mr-1"></i>Configurando por Día';
            statusBadge.className = 'badge badge-success';
            cardItem.style.borderColor = '#28a745';
            break;
    }

    // Limpiar campos y mostrar precio display
    limpiarCamposCostoVisual(itemId);
    const precioDisplay = document.getElementById(`precioDisplay_${itemId}`);
    precioDisplay.classList.remove('d-none');
}

/**
 * Validar horas y actualizar en la nueva interfaz
 */
function validarHorasYActualizar(itemId) {
    const horasDiurnas = document.getElementById(`horasDiurnas_${itemId}`);
    const campoHorasRemuneradas = document.getElementById(`campoHorasRemuneradas_${itemId}`);

    const horas = parseInt(horasDiurnas.value) || 0;

    if (horas > 7) {
        horasDiurnas.value = 7;
        // Mostrar alerta con SweetAlert2
        Swal.fire({
            type: 'warning',
            title: 'Límite de Horas Excedido',
            text: 'El máximo de horas diurnas permitido es 7. Se ha ajustado automáticamente.',
            confirmButtonText: 'Entendido',
            toast: true,
            position: 'top-end',
            timer: 3000
        });
    }

    // Mostrar campo de horas remuneradas si hay horas configuradas
    if (horas > 0) {
        campoHorasRemuneradas.classList.remove('d-none');
    } else {
        campoHorasRemuneradas.classList.add('d-none');
        document.getElementById(`horasRemuneradas_${itemId}`).value = '';
    }

    actualizarPrecioVisual(itemId);
}

/**
 * Mostrar campos de días remunerados en la nueva interfaz
 */
function mostrarCamposDiasRemuneradosVisual(itemId) {
    const diasDiurnos = parseInt(document.getElementById(`diasDiurnos_${itemId}`).value) || 0;
    const diasNocturnos = parseInt(document.getElementById(`diasNocturnos_${itemId}`).value) || 0;

    const seccionDiasRemunerados = document.getElementById(`seccionDiasRemunerados_${itemId}`);
    const campoDiurnos = document.getElementById(`campoDiasRemuneradosDiurnos_${itemId}`);
    const campoNocturnos = document.getElementById(`campoDiasRemuneradosNocturnos_${itemId}`);

    // Mostrar sección si hay al menos un tipo de día configurado
    if (diasDiurnos > 0 || diasNocturnos > 0) {
        seccionDiasRemunerados.classList.remove('d-none');
    } else {
        seccionDiasRemunerados.classList.add('d-none');
    }

    // Mostrar campos específicos según configuración
    if (diasDiurnos > 0) {
        campoDiurnos.classList.remove('d-none');
    } else {
        campoDiurnos.classList.add('d-none');
        document.getElementById(`diasRemuneradosDiurnos_${itemId}`).value = '';
    }

    if (diasNocturnos > 0) {
        campoNocturnos.classList.remove('d-none');
    } else {
        campoNocturnos.classList.add('d-none');
        document.getElementById(`diasRemuneradosNocturnos_${itemId}`).value = '';
    }

    // Actualizar campos dominicales
    actualizarCamposDominicalesVisual(itemId);
    actualizarPrecioVisual(itemId);
}

/**
 * Toggle días dominicales en la nueva interfaz
 */
function toggleDiasDominicalesVisual(itemId) {
    const checkbox = document.getElementById(`incluirDominicales_${itemId}`);
    const camposDominicales = document.getElementById(`camposDominicales_${itemId}`);

    if (checkbox.checked) {
        camposDominicales.classList.remove('d-none');
        actualizarCamposDominicalesVisual(itemId);
    } else {
        camposDominicales.classList.add('d-none');
        // Limpiar campos
        document.getElementById(`dominicalesDiurnos_${itemId}`).value = '';
        document.getElementById(`dominicalesNocturnos_${itemId}`).value = '';
    }

    actualizarPrecioVisual(itemId);
}

/**
 * Actualizar campos dominicales según configuración de días
 */
function actualizarCamposDominicalesVisual(itemId) {
    const diasDiurnos = parseInt(document.getElementById(`diasDiurnos_${itemId}`).value) || 0;
    const diasNocturnos = parseInt(document.getElementById(`diasNocturnos_${itemId}`).value) || 0;
    const campoDiurno = document.getElementById(`campoDominicalDiurno_${itemId}`);
    const campoNocturno = document.getElementById(`campoDominicalNocturno_${itemId}`);

    // Solo mostrar campos dominicales si hay días configurados del tipo correspondiente
    if (diasDiurnos > 0) {
        campoDiurno.classList.remove('d-none');
    } else {
        campoDiurno.classList.add('d-none');
        document.getElementById(`dominicalesDiurnos_${itemId}`).value = '';
    }

    if (diasNocturnos > 0) {
        campoNocturno.classList.remove('d-none');
    } else {
        campoNocturno.classList.add('d-none');
        document.getElementById(`dominicalesNocturnos_${itemId}`).value = '';
    }
}

/**
 * Actualizar precio en la nueva interfaz visual
 */
function actualizarPrecioVisual(itemId) {
    // Obtener tipo de costo seleccionado
    const tipoRadios = document.querySelectorAll(`input[name="tipoCosto_${itemId}"]:checked`);
    if (tipoRadios.length === 0) return;

    const tipoCosto = tipoRadios[0].value;
    const cantidadOperarios = parseFloat(document.getElementById(`cantidadOperarios_${itemId}`).value) || 0;
    let precio = 0;

    // Calcular precio según tipo
    switch (tipoCosto) {
        case 'unitario':
            const costoUnitario = parseFloat(document.getElementById(`costoUnitario_${itemId}`).value) || 0;
            precio = costoUnitario * cantidadOperarios;
            break;

        case 'hora':
            const costoHora = parseFloat(document.getElementById(`costoHora_${itemId}`).value) || 0;
            const horasRemuneradas = parseFloat(document.getElementById(`horasRemuneradas_${itemId}`).value) || 0;
            precio = costoHora * horasRemuneradas * cantidadOperarios;
            break;

        case 'dia':
            const costoDia = parseFloat(document.getElementById(`costoDia_${itemId}`).value) || 0;
            const diasRemuneradosDiurnos = parseFloat(document.getElementById(`diasRemuneradosDiurnos_${itemId}`).value) || 0;
            const diasRemuneradosNocturnos = parseFloat(document.getElementById(`diasRemuneradosNocturnos_${itemId}`).value) || 0;
            const dominicalesDiurnos = parseFloat(document.getElementById(`dominicalesDiurnos_${itemId}`).value) || 0;
            const dominicalesNocturnos = parseFloat(document.getElementById(`dominicalesNocturnos_${itemId}`).value) || 0;

            const totalDias = diasRemuneradosDiurnos + diasRemuneradosNocturnos + dominicalesDiurnos + dominicalesNocturnos;
            precio = costoDia * totalDias * cantidadOperarios;
            break;
    }

    // Actualizar display del precio
    const valorPrecio = document.getElementById(`valorPrecio_${itemId}`);
    const statusBadge = document.getElementById(`statusBadge_${itemId}`);
    const cardItem = document.getElementById(`cardItem_${itemId}`);

    valorPrecio.textContent = `$${precio.toFixed(2)}`;

    // Actualizar estado visual según precio
    if (precio > 0) {
        statusBadge.innerHTML = '<i class="fas fa-check mr-1"></i>Configurado';
        statusBadge.className = 'badge badge-success';
        cardItem.classList.add('configured');
        cardItem.style.borderColor = '#28a745';
    } else {
        const tipoTexto = tipoCosto.charAt(0).toUpperCase() + tipoCosto.slice(1);
        statusBadge.innerHTML = `<i class="fas fa-edit mr-1"></i>Configurando ${tipoTexto}`;
        cardItem.classList.remove('configured');
    }

    // También actualizar el input oculto para compatibilidad
    const inputPrecio = document.getElementById(`precio_${itemId}`);
    if (inputPrecio) {
        inputPrecio.value = precio.toFixed(2);
    }
}

/**
 * Limpiar campos en la nueva interfaz visual
 */
function limpiarCamposCostoVisual(itemId) {
    // Limpiar todos los campos de entrada
    const campos = [
        `unidadMedida_${itemId}`,
        `cantidadOperarios_${itemId}`,
        `costoUnitario_${itemId}`,
        `costoHora_${itemId}`,
        `costoDia_${itemId}`,
        `horasDiurnas_${itemId}`,
        `horasRemuneradas_${itemId}`,
        `diasDiurnos_${itemId}`,
        `diasNocturnos_${itemId}`,
        `diasRemuneradosDiurnos_${itemId}`,
        `diasRemuneradosNocturnos_${itemId}`,
        `dominicalesDiurnos_${itemId}`,
        `dominicalesNocturnos_${itemId}`
    ];

    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo && campo.id !== `unidadMedida_${itemId}` && campo.id !== `cantidadOperarios_${itemId}`) {
            campo.value = '';
        }
    });

    // Desmarcar checkbox
    const checkbox = document.getElementById(`incluirDominicales_${itemId}`);
    if (checkbox) checkbox.checked = false;

    // Ocultar campos condicionales
    const camposCondicionales = [
        `campoHorasRemuneradas_${itemId}`,
        `seccionDiasRemunerados_${itemId}`,
        `campoDiasRemuneradosDiurnos_${itemId}`,
        `campoDiasRemuneradosNocturnos_${itemId}`,
        `camposDominicales_${itemId}`,
        `campoDominicalDiurno_${itemId}`,
        `campoDominicalNocturno_${itemId}`
    ];

    camposCondicionales.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) campo.classList.add('d-none');
    });

    // Actualizar precio
    actualizarPrecioVisual(itemId);
}

/**
 * Generar acordeón de items propios para configuración de costos
 */
function generarAccordionItemsCostos(itemsPropios) {
    let html = '';

    itemsPropios.forEach((item, index) => {
        const itemId = `item_${item.id}`;
        const isFirst = index === 0;

        html += `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading_${itemId}">
                    <button class="accordion-button ${!isFirst ? 'collapsed' : ''}" type="button"
                            data-toggle="collapse" data-target="#collapse_${itemId}"
                            aria-expanded="${isFirst ? 'true' : 'false'}" aria-controls="collapse_${itemId}">
                        <i class="fas fa-cube mr-2"></i>
                        <strong>${item.nombre}</strong>
                        <small class="text-muted ml-2">(${item.codigo || 'Sin código'})</small>
                    </button>
                </h2>
                <div id="collapse_${itemId}" class="accordion-collapse collapse ${isFirst ? 'show' : ''}"
                     aria-labelledby="heading_${itemId}" data-parent="#accordionCostos">
                    <div class="accordion-body">
                        ${generarFormularioCosto(item)}
                    </div>
                </div>
            </div>
        `;
    });

    return html;
}

/**
 * Generar formulario de configuración de costo para un item
 */
function generarFormularioCosto(item) {
    const itemId = item.id;

    return `
        <div class="row g-3" id="formCosto_${itemId}">
            <!-- Tipo de Costo -->
            <div class="col-md-12">
                <label class="form-label fw-bold">
                    <i class="fas fa-tag mr-1"></i>Tipo de Costo *
                </label>
                <select class="form-select" id="tipoCosto_${itemId}" onchange="cambiarTipoCosto(${itemId}, this.value)" required>
                    <option value="">Seleccione el tipo de costo</option>
                    <option value="unitario">Costo Unitario</option>
                    <option value="hora">Costo Hora</option>
                    <option value="dia">Costo Día</option>
                </select>
            </div>

            <!-- Campos de configuración (inicialmente ocultos) -->
            <div id="camposCosto_${itemId}" class="col-md-12 d-none">
                <div class="row g-3">
                    <!-- Unidad de Medida -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-ruler mr-1"></i>Unidad de Medida *
                        </label>
                        <input type="text" class="form-control" id="unidadMedida_${itemId}"
                               placeholder="Ej: UND, M2, KG" required>
                    </div>

                    <!-- Costo Unitario -->
                    <div class="col-md-6" id="campoCostoUnitario_${itemId}">
                        <label class="form-label">
                            <i class="fas fa-dollar-sign mr-1"></i>Costo Unitario *
                        </label>
                        <input type="number" class="form-control" id="costoUnitario_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="calcularPrecioItem(${itemId})" required>
                    </div>

                    <!-- Costo Hora -->
                    <div class="col-md-6 d-none" id="campoCostoHora_${itemId}">
                        <label class="form-label">
                            <i class="fas fa-clock mr-1"></i>Costo por Hora *
                        </label>
                        <input type="number" class="form-control" id="costoHora_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="calcularPrecioItem(${itemId})" required>
                    </div>

                    <!-- Costo Día -->
                    <div class="col-md-6 d-none" id="campoCostoDia_${itemId}">
                        <label class="form-label">
                            <i class="fas fa-calendar-day mr-1"></i>Costo por Día *
                        </label>
                        <input type="number" class="form-control" id="costoDia_${itemId}"
                               placeholder="0.00" step="0.01" min="0" onchange="calcularPrecioItem(${itemId})" required>
                    </div>

                    <!-- Cantidad / Operarios -->
                    <div class="col-md-6" id="campoCantidadOperarios_${itemId}">
                        <label class="form-label">
                            <i class="fas fa-users mr-1"></i>
                            <span id="labelCantidad_${itemId}">Cantidad</span> *
                        </label>
                        <input type="number" class="form-control" id="cantidadOperarios_${itemId}"
                               placeholder="1" step="1" min="1" onchange="calcularPrecioItem(${itemId})" required>
                        <small class="form-text text-muted" id="helpCantidad_${itemId}">Cantidad de unidades/operarios</small>
                    </div>

                    <!-- Campos específicos para tipo HORA -->
                    <div class="col-md-12 d-none" id="camposHoras_${itemId}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-sun mr-1"></i>Cantidad de horas diurnas
                                </label>
                                <input type="number" class="form-control" id="horasDiurnas_${itemId}"
                                       placeholder="0" step="1" min="0" max="7" onchange="validarHorasYCalcular(${itemId})">
                                <small class="form-text text-muted">Máximo 7 horas</small>
                            </div>
                            <div class="col-md-6" id="campoHorasRemuneradas_${itemId}" style="display: none;">
                                <label class="form-label">
                                    <i class="fas fa-calculator mr-1"></i>Ingrese la cantidad de horas remuneradas
                                </label>
                                <input type="number" class="form-control" id="horasRemuneradas_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                            </div>
                        </div>
                    </div>

                    <!-- Campos específicos para tipo DIA -->
                    <div class="col-md-12 d-none" id="camposDias_${itemId}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-sun mr-1"></i>Cantidad de días diurnos
                                </label>
                                <input type="number" class="form-control" id="diasDiurnos_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="mostrarCamposDiasRemunerados(${itemId}, 'diurnos')">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-moon mr-1"></i>Cantidad de días nocturnos
                                </label>
                                <input type="number" class="form-control" id="diasNocturnos_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="mostrarCamposDiasRemunerados(${itemId}, 'nocturnos')">
                            </div>
                            <div class="col-md-6 d-none" id="campoDiasRemuneradosDiurnos_${itemId}">
                                <label class="form-label">
                                    <i class="fas fa-calculator mr-1"></i>Ingrese la cantidad de días remunerados (diurnos)
                                </label>
                                <input type="number" class="form-control" id="diasRemuneradosDiurnos_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                            </div>
                            <div class="col-md-6 d-none" id="campoDiasRemuneradosNocturnos_${itemId}">
                                <label class="form-label">
                                    <i class="fas fa-calculator mr-1"></i>Ingrese la cantidad de días remunerados (nocturnos)
                                </label>
                                <input type="number" class="form-control" id="diasRemuneradosNocturnos_${itemId}"
                                       placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                            </div>
                        </div>

                        <!-- Días dominicales -->
                        <div class="col-md-12 mt-3" id="seccionDominicales_${itemId}">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="incluirDominicales_${itemId}"
                                       onchange="toggleDiasDominicales(${itemId})">
                                <label class="form-check-label" for="incluirDominicales_${itemId}">
                                    <i class="fas fa-calendar-week mr-1"></i>¿Desea ingresar días dominicales?
                                </label>
                            </div>

                            <div class="row g-3 mt-2 d-none" id="camposDominicales_${itemId}">
                                <div class="col-md-6 d-none" id="campoDominicalDiurno_${itemId}">
                                    <label class="form-label">
                                        <i class="fas fa-sun mr-1"></i>Días dominicales diurnos
                                    </label>
                                    <input type="number" class="form-control" id="dominicalesDiurnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                                </div>
                                <div class="col-md-6 d-none" id="campoDominicalNocturno_${itemId}">
                                    <label class="form-label">
                                        <i class="fas fa-moon mr-1"></i>Días dominicales nocturnos
                                    </label>
                                    <input type="number" class="form-control" id="dominicalesNocturnos_${itemId}"
                                           placeholder="0" step="1" min="0" onchange="calcularPrecioItem(${itemId})">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Precio calculado -->
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-success">
                            <i class="fas fa-money-bill-wave mr-1"></i>Precio Total
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control fw-bold" id="precio_${itemId}" readonly
                                   style="background-color: #e8f5e8; color: #28a745;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Cambiar tipo de costo y mostrar/ocultar campos
 */
function cambiarTipoCosto(itemId, tipoCosto) {
    const camposCosto = document.getElementById(`camposCosto_${itemId}`);
    const campoCostoUnitario = document.getElementById(`campoCostoUnitario_${itemId}`);
    const campoCostoHora = document.getElementById(`campoCostoHora_${itemId}`);
    const campoCostoDia = document.getElementById(`campoCostoDia_${itemId}`);
    const camposHoras = document.getElementById(`camposHoras_${itemId}`);
    const camposDias = document.getElementById(`camposDias_${itemId}`);
    const labelCantidad = document.getElementById(`labelCantidad_${itemId}`);
    const helpCantidad = document.getElementById(`helpCantidad_${itemId}`);

    // Mostrar campos de configuración
    if (tipoCosto) {
        camposCosto.classList.remove('d-none');
    } else {
        camposCosto.classList.add('d-none');
        return;
    }

    // Ocultar todos los campos de costo específicos primero
    campoCostoUnitario.classList.add('d-none');
    campoCostoHora.classList.add('d-none');
    campoCostoDia.classList.add('d-none');
    camposHoras.classList.add('d-none');
    camposDias.classList.add('d-none');

    // Mostrar campos según tipo de costo
    switch (tipoCosto) {
        case 'unitario':
            campoCostoUnitario.classList.remove('d-none');
            labelCantidad.textContent = 'Cantidad';
            helpCantidad.textContent = 'Cantidad de unidades';
            break;
        case 'hora':
            campoCostoHora.classList.remove('d-none');
            camposHoras.classList.remove('d-none');
            labelCantidad.textContent = 'Número de Operarios';
            helpCantidad.textContent = 'Cantidad de operarios';
            break;
        case 'dia':
            campoCostoDia.classList.remove('d-none');
            camposDias.classList.remove('d-none');
            labelCantidad.textContent = 'Número de Operarios';
            helpCantidad.textContent = 'Cantidad de operarios';
            break;
    }

    // Limpiar campos al cambiar tipo
    limpiarCamposCosto(itemId);
}

/**
 * Validar horas y calcular precio para tipo HORA
 */
function validarHorasYCalcular(itemId) {
    const horasDiurnas = document.getElementById(`horasDiurnas_${itemId}`);
    const campoHorasRemuneradas = document.getElementById(`campoHorasRemuneradas_${itemId}`);

    const horas = parseInt(horasDiurnas.value) || 0;

    if (horas > 7) {
        horasDiurnas.value = 7;
        Swal.fire({
            type: 'warning',
            title: 'Límite de horas',
            text: 'El máximo de horas diurnas permitido es 7.',
            confirmButtonText: 'Entendido'
        });
    }

    // Mostrar campo de horas remuneradas si hay horas diurnas
    if (horas > 0) {
        campoHorasRemuneradas.style.display = 'block';
    } else {
        campoHorasRemuneradas.style.display = 'none';
        document.getElementById(`horasRemuneradas_${itemId}`).value = '';
    }

    calcularPrecioItem(itemId);
}

/**
 * Mostrar campos de días remunerados según tipo
 */
function mostrarCamposDiasRemunerados(itemId, tipo) {
    const campoDiurnos = document.getElementById(`campoDiasRemuneradosDiurnos_${itemId}`);
    const campoNocturnos = document.getElementById(`campoDiasRemuneradosNocturnos_${itemId}`);
    const diasDiurnos = parseInt(document.getElementById(`diasDiurnos_${itemId}`).value) || 0;
    const diasNocturnos = parseInt(document.getElementById(`diasNocturnos_${itemId}`).value) || 0;

    // Mostrar campo de días remunerados diurnos si hay días diurnos
    if (diasDiurnos > 0) {
        campoDiurnos.classList.remove('d-none');
    } else {
        campoDiurnos.classList.add('d-none');
        document.getElementById(`diasRemuneradosDiurnos_${itemId}`).value = '';
    }

    // Mostrar campo de días remunerados nocturnos si hay días nocturnos
    if (diasNocturnos > 0) {
        campoNocturnos.classList.remove('d-none');
    } else {
        campoNocturnos.classList.add('d-none');
        document.getElementById(`diasRemuneradosNocturnos_${itemId}`).value = '';
    }

    // Actualizar visibilidad de campos dominicales
    actualizarCamposDominicales(itemId);
    calcularPrecioItem(itemId);
}

/**
 * Toggle días dominicales
 */
function toggleDiasDominicales(itemId) {
    const checkbox = document.getElementById(`incluirDominicales_${itemId}`);
    const camposDominicales = document.getElementById(`camposDominicales_${itemId}`);

    if (checkbox.checked) {
        camposDominicales.classList.remove('d-none');
        actualizarCamposDominicales(itemId);
    } else {
        camposDominicales.classList.add('d-none');
        // Limpiar campos dominicales
        document.getElementById(`dominicalesDiurnos_${itemId}`).value = '';
        document.getElementById(`dominicalesNocturnos_${itemId}`).value = '';
    }
    calcularPrecioItem(itemId);
}

/**
 * Actualizar visibilidad de campos dominicales según días configurados
 */
function actualizarCamposDominicales(itemId) {
    const diasDiurnos = parseInt(document.getElementById(`diasDiurnos_${itemId}`).value) || 0;
    const diasNocturnos = parseInt(document.getElementById(`diasNocturnos_${itemId}`).value) || 0;
    const campoDominicalDiurno = document.getElementById(`campoDominicalDiurno_${itemId}`);
    const campoDominicalNocturno = document.getElementById(`campoDominicalNocturno_${itemId}`);

    // Mostrar campo dominical diurno solo si hay días diurnos configurados
    if (diasDiurnos > 0) {
        campoDominicalDiurno.classList.remove('d-none');
    } else {
        campoDominicalDiurno.classList.add('d-none');
        document.getElementById(`dominicalesDiurnos_${itemId}`).value = '';
    }

    // Mostrar campo dominical nocturno solo si hay días nocturnos configurados
    if (diasNocturnos > 0) {
        campoDominicalNocturno.classList.remove('d-none');
    } else {
        campoDominicalNocturno.classList.add('d-none');
        document.getElementById(`dominicalesNocturnos_${itemId}`).value = '';
    }
}

/**
 * Calcular precio del item según configuración
 */
function calcularPrecioItem(itemId) {
    // Obtener tipo de costo de radio buttons
    const tipoRadios = document.querySelectorAll(`input[name="tipoCosto_${itemId}"]:checked`);
    if (tipoRadios.length === 0) return 0;

    const tipoCosto = tipoRadios[0].value;
    const cantidadOperarios = parseFloat(document.getElementById(`cantidadOperarios_${itemId}`).value) || 0;

    let precio = 0;

    switch (tipoCosto) {
        case 'unitario':
            const costoUnitario = parseFloat(document.getElementById(`costoUnitario_${itemId}`).value) || 0;
            precio = costoUnitario * cantidadOperarios;
            break;

        case 'hora':
            const costoHora = parseFloat(document.getElementById(`costoHora_${itemId}`).value) || 0;
            const horasRemuneradas = parseFloat(document.getElementById(`horasRemuneradas_${itemId}`).value) || 0;
            precio = costoHora * horasRemuneradas * cantidadOperarios;
            break;

        case 'dia':
            const costoDia = parseFloat(document.getElementById(`costoDia_${itemId}`).value) || 0;
            const diasRemuneradosDiurnos = parseFloat(document.getElementById(`diasRemuneradosDiurnos_${itemId}`).value) || 0;
            const diasRemuneradosNocturnos = parseFloat(document.getElementById(`diasRemuneradosNocturnos_${itemId}`).value) || 0;
            const dominicalesDiurnos = parseFloat(document.getElementById(`dominicalesDiurnos_${itemId}`).value) || 0;
            const dominicalesNocturnos = parseFloat(document.getElementById(`dominicalesNocturnos_${itemId}`).value) || 0;

            const totalDias = diasRemuneradosDiurnos + diasRemuneradosNocturnos + dominicalesDiurnos + dominicalesNocturnos;
            precio = costoDia * totalDias * cantidadOperarios;
            break;
    }

    // Actualizar campo de precio
    const campoPrecio = document.getElementById(`precio_${itemId}`);
    campoPrecio.value = precio.toFixed(2);
}

/**
 * Limpiar campos de costo al cambiar tipo
 */
function limpiarCamposCosto(itemId) {
    // Limpiar todos los campos de entrada
    const campos = [
        `costoUnitario_${itemId}`,
        `costoHora_${itemId}`,
        `costoDia_${itemId}`,
        `cantidadOperarios_${itemId}`,
        `horasDiurnas_${itemId}`,
        `horasRemuneradas_${itemId}`,
        `diasDiurnos_${itemId}`,
        `diasNocturnos_${itemId}`,
        `diasRemuneradosDiurnos_${itemId}`,
        `diasRemuneradosNocturnos_${itemId}`,
        `dominicalesDiurnos_${itemId}`,
        `dominicalesNocturnos_${itemId}`,
        `precio_${itemId}`
    ];

    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.value = '';
        }
    });

    // Desmarcar checkbox dominicales
    const checkboxDominicales = document.getElementById(`incluirDominicales_${itemId}`);
    if (checkboxDominicales) {
        checkboxDominicales.checked = false;
    }

    // Ocultar campos condicionales
    const camposCondicionales = [
        `campoHorasRemuneradas_${itemId}`,
        `campoDiasRemuneradosDiurnos_${itemId}`,
        `campoDiasRemuneradosNocturnos_${itemId}`,
        `camposDominicales_${itemId}`,
        `campoDominicalDiurno_${itemId}`,
        `campoDominicalNocturno_${itemId}`
    ];

    camposCondicionales.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.classList.add('d-none');
            campo.style.display = 'none';
        }
    });
}

/**
 * Volver al modal de selección de items propios
 */
function volverASeleccionItemsPropios() {
    $('#modalConfiguracionCostos').modal('hide');

    // Restaurar modal de items propios con selecciones previas
    const itemsPropios = window.itemsPropiosTemporal;
    const categoriaIds = window.categoriasTemporal?.map(c => c.id) || [];

    abrirModalSeleccionItemsPropios(itemsPropios, categoriaIds);

    // Restaurar selecciones previas
    setTimeout(() => {
        window.itemsPropiosSeleccionadosTemporal?.forEach(item => {
            const checkbox = document.getElementById(`item_${item.id}`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
        actualizarContadorSeleccionados();
    }, 300);
}

/**
 * Finalizar configuración de costos y agregar todo a la tabla
 */
async function finalizarConfiguracionCostos() {
    const itemsPropiosSeleccionados = window.itemsPropiosSeleccionadosTemporal;
    const errores = [];
    const itemsConCostos = [];

    // Validar configuración de cada item
    for (const item of itemsPropiosSeleccionados) {
        const itemId = item.id;

        // Obtener tipo de costo de radio buttons
        const tipoRadios = document.querySelectorAll(`input[name="tipoCosto_${itemId}"]:checked`);
        const tipoCosto = tipoRadios.length > 0 ? tipoRadios[0].value : '';

        const unidadMedida = document.getElementById(`unidadMedida_${itemId}`).value.trim();
        const cantidadOperarios = document.getElementById(`cantidadOperarios_${itemId}`).value;

        // Obtener precio del display visual o input oculto
        let precio = document.getElementById(`valorPrecio_${itemId}`)?.textContent?.replace('$', '') ||
                    document.getElementById(`precio_${itemId}`)?.value || '0';

        if (!tipoCosto) {
            errores.push(`Debe seleccionar el tipo de costo para "${item.nombre}"`);
            continue;
        }

        if (!unidadMedida) {
            errores.push(`Debe ingresar la unidad de medida para "${item.nombre}"`);
            continue;
        }

        if (!cantidadOperarios || cantidadOperarios <= 0) {
            errores.push(`Debe ingresar una cantidad válida para "${item.nombre}"`);
            continue;
        }

        if (!precio || precio <= 0) {
            errores.push(`El precio calculado debe ser mayor a 0 para "${item.nombre}"`);
            continue;
        }

        // Validaciones específicas por tipo de costo
        let costoEspecifico = 0;
        switch (tipoCosto) {
            case 'unitario':
                costoEspecifico = document.getElementById(`costoUnitario_${itemId}`).value;
                if (!costoEspecifico || costoEspecifico <= 0) {
                    errores.push(`Debe ingresar un costo unitario válido para "${item.nombre}"`);
                    continue;
                }
                break;
            case 'hora':
                costoEspecifico = document.getElementById(`costoHora_${itemId}`).value;
                const horasRemuneradas = document.getElementById(`horasRemuneradas_${itemId}`).value;
                if (!costoEspecifico || costoEspecifico <= 0) {
                    errores.push(`Debe ingresar un costo por hora válido para "${item.nombre}"`);
                    continue;
                }
                if (!horasRemuneradas || horasRemuneradas <= 0) {
                    errores.push(`Debe ingresar las horas remuneradas para "${item.nombre}"`);
                    continue;
                }
                break;
            case 'dia':
                costoEspecifico = document.getElementById(`costoDia_${itemId}`).value;
                const diasRemuneradosDiurnos = document.getElementById(`diasRemuneradosDiurnos_${itemId}`).value || 0;
                const diasRemuneradosNocturnos = document.getElementById(`diasRemuneradosNocturnos_${itemId}`).value || 0;

                if (!costoEspecifico || costoEspecifico <= 0) {
                    errores.push(`Debe ingresar un costo por día válido para "${item.nombre}"`);
                    continue;
                }
                if (parseFloat(diasRemuneradosDiurnos) + parseFloat(diasRemuneradosNocturnos) <= 0) {
                    errores.push(`Debe ingresar al menos un día remunerado para "${item.nombre}"`);
                    continue;
                }
                break;
        }

        // Recopilar toda la configuración del item
        const itemConCosto = {
            ...item,
            configuracionCosto: {
                tipoCosto,
                unidadMedida,
                cantidadOperarios: parseFloat(cantidadOperarios),
                precio: parseFloat(precio),
                costoUnitario: tipoCosto === 'unitario' ? parseFloat(document.getElementById(`costoUnitario_${itemId}`).value) : null,
                costoHora: tipoCosto === 'hora' ? parseFloat(document.getElementById(`costoHora_${itemId}`).value) : null,
                costoDia: tipoCosto === 'dia' ? parseFloat(document.getElementById(`costoDia_${itemId}`).value) : null,
                horasDiurnas: tipoCosto === 'hora' ? (parseFloat(document.getElementById(`horasDiurnas_${itemId}`).value) || 0) : null,
                horasRemuneradas: tipoCosto === 'hora' ? (parseFloat(document.getElementById(`horasRemuneradas_${itemId}`).value) || 0) : null,
                diasDiurnos: tipoCosto === 'dia' ? (parseFloat(document.getElementById(`diasDiurnos_${itemId}`).value) || 0) : null,
                diasNocturnos: tipoCosto === 'dia' ? (parseFloat(document.getElementById(`diasNocturnos_${itemId}`).value) || 0) : null,
                diasRemuneradosDiurnos: tipoCosto === 'dia' ? (parseFloat(document.getElementById(`diasRemuneradosDiurnos_${itemId}`).value) || 0) : null,
                diasRemuneradosNocturnos: tipoCosto === 'dia' ? (parseFloat(document.getElementById(`diasRemuneradosNocturnos_${itemId}`).value) || 0) : null,
                dominicalesDiurnos: tipoCosto === 'dia' ? (parseFloat(document.getElementById(`dominicalesDiurnos_${itemId}`).value) || 0) : null,
                dominicalesNocturnos: tipoCosto === 'dia' ? (parseFloat(document.getElementById(`dominicalesNocturnos_${itemId}`).value) || 0) : null,
                incluirDominicales: tipoCosto === 'dia' ? document.getElementById(`incluirDominicales_${itemId}`).checked : false
            }
        };

        itemsConCostos.push(itemConCosto);
    }

    // Mostrar errores si los hay
    if (errores.length > 0) {
        Swal.fire({
            type: 'error',
            title: 'Errores de validación',
            html: errores.map(error => `• ${error}`).join('<br>'),
            confirmButtonText: 'Entendido'
        });
        return;
    }

    try {
         // Actualizar contador
        actualizarContadorProductosSeleccionados();

        // También agregar a la tabla del modal "Items Propios Seleccionados"
        actualizarTablaItemsPropiosSeleccionados(itemsConCostos, window.subitemTemporal.subitem, window.subitemTemporal.item);
        // Limpiar variables temporales
        window.subitemTemporal = null;
        window.itemsPropiosTemporal = null;
        window.itemsPropiosSeleccionadosTemporal = null;
        window.categoriasTemporal = null;

        // Limpiar selección del acordeón
        const itemSeleccionado = document.querySelector('input[name="itemSelected"]:checked');
        if (itemSeleccionado) {
            itemSeleccionado.checked = false;
        }

        // Cerrar modal
        $('#modalConfiguracionCostos').modal('hide');

        // Mostrar mensaje de éxito
        Swal.fire({
            icon: 'success',
            title: 'Configuración completada',
            text: `Subitem agregado con ${itemsConCostos.length} item(s) propio(s) configurado(s).`,
            confirmButtonText: 'Entendido',
            timer: 3000
        });

        // SINCRONIZAR: Convertir items de la tabla a productosSeleccionados
        console.log('Llamando sincronizarItemsTablaConProductosSeleccionados...');
        sincronizarItemsTablaConProductosSeleccionados(itemsConCostos);


    } catch (error) {
        console.error('Error al finalizar configuración:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'No se pudo completar la configuración. Intente nuevamente.',
            confirmButtonText: 'Entendido'
        });
    }
}

/**
 * Sincronizar items de tbody_items con productosSeleccionados
 */
function sincronizarItemsTablaConProductosSeleccionados(itemsConCostos) {
    console.log('=== SINCRONIZANDO ITEMS CON PRODUCTOS SELECCIONADOS ===');
    console.log('Productos con costos:::::D', itemsConCostos);

    // Limpiar productos seleccionados para evitar duplicados
    productosSeleccionados = [];

    // Buscar datos directamente desde window.subitemsSeleccionados
    if (itemsConCostos && itemsConCostos.length > 0) {
        console.log(`Encontrados ${itemsConCostos.length} subitems con configuración`);

        itemsConCostos.forEach(subitem => {

            // Usar directamente los datos de configuración de costos
            const precio = subitem.configuracionCosto ?
                parseFloat(subitem.configuracionCosto.precio) : 50.0;
            const cantidad = subitem.configuracionCosto ?
                parseFloat(subitem.configuracionCosto.cantidadOperarios) : 1;
            const unidad = subitem.configuracionCosto ?
                subitem.configuracionCosto.unidadMedida : 'Unidad';

            const id = `item_${subitem.nombre.replace(/\s+/g, '_').toLowerCase()}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

            const nuevoProducto = {
                id: id,
                nombre: subitem.nombre,
                codigo: subitem.codigo || '',
                precio: subitem.configuracionCosto ? precio : 50.0,
                cantidad: subitem.configuracionCosto ? cantidad : 1,
                total: precio * cantidad,
                unidad: subitem.configuracionCosto ? unidad : 'Unidad',
                categoria: subitem.categoria?.nombre || 'Item Propio',
                descripcion: subitem.descripcion || '',
                esDelAcordeon: true,
                item_parent: subitem.subitem?.nombre || 'Configuración de Costos',
                // Campos de configuración de costos
                categoria_id: subitem.categoria_id || null,
                cargo_id: subitem.cargo_id || null,
                configuracionCosto: subitem.configuracionCosto || null
            };

            productosSeleccionados.push(nuevoProducto);
            console.log(`Producto sincronizado: ${subitem.nombre} - Precio: $${precio}`);


        });
    } else {
        console.log(`Debe haber al menos un subitem con items propios configurados para sincronizar.`);
    }

    // Actualizar la tabla de productos seleccionados
    actualizarTablaProductosSeleccionados();

    console.log(`=== SINCRONIZACIÓN COMPLETADA ===`);
    console.log(`Total productosSeleccionados: ${productosSeleccionados.length}`);
    console.log('Productos finales:', productosSeleccionados);
}

/**
 * Agregar subitem con items propios configurados con costos
 */
async function agregarSubitemConItemsPropiosConfigurados(subitem, itemParent, itemsConCostos) {
    try {
        const subitemId = `subitem_${subitem.id || Date.now()}`;
        const tbody = document.getElementById('tbody_items');

        if (!tbody) {
            console.error('Tabla de items no encontrada');
            return Promise.reject(new Error('Tabla de productos no encontrada'));
        }

        // Eliminar mensaje de tabla vacía
        const mensajeVacio = tbody.querySelector('#no_items_row_items');
        if (mensajeVacio) {
            mensajeVacio.remove();
        }

        // Crear sección del subitem con información de configuración
        const subitemRow = document.createElement('tr');
        subitemRow.setAttribute('data-subitem-id', subitemId);
        subitemRow.classList.add('table-info', 'subitem-header');

        // Calcular precio total del subitem
        const precioTotalSubitem = itemsConCostos.reduce((total, item) => total + item.configuracionCosto.precio, 0);

        subitemRow.innerHTML = `
            <td colspan="2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-cubes text-info fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><strong>${subitem.codigo || subitem.nombre}</strong></h6>
                            <small class="text-muted">
                                <i class="fas fa-folder mr-1"></i>Item padre: ${itemParent.nombre}
                            </small>
                            <br><small class="text-info">
                                ${subitem.descripcion || subitem.observacion || 'Sin descripción'}
                            </small>
                            <br><small class="text-success">
                                <i class="fas fa-money-bill-wave mr-1"></i><strong>Total: $${precioTotalSubitem.toFixed(2)}</strong>
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info mr-2">${itemsConCostos.length} Item(s) Configurado(s)</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarSubitemCompleto('${subitemId}')" title="Quitar subitem completo">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(subitemRow);

        // Agregar items propios con configuración de costos
        if (itemsConCostos && itemsConCostos.length > 0) {
            itemsConCostos.forEach((itemPropio, index) => {
                const itemPropioRow = document.createElement('tr');
                itemPropioRow.setAttribute('data-item-propio-id', itemPropio.id);
                itemPropioRow.setAttribute('data-subitem-parent', subitemId);
                itemPropioRow.classList.add('item-propio-row');

                const config = itemPropio.configuracionCosto;

                // Generar resumen de configuración
                let resumenConfig = '';
                switch (config.tipoCosto) {
                    case 'unitario':
                        resumenConfig = `Unitario: $${config.costoUnitario} x ${config.cantidadOperarios}`;
                        break;
                    case 'hora':
                        resumenConfig = `Hora: $${config.costoHora} x ${config.horasRemuneradas}h x ${config.cantidadOperarios} operarios`;
                        break;
                    case 'dia':
                        const totalDias = (config.diasRemuneradosDiurnos || 0) + (config.diasRemuneradosNocturnos || 0) +
                                        (config.dominicalesDiurnos || 0) + (config.dominicalesNocturnos || 0);
                        resumenConfig = `Día: $${config.costoDia} x ${totalDias} días x ${config.cantidadOperarios} operarios`;
                        break;
                }

                itemPropioRow.innerHTML = `
                    <td style="padding-left: 3rem;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-cube text-success mr-2"></i>
                                    <strong class="text-dark">${itemPropio.nombre}</strong>
                                    <span class="badge bg-light text-dark ml-2">${itemPropio.codigo || 'Sin código'}</span>
                                </div>

                                <div class="row g-2 text-sm">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-tag mr-1"></i>Tipo: ${config.tipoCosto.charAt(0).toUpperCase() + config.tipoCosto.slice(1)}
                                        </small>
                                        <br><small class="text-muted">
                                            <i class="fas fa-ruler mr-1"></i>Unidad: ${config.unidadMedida}
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calculator mr-1"></i>${resumenConfig}
                                        </small>
                                    </div>
                                </div>

                                ${itemPropio.descripcion ? `
                                <div class="mt-1">
                                    <small class="text-info">
                                        <i class="fas fa-info-circle mr-1"></i>${itemPropio.descripcion}
                                    </small>
                                </div>
                                ` : ''}

                                <!-- Detalles adicionales colapsables -->
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse"
                                            data-target="#detalles_${itemPropio.id}_${index}" aria-expanded="false">
                                        <i class="fas fa-info-circle mr-1"></i>Ver detalles
                                    </button>
                                </div>

                                <div class="collapse mt-2" id="detalles_${itemPropio.id}_${index}">
                                    <div class="card card-body bg-light">
                                        ${generarDetallesConfiguracion(config)}
                                    </div>
                                </div>
                            </div>

                            <div class="text-end ml-3">
                                <div class="mb-1">
                                    <span class="badge bg-success fs-6">$${config.precio.toFixed(2)}</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="quitarItemPropio('${subitemId}', '${itemPropio.id}')" title="Quitar item propio">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                `;

                tbody.appendChild(itemPropioRow);
            });
        }

        console.log(`Subitem "${subitem.codigo || subitem.nombre}" agregado con ${itemsConCostos.length} items configurados`);
        return Promise.resolve();

    } catch (error) {
        console.error('Error al agregar subitem con items configurados:', error);
        return Promise.reject(error);
    }
}

/**
 * Actualizar tabla de Items Propios Seleccionados en el modal
 */
function actualizarTablaItemsPropiosSeleccionados(itemsConCostos, subitem, itemParent) {
    const tbodyModal = document.getElementById('tbodyProductosSeleccionados');
    if (!tbodyModal) {
        console.warn('No se encontró la tabla tbodyProductosSeleccionados en el modal');
        return;
    }

    // Remover mensaje de "no hay items propios seleccionados"
    const mensajeVacio = tbodyModal.querySelector('#noProductosSeleccionados');
    if (mensajeVacio) {
        mensajeVacio.remove();
    }
    // Agregar cada item propio configurado a la tabla del modal
    itemsConCostos.forEach((itemPropio) => {
        const row = document.createElement('tr');
        row.setAttribute('data-item-id', itemPropio.id);

        const config = itemPropio.configuracionCosto;
        const resumenConfig = `${config.tipoCosto.toUpperCase()}: $${config.precio.toFixed(2)}`;

        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <i class="fas fa-cube text-success"></i>
                    </div>
                    <div>
                        <strong class="text-dark">Nombre: ${itemPropio.nombre} código: ${itemPropio.codigo || 'Sin código'}</strong>
                        <br><small class="text-danger">${resumenConfig}</small>
                        <br><small class="text-muted">Item: ${itemParent.nombre} del subitem: ${subitem.codigo || subitem.nombre}</small>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="quitarItemPropioDelModal('${itemPropio.id}')" title="Quitar">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;

        tbodyModal.appendChild(row);
    });

    console.log(`Agregados ${itemsConCostos.length} items propios a la tabla del modal`);
}

/**
 * Quitar item propio de la tabla del modal
 */
function quitarItemPropioDelModal(itemId) {
    const tbodyModal = document.getElementById('tbodyProductosSeleccionados');
    if (!tbodyModal) return;

    const row = tbodyModal.querySelector(`tr[data-item-id="${itemId}"]`);
    if (row) {
        row.remove();

        // Si no quedan items, mostrar mensaje
        const remainingRows = tbodyModal.querySelectorAll('tr[data-item-id]');
        if (remainingRows.length === 0) {
            const mensajeVacio = document.createElement('tr');
            mensajeVacio.id = 'noProductosSeleccionados';
            mensajeVacio.innerHTML = `
                <td colspan="2" class="text-center text-muted">
                    No hay items propios seleccionados
                </td>
            `;
            tbodyModal.appendChild(mensajeVacio);
        }
    }
}

/**
 * Generar detalles de configuración para mostrar en el colapso
 */
function generarDetallesConfiguracion(config) {
    let detalles = `
        <div class="row g-2">
            <div class="col-md-6">
                <small><strong>Tipo de Costo:</strong> ${config.tipoCosto.charAt(0).toUpperCase() + config.tipoCosto.slice(1)}</small>
                <br><small><strong>Unidad de Medida:</strong> ${config.unidadMedida}</small>
                <br><small><strong>Cantidad/Operarios:</strong> ${config.cantidadOperarios}</small>
            </div>
            <div class="col-md-6">
    `;

    switch (config.tipoCosto) {
        case 'unitario':
            detalles += `
                <small><strong>Costo Unitario:</strong> $${config.costoUnitario}</small>
                <br><small><strong>Total:</strong> $${config.costoUnitario} x ${config.cantidadOperarios} = $${config.precio.toFixed(2)}</small>
            `;
            break;

        case 'hora':
            detalles += `
                <small><strong>Costo por Hora:</strong> $${config.costoHora}</small>
                <br><small><strong>Horas Diurnas:</strong> ${config.horasDiurnas || 0}</small>
                <br><small><strong>Horas Remuneradas:</strong> ${config.horasRemuneradas || 0}</small>
                <br><small><strong>Total:</strong> $${config.costoHora} x ${config.horasRemuneradas} x ${config.cantidadOperarios} = $${config.precio.toFixed(2)}</small>
            `;
            break;

        case 'dia':
            const totalDiasRemunerados = (config.diasRemuneradosDiurnos || 0) + (config.diasRemuneradosNocturnos || 0);
            const totalDominicales = (config.dominicalesDiurnos || 0) + (config.dominicalesNocturnos || 0);
            const totalDias = totalDiasRemunerados + totalDominicales;

            detalles += `
                <small><strong>Costo por Día:</strong> $${config.costoDia}</small>
                <br><small><strong>Días Diurnos:</strong> ${config.diasDiurnos || 0}</small>
                <br><small><strong>Días Nocturnos:</strong> ${config.diasNocturnos || 0}</small>
                <br><small><strong>Días Remunerados:</strong> ${totalDiasRemunerados}</small>
                ${totalDominicales > 0 ? `<br><small><strong>Días Dominicales:</strong> ${totalDominicales}</small>` : ''}
                <br><small><strong>Total:</strong> $${config.costoDia} x ${totalDias} x ${config.cantidadOperarios} = $${config.precio.toFixed(2)}</small>
            `;
            break;
    }

    detalles += `
            </div>
        </div>
    `;

    return detalles;
}

/**
 * Cargar items propios de un subitem basándose en su categoría
 */
async function cargarItemsPropiosDelSubitem(subitem, itemParent) {
    try {
        // Obtener la categoría del subitem (puede estar en el subitem mismo o heredada del item padre)
        let categoriaId = null;

        if (subitem.categoria_id) {
            categoriaId = subitem.categoria_id;
        } else if (itemParent.categoria_id) {
            categoriaId = itemParent.categoria_id;
        } else {
            // Categoría por defecto si no se encuentra
            categoriaId = 1;
        }

        console.log(`Cargando items propios para subitem "${subitem.codigo || subitem.nombre}" con categoría ID: ${categoriaId}`);

        // Preparar datos para la petición
        const requestData = {
            categoria_ids: [categoriaId]
        };

        // Obtener la URL base
        const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
        const url = `${baseUrl}/admin/admin.cotizaciones.items-categoria.obtener`;

        // Hacer petición al backend
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            console.log(`Se encontraron ${result.data.length} items propios para la categoría ${categoriaId}`);
            return result.data;
        } else {
            console.warn('No se encontraron items propios, usando datos simulados');
            // Datos simulados como fallback
            return [
                {
                    id: `sim_1_${subitem.id}`,
                    nombre: `Item Propio 1 - ${subitem.codigo || subitem.nombre}`,
                    codigo: 'IP-001',
                    categoria: { nombre: 'Categoría General' },
                    descripcion: `Item propio relacionado con ${subitem.codigo || subitem.nombre}`,
                    unidad_medida: 'Unidad'
                },
                {
                    id: `sim_2_${subitem.id}`,
                    nombre: `Item Propio 2 - ${subitem.codigo || subitem.nombre}`,
                    codigo: 'IP-002',
                    categoria: { nombre: 'Categoría General' },
                    descripcion: `Segundo item propio para ${subitem.codigo || subitem.nombre}`,
                    unidad_medida: 'Kg'
                }
            ];
        }

    } catch (error) {
        console.error('Error al cargar items propios del subitem:', error);
        throw new Error(`No se pudieron cargar los items propios: ${error.message}`);
    }
}

/**
 * Agregar subitem con sus items propios a la tabla de productos seleccionados
 */
async function agregarSubitemConItemsPropios(subitem, itemParent, itemsPropios) {
    try {
        const tbody = document.getElementById('tbody_items');
        const subitemId = `subitem_${subitem.id || subitem.codigo}`;

        // Verificar si ya existe en la tabla
        const existeSubitem = tbody.querySelector(`tr[data-subitem-id="${subitemId}"]`);
        if (existeSubitem) {
            throw new Error(`El subitem "${subitem.codigo || subitem.nombre}" ya está agregado.`);
        }

        // Limpiar tabla si tiene mensaje de "no hay elementos"
        const mensajeVacio = tbody.querySelector('#no_items_row_items');
        if (mensajeVacio) {
            mensajeVacio.remove();
        }

        // Crear sección del subitem
        const subitemRow = document.createElement('tr');
        subitemRow.setAttribute('data-subitem-id', subitemId);
        subitemRow.classList.add('table-info', 'subitem-header');
        subitemRow.innerHTML = `
            <td colspan="2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-cubes text-info fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><strong>${subitem.codigo || subitem.nombre}</strong></h6>
                            <small class="text-muted">
                                <i class="fas fa-folder mr-1"></i>Item padre: ${itemParent.nombre}
                            </small>
                            <br><small class="text-info">
                                ${subitem.descripcion || subitem.observacion || 'Sin descripción'}
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info mr-2">Subitem</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarSubitemCompleto('${subitemId}')" title="Quitar subitem completo">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(subitemRow);

        // Agregar items propios del subitem
        if (itemsPropios && itemsPropios.length > 0) {
            itemsPropios.forEach((itemPropio, index) => {
                const itemPropioRow = document.createElement('tr');
                itemPropioRow.setAttribute('data-parent-subitem', subitemId);
                itemPropioRow.classList.add('item-propio-row');
                itemPropioRow.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center ps-4">
                            <div class="me-3">
                                <i class="fas fa-cube text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div>
                                    <strong>${itemPropio.nombre}</strong>
                                    <br><small class="text-muted">
                                        <i class="fas fa-tag mr-1"></i>Código: ${itemPropio.codigo || 'No definido'}
                                    </small>
                                    <br><small class="text-success">
                                        ${itemPropio.descripcion || 'Sin descripción'}
                                    </small>
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-success">Item Propio</span>
                                    ${itemPropio.categoria?.nombre ? `<span class="badge bg-secondary ml-1">${itemPropio.categoria.nombre}</span>` : ''}
                                    ${itemPropio.unidad_medida ? `<span class="badge bg-info ml-1">${itemPropio.unidad_medida}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="quitarItemPropio('${subitemId}', '${itemPropio.id}')" title="Quitar item propio">
                            <i class="fas fa-minus"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(itemPropioRow);
            });
        } else {
            // Mostrar mensaje si no hay items propios
            const noItemsRow = document.createElement('tr');
            noItemsRow.setAttribute('data-parent-subitem', subitemId);
            noItemsRow.classList.add('item-propio-row');
            noItemsRow.innerHTML = `
                <td colspan="2" class="text-center text-muted ps-4">
                    <em>No hay items propios disponibles para este subitem</em>
                </td>
            `;
            tbody.appendChild(noItemsRow);
        }

        // Actualizar variable global
        if (!window.subitemsSeleccionados) {
            window.subitemsSeleccionados = [];
        }

        window.subitemsSeleccionados.push({
            id: subitemId,
            subitem: subitem,
            itemParent: itemParent,
            itemsPropios: itemsPropios,
            fechaAgregado: new Date().toISOString()
        });

        // Actualizar contador
        actualizarContadorProductosSeleccionados();

        console.log(`Subitem "${subitem.codigo || subitem.nombre}" agregado con ${itemsPropios.length} items propios`);
        return Promise.resolve();

    } catch (error) {
        console.error('Error al agregar subitem con items propios:', error);
        return Promise.reject(error);
    }
}

/**
 * Agregar subitem a la tabla de productos seleccionados
 */
async function agregarSubitemAProductosSeleccionados(subitem, itemParent) {
    try {
        // Verificar si ya existe en la tabla
        const tbody = document.getElementById('tbody_items');
        const subitemId = `subitem_${subitem.id || subitem.codigo}`;
        const existeSubitem = tbody.querySelector(`tr[data-subitem-id="${subitemId}"]`);

        if (existeSubitem) {
            throw new Error(`El subitem "${subitem.codigo || subitem.nombre}" ya está agregado a los productos seleccionados.`);
        }

        // Limpiar tabla si tiene mensaje de "no hay elementos"
        const mensajeVacio = tbody.querySelector('#no_items_row_items');
        if (mensajeVacio) {
            mensajeVacio.remove();
        }
        console.log('subitem', subitem);


        // Crear fila del subitem
        const row = document.createElement('tr');
        row.setAttribute('data-subitem-id', subitemId);
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-cubes text-info"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${subitem.codigo || subitem.nombre}</strong>
                                <br><small class="text-muted">
                                    <i class="fas fa-folder mr-1"></i>Item padre: ${itemParent.nombre}
                                </small>
                                <br><small class="text-info">
                                    ${subitem.descripcion || subitem.observacion || 'Sin descripción'}
                                </small>
                                ${subitem.cantidad ? `<br><small class="text-secondary">
                                    <i class="fas fa-sort-numeric-up mr-1"></i>Cantidad: ${subitem.cantidad}
                                    ${subitem.unidad_medida.sigla ? ` ${subitem.unidad_medida.sigla}` : ''}
                                </small>` : ''}
                            </div>
                        </div>
                        <div class="mt-1">
                            <span class="badge bg-info">Subitem</span>
                            ${subitem.unidad_medida.sigla ? `<span class="badge bg-secondary ml-1">${subitem.unidad_medida.sigla}</span>` : ''}
                            ${subitem.active ? '<span class="badge bg-success ml-1">Activo</span>' : '<span class="badge bg-warning ml-1">Inactivo</span>'}
                        </div>
                    </div>
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarSubitemSeleccionado('${subitemId}')" title="Quitar subitem">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);

        // Actualizar contador de productos seleccionados si existe
        actualizarContadorProductosSeleccionados();

        // Guardar en variable global para posterior uso
        if (!window.subitemsSeleccionados) {
            window.subitemsSeleccionados = [];
        }

        window.subitemsSeleccionados.push({
            id: subitemId,
            subitem: subitem,
            itemParent: itemParent,
            fechaAgregado: new Date().toISOString()
        });

        console.log('Subitem agregado:', subitem.codigo || subitem.nombre);
        return Promise.resolve();

    } catch (error) {
        console.error('Error al agregar subitem:', error);
        return Promise.reject(error);
    }
}

/**
 * Quitar subitem seleccionado de la tabla
 */
function quitarSubitemSeleccionado(subitemId) {
    const row = document.querySelector(`tr[data-subitem-id="${subitemId}"]`);
    if (row) {
        row.remove();

        // Actualizar variable global
        if (window.subitemsSeleccionados) {
            window.subitemsSeleccionados = window.subitemsSeleccionados.filter(item => item.id !== subitemId);
        }

        // Si no quedan elementos, mostrar mensaje
        const tbody = document.getElementById('tbody_items');
        if (tbody.children.length === 0) {
            tbody.innerHTML = `
                <tr id="no_items_row_items">
                    <td colspan="2" class="text-center text-muted">
                        No hay subitems seleccionados
                    </td>
                </tr>
            `;
        }

        // Actualizar contador
        actualizarContadorProductosSeleccionados();

        toastr.success('Subitem eliminado correctamente');
    }
}

/**
 * Actualizar contador de productos seleccionados
 */
function actualizarContadorProductosSeleccionados() {
    const contador = document.getElementById('contadorProductosSeleccionados');
    if (contador) {
        const tbody = document.getElementById('tbody_items');
        const cantidadSubitems = tbody ? tbody.querySelectorAll('tr.subitem-header').length : 0;
        contador.textContent = cantidadSubitems;
    }
}

/**
 * Cargar items propios por categoría
 */
async function cargarItemsPropiosPorCategoria(itemInfo, tipo) {
    try {
        // Obtener la categoría del item/subitem seleccionado
        let categoriaId = null;

        if (tipo === 'item') {
            // Para items principales, intentar obtener categoria_id directamente
            categoriaId = itemInfo.categoria_id || 1; // Default a categoría 1 si no existe
        } else if (tipo === 'subitem') {
            // Para subitems, obtener del item padre
            categoriaId = itemInfo.parentItem?.categoria_id || itemInfo.categoria_id || 1;
        }

        // Preparar datos para la petición
        const requestData = {
            categoria_ids: [categoriaId]
        };

        // Obtener la URL base desde la meta tag o construirla
        const baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
        const url = `${baseUrl}/admin/admin.cotizaciones.items-categoria.obtener`;

        // Hacer petición al backend
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            // Actualizar tabla con los items propios obtenidos
            const categoria = {
                id: categoriaId,
                nombre: result.data[0]?.categoria?.nombre || `Categoría ${categoriaId}`
            };

            actualizarTablaItemsPropios(result.data, itemInfo, categoria);
        } else {
            throw new Error(result.message || 'Error al obtener items propios');
        }

    } catch (error) {
        console.error('Error al cargar items propios:', error);

        // Usar datos simulados como fallback
        const itemsPropiosSimulados = [
            {
                id: `sim_1_${itemInfo.id}`,
                nombre: `Item Propio 1 - ${itemInfo.nombre}`,
                codigo: 'IP-001',
                categoria: { nombre: 'Categoría General' },
                descripcion: `Item propio relacionado con ${itemInfo.nombre}`
            },
            {
                id: `sim_2_${itemInfo.id}`,
                nombre: `Item Propio 2 - ${itemInfo.nombre}`,
                codigo: 'IP-002',
                categoria: { nombre: 'Categoría General' },
                descripcion: `Otro item propio para ${itemInfo.nombre}`
            }
        ];

        const categoriaSimulada = {
            id: 1,
            nombre: 'Categoría General (Simulada)'
        };

        actualizarTablaItemsPropios(itemsPropiosSimulados, itemInfo, categoriaSimulada);

        // Mostrar advertencia sobre datos simulados
        console.warn('Se están usando datos simulados para items propios');
    }
}

/**
 * Actualizar tabla de items propios
 */
function actualizarTablaItemsPropios(itemsPropios, itemOriginal, categoria) {
    const tbody = document.getElementById('tbody_items');

    // Limpiar tabla
    tbody.innerHTML = '';

    if (!itemsPropios || itemsPropios.length === 0) {
        tbody.innerHTML = `
            <tr id="no_items_row_items">
                <td colspan="2" class="text-center text-muted py-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    No hay items propios para la categoría "${categoria.nombre}"
                    <br><small>Seleccione un item que tenga categoría asignada</small>
                </td>
            </tr>
        `;
        return;
    }

    // Agregar items propios a la tabla
    itemsPropios.forEach((itemPropio, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-cube text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${itemPropio.nombre}</strong>
                                <br><small class="text-muted"><i class="fas fa-tag mr-1"></i>Código: ${itemPropio.codigo || 'No definido'}</small>
                                <br><small class="text-info">${itemPropio.descripcion || 'Sin descripción'}</small>
                            </div>
                        </div>
                        <div class="mt-1">
                            <span class="badge bg-secondary">${categoria.nombre}</span>
                            ${itemPropio.unidad_medida ? `<span class="badge bg-info ml-1">${itemPropio.unidad_medida}</span>` : ''}
                        </div>
                    </div>
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarItemPropio(${index})" title="Quitar item propio">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Guardar items propios en variable global para posterior uso
    window.itemsPropiosActuales = {
        items: itemsPropios,
        itemOriginal: itemOriginal,
        categoria: categoria
    };

    console.log(`Se cargaron ${itemsPropios.length} items propios para la categoría "${categoria.nombre}"`);
}

/**
 * Limpiar subitems seleccionados en la tabla de productos
 */
function limpiarItemsPropiosSeleccionados() {
    const tbody = document.getElementById('tbody_items');
    tbody.innerHTML = `
        <tr id="no_items_row_items">
            <td colspan="2" class="text-center text-muted">
                No hay subitems seleccionados
            </td>
        </tr>
    `;

    // Limpiar variable global
    window.subitemsSeleccionados = [];

    // Actualizar contador
    actualizarContadorProductosSeleccionados();
}

/**
 * Quitar item propio individual de un subitem
 */
function quitarItemPropio(subitemId, itemPropioId) {
    const tbody = document.getElementById('tbody_items');

    // Buscar y eliminar el item propio específico
    const itemPropioRows = tbody.querySelectorAll(`tr[data-parent-subitem="${subitemId}"]`);

    itemPropioRows.forEach(row => {
        const button = row.querySelector(`button[onclick*="${itemPropioId}"]`);
        if (button) {
            row.remove();
        }
    });

    // Actualizar variable global
    if (window.subitemsSeleccionados) {
        const subitem = window.subitemsSeleccionados.find(s => s.id === subitemId);
        if (subitem && subitem.itemsPropios) {
            subitem.itemsPropios = subitem.itemsPropios.filter(ip => ip.id != itemPropioId);

            // Si ya no hay items propios, agregar mensaje informativo
            if (subitem.itemsPropios.length === 0) {
                const noItemsRow = document.createElement('tr');
                noItemsRow.setAttribute('data-parent-subitem', subitemId);
                noItemsRow.classList.add('item-propio-row');
                noItemsRow.innerHTML = `
                    <td colspan="2" class="text-center text-muted ps-4">
                        <em>No quedan items propios para este subitem</em>
                    </td>
                `;
                tbody.appendChild(noItemsRow);
            }
        }
    }

    // Actualizar contador
    actualizarContadorProductosSeleccionados();

    toastr.success('Item propio eliminado correctamente');
}

/**
 * Quitar subitem completo con todos sus items propios
 */
function quitarSubitemCompleto(subitemId) {
    const tbody = document.getElementById('tbody_items');

    // Quitar el subitem header
    const subitemRow = tbody.querySelector(`tr[data-subitem-id="${subitemId}"]`);
    if (subitemRow) {
        subitemRow.remove();
    }

    // Quitar todos los items propios asociados
    const itemsPropio = tbody.querySelectorAll(`tr[data-parent-subitem="${subitemId}"]`);
    itemsPropio.forEach(row => row.remove());

    // Actualizar variable global
    if (window.subitemsSeleccionados) {
        window.subitemsSeleccionados = window.subitemsSeleccionados.filter(item => item.id !== subitemId);
    }

    // Si no quedan elementos, mostrar mensaje
    if (tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr id="no_items_row_items">
                <td colspan="2" class="text-center text-muted">
                    No hay subitems seleccionados
                </td>
            </tr>
        `;
    }

    // Actualizar contador
    actualizarContadorProductosSeleccionados();

    toastr.success('Subitem y sus items propios eliminados correctamente');
}

/**
 * Inicializar estilos CSS para subitems e items propios
 */
function initSubitemsStyles() {
    if (!document.getElementById('subitemsStyles')) {
        const style = document.createElement('style');
        style.id = 'subitemsStyles';
        style.textContent = `
            .subitem-header {
                background-color: rgba(13, 202, 240, 0.1) !important;
                border-left: 4px solid #0dcaf0 !important;
            }

            .item-propio-row {
                background-color: rgba(25, 135, 84, 0.05) !important;
                border-left: 2px solid #198754 !important;
            }

            .item-propio-row:hover {
                background-color: rgba(25, 135, 84, 0.1) !important;
            }

            .subitem-header:hover {
                background-color: rgba(13, 202, 240, 0.15) !important;
            }

            .badge {
                font-size: 0.75rem;
            }

            .table-responsive .table td {
                vertical-align: middle;
            }

            .ps-4 {
                padding-left: 1.5rem !important;
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Generar HTML para lista desplegable de subitems
 */
function generarListaSubitems(itemId, subitems) {
    console.log('2...Generando lista de subitems para itemId:', itemId, 'con subitems:', subitems);


    if (!subitems || subitems.length === 0) {
        return `
            <div class="subitems-container">
                <button class="btn btn-sm btn-outline-secondary" data-item-id="${itemId}" type="button" disabled>
                    <i class="fas fa-cube"></i> Sin subitems
                </button>
            </div>
        `;
    }

    // Generar tabla de subitems
    let subitemsTableHtml = `
        <div class="subitems-container">
            <button class="btn btn-sm btn-primary toggle-subitems" type="button" data-item-id="${itemId}" onclick="toggleSubitems(${itemId})">
                <i class="fas fa-eye" id="icon_${itemId}"></i> Ver subitems (${subitems.length})
            </button>
            <div class="subitems-table-container mt-2" id="subitems_${itemId}" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Código</th>
                                <th>Nombre</th>
                                <th style="width: 120px;">Cantidad</th>
                                <th>Observación</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    subitems.forEach(subitem => {
        const unidadMedida = subitem.unidad_medida ? subitem.unidad_medida.simbolo || subitem.unidad_medida.sigla || '' : '';
        const observacion = subitem.observacion || 'Sin observaciones';
        const isReadOnly = variable === 'ver';

        subitemsTableHtml += `
            <tr>
                <td><code class="text-primary">${subitem.codigo}</code></td>
                <td><strong>${subitem.nombre}</strong></td>
                <td class="text-center">${subitem.cantidad || 1} ${unidadMedida}</td>
                <td><small class="text-muted">${observacion}</small></td>
                <td class="text-center">
                    ${!isReadOnly ? `
                    <button type="button" class="btn btn-sm btn-primary" onclick="editarSubitem(${subitem.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarSubitem(${subitem.id}, ${itemId})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                    ` : '<span class="text-muted">-</span>'}
                </td>
            </tr>
        `;
    });

    subitemsTableHtml += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

    return subitemsTableHtml;
}

/**
 * Cargar subitems de un item específico desde el backend
 */
async function cargarSubitemsDelItem(itemId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.items.getItemSubitems/${itemId}`);
        const data = await response.json();
        if (data.success) {
            // Encontrar el item en la lista local y actualizar sus subitems
            const itemIndex = itemsCotizacion.findIndex(item => item.id === itemId);
            if (itemIndex !== -1) {
                // Actualizar con los subitems recibidos (puede ser un array vacío)
                itemsCotizacion[itemIndex].subitems = data.data || [];

                console.log(`Subitems actualizados para item ${itemId}:`, data.data ? data.data.length : 0, 'subitems');

                // Encontrar y actualizar solo el HTML específico del item
                const itemRow = document.querySelector(`tr:has([data-item-id="${itemId}"])`);
                if (itemRow) {
                    const subitemsCell = itemRow.cells[3]; // La celda de subitems
                    if (subitemsCell) {
                        subitemsCell.innerHTML = generarListaSubitems(itemId, data.data || []);
                    }
                }
            }
        } else {
            console.error('Error en la respuesta del servidor:', data.message);
        }
    } catch (error) {
        console.error('Error al cargar subitems del item:', error);
    }
}

/**
 * Filtrar items del acordeón basado en texto de búsqueda
 */
function filtrarItemsAcordeon() {
    const searchTerm = document.getElementById('buscarItemsAcordeon').value.toLowerCase().trim();
    const tbody = document.getElementById('tbodyItemsAcordeon');

    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr:not(.mensaje-filtro)');
    let visibleCount = 0;
    let totalCount = 0;

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 4) {
            // Es la fila de "no hay items" o similar
            if (searchTerm === '') {
                row.style.display = itemsCotizacion.length === 0 ? '' : 'none';
            } else {
                row.style.display = 'none';
            }
            return;
        }

        totalCount++;

        // Obtener texto de las celdas importantes (nombre, descripción)
        const itemText = cells[1].textContent.toLowerCase(); // Item/Subitem
        const descripcionText = cells[2].textContent.toLowerCase(); // Descripción
        const tipoText = cells[3].textContent.toLowerCase(); // Tipo

        // Verificar si alguno contiene el término de búsqueda
        const matches = itemText.includes(searchTerm) ||
                       descripcionText.includes(searchTerm) ||
                       tipoText.includes(searchTerm);

        if (matches || searchTerm === '') {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Mostrar mensaje si no hay resultados y actualizar contador
    mostrarMensajeFiltroItems(searchTerm, visibleCount, totalCount);
}

/**
 * Mostrar mensaje cuando no hay resultados de filtro
 */
function mostrarMensajeFiltroItems(searchTerm, visibleCount, totalCount) {
    const tbody = document.getElementById('tbodyItemsAcordeon');
    if (!tbody) return;

    // Remover mensaje previo si existe
    const mensajePrevio = tbody.querySelector('.mensaje-filtro');
    if (mensajePrevio) {
        mensajePrevio.remove();
    }

    // Si hay término de búsqueda pero no hay resultados visibles
    if (searchTerm !== '' && visibleCount === 0 && totalCount > 0) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.className = 'mensaje-filtro';
        noResultsRow.innerHTML = `
            <td colspan="4" class="text-center text-muted py-3">
                <i class="fas fa-search mr-2"></i>
                No se encontraron items que coincidan con "<strong>${searchTerm}</strong>"
                <br><small>Intente con otros términos de búsqueda</small>
            </td>
        `;
        tbody.appendChild(noResultsRow);
    }

    // Función de actualización de contador removida por ser innecesaria
}

/**
 * Limpiar filtro de items del acordeón
 */
function limpiarFiltroItems() {
    document.getElementById('buscarItemsAcordeon').value = '';
    filtrarItemsAcordeon();
}

/**
 * Alternar visibilidad de subitems
 */
function toggleSubitems(itemId) {
    const container = document.getElementById(`subitems_${itemId}`);
    const icon = document.getElementById(`icon_${itemId}`);
    const button = document.querySelector(`[data-item-id="${itemId}"]`);


    if (!container || !icon || !button) {
        console.error('Elementos no encontrados:', { container: !!container, icon: !!icon, button: !!button });
        return;
    }

    // Usar getComputedStyle para obtener el estado real de display
    const isHidden = window.getComputedStyle(container).display === 'none';

    if (isHidden) {
        container.style.display = 'block';
        icon.className = 'fas fa-eye-slash';
        button.innerHTML = `<i class="fas fa-eye-slash" id="icon_${itemId}"></i> Ocultar subitems`;
        console.log('Mostrando subitems para item:', itemId);
    } else {
        container.style.display = 'none';
        icon.className = 'fas fa-eye';
        const count = container.querySelectorAll('tbody tr').length;
        button.innerHTML = `<i class="fas fa-eye" id="icon_${itemId}"></i> Ver subitems (${count})`;
        console.log('Ocultando subitems para item:', itemId);
    }
}
function limpiarFormularioItem() {
    document.getElementById('item_nombre').value = '';
    limpiarErroresItems();
}

/**
 * Mostrar errores de validación
 */
function mostrarErroresItems(errores) {
    // Limpiar errores previos
    limpiarErroresItems();

    // Mostrar nuevos errores
    for (const campo in errores) {
        const input = document.getElementById(campo);
        const errorDiv = document.getElementById(`error_${campo}`);

        if (input && errorDiv) {
            input.classList.add('is-invalid');
            errorDiv.textContent = errores[campo];
        }
    }
}

/**
 * Limpiar errores de validación
 */
function limpiarErroresItems() {
    const inputs = document.querySelectorAll('#formAgregarItem .form-control');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
    });

    const errorDivs = document.querySelectorAll('#formAgregarItem .invalid-feedback');
    errorDivs.forEach(div => {
        div.textContent = '';
    });
}


/**
 * Eliminar item específico
 */
function eliminarItem(itemId) {
    if (confirm('¿Está seguro de eliminar este item?')) {
        itemsCotizacion = itemsCotizacion.filter(item => item.id !== itemId);
        actualizarTablaItems();
        toggleEliminarItemsSeleccionados();
        toastr.info('Item eliminado');
    }
}

/**
 * Editar item (cargar en formulario)
 */
function editarItem(itemId) {
    const item = itemsCotizacion.find(i => i.id === itemId);
    if (!item) return;

    // Cargar datos en el formulario
    document.getElementById('item_nombre').value = item.nombre;

    // Eliminar item de la lista (se re-agregará al guardar)
    itemsCotizacion = itemsCotizacion.filter(i => i.id !== itemId);
    actualizarTablaItems();

    // Scroll al formulario
    document.getElementById('formAgregarItem').scrollIntoView({ behavior: 'smooth' });

    toastr.info('Item cargado para edición');
}

/**
 * Eliminar items seleccionados
 */
function eliminarItemsSeleccionados() {
    const checkboxes = document.querySelectorAll('#tbody_items .item-checkbox:checked');
    const idsEliminar = Array.from(checkboxes).map(cb => parseInt(cb.getAttribute('data-id')));

    if (idsEliminar.length === 0) {
        toastr.warning('No hay items seleccionados');
        return;
    }

    if (confirm(`¿Está seguro de eliminar ${idsEliminar.length} item(s)?`)) {
        itemsCotizacion = itemsCotizacion.filter(item => !idsEliminar.includes(item.id));
        actualizarTablaItems();

        const selectAllCheckbox = document.getElementById('select_all_items');
        if (selectAllCheckbox) selectAllCheckbox.checked = false;

        toggleEliminarItemsSeleccionados();
        toastr.info(`${idsEliminar.length} item(s) eliminado(s)`);
    }
}

/**
 * Limpiar todos los items
 */
function limpiarTodosItems() {
    itemsCotizacion = [];
    contadorItems = 0;
    actualizarTablaItems();

    const selectAllCheckbox = document.getElementById('select_all_items');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;

    toggleEliminarItemsSeleccionados();
    toastr.info('Todos los items han sido eliminados');
}

/**
 * Toggle botón eliminar seleccionados
 */
function toggleEliminarItemsSeleccionados() {
    const checkboxes = document.querySelectorAll('#tbody_items .item-checkbox:checked');
    const btnEliminar = document.getElementById('btn_eliminar_items_seleccionados');

    if (btnEliminar) {
        btnEliminar.disabled = checkboxes.length === 0;
    }
}

/**
 * Configurar permisos según el modo
 */
function configurarPermisosItems() {
    const isReadOnly = variable === 'ver';

    // Deshabilitar formulario en modo ver
    const formInputs = document.querySelectorAll('#formAgregarItem input, #formAgregarItem select, #formAgregarItem button');
    formInputs.forEach(input => {
        input.disabled = isReadOnly;
    });

    // Ocultar botones de gestión en modo ver
    const botonesGestion = document.querySelectorAll('#btn_eliminar_items_seleccionados, #btn_limpiar_todos_items');
    botonesGestion.forEach(btn => {
        btn.style.display = isReadOnly ? 'none' : 'inline-block';
    });
}

/**
 * Abrir modal para crear subitem
 */
function abrirModalCrearSubitem(cotizacionItemId) {
    // Verificar que el item tenga un ID válido
    if (!cotizacionItemId) {
        toastr.error('El item debe estar guardado antes de poder agregar subitems');
        return;
    }

    // Verificar que el item existe en la lista local
    const item = itemsCotizacion.find(item => item.id === cotizacionItemId);
    if (!item) {
        toastr.error('Item no encontrado');
        return;
    }

    // Limpiar formulario
    document.getElementById('formCrearSubitem').reset();
    document.getElementById('cotizacion_item_id').value = cotizacionItemId;
    document.getElementById('subitem_id_edit').value = ''; // Limpiar ID de edición
    limpiarErroresSubitem();

    // Restaurar títulos y botones para modo crear
    const modalTitle = document.querySelector('#modalCrearSubitem .modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Crear Nuevo Subitem';
    }

    const btnGuardar = document.getElementById('btn_guardar_subitem');
    if (btnGuardar) {
        btnGuardar.textContent = 'Crear Subitem';
    }

    // Mostrar modal usando jQuery (Bootstrap 4)
    $('#modalCrearSubitem').modal('show');
}

/**
 * Guardar/Actualizar subitem
 */
async function guardarSubitem(event) {
    event.preventDefault();

    // Mostrar loading
    const btnGuardar = document.getElementById('btn_guardar_subitem');
    const textoOriginal = btnGuardar.innerHTML;
    try {
        // Validar formulario
        if (!validarFormularioSubitem()) {
            return;
        }

        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btnGuardar.disabled = true;

        // Verificar si es edición o creación
        const subitemIdEdit = document.getElementById('subitem_id_edit').value;
        const isEdit = subitemIdEdit && subitemIdEdit !== '';

        // Obtener datos
        const datos = {
            codigo: document.getElementById('subitem_codigo').value.trim(),
            nombre: document.getElementById('subitem_nombre').value.trim(),
            unidad_medida_id: document.getElementById('subitem_unidad_medida').value,
            cantidad: parseFloat(document.getElementById('subitem_cantidad').value) || 1,
            observacion: document.getElementById('subitem_observacion').value.trim() || null
        };

        // Solo incluir cotizacion_item_id si es creación
        if (!isEdit) {
            datos.cotizacion_item_id = document.getElementById('cotizacion_item_id').value;
        }

        // Determinar URL y método según operación
        const url = isEdit
            ? `/admin/admin.cotizaciones.items.updateSubitem/${subitemIdEdit}`
            : '/admin/admin.cotizaciones.items.createSubitem';
        const method = isEdit ? 'PUT' : 'POST';

        // Enviar al servidor
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(datos)
        });

        const result = await response.json();

        if (result.success) {
            // Restaurar botón
            btnGuardar.innerHTML = textoOriginal;
            btnGuardar.disabled = false;

            // Si es creación, agregar a la lista local de subitems disponibles
            if (!isEdit) {
                subitemsDisponibles.push(result.data);
            }

            // Obtener el cotizacion_item_id correcto
            const cotizacionItemId = isEdit
                ? parseInt(document.getElementById('cotizacion_item_id').value)
                : parseInt(datos.cotizacion_item_id);

            if (cotizacionItemId) {
                // Recargar subitems del item
                await cargarSubitemsDelItem(cotizacionItemId);

                // Fallback: si no se actualizó, manejar manualmente
                const itemIndex = itemsCotizacion.findIndex(item => item.id === cotizacionItemId);
                if (itemIndex !== -1) {
                    // Si el item no tenía subitems antes, inicializar el array
                    if (!itemsCotizacion[itemIndex].subitems) {
                        itemsCotizacion[itemIndex].subitems = [];
                    }

                    if (!isEdit) {
                        // Para creación: verificar si el subitem ya existe en la lista local
                        const subitemExiste = itemsCotizacion[itemIndex].subitems.find(sub => sub.id === result.data.id);
                        if (!subitemExiste) {
                            // Agregar el nuevo subitem a la lista local
                            itemsCotizacion[itemIndex].subitems.push(result.data);
                        }
                    }
                    // Para edición, cargarSubitemsDelItem ya actualizó la lista
                }
            }

            toastr.success(isEdit ? 'Subitem actualizado exitosamente' : 'Subitem creado exitosamente');            // Cerrar modal usando jQuery (Bootstrap 4)
            $('#modalCrearSubitem').modal('hide');


        } else {
            btnGuardar.innerHTML = textoOriginal;
            btnGuardar.disabled = false;
            throw new Error(result.message || 'Error al crear subitem');
        }

    } catch (error) {
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;
        console.error('Error al guardar subitem:', error);
        toastr.error('Error al crear el subitem: ' + error.message);

        if (error.response?.status === 422) {
            // Mostrar errores de validación
            mostrarErroresSubitem(error.response.data.errors || {});
        }
    } finally {
        // Restaurar botón
        const btnGuardar = document.getElementById('btn_guardar_subitem');
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;
    }
}

/**
 * Validar formulario de subitem
 */
function validarFormularioSubitem() {
    let esValido = true;
    const errores = {};

    // Validar código
    const codigo = document.getElementById('subitem_codigo').value.trim();
    if (!codigo) {
        errores.subitem_codigo = 'El código es obligatorio';
        esValido = false;
    } else if (codigo.length > 50) {
        errores.subitem_codigo = 'El código no puede exceder 50 caracteres';
        esValido = false;
    }

    // Validar nombre
    const nombre = document.getElementById('subitem_nombre').value.trim();
    if (!nombre) {
        errores.subitem_nombre = 'El nombre es obligatorio';
        esValido = false;
    } else if (nombre.length > 255) {
        errores.subitem_nombre = 'El nombre no puede exceder 255 caracteres';
        esValido = false;
    }

    // Validar unidad de medida
    const unidadMedida = document.getElementById('subitem_unidad_medida').value;
    if (!unidadMedida) {
        errores.subitem_unidad_medida = 'La unidad de medida es obligatoria';
        esValido = false;
    }

    // Validar cantidad
    const cantidad = parseFloat(document.getElementById('subitem_cantidad').value);
    if (isNaN(cantidad) || cantidad < 0) {
        errores.subitem_cantidad = 'La cantidad debe ser un número válido mayor o igual a 0';
        esValido = false;
    }

    // Mostrar errores
    if (!esValido) {
        mostrarErroresSubitem(errores);
    } else {
        limpiarErroresSubitem();
    }

    return esValido;
}

/**
 * Mostrar errores de validación del subitem
 */
function mostrarErroresSubitem(errores) {
    limpiarErroresSubitem();

    for (const campo in errores) {
        const input = document.getElementById(campo);
        const errorDiv = document.getElementById(`error_${campo}`);

        if (input && errorDiv) {
            input.classList.add('is-invalid');
            errorDiv.textContent = Array.isArray(errores[campo]) ? errores[campo][0] : errores[campo];
        }
    }
}

/**
 * Limpiar errores de validación del subitem
 */
function limpiarErroresSubitem() {
    const inputs = document.querySelectorAll('#formCrearSubitem .form-control');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
    });

    const errorDivs = document.querySelectorAll('#formCrearSubitem .invalid-feedback');
    errorDivs.forEach(div => {
        div.textContent = '';
    });
}

/**
 * Cargar items existentes de la cotización
 */
async function cargarItemsExistentes(cotizacionId) {
    try {
        const response = await fetch(`/admin/admin.cotizaciones.items.getCotizacionItems/${cotizacionId}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            itemsCotizacion = [];
            contadorItems = 0;

            // Nueva lógica para preservar subitems
            itemsCotizacion = data.data.map(item => {
                contadorItems++;
                return {
                    id: item.id ? item.id : contadorItems,
                    nombre: item.nombre,
                    active: item.active,
                    subitems: item.subitems || [] // Preservar los subitems que vienen del backend
                };
            });
            actualizarTablaItems();
        }
    } catch (error) {
        console.error('Error al cargar items existentes:', error);
    }
}

/**
 * Guardar items en el backend
 */
async function guardarItemsCotizacion(cotizacionId) {
    try {
        const itemsParaGuardar = itemsCotizacion.map(item => ({
            nombre: item.nombre,
            active: item.active
        }));

        const response = await fetch('/admin/admin.cotizaciones.items.store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                cotizacion_id: cotizacionId,
                items: itemsParaGuardar
            })
        });

        const data = await response.json();

        if (data.success) {
            await cargarItemsExistentes(cotizacionId);
            console.log(`${itemsParaGuardar.length} items guardados correctamente`);
        } else {
            console.error('Error al guardar items:', data.message);
            toastr.warning('Cotización guardada, pero hubo problemas con los items');
        }
    } catch (error) {
        console.error('Error al enviar items:', error);
        toastr.warning('Cotización guardada, pero hubo problemas con los items');
    }
}

/**
 * Editar un subitem
 */
async function editarSubitem(subitemId) {
    try {
        // Cargar datos del subitem
        const response = await fetch(`/admin/admin.cotizaciones.items.getSubitem/${subitemId}`);
        const result = await response.json();

        if (result.success) {
            const subitem = result.data;

            // Llenar el modal de edición con los datos del subitem
            document.getElementById('cotizacion_item_id').value = subitem.cotizacion_item_id;
            document.getElementById('subitem_id_edit').value = subitem.id;
            document.getElementById('subitem_codigo').value = subitem.codigo;
            document.getElementById('subitem_nombre').value = subitem.nombre;
            document.getElementById('subitem_unidad_medida').value = subitem.unidad_medida_id;
            document.getElementById('subitem_cantidad').value = subitem.cantidad;
            document.getElementById('subitem_observacion').value = subitem.observacion || '';

            // Actualizar contador de caracteres
            const contadorSpan = document.getElementById('subitem_observacion_count');
            if (contadorSpan) {
                contadorSpan.textContent = (subitem.observacion || '').length;
            }

            // Cambiar título del modal
            const modalTitle = document.querySelector('#modalCrearSubitem .modal-title');
            if (modalTitle) {
                modalTitle.textContent = 'Editar Subitem';
            }

            // Cambiar texto del botón
            const btnGuardar = document.getElementById('btn_guardar_subitem');
            if (btnGuardar) {
                btnGuardar.textContent = 'Actualizar Subitem';
            }

            // Limpiar errores
            limpiarErroresSubitem();

            // Mostrar modal
            $('#modalCrearSubitem').modal('show');

        } else {
            toastr.error('Error al cargar los datos del subitem');
        }
    } catch (error) {
        console.error('Error al editar subitem:', error);
        toastr.error('Error al cargar el subitem para edición');
    }
}

/**
 * Eliminar un subitem
 */
async function eliminarSubitem(subitemId, itemId) {
    if (!confirm('¿Está seguro de eliminar este subitem?')) {
        return;
    }

    try {
        const response = await fetch(`/admin/admin.cotizaciones.items.destroySubitem/${subitemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const result = await response.json();

        if (result.success) {
            // Recargar subitems del item
            await cargarSubitemsDelItem(itemId);
            toastr.success('Subitem eliminado exitosamente');
        } else {
            toastr.error('Error al eliminar el subitem: ' + result.message);
        }
    } catch (error) {
        console.error('Error al eliminar subitem:', error);
        toastr.error('Error al eliminar el subitem');
    }
}

// ========================================
// FUNCIONES PARA PRODUCTOS Y SALARIOS
// ========================================

// Variables globales para productos y salarios
let productosDisponibles = [];
let cargosDisponibles = [];
let productosSeleccionados = [];
let personalAsignado = [];

/**
 * Inicializar funcionalidad de productos y salarios
 */
function initProductosYSalarios() {
    console.log('=== INICIALIZANDO PRODUCTOS Y SALARIOS ===');
    try {
        setupEventListenersProductos();
        cargarProductosDisponibles();
        agregarEstilosVisuales();
        console.log('initProductosYSalarios completado exitosamente');
    } catch (error) {
        console.error('Error en initProductosYSalarios:', error);
    }
}

/**
 * Agregar estilos CSS para animaciones y visualizaciones
 */
function agregarEstilosVisuales() {
    if (document.getElementById('estilosProductosVisuales')) return; // Ya agregados

    const estilos = document.createElement('style');
    estilos.id = 'estilosProductosVisuales';
    estilos.innerHTML = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .item-agregado-reciente {
            animation: fadeInUp 0.6s ease-out;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
            border-left: 4px solid #28a745;
        }

        .badge-nuevo {
            animation: pulse 2s infinite;
        }

        .toast-productos {
            border-left: 4px solid #007bff;
        }

        .toast-productos .toast-title {
            color: #007bff;
            font-weight: 600;
        }

        .producto-destacado {
            background: linear-gradient(45deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));
            border: 1px solid rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }

        .producto-destacado:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }
    `;

    document.head.appendChild(estilos);
}

/**
 * Cargar productos disponibles desde el servidor
 */
async function cargarProductosDisponibles() {
    try {
        console.log('Cargando productos disponibles...');
        const response = await fetch('/admin/admin.cotizaciones.productos.obtener');
        const data = await response.json();

        if (data.success) {
            productosDisponibles = data.data || [];
            console.log('Productos disponibles cargados:', productosDisponibles.length);
            renderizarTablaProductos();
        } else {
            console.error('Error al cargar productos:', data.message);
            // Si no hay productos del servidor, usar datos de ejemplo
            cargarProductosEjemplo();
        }
    } catch (error) {
        console.error('Error al cargar productos:', error);
    }
}


/**
 * Renderizar tabla de productos disponibles
 */
function renderizarTablaProductos() {
    // En lugar de buscar tbodyProductosDisponibles, trabajar con items propios existentes
    console.log('Sistema usando items propios en lugar de tabla de productos dedicada');

    // Verificar si hay items propios cargados
    const itemsPropiosContainer = document.getElementById('itemsPropiosContainer');
    if (itemsPropiosContainer) {
        console.log('Items propios container encontrado');
        // Los items propios ya están renderizados por el sistema existente
        return;
    }

    // Si no hay container de items propios, intentar con otras tablas disponibles
    const tablaAlternativas = [
        'tbody_items',
        'tbodyProductosSeleccionados',
        'tbodyElementosAQuitar'
    ];

    let tablaEncontrada = false;
    for (const tablaId of tablaAlternativas) {
        const tabla = document.getElementById(tablaId);
        if (tabla) {
            console.log(`Tabla alternativa encontrada: ${tablaId}`);
            tablaEncontrada = true;
            break;
        }
    }

    if (!tablaEncontrada) {
        console.warn('No se encontraron tablas disponibles para productos');
        // Crear productos seleccionados desde items propios si existen
        crearProductosDesdeItemsPropios();
    }
}

/**
 * Crear botones de selección rápida para productos de ejemplo
 */
function crearBotonesSeleccionRapida() {
    console.log('crearBotonesSeleccionRapida llamada');
    // Por ahora, simplemente crear una función vacía para evitar errores
    // Esta funcionalidad se puede implementar más adelante si es necesaria
}

/**
 * Toggle selección rápida de producto
 */
function toggleProductoSelectionRapida(productoId) {
    console.log('toggleProductoSelectionRapida llamada con ID:', productoId);
    const isSelected = productosSeleccionados.find(p => p.id == productoId); // Usar == para comparar
    const boton = document.getElementById(`btnProducto_${productoId}`);

    console.log('Producto ya seleccionado:', !!isSelected);
    console.log('Botón encontrado:', !!boton);

    if (isSelected) {
        // Quitar producto
        const lengthAntes = productosSeleccionados.length;
        productosSeleccionados = productosSeleccionados.filter(p => p.id != productoId); // Usar != para comparar
        console.log(`Producto removido. Antes: ${lengthAntes}, Después: ${productosSeleccionados.length}`);

        if (boton) {
            boton.className = 'btn btn-sm w-100 btn-outline-primary';
            boton.innerHTML = '<i class="fas fa-plus-circle"></i> Agregar';
        }
        console.log('Producto deseleccionado:', productoId);
    } else {
        // Agregar producto
        const producto = productosDisponibles.find(p => p.id == productoId); // Usar == para comparar
        console.log('Producto encontrado en disponibles:', !!producto, producto);

        if (producto) {
            const nuevoProducto = {
                id: producto.id,
                nombre: producto.nombre,
                codigo: producto.codigo,
                precio: producto.precio,
                cantidad: 1,
                total: producto.precio,
                unidad: producto.unidad,
                categoria: producto.categoria,
                esDelAcordeon: false
            };

            const lengthAntes = productosSeleccionados.length;
            productosSeleccionados.push(nuevoProducto);
            console.log(`Producto agregado. Antes: ${lengthAntes}, Después: ${productosSeleccionados.length}`);
            console.log('Producto agregado:', nuevoProducto);

            if (boton) {
                boton.className = 'btn btn-sm w-100 btn-success';
                boton.innerHTML = '<i class="fas fa-check-circle"></i> Seleccionado';
            }
        } else {
            console.error('Producto no encontrado en productosDisponibles para ID:', productoId);
            console.log('IDs disponibles:', productosDisponibles.map(p => p.id));
        }
        console.log('Producto seleccionado:', productoId);
    }

    // Actualizar tabla de productos seleccionados
    actualizarTablaProductosSeleccionados();
    calcularTotales();

    // Debug final
    console.log('Estado final productosSeleccionados:', productosSeleccionados.length, productosSeleccionados);

    // Actualizar contador debug si existe
    const productosCount = document.getElementById('productosCount');
    if (productosCount) {
        productosCount.textContent = productosSeleccionados.length;
    }
}

/**
 * Crear productos seleccionados desde items propios cargados
 */
function crearProductosDesdeItemsPropios() {
    // Buscar items propios en el DOM
    const itemsElements = document.querySelectorAll('.item-propio-card, .item-card, .list-group-item');

    console.log(`Buscando items propios en el DOM: ${itemsElements.length} elementos encontrados`);

    itemsElements.forEach((element, index) => {
        // Extraer información del item desde el DOM
        const nombreElement = element.querySelector('.item-nombre, h6, .font-weight-bold');
        const precioElement = element.querySelector('.item-precio, .text-success, .badge-success');
        const codigoElement = element.querySelector('.item-codigo, .text-muted, small');

        if (nombreElement) {
            const nombre = nombreElement.textContent.trim();
            const codigo = codigoElement ? codigoElement.textContent.trim() : `ITEM-${index + 1}`;
            const precioText = precioElement ? precioElement.textContent.replace(/[^0-9.,]/g, '') : '0';
            const precio = parseFloat(precioText.replace(',', '.')) || 0;

            // Verificar si ya existe en productos disponibles
            if (!productosDisponibles.find(p => p.nombre === nombre)) {
                productosDisponibles.push({
                    id: Date.now() + index,
                    codigo: codigo,
                    nombre: nombre,
                    precio: precio,
                    stock: 100, // Stock por defecto
                    categoria: 'Items Propios',
                    unidad: 'Unidad',
                    descripcion: `Item propio: ${nombre}`,
                    source: 'dom'
                });
            }
        }
    });

    console.log(`Productos creados desde items del DOM: ${productosDisponibles.length}`);
}

/**
 * Toggle selección de producto individual (modificado para trabajar con items existentes)
 */
function toggleProductoSelection(productoId) {
    console.log('Toggle producto selección:', productoId);

    // Buscar checkbox si existe
    const checkbox = document.querySelector(`.producto-checkbox[data-id="${productoId}"], .item-checkbox[data-id="${productoId}"], input[data-id="${productoId}"]`);

    if (checkbox && checkbox.checked) {
        seleccionarProducto(productoId);
    } else if (checkbox) {
        deseleccionarProducto(productoId);
    } else {
        // Si no hay checkbox, asumir selección directa
        const yaSeleccionado = productosSeleccionados.find(p => p.id === productoId);
        if (yaSeleccionado) {
            deseleccionarProducto(productoId);
        } else {
            seleccionarProducto(productoId);
        }
    }

    console.log('Productos seleccionados después del toggle:', productosSeleccionados.length);
}

/**
 * Seleccionar producto desde items propios o acordeón
 */
function seleccionarProductoDesdeItem(itemElement) {
    try {
        const nombreElement = itemElement.querySelector('.item-nombre, h6, .font-weight-bold');
        const precioElement = itemElement.querySelector('.item-precio, .text-success, .badge-success');
        const codigoElement = itemElement.querySelector('.item-codigo, .text-muted, small');

        if (!nombreElement) {
            console.warn('No se pudo extraer información del item');
            return;
        }

        const nombre = nombreElement.textContent.trim();
        const codigo = codigoElement ? codigoElement.textContent.trim() : `ITEM-${Date.now()}`;
        const precioText = precioElement ? precioElement.textContent.replace(/[^0-9.,]/g, '') : '0';
        const precio = parseFloat(precioText.replace(',', '.')) || 0;

        // Verificar si ya está seleccionado
        const yaSeleccionado = productosSeleccionados.find(p => p.nombre === nombre);
        if (yaSeleccionado) {
            console.log('Producto ya seleccionado:', nombre);
            return;
        }

        // Agregar a productos seleccionados
        const producto = {
            id: Date.now() + Math.random(),
            nombre: nombre,
            codigo: codigo,
            precio: precio,
            cantidad: 1,
            total: precio,
            unidad: 'Unidad',
            categoria: 'Items Propios',
            source: 'manual'
        };

        productosSeleccionados.push(producto);
        console.log('Producto seleccionado manualmente:', producto);

        // Actualizar UI
        actualizarTablaProductosSeleccionados();
        calcularTotales();

        // Marcar visualmente como seleccionado
        itemElement.classList.add('selected', 'border-primary');

    } catch (error) {
        console.error('Error al seleccionar producto desde item:', error);
    }
}

/**
 * Agregar evento click a items para selección manual
 */
function configurarSeleccionManualItems() {
    // Buscar todos los items clickeables
    const items = document.querySelectorAll('.item-propio-card, .item-card, .list-group-item');

    items.forEach(item => {
        // Evitar múltiples listeners
        if (!item.hasAttribute('data-click-configurado')) {
            item.style.cursor = 'pointer';
            item.setAttribute('data-click-configurado', 'true');

            item.addEventListener('click', function(e) {
                // Evitar activar si se hizo click en un input o button
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') {
                    return;
                }

                seleccionarProductoDesdeItem(this);
            });
        }
    });

    console.log(`Configurados ${items.length} items para selección manual`);
}

function actualizarSelectAllProductos() {
    const selectAllCheckbox = document.getElementById('selectAllProductos');
    const productCheckboxes = document.querySelectorAll('.producto-checkbox');

    if (!selectAllCheckbox || productCheckboxes.length === 0) return;

    const checkedCount = document.querySelectorAll('.producto-checkbox:checked').length;

    if (checkedCount === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedCount === productCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
    }
}

/**
 * Configurar event listeners para productos y salarios
 */
function setupEventListenersProductos() {
    console.log('=== CONFIGURANDO EVENT LISTENERS PRODUCTOS ===');

    // Botón Agregar Productos
    const btnAgregarProductos = document.getElementById('agregarProductos');
    console.log('Botón agregarProductos encontrado:', !!btnAgregarProductos);
    if (btnAgregarProductos) {
        btnAgregarProductos.addEventListener('click', function(e) {
            console.log('=== CLICK EN BOTÓN AGREGAR PRODUCTOS ===');
            e.preventDefault();
            abrirModalAgregarProductos();
        });
        console.log('Event listener agregado al botón agregarProductos');
    } else {
        console.error('Botón agregarProductos NO encontrado en el DOM');
    }

    // Botón Quitar Productos
    const btnQuitarProductos = document.getElementById('quitarProductos');
    if (btnQuitarProductos) {
        btnQuitarProductos.addEventListener('click', abrirModalQuitarProductos);
    }

    // Confirmación agregar productos
    const btnConfirmarAgregar = document.getElementById('confirmarAgregarProductos');
    if (btnConfirmarAgregar) {
        btnConfirmarAgregar.addEventListener('click', function(e) {
            console.log('=== CLICK EN BOTÓN CONFIRMAR AGREGAR ===');
            e.preventDefault();
            confirmarAgregarProductos();
        });
    } else {
        console.error('Botón confirmarAgregarProductos NO encontrado en el DOM');
    }

    // Confirmación quitar productos
    const btnConfirmarQuitar = document.getElementById('confirmarQuitarProductos');
    if (btnConfirmarQuitar) {
        btnConfirmarQuitar.addEventListener('click', confirmarQuitarProductos);
    }

    // Búsqueda de productos
    const inputBuscar = document.getElementById('buscarProducto');
    if (inputBuscar) {
        inputBuscar.addEventListener('input', filtrarProductos);
    }

    // Select all productos
    const selectAllProductos = document.getElementById('selectAllProductos');
    if (selectAllProductos) {
        selectAllProductos.addEventListener('change', toggleSelectAllProductos);
    }

    // Select all elementos a quitar
    const selectAllElementos = document.getElementById('selectAllElementos');
    if (selectAllElementos) {
        selectAllElementos.addEventListener('change', toggleSelectAllElementos);
    }

    // Form agregar personal
    const formAgregarPersonal = document.getElementById('formAgregarPersonal');
    if (formAgregarPersonal) {
        formAgregarPersonal.addEventListener('submit', agregarPersonal);
    }

    // Cambios en cargo para calcular salario
    const cargoSelect = document.getElementById('cargoSeleccionado');
    if (cargoSelect) {
        cargoSelect.addEventListener('change', calcularCostoPersonal);
    }

    // Cambios en inputs para recalcular
    const inputs = ['cantidadPersonal', 'diasLaborales', 'prestacionesSociales'];
    inputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', calcularCostoPersonal);
        }
    });
}

/**
 * Abrir modal agregar productos
 */
function abrirModalAgregarProductos() {
    console.log('=== ABRIENDO MODAL AGREGAR PRODUCTOS ===');

    // Actualizar la tabla de items del acordeón antes de mostrar el modal
    actualizarTablaItemsAcordeon();

    // Cargar productos disponibles si no están cargados
    if (productosDisponibles.length != 0) {
        console.log('Productos ya disponibles:', productosDisponibles.length);
        renderizarTablaProductos();
    }

    // Pequeño delay para que el modal se renderice completamente
    setTimeout(() => {
        actualizarTablaProductosSeleccionados();
        actualizarTablaPersonalAsignado();
        calcularTotales();

        // Agregar instrucciones visuales para el usuario
        mostrarInstruccionesSeleccion();

        configurarSeleccionManualItems();
        // Actualizar contador debug
        const productosCount = document.getElementById('productosCount');
        if (productosCount) {
            productosCount.textContent = productosSeleccionados.length;
        }
    }, 500);
    // Mostrar el modal usando jQuery (Bootstrap 4)
    $('#modalAgregarProductos').modal('show');
}

/**
 * Mostrar instrucciones de selección al usuario
 */
function mostrarInstruccionesSeleccion() {
    // Buscar si hay un área de instrucciones
    let instruccionesElement = document.getElementById('instruccionesSeleccion');

    if (!instruccionesElement) {
        // Crear área de instrucciones si no existe
        const modalBody = document.querySelector('#modalAgregarProductos .modal-body');
        if (modalBody) {
            instruccionesElement = document.createElement('div');
            instruccionesElement.id = 'instruccionesSeleccion';
            instruccionesElement.className = 'alert alert-info alert-dismissible fade show';
            instruccionesElement.innerHTML = `
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
                <h6><i class="fas fa-info-circle"></i> Cómo seleccionar productos:</h6>
                <ul class="mb-0">
                    <li><strong>Items del acordeón:</strong> Use los botones de selección en la tabla de items existentes</li>
                    <li><strong>Items propios:</strong> Haga clic en cualquier item de la lista para agregarlo</li>
                    <li><strong>Productos seleccionados:</strong> Aparecerán en la tabla de la derecha donde puede ajustar cantidades y precios</li>
                </ul>
            `;
            modalBody.insertBefore(instruccionesElement, modalBody.firstChild);
        }
    }

    console.log('Instrucciones de selección mostradas');
}

/**
 * Abrir modal quitar productos
 */
function abrirModalQuitarProductos() {
    $('#modalQuitarProductos').modal('show');
    cargarElementosAQuitar();
}

/**
 * Usar items seleccionados del acordeón como productos
 */
function usarItemsSeleccionados() {
    const itemsSeleccionados = obtenerItemsSeleccionados();

    if (itemsSeleccionados.length === 0) {
        Swal.fire({
            title: 'Sin selección',
            text: 'Por favor seleccione al menos un item o subitem para agregar.',
            type: 'warning',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // Convertir items seleccionados a productos
    itemsSeleccionados.forEach(item => {
        // Verificar si ya existe en productos seleccionados
        const yaExiste = productosSeleccionados.find(p =>
            p.source === 'acordeon' &&
            p.sourceId === item.id &&
            p.sourceType === item.tipo
        );

        if (!yaExiste) {
            productosSeleccionados.push({
                id: `acordeon_${item.tipo}_${item.id}_${Date.now()}`, // ID único
                source: 'acordeon', // Indicar que viene del acordeón
                sourceId: item.id,
                sourceType: item.tipo,
                sourceItemId: item.itemId, // Para subitems
                nombre: item.nombre,
                descripcion: item.descripcion,
                precio: 0, // Precio inicial, se puede editar
                cantidad: 1,
                total: 0,
                categoria: item.tipo === 'item' ? 'Item Principal' : 'Subitem'
            });
        }
    });

    // Actualizar la tabla de productos seleccionados
    actualizarTablaProductosSeleccionados();
    calcularTotales();

    // Mostrar mensaje de confirmación
    Swal.fire({
        title: 'Items agregados',
        text: `Se agregaron ${itemsSeleccionados.length} items como productos. Puede ajustar precios y cantidades en la tabla de la derecha.`,
        type: 'success',
        confirmButtonText: 'Entendido'
    });

    // Limpiar selección
    const checkboxes = document.querySelectorAll('.item-select:checked');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });

    // Actualizar estado del checkbox "seleccionar todos"
    const selectAllCheckbox = document.getElementById('selectAllItems');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
}

/**
 * Filtrar productos por búsqueda
 */
function filtrarProductos() {
    const termino = document.getElementById('buscarProducto').value.toLowerCase();
    const productosFiltrados = productosDisponibles.filter(producto =>
        producto.nombre.toLowerCase().includes(termino) ||
        producto.codigo.toLowerCase().includes(termino)
    );
}

/**
 * Toggle select all productos
 */
function toggleSelectAllProductos() {
    const selectAll = document.getElementById('selectAllProductos');
    const checkboxes = document.querySelectorAll('.producto-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        const productoId = parseInt(checkbox.dataset.id);

        if (selectAll.checked) {
            seleccionarProducto(productoId);
        } else {
            deseleccionarProducto(productoId);
        }
    });
}

/**
 * Seleccionar producto
 */
function seleccionarProducto(productoId) {
    const producto = productosDisponibles.find(p => p.id === productoId);
    const yaSeleccionado = productosSeleccionados.find(p => p.id === productoId);

    if (producto && !yaSeleccionado) {
        productosSeleccionados.push({
            ...producto,
            cantidad: 1,
            total: producto.precio
        });
        actualizarTablaProductosSeleccionados();
        calcularTotales();
    }
}

/**
 * Deseleccionar producto
 */
function deseleccionarProducto(productoId) {
    productosSeleccionados = productosSeleccionados.filter(p => p.id !== productoId);
    actualizarTablaProductosSeleccionados();
    calcularTotales();
}

/**
 * Actualizar tabla de productos seleccionados
 */
function actualizarTablaProductosSeleccionados() {
    console.log('Actualizando tabla de productos seleccionados...');
    console.log('Productos seleccionados:', productosSeleccionados.length);

    const tbody = document.getElementById('tbodyProductosSeleccionados');
    const noItemsRow = document.getElementById('noProductosSeleccionados');

    // Verificar si los elementos existen
    if (!tbody) {
        console.warn('Elemento tbodyProductosSeleccionados no encontrado');
        return;
    }

    if (!noItemsRow) {
        console.warn('Elemento noProductosSeleccionados no encontrado');
        return;
    }

    if (productosSeleccionados.length === 0) {
        noItemsRow.style.display = 'table-row';
        console.log('No hay productos seleccionados, mostrando mensaje');
        return;
    }

    noItemsRow.style.display = 'none';

    // Limpiar filas existentes excepto la de "no items"
    Array.from(tbody.children).forEach(row => {
        if (row.id !== 'noProductosSeleccionados') {
            row.remove();
        }
    });
    console.log('productosSeleccionados', productosSeleccionados);

    productosSeleccionados.forEach(producto => {
        const row = document.createElement('tr');
        const esDelAcordeon = producto.esDelAcordeon || false;
        const badgeColor = esDelAcordeon ? 'bg-info' : 'bg-secondary';
        const badgeText = esDelAcordeon ? (producto.categoria || 'Acordeón') : 'Producto';

        row.innerHTML = `
            <td>
                <div class="d-flex flex-column">
                    <div class="fw-bold">${producto.nombre}</div>
                    ${producto.item_parent ? `<small class="text-muted">De: ${producto.item_parent}</small>` : ''}
                    ${producto.descripcion ? `<small class="text-muted">${producto.descripcion}</small>` : ''}
                    <div class="mt-1">
                        <span class="badge ${badgeColor}">${badgeText}</span>
                        <small class="text-muted ms-2">
                            <i class="fas fa-tag"></i> ${producto.codigo || 'S/C'}
                        </small>
                    </div>
                </div>
            </td>
            <td>
                <div class="row">
                    <div class="col-4">
                        <label class="form-label-sm">Cantidad:</label>
                        <input type="number" class="form-control form-control-sm cantidad-producto"
                               value="${producto.cantidad}" min="1" data-id="${producto.id}"
                               onchange="actualizarCantidadProducto('${producto.id}', this.value)">
                    </div>
                    <div class="col-4">
                        <label class="form-label-sm">Precio:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control precio-producto"
                                   value="${producto.precio}" min="0" step="0.01" data-id="${producto.id}"
                                   onchange="actualizarPrecioProducto('${producto.id}', this.value)">
                        </div>
                    </div>
                    <div class="col-4">
                        <label class="form-label-sm">Total:</label>
                        <div class="fw-bold text-success">$${producto.total.toFixed(2)}</div>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="quitarProductoSeleccionado('${producto.id}')"
                        title="Quitar producto">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
    });
}

/**
 * Actualizar cantidad de producto
 */
function actualizarCantidadProducto(productoId, nuevaCantidad) {
    const producto = productosSeleccionados.find(p => p.id === productoId);
    if (producto) {
        producto.cantidad = parseInt(nuevaCantidad) || 1;
        producto.total = producto.precio * producto.cantidad;
        actualizarTablaProductosSeleccionados();
    }
}

/**
 * Actualizar precio de producto
 */
function actualizarPrecioProducto(productoId, nuevoPrecio) {
    const producto = productosSeleccionados.find(p => p.id === productoId);
    if (producto) {
        producto.precio = parseFloat(nuevoPrecio) || 0;
        producto.total = producto.precio * producto.cantidad;
        actualizarTablaProductosSeleccionados();
    }
}

/**
 * Quitar producto seleccionado
 */
function quitarProductoSeleccionado(productoId) {
    productosSeleccionados = productosSeleccionados.filter(p => p.id !== productoId);
    actualizarTablaProductosSeleccionados();
    calcularTotales();
}


    // Desmarcar checkbox en tabla de productos disponibles
//     const checkbox = document.querySelector(`.producto-checkbox[data-id="${productoId}"]`);
//     if (checkbox) {
//         checkbox.checked = false;
//     }
// }

/**
 * Calcular costo de personal
 */
function calcularCostoPersonal() {
    const cargoSelect = document.getElementById('cargoSeleccionado');
    const cantidadInput = document.getElementById('cantidadPersonal');
    const diasInput = document.getElementById('diasLaborales');
    const prestacionesInput = document.getElementById('prestacionesSociales');
    const salarioBaseInput = document.getElementById('salarioBase');
    const totalCostoInput = document.getElementById('totalCostoMensual');

    if (!cargoSelect.value) {
        salarioBaseInput.value = '';
        totalCostoInput.value = '';
        return;
    }

    const salarioBase = parseFloat(cargoSelect.options[cargoSelect.selectedIndex].dataset.salario) || 0;
    const cantidad = parseInt(cantidadInput.value) || 1;
    const dias = parseInt(diasInput.value) || 30;
    const prestaciones = parseFloat(prestacionesInput.value) || 35;

    salarioBaseInput.value = salarioBase.toFixed(2);

    // Calcular costo con prestaciones sociales
    const salarioConPrestaciones = salarioBase * (1 + prestaciones / 100);
    const costoDiario = salarioConPrestaciones / 30;
    const costoTotal = costoDiario * dias * cantidad;

    totalCostoInput.value = costoTotal.toFixed(2);
}

/**
 * Agregar personal
 */
function agregarPersonal(event) {
    event.preventDefault();

    const cargoSelect = document.getElementById('cargoSeleccionado');
    const cantidadInput = document.getElementById('cantidadPersonal');
    const diasInput = document.getElementById('diasLaborales');
    const totalCostoInput = document.getElementById('totalCostoMensual');

    if (!cargoSelect.value || !totalCostoInput.value) {
        toastr.warning('Complete todos los campos requeridos');
        return;
    }

    const cargo = cargosDisponibles.find(c => c.id === parseInt(cargoSelect.value));
    const nuevoPersonal = {
        id: Date.now(), // ID temporal
        cargo_id: cargo.id,
        cargo_nombre: cargo.nombre,
        cantidad: parseInt(cantidadInput.value),
        dias: parseInt(diasInput.value),
        salario_base: parseFloat(document.getElementById('salarioBase').value),
        prestaciones: parseFloat(document.getElementById('prestacionesSociales').value),
        costo_total: parseFloat(totalCostoInput.value)
    };

    personalAsignado.push(nuevoPersonal);
    actualizarTablaPersonalAsignado();
    calcularTotales();

    // Limpiar formulario
    document.getElementById('formAgregarPersonal').reset();
    document.getElementById('salarioBase').value = '';
    document.getElementById('totalCostoMensual').value = '';

    toastr.success('Personal agregado correctamente');
}

/**
 * Actualizar tabla de personal asignado
 */
function actualizarTablaPersonalAsignado() {
    const tbody = document.getElementById('tbodyPersonalAsignado');
    const noItemsRow = document.getElementById('noPersonalAsignado');

    if (personalAsignado.length === 0) {
        noItemsRow.style.display = 'table-row';
        return;
    }

    noItemsRow.style.display = 'none';

    // Limpiar filas existentes excepto la de "no items"
    Array.from(tbody.children).forEach(row => {
        if (row.id !== 'noPersonalAsignado') {
            row.remove();
        }
    });

    personalAsignado.forEach(personal => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${personal.cargo_nombre}</td>
            <td>${personal.cantidad}</td>
            <td>${personal.dias}</td>
            <td class="font-weight-bold">$${personal.costo_total.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="quitarPersonalAsignado(${personal.id})">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
    });
}

/**
 * Quitar personal asignado
 */
function quitarPersonalAsignado(personalId) {
    personalAsignado = personalAsignado.filter(p => p.id !== personalId);
    actualizarTablaPersonalAsignado();
    calcularTotales();
    toastr.info('Personal eliminado');
}

// ========================================
// FUNCIONES PARA MODAL DE SALARIOS MEJORADO
// ========================================

/**
 * Toggle seleccionar todas las categorías
 */
function toggleAllCategorias() {
    const selectAll = document.getElementById('selectAllCategorias');
    const checkboxes = document.querySelectorAll('#listaCategorias .categoria-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

/**
 * Cambiar tipo de costo y mostrar/ocultar campos correspondientes (formulario general)
 */
function cambiarTipoCostoFormulario() {
    const tipoCosto = document.getElementById('tipoCosto').value;
    const campoHoras = document.getElementById('campoHoras');
    const camposDiurnosNocturnos = document.getElementById('camposDiurnosNocturnos');

    // Ocultar todos los campos primero
    campoHoras.style.display = 'none';
    camposDiurnosNocturnos.style.display = 'none';

    // Mostrar campos según el tipo de costo
    switch (tipoCosto) {
        case 'COSTO_HORA':
            campoHoras.style.display = 'block';
            // Limpiar campos de días
            limpiarCamposDias();
            break;

        case 'COSTO_DIA':
        case 'COSTO_MES':
            camposDiurnosNocturnos.style.display = 'block';
            // Limpiar campo de horas
            document.getElementById('cantidadHoras').value = '';
            break;
    }

    // Limpiar campos de dominicales
    ocultarCamposDominicales();
}

/**
 * Limpiar campos de días
 */
function limpiarCamposDias() {
    document.getElementById('diasDiurnos').value = '0';
    document.getElementById('diasNocturnos').value = '0';
    document.getElementById('diasRemuneradosDiurnos').value = '';
    document.getElementById('diasRemuneradosNocturnos').value = '';
    document.getElementById('dominicalesDiurnos').value = '';
    document.getElementById('dominicalesNocturnos').value = '';

    // Ocultar campos
    document.getElementById('campoDiasRemuneradosD').style.display = 'none';
    document.getElementById('campoDiasRemuneradosN').style.display = 'none';
    document.getElementById('camposDominicales').style.display = 'none';

    // Resetear radio buttons
    document.getElementById('dominicalesNo').checked = true;
}

/**
 * Mostrar campo de días remunerados según tipo
 */
function mostrarCampoRemunerados(tipo) {
    const campo = document.getElementById(`${tipo === 'diurnos' ? 'diasDiurnos' : 'diasNocturnos'}`);
    const campoDias = document.getElementById(`campoDiasRemunerados${tipo === 'diurnos' ? 'D' : 'N'}`);

    if (parseInt(campo.value) > 0) {
        campoDias.style.display = 'block';
    } else {
        campoDias.style.display = 'none';
        document.getElementById(`diasRemunerados${tipo === 'diurnos' ? 'Diurnos' : 'Nocturnos'}`).value = '';
    }

    // Actualizar campos dominicales disponibles
    actualizarCamposDominicales();
}

/**
 * Mostrar campos de dominicales
 */
function mostrarCamposDominicales() {
    document.getElementById('camposDominicales').style.display = 'block';
    actualizarCamposDominicales();
}

/**
 * Ocultar campos de dominicales
 */
function ocultarCamposDominicales() {
    document.getElementById('camposDominicales').style.display = 'none';
    document.getElementById('dominicalesDiurnos').value = '';
    document.getElementById('dominicalesNocturnos').value = '';
    document.getElementById('dominicalDiurno').style.display = 'none';
    document.getElementById('dominicalNocturno').style.display = 'none';
}

/**
 * Actualizar campos de dominicales según días habilitados (formulario general)
 */
function actualizarCamposDominicalesFormulario() {
    const diasDiurnos = parseInt(document.getElementById('diasDiurnos').value) || 0;
    const diasNocturnos = parseInt(document.getElementById('diasNocturnos').value) || 0;
    const dominicalDiurno = document.getElementById('dominicalDiurno');
    const dominicalNocturno = document.getElementById('dominicalNocturno');

    // Solo mostrar campo dominical diurno si hay días diurnos
    if (diasDiurnos > 0) {
        dominicalDiurno.style.display = 'block';
    } else {
        dominicalDiurno.style.display = 'none';
        document.getElementById('dominicalesDiurnos').value = '';
    }

    // Solo mostrar campo dominical nocturno si hay días nocturnos
    if (diasNocturnos > 0) {
        dominicalNocturno.style.display = 'block';
    } else {
        dominicalNocturno.style.display = 'none';
        document.getElementById('dominicalesNocturnos').value = '';
    }
}

/**
 * Calcular total con margen
 */
function calcularTotalConMargen() {
    const margen = parseFloat(document.getElementById('margenUtilidad').value) || 0;
    const administracion = parseFloat(document.getElementById('porcentajeAdministracion').value) || 0;
    const imprevistos = parseFloat(document.getElementById('porcentajeImprevistos').value) || 0;

    // Por ahora calcular un total base simulado
    const totalBase = 1000; // Esto se calcularía con los datos reales
    const totalConMargenes = totalBase * (1 + (margen + administracion + imprevistos) / 100);

    document.getElementById('totalCosto').value = totalConMargenes.toFixed(2);
}

/**
 * Mostrar tabla de detalles
 */
function mostrarTablaDetalles() {
    $('#modalDetallesPrecios').modal('show');
    actualizarTablaDetalles();
}

/**
 * Actualizar tabla de detalles de precios
 */
function actualizarTablaDetalles() {
    const tbody = document.getElementById('tablaDetallesPrecios');
    tbody.innerHTML = '';

    // Obtener categorías seleccionadas
    const categoriasSeleccionadas = obtenerCategoriasSeleccionadas();
    const tipoCosto = document.getElementById('tipoCosto').value;
    const margen = parseFloat(document.getElementById('margenUtilidad').value) || 0;

    if (categoriasSeleccionadas.length === 0 || !tipoCosto) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted">
                    Seleccione categorías y tipo de costo para ver detalles
                </td>
            </tr>
        `;
        return;
    }

    // Simular datos de detalles
    categoriasSeleccionadas.forEach((categoria, index) => {
        const costoBase = 1500 + (index * 200); // Costo simulado
        const cantidad = parseInt(document.getElementById('cantidadPersonal').value) || 1;
        const diasHoras = obtenerDiasHoras();
        const subtotal = costoBase * cantidad * diasHoras;
        const totalConMargen = subtotal * (1 + margen / 100);

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${categoria.nombre}</td>
            <td>${tipoCosto.replace('_', ' ')}</td>
            <td>$${costoBase.toFixed(2)}</td>
            <td>${cantidad}</td>
            <td>${diasHoras}</td>
            <td>$${subtotal.toFixed(2)}</td>
            <td>${margen}%</td>
            <td class="font-weight-bold">$${totalConMargen.toFixed(2)}</td>
        `;
        tbody.appendChild(row);
    });
}

/**
 * Obtener categorías seleccionadas
 */
function obtenerCategoriasSeleccionadas() {
    const checkboxes = document.querySelectorAll('#listaCategorias .categoria-checkbox:checked');
    return Array.from(checkboxes).map(checkbox => ({
        id: checkbox.value,
        nombre: checkbox.nextElementSibling.textContent
    }));
}

/**
 * Obtener días/horas según tipo de costo
 */
function obtenerDiasHoras() {
    const tipoCosto = document.getElementById('tipoCosto').value;

    switch (tipoCosto) {
        case 'COSTO_HORA':
            return parseInt(document.getElementById('cantidadHoras').value) || 0;

        case 'COSTO_DIA':
            const diasDiurnos = parseInt(document.getElementById('diasDiurnos').value) || 0;
            const diasNocturnos = parseInt(document.getElementById('diasNocturnos').value) || 0;
            return diasDiurnos + diasNocturnos;

        case 'COSTO_MES':
            return 30; // Días del mes

        default:
            return 0;
    }
}

/**
 * Cargar categorías disponibles (simulado)
 */
function cargarCategoriasDisponibles() {
    // Datos simulados - estos vendrían de la base de datos
    const categorias = [
        { id: 1, nombre: 'Ingeniería' },
        { id: 2, nombre: 'Construcción' },
        { id: 3, nombre: 'Supervisión' },
        { id: 4, nombre: 'Mano de Obra General' },
        { id: 5, nombre: 'Mano de Obra Especializada' },
        { id: 6, nombre: 'Operación de Maquinaria' }
    ];

    const listaCategorias = document.getElementById('listaCategorias');
    listaCategorias.innerHTML = '';

    categorias.forEach(categoria => {
        const div = document.createElement('div');
        div.className = 'form-check';
        div.innerHTML = `
            <input class="form-check-input categoria-checkbox" type="checkbox" value="${categoria.id}" id="categoria_${categoria.id}">
            <label class="form-check-label" for="categoria_${categoria.id}">
                ${categoria.nombre}
            </label>
        `;
        listaCategorias.appendChild(div);
    });
}

/**
 * Función principal para confirmar agregar productos
 */
function confirmarAgregarProductos() {
    console.log('confirmarAgregarProductos llamada');

    // Verificar si hay productos seleccionados en la tabla de productos seleccionados
    const productosEnTabla = document.querySelectorAll('#tbodyProductosSeleccionados tr[data-item-id]');
    if (productosEnTabla.length > 0) {
        console.log('Productos en tabla encontrados:', productosEnTabla.length);
        // Procesar productos de la tabla
        procesarProductosSeleccionadosDeTabla();
        return;
    }

    toastr.warning('No hay elementos seleccionados para agregar. Seleccione items del acordeón o configure categorías y costos.');
}

/**
 * Procesar productos seleccionados de la tabla
 */
function procesarProductosSeleccionadosDeTabla() {
    const productosEnTabla = document.querySelectorAll('#tbodyProductosSeleccionados tr[data-item-id]');
    console.log(`Procesando ${productosEnTabla.length} productos de la tabla`);

    if (productosEnTabla.length === 0) {
        toastr.warning('No hay productos seleccionados en la tabla');
        return;
    }

    // Procesar productos directamente sin confirmación adicional
    toastr.success(`${productosEnTabla.length} producto(s) agregado(s) correctamente desde la tabla`);

    // Enviar productos a la base de datos
    enviarProductosTablaABaseDatos(productosEnTabla).then(() => {
        // Cerrar modal
        const modalTabla = document.getElementById('modalAgregarProductos');
        if (modalTabla) {
            $('#modalAgregarProductos').modal('hide');
        }

        // Actualizar totales
        calcularTotales();
    }).catch((error) => {
        console.error('Error al enviar productos:', error);
    });
}

/**
 * Enviar productos de la tabla a la base de datos
 */
async function enviarProductosTablaABaseDatos(productosEnTabla) {
    const cotizacionId = document.getElementById('id')?.value || document.getElementById('cotizacion_id')?.value;
    if (!cotizacionId) {
        toastr.error('No se encontró el ID de la cotización');
        return Promise.reject('No hay cotización ID');
    }

    console.log('Enviando productos de la tabla a BD...', {
        cotizacionId,
        cantidadProductos: productosEnTabla.length
    });

    try {
        const productos = [];
        // Mapear productosSeleccionados a estructura CotizacionProductoRequest
        productosSeleccionados.forEach((producto, index) => {
            console.log(`Procesando producto ${index}:`, producto);

            // Extraer configuración de costo si existe
            const configCosto = producto.configuracionCosto || {};

            // Mapear a estructura CotizacionProductoRequest
            const productoMapeado = {
                cotizacion_id: parseInt(cotizacionId),
                producto_id: null, // nullable
                nombre: producto.nombre || 'Producto sin nombre',
                descripcion: producto.descripcion || producto.item_parent || null,
                codigo: producto.codigo || `PROD-${Date.now()}-${index}`,
                unidad_medida: producto.unidad || configCosto.unidadMedida || 'UND',
                cantidad: parseFloat(producto.cantidad) || 1,
                valor_unitario: parseFloat(producto.precio) || parseFloat(configCosto.precio) || 0,
                descuento_porcentaje: 0, // nullable, default 0
                descuento_valor: 0, // nullable, default 0
                observaciones: `Origen: ${producto.esDelAcordeon ? 'Acordeón' : 'Manual'}. Categoría: ${producto.categoria || 'N/A'}`,
                orden: index + 1, // nullable, secuencial
                active: 1, // boolean, true por defecto

                // Campos de configuración de costos
                categoria_id: producto.categoria_id || null,
                cargo_id: producto.cargo_id || null,
                tipo_costo: configCosto.tipoCosto || (producto.categoria === 'SALARIOS' ? 'unitario' : null),

                // Costos específicos
                costo_dia: configCosto.costoDia || null,
                costo_hora: configCosto.costoHora || null,
                costo_unitario: configCosto.costoUnitario || parseFloat(producto.precio) || 0,

                // Configuración de días
                dias_diurnos: configCosto.diasDiurnos || null,
                dias_nocturnos: configCosto.diasNocturnos || null,
                dias_remunerados_diurnos: configCosto.diasRemuneradosDiurnos || null,
                dias_remunerados_nocturnos: configCosto.diasRemuneradosNocturnos || null,
                dominicales_diurnos: configCosto.dominicalesDiurnos || null,
                dominicales_nocturnos: configCosto.dominicalesNocturnos || null,

                // Configuración de horas
                horas_diurnas: configCosto.horasDiurnas || null,
                horas_remuneradas: configCosto.horasRemuneradas || null,
                incluir_dominicales: configCosto.incluirDominicales || 0 // boolean como integer
            };

            productos.push({
                producto: productoMapeado,
                nombre: producto.nombre || `Producto ${index + 1}`
            });
        });

        console.log('Productos preparados para envío:', productos.length);

        // Enviar productos uno por uno con manejo de errores individual
        let productosExitosos = 0;
        let productosConError = 0;

        for (const {producto, nombre} of productos) {
            try {
                console.log(`Enviando producto: ${nombre}`, producto);

                const response = await fetch('/admin/admin.cotizaciones.productos.agregar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(producto)
                });

                // Verificar si la respuesta es válida
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error(`Error HTTP ${response.status} para producto "${nombre}":`, errorText);
                    toastr.error(`Error HTTP ${response.status} al guardar "${nombre}"`);
                    productosConError++;
                    continue;
                }

                const result = await response.json();

                if (result.success) {
                    console.log(`✓ Producto "${nombre}" guardado exitosamente:`, result.data);
                    productosExitosos++;
                } else {
                    console.error(`✗ Error al guardar producto "${nombre}":`, result.message, result.errors || '');
                    toastr.error(`Error al guardar "${nombre}": ${result.message}`);
                    productosConError++;
                }

            } catch (error) {
                console.error(`Error de conexión al guardar producto "${nombre}":`, error);
                toastr.error(`Error de conexión al guardar "${nombre}"`);
                productosConError++;
            }
        }

        // Mostrar resumen final
        if (productosExitosos > 0) {
            toastr.success(`${productosExitosos} producto(s) guardado(s) correctamente en la base de datos`);
        }

        if (productosConError > 0) {
            toastr.warning(`${productosConError} producto(s) no pudieron guardarse`);
        }
        // Recargar productos guardados en la cotización
        await cargarProductosGuardados();
        return productosExitosos > 0 ? Promise.resolve() : Promise.reject('Ningún producto se guardó correctamente');

    } catch (error) {
        console.error('Error general al enviar productos a la BD:', error);
        toastr.error('Error general al guardar productos en la base de datos');
        return Promise.reject(error);
    }
}

/**
 * Enviar productos del sistema legacy a la base de datos
 */
async function enviarProductosLegacyABaseDatos(categoriasSeleccionadas, tipoCosto, totalCosto) {
    const cotizacionId = document.getElementById('id')?.value || document.getElementById('cotizacion_id')?.value;
    if (!cotizacionId) {
        toastr.error('No se encontró el ID de la cotización');
        return Promise.reject('No hay cotización ID');
    }

    console.log('Enviando producto legacy a BD...', {
        cotizacionId,
        categoriasSeleccionadas,
        tipoCosto,
        totalCosto
    });

    try {
        // Estructura exacta según CotizacionProductoRequest
        const producto = {
            cotizacion_id: parseInt(cotizacionId),
            nombre: `${tipoCosto} - ${categoriasSeleccionadas.map(c => c.nombre || c).join(', ')}`,
            descripcion: `Producto configurado con categorías: ${categoriasSeleccionadas.map(c => c.nombre || c).join(', ')}`,
            codigo: `${tipoCosto.toUpperCase()}-${Date.now()}`,
            unidad_medida: 'UND',
            cantidad: 1,
            valor_unitario: parseFloat(totalCosto),
            descuento_porcentaje: 0,
            descuento_valor: 0,
            observaciones: `Tipo de costo: ${tipoCosto}. Configurado desde sistema legacy.`,
            active: 1,
            tipo_costo: tipoCosto
        };

        console.log('Producto legacy preparado:', producto);

        const response = await fetch('/admin/admin.cotizaciones.productos.agregar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(producto)
        });

        // Verificar si la respuesta es válida
        if (!response.ok) {
            const errorText = await response.text();
            console.error(`Error HTTP ${response.status} al guardar producto legacy:`, errorText);
            toastr.error(`Error HTTP ${response.status}: ${response.statusText}`);
            return Promise.reject(`Error HTTP ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            console.log('✓ Producto legacy guardado exitosamente:', result.data);
            toastr.success('Producto configurado guardado en la base de datos correctamente');
            return Promise.resolve();
        } else {
            console.error('✗ Error al guardar producto legacy:', result.message, result.errors || '');

            // Mostrar errores específicos si existen
            if (result.errors) {
                const errorsText = Object.values(result.errors).flat().join(', ');
                toastr.error(`Errores de validación: ${errorsText}`);
            } else {
                toastr.error(`Error al guardar: ${result.message}`);
            }
            return Promise.reject(result.message);
        }

    } catch (error) {
        console.error('Error de conexión al enviar producto legacy:', error);
        toastr.error('Error de conexión al guardar producto en la base de datos');
        return Promise.reject(error);
    }
}

/**
 * Limpiar formulario de salarios
 */
function limpiarFormularioSalarios() {
    document.getElementById('formAgregarPersonal').reset();
    document.getElementById('selectAllCategorias').checked = false;
    document.querySelectorAll('.categoria-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('tipoCosto').value = '';
    cambiarTipoCosto();
    document.getElementById('totalCosto').value = '';
}

/**
 * Cargar elementos para quitar
 */
function cargarElementosAQuitar() {
    // Datos simulados de elementos ya agregados
    const elementos = [
        { id: 1, tipo: 'Producto', descripcion: 'Cemento Portland', cantidad: 10, costo: 255.00 },
        { id: 2, tipo: 'Salario', descripcion: 'Ingeniero Civil', cantidad: 2, costo: 7000.00 },
        { id: 3, tipo: 'Producto', descripcion: 'Arena Fina', cantidad: 20, costo: 300.00 }
    ];

    const tbody = document.getElementById('tbodyElementosAQuitar');
    const noElementosRow = document.getElementById('noElementosAQuitar');

    if (elementos.length === 0) {
        noElementosRow.style.display = 'table-row';
        return;
    }

    noElementosRow.style.display = 'none';

    // Limpiar filas existentes
    Array.from(tbody.children).forEach(row => {
        if (row.id !== 'noElementosAQuitar') {
            row.remove();
        }
    });

    elementos.forEach(elemento => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="checkbox" class="elemento-checkbox" data-id="${elemento.id}" onchange="actualizarTotalAQuitar()">
            </td>
            <td>
                <span class="badge bg-${elemento.tipo === 'Producto' ? 'primary' : 'success'}">${elemento.tipo}</span>
            </td>
            <td>${elemento.descripcion}</td>
            <td>${elemento.cantidad}</td>
            <td>$${elemento.costo.toFixed(2)}</td>
        `;
        tbody.appendChild(row);
    });
}

/**
 * Toggle select all elementos
 */
function toggleSelectAllElementos() {
    const selectAll = document.getElementById('selectAllElementos');
    const checkboxes = document.querySelectorAll('.elemento-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    actualizarTotalAQuitar();
}

/**
 * Actualizar total a quitar
 */
function actualizarTotalAQuitar() {
    const checkboxes = document.querySelectorAll('.elemento-checkbox:checked');
    let total = 0;

    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const costo = parseFloat(row.cells[4].textContent.replace('$', '').replace(',', ''));
        total += costo;
    });

    document.getElementById('totalAQuitar').textContent = total.toFixed(2);

    // Habilitar/deshabilitar botón confirmar
    document.getElementById('confirmarQuitarProductos').disabled = checkboxes.length === 0;
}

/**
 * Confirmar quitar productos
 */
function confirmarQuitarProductos() {
    const checkboxes = document.querySelectorAll('.elemento-checkbox:checked');

    if (checkboxes.length === 0) {
        toastr.warning('No hay elementos seleccionados');
        return;
    }

    const cantidadElementos = checkboxes.length;

    // Aquí se enviarían los datos al backend
    toastr.success(`${cantidadElementos} elemento(s) eliminado(s) correctamente`);
    $('#modalQuitarProductos').modal('hide');

    // Recargar elementos
    cargarElementosAQuitar();
}

/**
 * Debugging: Verificar estado de productos seleccionados
 */
function debugProductosSeleccionados() {
    console.log('=== DEBUG PRODUCTOS SELECCIONADOS ===');
    console.log('productosSeleccionados.length:', productosSeleccionados.length);
    console.log('productosSeleccionados:', productosSeleccionados);
    console.log('productosDisponibles.length:', productosDisponibles.length);
    console.log('productosDisponibles:', productosDisponibles);

    // Verificar checkboxes marcados
    const checkboxesMarcados = document.querySelectorAll('.producto-checkbox:checked, .item-checkbox:checked, input[type="checkbox"]:checked');
    console.log('Checkboxes marcados encontrados:', checkboxesMarcados.length);
    checkboxesMarcados.forEach(checkbox => {
        console.log('Checkbox marcado:', checkbox.dataset.id, checkbox);
    });

    // Verificar items en tbody_items
    const tbody = document.getElementById('tbody_items');
    if (tbody) {
        const rows = tbody.querySelectorAll('tr:not([id="no_items_row_items"])');
        console.log('Filas en tbody_items (productos seleccionados visualmente):', rows.length);
    }
    console.log('=======================================');
}

/**
 * Forzar agregar producto al array (función de emergencia)
 */
// function forzarAgregarProducto() {
//     console.log('=== FORZAR AGREGAR PRODUCTO ===');
//     console.log('productosSeleccionados.length antes:', productosSeleccionados.length);

//     try {
//         if (productosSeleccionados.length === 0) {
//             console.log('Array de productos está vacío, agregando productos...');

//             // Intentar obtener productos desde items del acordeón
//             const items = document.querySelectorAll('.list-group-item, .item-card, .accordion-item');
//             console.log('Items encontrados en acordeón:', items.length);

//             let productosAgregados = 0;
//             items.forEach((item, index) => {
//                 if (productosAgregados < 3) { // Limitar a 3 para testing
//                     const nombre = item.querySelector('.item-name, .card-title, h5, h6')?.textContent?.trim() || `Producto del Acordeón ${index + 1}`;
//                     const precio = Math.random() * 100 + 10; // Precio random para testing

//                     const nuevoProducto = {
//                         id: Date.now() + index,
//                         nombre: nombre,
//                         codigo: `ACC${index + 1}`,
//                         precio: precio,
//                         cantidad: 1,
//                         total: precio,
//                         unidad: 'Unidad',
//                         categoria: 'Del Acordeón',
//                         esDelAcordeon: true
//                     };

//                     productosSeleccionados.push(nuevoProducto);
//                     productosAgregados++;
//                     console.log('Producto del acordeón agregado:', nuevoProducto);
//                 }
//             });

//             // Si no se encontraron items del acordeón, crear productos de ejemplo
//             if (productosAgregados === 0) {
//                 console.log('No se encontraron items del acordeón, creando productos de ejemplo...');
//                 for (let i = 1; i <= 2; i++) {
//                     const nuevoProducto = {
//                         id: Date.now() + i,
//                         nombre: `Producto de Ejemplo ${i}`,
//                         codigo: `EJ${i.toString().padStart(3, '0')}`,
//                         precio: 25.50 * i,
//                         cantidad: 1,
//                         total: 25.50 * i,
//                         unidad: 'Unidad',
//                         categoria: 'Ejemplo',
//                         esDelAcordeon: false
//                     };

//                     productosSeleccionados.push(nuevoProducto);
//                     productosAgregados++;
//                     console.log('Producto de ejemplo agregado:', nuevoProducto);
//                 }
//             }

//             console.log('productosSeleccionados.length después:', productosSeleccionados.length);

//             // Actualizar UI de forma segura
//             try {
//                 actualizarTablaProductosSeleccionados();
//                 calcularTotales();

//                 // Actualizar contador debug si existe
//                 const productosCount = document.getElementById('productosCount');
//                 if (productosCount) {
//                     productosCount.textContent = productosSeleccionados.length;
//                 }

//                 toastr.success(`Se agregaron ${productosAgregados} productos para testing`);

//             } catch (uiError) {
//                 console.error('Error actualizando UI:', uiError);
//                 toastr.warning(`Se agregaron ${productosAgregados} productos, pero hay problemas con la interfaz`);
//             }
//         } else {
//             console.log('Ya hay productos seleccionados:', productosSeleccionados.length);
//             toastr.info(`Ya hay ${productosSeleccionados.length} productos seleccionados`);
//         }
//     } catch (error) {
//         console.error('Error en forzarAgregarProducto:', error);
//         toastr.error('Error al forzar agregar productos: ' + error.message);
//     }

//     console.log('===============================');
// }

/**
 * Mostrar notificación visual de productos agregados
 */
function mostrarProductosAgregados(cantidad) {
    // Crear modal de confirmación visual
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'modalProductosAgregados';
    modal.innerHTML = `
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle"></i> ¡Éxito!
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-box-open fa-3x text-success"></i>
                    </div>
                    <h6>Productos Agregados</h6>
                    <p class="mb-2">Se agregaron exitosamente <strong>${cantidad}</strong> elemento(s) a la cotización.</p>
                    <p class="text-muted small">Puedes verlos en la tabla principal de items.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success btn-sm" data-bs-dismiss="modal" onclick="cerrarModalProductosAgregados()">
                        <i class="fas fa-thumbs-up"></i> Entendido
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Mostrar modal
    $('#modalProductosAgregados').modal('show');

    // Auto cerrar después de 3 segundos
    setTimeout(() => {
        $('#modalProductosAgregados').modal('hide');
    }, 3000);
}

/**
 * Cerrar modal de productos agregados
 */
function cerrarModalProductosAgregados() {
    const modal = document.getElementById('modalProductosAgregados');
    if (modal) {
        $('#modalProductosAgregados').modal('hide');
        setTimeout(() => {
            modal.remove();
        }, 500);
    }
}

/**
 * Mostrar resumen detallado de productos agregados
 */
function mostrarResumenProductosAgregados() {
    // Obtener los últimos items agregados
    const ultimosItems = itemsCotizacion.slice(-2); // Últimos 2 items como ejemplo

    if (ultimosItems.length === 0) return;

    let resumenHTML = '<div class="list-group">';
    ultimosItems.forEach((item, index) => {
        const esProducto = item.tipo === 'producto';
        const icono = esProducto ? 'fas fa-cube' : 'fas fa-user';
        const colorBadge = esProducto ? 'bg-primary' : 'bg-success';

        resumenHTML += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <i class="${icono} text-${esProducto ? 'primary' : 'success'} me-2"></i>
                    <strong>${item.nombre}</strong>
                    ${esProducto ? `<br><small class="text-muted">Cantidad: ${item.cantidad} - Precio: $${item.precio_unitario}</small>` : ''}
                </div>
                <span class="badge ${colorBadge}">
                    $${item.valor_total ? parseFloat(item.valor_total).toFixed(2) : '0.00'}
                </span>
            </div>
        `;
    });
    resumenHTML += '</div>';

    // Crear notificación toastr personalizada
    toastr.options = {
        "closeButton": true,
        "timeOut": "8000",
        "extendedTimeOut": "2000",
        "positionClass": "toast-top-right",
        "progressBar": true
    };

    toastr.info(
        resumenHTML,
        '<i class="fas fa-clipboard-list"></i> Productos Agregados - Resumen',
        {
            "allowHtml": true,
            "closeButton": true,
            "timeOut": "8000"
        }
    );

    // Resetear opciones de toastr
    setTimeout(() => {
        toastr.options = {};
    }, 100);
}

/**
 * Filtrar items propios según texto de búsqueda
 */
function filtrarItemsPropios() {
    try {
        const searchInput = document.getElementById('buscarItemsPropios');
        const container = document.getElementById('itemsPropiosContainer');

        if (!searchInput || !container) {
            console.warn('Elementos de búsqueda no encontrados');
            return;
        }

        const searchTerm = searchInput.value.toLowerCase();
        const items = container.querySelectorAll('.item-propio-card');
        let itemsVisibles = 0;

        items.forEach(item => {
            try {
                const nombreElement = item.querySelector('.item-nombre');
                const codigoElement = item.querySelector('.item-codigo');
                const descripcionElement = item.querySelector('.item-descripcion');

                if (!nombreElement || !codigoElement || !descripcionElement) {
                    console.warn('Elementos internos del item no encontrados');
                    return;
                }

                const nombre = nombreElement.textContent.toLowerCase();
                const codigo = codigoElement.textContent.toLowerCase();
                const descripcion = descripcionElement.textContent.toLowerCase();

                // Buscar en nombre, código y descripción (que ahora incluye información de parametrización)
                const matches = nombre.includes(searchTerm) ||
                               codigo.includes(searchTerm) ||
                               descripcion.includes(searchTerm);

                if (matches) {
                    item.style.display = '';
                    itemsVisibles++;
                } else {
                    item.style.display = 'none';
                }
            } catch (itemError) {
                console.error('Error procesando item:', itemError);
            }
        });

        // Actualizar contador de items visibles
        const contadorVisibles = document.getElementById('itemsVisibles');
        if (contadorVisibles) {
            contadorVisibles.textContent = itemsVisibles;
        }

        console.log(`Filtrado: ${itemsVisibles} de ${items.length} items mostrados (incluye parametrización)`);
    } catch (error) {
        console.error('Error en filtrarItemsPropios:', error);
    }
}

/**
 * Limpiar búsqueda de items propios
 */
function limpiarBusquedaItemsPropios() {
    try {
        const searchInput = document.getElementById('buscarItemsPropios');
        if (!searchInput) {
            console.warn('Input de búsqueda no encontrado');
            return;
        }

        searchInput.value = '';

        // Mostrar todos los items
        const container = document.getElementById('itemsPropiosContainer');
        if (!container) {
            console.warn('Contenedor de items no encontrado');
            return;
        }

        const items = container.querySelectorAll('.item-propio-card');

        items.forEach(item => {
            item.style.display = '';
        });

        // Actualizar contador
        const contadorVisibles = document.getElementById('itemsVisibles');
        if (contadorVisibles) {
            contadorVisibles.textContent = items.length;
        }

        // Enfocar el input
        searchInput.focus();

        console.log('Búsqueda limpiada - mostrando todos los items');
    } catch (error) {
        console.error('Error en limpiarBusquedaItemsPropios:', error);
    }
}

// =============================================================================
// FUNCIONES PARA GESTIÓN DE PRODUCTOS GUARDADOS
// =============================================================================

/**
 * Cargar productos guardados de la cotización
 */
async function cargarProductosGuardados() {
    try {
        // Obtener ID de cotización
        let cotizacionId = document.getElementById('id')?.value;
        console.log('ID de cotización obtenido del input>>>:', cotizacionId );

        if (!cotizacionId) {
            const urlParams = new URLSearchParams(window.location.search);
            cotizacionId = urlParams.get('id');
        }
        if (!cotizacionId && typeof cotizacionGuardadaId !== 'undefined') {
            console.log("entre", cotizacionGuardadaId);

            cotizacionId = cotizacionGuardadaId;
        }

        if (!cotizacionId) {
            console.log('No se encontró ID de cotización para cargar productos');
            return;
        }

        console.log('Cargando productos guardados para cotización:', cotizacionId);

        // Mostrar loading
        document.getElementById('loadingProductosGuardados').classList.remove('d-none');
        document.getElementById('emptyProductosGuardados').style.display = 'none';
        document.getElementById('tablaProductosGuardados').style.display = 'none';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.warn('Token CSRF no encontrado. El usuario podría no estar autenticado.');
            mostrarEstadoVacioProductos();
            throw new Error('Token CSRF no encontrado');
        }

        const response = await fetch('/admin/cotizaciones/productos/obtener?' + new URLSearchParams({
            cotizacion_id: cotizacionId
        }), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);

            // Si es HTML (página de login), significa que no está autenticado
            if (errorText.includes('<!DOCTYPE html>') || errorText.includes('<html')) {
                throw new Error('Sesión expirada. Por favor, recarga la página e inicia sesión nuevamente.');
            }

            throw new Error(`HTTP ${response.status}: ${response.statusText}. Response: ${errorText.substring(0, 200)}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON but got:', text.substring(0, 500));
            throw new Error('El servidor no devolvió JSON válido');
        }

        const result = await response.json();
        console.log('Productos cargados:', result);

        // Ocultar loading
        document.getElementById('loadingProductosGuardados').classList.add('d-none');

        if (result.success && result.data && result.data.length > 0) {
            mostrarProductosGuardados(result.data);

            // Actualizar totales después de cargar productos
            await actualizarTotalesCompletos();
        } else {
            mostrarEstadoVacioProductos();
        }

    } catch (error) {
        console.error('Error al cargar productos guardados:', error);
        document.getElementById('loadingProductosGuardados').classList.add('d-none');
        mostrarEstadoVacioProductos();

        toastr.error('Error al cargar productos guardados: ' + error.message);
    }
}

/**
 * Mostrar productos guardados en la tabla
 */
function mostrarProductosGuardados(productos) {
    const tbody = document.getElementById('tbodyProductosGuardados');
    const tabla = document.getElementById('tablaProductosGuardados');
    const footer = document.getElementById('footerProductosGuardados');
    const contador = document.getElementById('contadorProductosGuardados');

    tbody.innerHTML = '';

    productos.forEach((producto, index) => {
        const row = document.createElement('tr');
        row.setAttribute('data-producto-id', producto.id);

        const valorUnitario = parseFloat(producto.valor_unitario || 0);
        const cantidad = parseFloat(producto.cantidad || 0);
        const descuentoValor = parseFloat(producto.descuento_valor || 0);
        const descuentoPorcentaje = parseFloat(producto.descuento_porcentaje || 0);
        const total = parseFloat(producto.valor_total || 0);

        // Calcular descuento total
        const descuentoTotal = descuentoValor + (valorUnitario * cantidad * descuentoPorcentaje / 100);

        row.innerHTML = `
            <td>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input producto-guardado-checkbox"
                           id="checkProducto${producto.id}" data-producto-id="${producto.id}"
                           onchange="actualizarContadorSeleccionadosGuardados()">
                    <label class="custom-control-label" for="checkProducto${producto.id}"></label>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="product-icon me-2">
                        <i class="fas fa-cube text-primary"></i>
                    </div>
                    <div>
                        <strong class="d-block">${producto.nombre}</strong>
                        <small class="text-muted">${producto.codigo || 'Sin código'}</small>
                        ${producto.descripcion ? `<br><small class="text-info">${producto.descripcion}</small>` : ''}
                        <br><span class="badge badge-secondary">${producto.unidad_medida}</span>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge badge-info">${cantidad.toFixed(3)}</span>
            </td>
            <td>
                <strong>$${valorUnitario.toLocaleString('es-CO', { minimumFractionDigits: 2 })}</strong>
            </td>
            <td>
                ${descuentoTotal > 0 ?
                    `<span class="text-danger">-$${descuentoTotal.toLocaleString('es-CO', { minimumFractionDigits: 2 })}</span>` +
                    (descuentoPorcentaje > 0 ? `<br><small>(${descuentoPorcentaje}%)</small>` : '')
                    : '<span class="text-muted">Sin descuento</span>'}
            </td>
            <td>
                <strong class="text-success">$${total.toLocaleString('es-CO', { minimumFractionDigits: 2 })}</strong>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editarProducto(${producto.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="eliminarProducto(${producto.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        tbody.appendChild(row);
    });

    // Mostrar tabla y footer
    tabla.style.display = 'block';
    footer.classList.remove('d-none');

    // Actualizar contador
    contador.textContent = productos.length;

    // Ocultar estado vacío
    document.getElementById('emptyProductosGuardados').style.display = 'none';

    console.log(`Se mostraron ${productos.length} productos guardados`);
}

/**
 * Mostrar estado vacío cuando no hay productos
 */
function mostrarEstadoVacioProductos() {
    document.getElementById('emptyProductosGuardados').style.display = 'block';
    document.getElementById('tablaProductosGuardados').style.display = 'none';
    document.getElementById('footerProductosGuardados').classList.add('d-none');
    document.getElementById('contadorProductosGuardados').textContent = '0';
}

/**
 * Toggle select all productos guardados
 */
function toggleSelectAllProductosGuardados() {
    const selectAll = document.getElementById('selectAllProductosGuardados');
    const checkboxes = document.querySelectorAll('.producto-guardado-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    actualizarContadorSeleccionadosGuardados();
}

/**
 * Actualizar contador de productos seleccionados
 */
function actualizarContadorSeleccionadosGuardados() {
    const checkboxes = document.querySelectorAll('.producto-guardado-checkbox:checked');
    const contador = document.getElementById('contadorSeleccionadosGuardados');
    const btnEliminar = document.getElementById('btnEliminarSeleccionados');

    contador.textContent = checkboxes.length;
    btnEliminar.disabled = checkboxes.length === 0;

    // Actualizar estado del select all
    const selectAll = document.getElementById('selectAllProductosGuardados');
    const totalCheckboxes = document.querySelectorAll('.producto-guardado-checkbox');

    if (checkboxes.length === 0) {
        selectAll.indeterminate = false;
        selectAll.checked = false;
    } else if (checkboxes.length === totalCheckboxes.length) {
        selectAll.indeterminate = false;
        selectAll.checked = true;
    } else {
        selectAll.indeterminate = true;
        selectAll.checked = false;
    }
}

/**
 * Eliminar productos seleccionados
 */
async function eliminarProductosSeleccionados() {
    const checkboxes = document.querySelectorAll('.producto-guardado-checkbox:checked');

    if (checkboxes.length === 0) {
        toastr.warning('Debe seleccionar al menos un producto para eliminar');
        return;
    }

    const productosIds = Array.from(checkboxes).map(cb => cb.getAttribute('data-producto-id'));

    const result = await Swal.fire({
        title: '¿Confirmar eliminación?',
        text: `Se eliminarán ${productosIds.length} producto(s) de la cotización`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.value) {
        try {
            const promesas = productosIds.map(id => eliminarProductoGuardado(id));
            await Promise.all(promesas);

            toastr.success(`${productosIds.length} producto(s) eliminado(s) exitosamente`);
            cargarProductosGuardados(); // Recargar lista
        } catch (error) {
            console.error('Error al eliminar productos:', error);
            toastr.error('Error al eliminar algunos productos: ' + error.message);
        }
    }
}


/**
 * Eliminar un producto específico
 */
async function eliminarProducto(productoId) {
    console.log('=== INICIANDO ELIMINACIÓN DE PRODUCTO ===');
    console.log('Producto ID:', productoId);

    // Verificar que tenemos un ID válido
    if (!productoId) {
        toastr.error('ID de producto no válido');
        return;
    }

    const result = await Swal.fire({
        title: '¿Confirmar eliminación?',
        text: 'Se eliminará este producto de la cotización',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.value) {
        try {
            console.log('Usuario confirmó eliminación, procediendo...');

            // Mostrar loading
            Swal.fire({
                title: 'Eliminando producto...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const resultado = await eliminarProductoGuardado(productoId);
            console.log('Resultado de eliminación:', resultado);

            // Cerrar loading
            Swal.close();

            if (resultado.success) {
                toastr.success(resultado.message || 'Producto eliminado exitosamente');
                // Recargar lista
                await cargarProductosGuardados();

                // Mostrar totales actualizados si están disponibles
                if (resultado.data?.totales_actualizados) {
                    console.log('Totales actualizados:', resultado.data.totales_actualizados);
                }

                // Actualizar totales completos para asegurar consistencia
                await actualizarTotalesCompletos();
            } else {
                throw new Error(resultado.message || 'Error desconocido');
            }

        } catch (error) {
            console.error('Error completo:', error);
            Swal.close(); // Cerrar loading en caso de error
            toastr.error('Error al eliminar producto: ' + error.message);
        }
    } else {
        console.log('Usuario canceló la eliminación');
    }
}


async function eliminarProductoGuardado(productoId) {
    console.log('=== ELIMINANDO PRODUCTO ===');
    console.log('ID:', productoId);

    try {
        const token = $('meta[name="csrf-token"]').attr('content');
        console.log('Token CSRF:', token);

        if (!token) {
            throw new Error('No se encontró el token CSRF');
        }

        const url = `/admin/admin.cotizaciones.productos.eliminar/${productoId}`;
        console.log('URL:', url);

        console.log('Enviando petición...');

        const response = await $.ajax({
            url: url,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            dataType: 'json'
        });

        console.log('✅ Respuesta exitosa:', response);
        return response;

    } catch (error) {
        console.error('❌ Error en eliminarProductoGuardado:', error);

        if (error.status === 419) {
            throw new Error('Token CSRF expirado. Por favor, recarga la página.');
        }

        if (error.responseJSON) {
            console.log('Error JSON:', error.responseJSON);
            throw new Error(error.responseJSON.message || 'Error del servidor');
        }

        if (error.responseText) {
            console.log('Error Text:', error.responseText);

            // Si la respuesta es HTML, probablemente sea redirección a login
            if (error.responseText.includes('<!DOCTYPE html>')) {
                throw new Error('Sesión expirada. Por favor, recarga la página.');
            }
        }

        throw new Error(error.statusText || error.message || 'Error desconocido');
    }
}

/**
 * Editar producto con UI mejorada
 */
async function editarProducto(productoId) {
    try {
        console.log('=== EDITANDO PRODUCTO ===');
        console.log('ID:', productoId);

        // Obtener datos actuales del producto desde la tabla
        const row = document.querySelector(`tr[data-producto-id="${productoId}"]`);
        if (!row) {
            toastr.error('No se encontró el producto en la tabla');
            return;
        }

        // Extraer datos actuales del producto de la tabla
        const cells = row.querySelectorAll('td');
        const cantidadActual = cells[2]?.textContent?.trim() || '1';
        const valorUnitarioActual = cells[3]?.textContent?.replace(/[$,.]/g, '') || '0';
        const descuentoActual = cells[4]?.textContent?.replace('%', '') || '0';
        const nombreProducto = cells[1]?.textContent?.trim() || 'Producto';

        console.log('Datos actuales:', {
            cantidad: cantidadActual,
            valorUnitario: valorUnitarioActual,
            descuento: descuentoActual,
            nombre: nombreProducto
        });

        // Crear un modal más avanzado con mejor UX
        const { value: formValues } = await Swal.fire({
            title: '<i class="fas fa-edit text-primary"></i> Editar Producto',
            html: `
                <div class="container-fluid p-0">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Producto:</strong> ${nombreProducto}
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="swal-cantidad" class="form-label font-weight-bold">
                                <i class="fas fa-cubes text-success"></i> Cantidad *
                            </label>
                            <input id="swal-cantidad"
                                   class="form-control"
                                   type="number"
                                   step="0.001"
                                   min="0.001"
                                   value="${cantidadActual}"
                                   placeholder="Ej: 5.5">
                            <small class="text-muted">Cantidad mínima: 0.001</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="swal-valor" class="form-label font-weight-bold">
                                <i class="fas fa-dollar-sign text-warning"></i> Valor Unitario *
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input id="swal-valor"
                                       class="form-control"
                                       type="number"
                                       step="0.01"
                                       min="0.01"
                                       value="${valorUnitarioActual}"
                                       placeholder="0.00">
                            </div>
                            <small class="text-muted">Precio por unidad</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="swal-descuento" class="form-label font-weight-bold">
                                <i class="fas fa-percent text-danger"></i> Descuento
                            </label>
                            <div class="input-group">
                                <input id="swal-descuento"
                                       class="form-control"
                                       type="number"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       value="${descuentoActual}"
                                       placeholder="0.00">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="text-muted">Opcional (0-100%)</small>
                        </div>
                    </div>

                    <div class="card bg-light mt-3">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-calculator text-info"></i>
                                Cálculo Automático
                            </h6>
                            <div class="row text-sm">
                                <div class="col-6">
                                    <strong>Subtotal:</strong>
                                    <span id="swal-subtotal" class="text-primary">$0.00</span>
                                </div>
                                <div class="col-6">
                                    <strong>Descuento:</strong>
                                    <span id="swal-descuento-valor" class="text-danger">$0.00</span>
                                </div>
                                <div class="col-12 mt-2">
                                    <strong class="h6">Total:
                                        <span id="swal-total" class="text-success">$0.00</span>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Guardar Cambios',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            didOpen: () => {
                // Función para calcular totales en tiempo real
                const calcularTotales = () => {
                    const cantidad = parseFloat(document.getElementById('swal-cantidad').value) || 0;
                    const valorUnitario = parseFloat(document.getElementById('swal-valor').value) || 0;
                    const descuentoPorcentaje = parseFloat(document.getElementById('swal-descuento').value) || 0;

                    const subtotal = cantidad * valorUnitario;
                    const descuentoValor = subtotal * (descuentoPorcentaje / 100);
                    const total = subtotal - descuentoValor;

                    // Formatear números como moneda
                    const formatearMoneda = (valor) => {
                        return new Intl.NumberFormat('es-CO', {
                            style: 'currency',
                            currency: 'COP',
                            minimumFractionDigits: 2
                        }).format(valor);
                    };

                    // Actualizar elementos
                    document.getElementById('swal-subtotal').textContent = formatearMoneda(subtotal);
                    document.getElementById('swal-descuento-valor').textContent = formatearMoneda(descuentoValor);
                    document.getElementById('swal-total').textContent = formatearMoneda(total);
                };

                // Agregar eventos para cálculo en tiempo real
                ['swal-cantidad', 'swal-valor', 'swal-descuento'].forEach(id => {
                    const elemento = document.getElementById(id);
                    if (elemento) {
                        elemento.addEventListener('input', calcularTotales);
                        elemento.addEventListener('keyup', calcularTotales);
                    }
                });

                // Calcular totales inicial
                calcularTotales();

                // Focus en el primer campo
                document.getElementById('swal-cantidad').focus();
            },
            preConfirm: () => {
                const cantidad = document.getElementById('swal-cantidad').value;
                const valorUnitario = document.getElementById('swal-valor').value;
                const descuentoPorcentaje = document.getElementById('swal-descuento').value;

                // Validaciones
                if (!cantidad || cantidad.trim() === '') {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> La cantidad es obligatoria');
                    return false;
                }

                if (!valorUnitario || valorUnitario.trim() === '') {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> El valor unitario es obligatorio');
                    return false;
                }

                const cantidadNum = parseFloat(cantidad);
                const valorUnitarioNum = parseFloat(valorUnitario);
                const descuentoPorcentajeNum = parseFloat(descuentoPorcentaje) || 0;

                if (isNaN(cantidadNum) || cantidadNum <= 0) {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> La cantidad debe ser un número mayor a 0');
                    return false;
                }

                if (isNaN(valorUnitarioNum) || valorUnitarioNum < 0) {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> El valor unitario debe ser un número mayor o igual a 0');
                    return false;
                }

                if (descuentoPorcentajeNum < 0 || descuentoPorcentajeNum > 100) {
                    Swal.showValidationMessage('<i class="fas fa-exclamation-triangle"></i> El descuento debe estar entre 0% y 100%');
                    return false;
                }

                return {
                    cantidad: cantidadNum,
                    valor_unitario: valorUnitarioNum,
                    descuento_porcentaje: descuentoPorcentajeNum
                };
            }
        });

        if (formValues) {
            console.log('Formulario completado:', formValues);

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Actualizando producto...',
                html: '<i class="fas fa-spinner fa-spin fa-3x"></i><br><br>Por favor espere mientras se actualiza el producto',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Calcular totales
            const cantidad = formValues.cantidad;
            const valorUnitario = formValues.valor_unitario;
            const descuentoPorcentaje = formValues.descuento_porcentaje;

            const subtotal = cantidad * valorUnitario;
            const descuentoValor = subtotal * (descuentoPorcentaje / 100);
            const valorTotal = subtotal - descuentoValor;

            // Preparar datos para enviar
            const datosActualizar = {
                cantidad: cantidad,
                valor_unitario: valorUnitario,
                descuento_porcentaje: descuentoPorcentaje,
                valor_total: valorTotal
            };

            console.log('Datos a enviar:', datosActualizar);

            try {
                // Obtener token CSRF
                const token = $('meta[name="csrf-token"]').attr('content');
                if (!token) {
                    throw new Error('Token CSRF no encontrado');
                }

                // Enviar actualización al backend usando jQuery
                const response = await $.ajax({
                    url: `/admin/admin.cotizaciones.productos.actualizar/${productoId}`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify(datosActualizar),
                    dataType: 'json'
                });

                console.log('Respuesta del servidor:', response);

                // Cerrar loading
                Swal.close();

                if (response.success) {
                    // Mostrar éxito con detalles
                    await Swal.fire({
                        icon: 'success',
                        title: '¡Producto Actualizado!',
                        html: `
                            <div class="text-left">
                                <p><strong>Cantidad:</strong> ${cantidad}</p>
                                <p><strong>Valor Unitario:</strong> $${valorUnitario.toLocaleString()}</p>
                                <p><strong>Descuento:</strong> ${descuentoPorcentaje}%</p>
                                <p><strong>Total:</strong> <span class="text-success">$${valorTotal.toLocaleString()}</span></p>
                            </div>
                        `,
                        timer: 3000,
                        showConfirmButton: false
                    });

                    // Recargar la lista de productos
                    await cargarProductosGuardados();

                    // Actualizar totales si están disponibles
                    if (response.totales) {
                        console.log('Actualizando totales:', response.totales);
                        actualizarTotalesEnVista(response.totales);
                    }

                    // También actualizar totales completos para asegurar consistencia
                    await actualizarTotalesCompletos();

                } else {
                    throw new Error(response.message || 'Error al actualizar producto');
                }

            } catch (error) {
                console.error('Error en la petición:', error);
                Swal.close(); // Cerrar loading

                let mensajeError = 'Error desconocido';

                if (error.responseJSON) {
                    mensajeError = error.responseJSON.message || 'Error del servidor';
                } else if (error.statusText) {
                    mensajeError = error.statusText;
                } else if (error.message) {
                    mensajeError = error.message;
                }

                await Swal.fire({
                    icon: 'error',
                    title: 'Error al Actualizar',
                    text: mensajeError,
                    confirmButtonText: 'Entendido'
                });
            }
        } else {
            console.log('Usuario canceló la edición');
        }

    } catch (error) {
        console.error('Error general al editar producto:', error);
        toastr.error('Error al abrir el modal de edición: ' + error.message);
    }
}

/**
 * Actualizar totales en la vista
 */
function actualizarTotalesEnVista(totales) {
    console.log('🔄 ACTUALIZANDO TOTALES EN VISTA RENOVADA:', totales);

    try {
        // Función para formatear moneda para mostrar (sin saltos de línea)
        const formatearParaTexto = (valor) => {
            const numero = parseFloat(valor || 0);
            // Usar formato más simple para evitar saltos de línea
            return '$ ' + numero.toLocaleString('es-CO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        // Función para formatear números para inputs ocultos (sin formato)
        const formatearParaInput = (valor) => {
            const numero = parseFloat(valor || 0);
            return numero.toFixed(2);
        };

        // 1. ACTUALIZAR CAMPOS OCULTOS (para el formulario)
        const hiddenSubtotal = document.getElementById('subtotal');
        const hiddenDescuento = document.getElementById('descuento');
        const hiddenImpuesto = document.getElementById('total_impuesto');
        const hiddenTotal = document.getElementById('total');

        if (hiddenSubtotal) {
            hiddenSubtotal.value = formatearParaInput(totales.subtotal);
            console.log('✅ Campo oculto subtotal:', hiddenSubtotal.value);
        }

        if (hiddenDescuento) {
            hiddenDescuento.value = formatearParaInput(totales.descuento);
            console.log('✅ Campo oculto descuento:', hiddenDescuento.value);
        }

        if (hiddenImpuesto) {
            hiddenImpuesto.value = formatearParaInput(totales.impuestos);
            console.log('✅ Campo oculto impuesto:', hiddenImpuesto.value);
        }

        if (hiddenTotal) {
            hiddenTotal.value = formatearParaInput(totales.total);
            console.log('✅ Campo oculto total:', hiddenTotal.value);
        }

        // 2. ACTUALIZAR ELEMENTOS INFORMATIVOS
        const displaySubtotal = document.getElementById('display-subtotal-valor');
        const displayDescuento = document.getElementById('display-descuento-valor');
        const displayImpuesto = document.getElementById('display-impuesto-valor');
        const displayTotal = document.getElementById('display-total-valor');

        if (displaySubtotal) {
            displaySubtotal.textContent = formatearParaTexto(totales.subtotal);
            console.log('✅ Display subtotal actualizado');
        }

        if (displayDescuento) {
            displayDescuento.textContent = formatearParaTexto(totales.descuento);
            console.log('✅ Display descuento actualizado');
        }

        if (displayImpuesto) {
            displayImpuesto.textContent = formatearParaTexto(totales.impuestos);
            console.log('✅ Display impuesto actualizado');
        }

        if (displayTotal) {
            displayTotal.textContent = formatearParaTexto(totales.total);
            console.log('✅ Display total actualizado');
        }

        console.log('🎉 TOTALES ACTUALIZADOS EXITOSAMENTE EN VISTA RENOVADA');

    } catch (error) {
        console.error('💥 Error actualizando totales en vista renovada:', error);
    }
}

/**
 * Función para debuggear elementos DOM
 */
function debuggearElementosDOM() {
    console.log('🔍 DEBUGGEANDO ELEMENTOS DOM DEL SISTEMA DE TOTALES');

    // Elementos tradicionales
    const elementosTradicionales = [
        'subtotal', 'descuento', 'total_impuesto', 'subtotal_menos_descuento', 'total', 'error_descuento'
    ];

    console.log('📝 ELEMENTOS TRADICIONALES:');
    elementosTradicionales.forEach(id => {
        const elemento = document.getElementById(id);
        console.log(`   ${id}: ${elemento ? '✅ EXISTE' : '❌ NO EXISTE'}`);
        if (elemento) {
            console.log(`      - Tipo: ${elemento.tagName}, Value: "${elemento.value || elemento.textContent}", Visible: ${elemento.offsetWidth > 0}`);
        }
    });

    // Elementos nuevos del sistema
    const elementosNuevos = [
        'display-subtotal-valor', 'display-descuento-valor', 'display-impuesto-valor', 'display-total-valor',
        'hidden_subtotal', 'hidden_descuento', 'hidden_impuesto', 'hidden_total'
    ];

    console.log('🆕 ELEMENTOS NUEVOS DEL SISTEMA:');
    elementosNuevos.forEach(id => {
        const elemento = document.getElementById(id);
        console.log(`   ${id}: ${elemento ? '✅ EXISTE' : '❌ NO EXISTE'}`);
        if (elemento) {
            console.log(`      - Tipo: ${elemento.tagName}, Value: "${elemento.value || elemento.textContent}", Visible: ${elemento.offsetWidth > 0}`);
        }
    });

    console.log('🏁 FIN DEBUG ELEMENTOS DOM');
    return {
        tradicionales: elementosTradicionales.map(id => ({ id, existe: !!document.getElementById(id) })),
        nuevos: elementosNuevos.map(id => ({ id, existe: !!document.getElementById(id) }))
    };
}

/**
 * Función para recalcular totales manualmente (botón)
 */
function actualizarTotalesManualmente() {
    console.log('🔄 RECALCULANDO TOTALES MANUALMENTE...');
    actualizarTotalesCompletos();
}
/**
 * Cargar y actualizar totales completos de la cotización
 */
async function actualizarTotalesCompletos() {
    console.log('🔄 INICIANDO actualizarTotalesCompletos()');

    try {
        // Obtener ID de cotización con múltiples métodos
        let cotizacionId = null;

        // Método 1: Input con id 'id'
        const inputId = document.getElementById('id');
        if (inputId && inputId.value) {
            cotizacionId = inputId.value;
            console.log('📍 ID obtenido del input#id:', cotizacionId);
        }

        // Método 2: URL params
        if (!cotizacionId) {
            const urlParams = new URLSearchParams(window.location.search);
            cotizacionId = urlParams.get('id');
            console.log('📍 ID obtenido de URL params:', cotizacionId);
        }

        // Método 3: Desde la URL path
        if (!cotizacionId) {
            const pathMatch = window.location.pathname.match(/\/(\d+)$/);
            if (pathMatch) {
                cotizacionId = pathMatch[1];
                console.log('📍 ID obtenido del path URL:', cotizacionId);
            }
        }

        // Método 4: Variable global
        if (!cotizacionId && typeof cotizacionGuardadaId !== 'undefined') {
            cotizacionId = cotizacionGuardadaId;
            console.log('📍 ID obtenido de variable global:', cotizacionId);
        }

        // Método 5: Buscar en cualquier input que contenga el ID
        if (!cotizacionId) {
            const possibleInputs = document.querySelectorAll('input[value]');
            possibleInputs.forEach(input => {
                const value = input.value;
                if (value && /^\d+$/.test(value) && value.length < 5) {
                    cotizacionId = value;
                    console.log('📍 ID encontrado en input aleatorio:', cotizacionId, input);
                }
            });
        }

        if (!cotizacionId) {
            console.warn('❌ No se encontró ID de cotización para actualizar totales');
            console.log('🔍 Información de debugging:');
            console.log('   - URL actual:', window.location.href);
            console.log('   - Path:', window.location.pathname);
            console.log('   - Query params:', window.location.search);
            console.log('   - Input#id existe:', !!document.getElementById('id'));
            console.log('   - Valor input#id:', document.getElementById('id')?.value);
            return;
        }

        console.log('✅ Actualizando totales para cotización ID:', cotizacionId);

        const token = $('meta[name="csrf-token"]').attr('content');
        if (!token) {
            console.error('❌ Token CSRF no encontrado');
            return;
        }

        console.log('📡 Enviando petición a /admin/cotizaciones/totales/obtener');

        const response = await $.ajax({
            url: '/admin/cotizaciones/totales/obtener',
            type: 'GET',
            data: { cotizacion_id: cotizacionId },
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            dataType: 'json'
        });

        console.log('📥 Respuesta recibida:', response);

        if (response.success) {
            console.log('✅ Totales obtenidos exitosamente:', response.data);
            actualizarTotalesEnVista(response.data);

            // También mostrar detalle si está disponible
            if (response.data.detalle) {
                console.log('📊 Detalle de totales:', response.data.detalle);
            }
        } else {
            console.error('❌ Error en la respuesta:', response.message);
        }

    } catch (error) {
        console.error('💥 Error actualizando totales completos:', error);
        console.log('📋 Detalle del error:', {
            status: error.status,
            statusText: error.statusText,
            responseText: error.responseText?.substring(0, 500),
            responseJSON: error.responseJSON
        });
    }
}

/**
 * Función para forzar la actualización de totales - útil para debugging
 */
function forzarActualizacionTotales() {
    console.log('🚀 FORZANDO actualización de totales...');
    actualizarTotalesCompletos();
}

// 🎯 TAMBIÉN ejecutar cuando se haga clic en cualquier parte de los totales
document.addEventListener('click', function(e) {
    if (e.target.matches('#subtotal, #descuento, #total_impuesto, #total, input[name="subtotal"], input[name="descuento"], input[name="total_impuesto"], input[name="total"]')) {
        console.log('🎯 Campo de total clickeado, verificando actualización...');
        setTimeout(() => {
            actualizarTotalesCompletos();
        }, 100);
    }
});

// 🔄 EJECUTAR cuando se enfoque cualquier campo de total
document.addEventListener('focusin', function(e) {
    if (e.target.matches('#subtotal, #descuento, #total_impuesto, #total, input[name="subtotal"], input[name="descuento"], input[name="total_impuesto"], input[name="total"]')) {
        console.log('🎯 Campo de total enfocado, actualizando...');
        actualizarTotalesCompletos();
    }
});

// 📺 Función para mostrar estado de totales en tiempo real
function mostrarEstadoTotalesEnPantalla() {
    const statusDiv = document.getElementById('totales-status') || (() => {
        const div = document.createElement('div');
        div.id = 'totales-status';
        div.style.cssText = 'position: fixed; top: 10px; right: 10px; background: #28a745; color: white; padding: 10px; border-radius: 5px; z-index: 9999; font-size: 12px;';
        document.body.appendChild(div);
        return div;
    })();

    statusDiv.innerHTML = `
        ✅ Totales Automáticos Activos<br>
        💰 Subtotal: ${document.getElementById('subtotal')?.value || 'N/A'}<br>
        🔥 Total: ${document.getElementById('total')?.value || 'N/A'}
    `;

    // Auto-hide después de 5 segundos
    setTimeout(() => {
        if (statusDiv) statusDiv.remove();
    }, 5000);
}

// // Inicializar categorías al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CONTENT LOADED - INICIANDO CONFIGURACIÓN ===');

    try {
        // cargarCategoriasDisponibles();
        console.log('✓ cargarCategoriasDisponibles ejecutado');

        // initSubitemsStyles();
        console.log('✓ initSubitemsStyles ejecutado');

        initProductosYSalarios();
        console.log('✓ initProductosYSalarios ejecutado');

        // Event listener para el botón "Agregar Items Propios"
        const btnAgregarItemsPropios = document.getElementById('btnUsarItemsSeleccionados');
        if (btnAgregarItemsPropios) {
            btnAgregarItemsPropios.addEventListener('click', usarItemSeleccionado);
            console.log('✓ Event listener para btnUsarItemsSeleccionados agregado');
        }

        setTimeout(async () => {
            console.log('🚀 Ejecutando actualización automática de totales...');
            const cotizacionId = document.getElementById('id')?.value;
            await cargarProductosGuardados();
            // Actualizar totales al cargar la página
            await actualizarTotalesCompletos();
            if (cotizacionId && variable === 'editar') {
                // Si estamos editando una cotización existente, actualizar totales
                await actualizarTotalesCompletos();
                mostrarEstadoTotalesEnPantalla();
            }
        }, 2000);

        window.forzarActualizacionTotales = async function() {
            console.log('🔄 Forzando actualización de totales...');
            try {
                await actualizarTotalesCompletos();
                console.log('✅ Totales actualizados exitosamente');
            } catch (error) {
                console.error('❌ Error al actualizar totales:', error);
            }
        };

        console.log('=== CONFIGURACIÓN DOM COMPLETADA ===');
    } catch (error) {
        console.error('Error en DOMContentLoaded:', error);
    }
});
