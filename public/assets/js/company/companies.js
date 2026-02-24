/**
 * Companies Management JavaScript
 * Gestión completa de empresas con licencias y personalización
 * @author Panel Administrativo
 * @version 1.0
 */

$(document).ready(function() {
    console.log('hola');

    initializeCompaniesDataTable();
    initializeFormValidation();
    initializeEventHandlers();
});

// ========================================
// DATATABLE INITIALIZATION
// ========================================
let companiesTable;

function initializeCompaniesDataTable() {
    console.log('Initializing companies data table...');

    companiesTable = $('#companies-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/admin/admin.companies.index',
            error: function(xhr, error, code) {
                console.error('Error al cargar datos:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar datos',
                    html: 'No se pudieron cargar las empresas.<br><br>' +
                          '<strong>Posibles causas:</strong><br>' +
                          '• La tabla "companies" no existe<br>' +
                          '• No se ha ejecutado el script SQL<br>' +
                          '• Problema de conexión a la base de datos',
                    confirmButtonText: 'Entendido'
                });
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'nit', name: 'nit' },
            { data: 'email', name: 'email' },
            { data: 'status', name: 'status', orderable: false },
            { data: 'license_info', name: 'license_info', orderable: false },
            { data: 'users_count', name: 'users_count', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]],
        language: {
            url: '/assets/js/spanish.json'
        },
        responsive: true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'ltr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            }
        ]
    });
}

// ========================================
// COMPANY CRUD OPERATIONS
// ========================================

/**
 * Crear nueva empresa
 */
function createCompany() {
    window.location.href = '/admin/admin.companies.create';
}

/**
 * Ver detalles de empresa
 * @param {number} id
 */
function showCompany(id) {
    window.location.href = `/admin/admin.companies.show/${id}`;
}

/**
 * Editar empresa
 * @param {number} id
 */
function editCompany(id) {
    window.location.href = `/admin/admin.companies.edit/${id}`;
}

/**
 * Eliminar empresa
 * @param {number} id
 * @param {string} name
 */
function deleteCompany(id, name) {
    Swal.fire({
        title: '¿Estás seguro?',
        html: `Se eliminará la empresa: <strong>${name}</strong><br><br>
               <div class="alert alert-danger">
                   <i class="fas fa-exclamation-triangle"></i>
                   <strong>¡Atención!</strong> Esta acción también eliminará:
                   <ul class="text-left mt-2">
                       <li>Todos los usuarios de la empresa</li>
                       <li>Cotizaciones asociadas</li>
                       <li>Productos y configuraciones</li>
                   </ul>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: `/admin/admin.companies.destroy/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).then(response => {
                return response;
            }).catch(error => {
                console.error('Error:', error);
                Swal.showValidationMessage('Error al eliminar la empresa');
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            showAlert('success', '¡Eliminada!', 'La empresa ha sido eliminada correctamente.');
            companiesTable.ajax.reload();
        }
    });
}

// ========================================
// LICENSE MANAGEMENT
// ========================================

/**
 * Renovar licencia de empresa
 * @param {number} id
 * @param {string} name
 */
function renewLicense(id, name) {
    Swal.fire({
        title: 'Renovar Licencia',
        html: `
            <div class="text-left">
                <p><strong>Empresa:</strong> ${name}</p>
                <div class="form-group">
                    <label for="license_type">Tipo de Licencia:</label>
                    <select id="license_type" class="form-control">
                        <option value="trial">Trial (30 días)</option>
                        <option value="standard">Standard (1 año)</option>
                        <option value="premium">Premium (1 año)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="license_months">Duración (meses):</label>
                    <input type="number" id="license_months" class="form-control" value="12" min="1" max="60">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-sync-alt"></i> Renovar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const license_type = $('#license_type').val();
            const license_months = $('#license_months').val();

            if (!license_type || !license_months) {
                Swal.showValidationMessage('Todos los campos son requeridos');
                return false;
            }

            return $.ajax({
                url: `/admin/admin.companies.renew-license/${id}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    license_type: license_type,
                    license_months: license_months
                }
            }).then(response => {
                return response;
            }).catch(error => {
                console.error('Error:', error);
                let message = 'Error al renovar la licencia';
                if (error.responseJSON && error.responseJSON.message) {
                    message = error.responseJSON.message;
                }
                Swal.showValidationMessage(message);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            showAlert('success', '¡Renovada!', 'La licencia ha sido renovada correctamente.');
            companiesTable.ajax.reload();
        }
    });
}

/**
 * Cambiar estado de empresa (activa/inactiva)
 * @param {number} id
 * @param {string} name
 * @param {boolean} isActive
 */
function toggleStatus(id, name, isActive) {
    const action = isActive ? 'desactivar' : 'activar';
    const icon = isActive ? 'pause' : 'play';
    const color = isActive ? '#ffc107' : '#28a745';

    Swal.fire({
        title: `¿${action.charAt(0).toUpperCase() + action.slice(1)} empresa?`,
        text: `Se ${action}á la empresa: ${name}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: color,
        cancelButtonColor: '#6c757d',
        confirmButtonText: `<i class="fas fa-${icon}"></i> ${action.charAt(0).toUpperCase() + action.slice(1)}`,
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: `/admin/admin.companies.toggle-status/${id}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).then(response => {
                return response;
            }).catch(error => {
                console.error('Error:', error);
                Swal.showValidationMessage(`Error al ${action} la empresa`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            const message = isActive ? 'desactivada' : 'activada';
            showAlert('success', '¡Actualizada!', `La empresa ha sido ${message} correctamente.`);
            companiesTable.ajax.reload();
        }
    });
}

// ========================================
// FORM VALIDATION AND HANDLERS
// ========================================

function initializeFormValidation() {
    // Validación para formulario de crear/editar empresa
    if ($('#company-form').length) {
        $('#company-form').validate({
            rules: {
                name: {
                    required: true,
                    minlength: 3,
                    maxlength: 255
                },
                nit: {
                    required: true,
                    minlength: 5,
                    maxlength: 20
                },
                email: {
                    required: true,
                    email: true,
                    maxlength: 255
                },
                phone: {
                    maxlength: 20
                },
                address: {
                    maxlength: 500
                },
                license_type: {
                    required: true
                },
                max_users: {
                    required: true,
                    min: 1,
                    max: 1000
                },
                primary_color: {
                    required: true
                },
                secondary_color: {
                    required: true
                }
            },
            messages: {
                name: {
                    required: 'El nombre de la empresa es requerido',
                    minlength: 'El nombre debe tener al menos 3 caracteres',
                    maxlength: 'El nombre no puede exceder 255 caracteres'
                },
                nit: {
                    required: 'El NIT es requerido',
                    minlength: 'El NIT debe tener al menos 5 caracteres',
                    maxlength: 'El NIT no puede exceder 20 caracteres'
                },
                email: {
                    required: 'El email es requerido',
                    email: 'Ingrese un email válido',
                    maxlength: 'El email no puede exceder 255 caracteres'
                },
                phone: {
                    maxlength: 'El teléfono no puede exceder 20 caracteres'
                },
                address: {
                    maxlength: 'La dirección no puede exceder 500 caracteres'
                },
                license_type: {
                    required: 'Seleccione un tipo de licencia'
                },
                max_users: {
                    required: 'El número máximo de usuarios es requerido',
                    min: 'Debe permitir al menos 1 usuario',
                    max: 'No puede exceder 1000 usuarios'
                },
                primary_color: {
                    required: 'Seleccione un color primario'
                },
                secondary_color: {
                    required: 'Seleccione un color secundario'
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                submitCompanyForm(form);
            }
        });
    }
}

function initializeEventHandlers() {
    // Manejo de vista previa de logo
    $('#logo').on('change', function() {
        previewLogo(this);
    });

    // Actualizar configuración al cambiar tipo de licencia
    $('#license_type').on('change', function() {
        updateLicenseConfig($(this).val());
    });

    // Vista previa de colores
    $('#primary_color, #secondary_color').on('change', function() {
        previewColors();
    });

    // Copiar configuraciones de otra empresa
    $('#copy_from_company').on('change', function() {
        if ($(this).val()) {
            copyCompanySettings($(this).val());
        }
    });
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

/**
 * Vista previa del logo
 * @param {HTMLInputElement} input
 */
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#logo-preview').attr('src', e.target.result).show();
            $('#logo-current').hide();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Actualizar configuración según tipo de licencia
 * @param {string} licenseType
 */
function updateLicenseConfig(licenseType) {
    const configs = {
        trial: {
            max_users: 3,
            months: 1
        },
        standard: {
            max_users: 25,
            months: 12
        },
        premium: {
            max_users: 100,
            months: 12
        }
    };

    if (configs[licenseType]) {
        $('#max_users').val(configs[licenseType].max_users);

        // Actualizar fecha de expiración
        const expirationDate = new Date();
        expirationDate.setMonth(expirationDate.getMonth() + configs[licenseType].months);
        $('#license_expires_at').val(expirationDate.toISOString().split('T')[0]);
    }
}

/**
 * Vista previa de colores
 */
function previewColors() {
    const primaryColor = $('#primary_color').val();
    const secondaryColor = $('#secondary_color').val();

    if (primaryColor) {
        $('#color-preview-primary').css('background-color', primaryColor);
    }
    if (secondaryColor) {
        $('#color-preview-secondary').css('background-color', secondaryColor);
    }
}

/**
 * Copiar configuraciones de otra empresa
 * @param {number} companyId
 */
function copyCompanySettings(companyId) {
    $.ajax({
        url: `/admin/admin.companies.show/${companyId}`,
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    }).done(function(response) {
        if (response.company) {
            const company = response.company;
            $('#primary_color').val(company.primary_color);
            $('#secondary_color').val(company.secondary_color);

            if (company.settings) {
                $('#timezone').val(company.settings.timezone || '');
                $('#currency').val(company.settings.currency || '');
                $('#date_format').val(company.settings.date_format || '');
            }

            previewColors();
            showAlert('success', 'Copiado', 'Configuraciones copiadas correctamente');
        }
    }).fail(function(error) {
        console.error('Error al copiar configuraciones:', error);
        showAlert('error', 'Error', 'No se pudieron copiar las configuraciones');
    });
}

/**
 * Enviar formulario de empresa
 * @param {HTMLFormElement} form
 */
function submitCompanyForm(form) {
    const formData = new FormData(form);
    const isEdit = $(form).data('method') === 'PUT';
    const url = isEdit ? $(form).attr('action') : '/admin/admin.companies.store';
    const method = isEdit ? 'PUT' : 'POST';

    // Mostrar loading
    Swal.fire({
        title: 'Procesando...',
        text: 'Guardando información de la empresa',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Si es edición, agregar _method para Laravel
    if (isEdit) {
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    }).done(function(response) {
        Swal.close();

        if (response.success) {
            showAlert('success', '¡Éxito!', response.message || 'Empresa guardada correctamente');

            // Redirigir después de un breve delay
            setTimeout(() => {
                window.location.href = '/admin/admin.companies.index';
            }, 1500);
        }
    }).fail(function(xhr) {
        Swal.close();

        if (xhr.status === 422) {
            // Errores de validación
            const errors = xhr.responseJSON.errors;
            let errorMessage = 'Por favor corrija los siguientes errores:\n';

            Object.keys(errors).forEach(key => {
                errorMessage += `• ${errors[key][0]}\n`;
                // Marcar campo con error
                $(`[name="${key}"]`).addClass('is-invalid');
            });

            showAlert('error', 'Errores de validación', errorMessage);
        } else {
            const message = xhr.responseJSON?.message || 'Error al guardar la empresa';
            showAlert('error', 'Error', message);
        }
    });
}

/**
 * Mostrar alerta con SweetAlert2
 * @param {string} type - success, error, warning, info
 * @param {string} title
 * @param {string} text
 */
function showAlert(type, title, text) {
    const icons = {
        success: 'success',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };

    Swal.fire({
        icon: icons[type] || 'info',
        title: title,
        text: text,
        confirmButtonColor: '#007bff',
        timer: type === 'success' ? 3000 : null,
        timerProgressBar: type === 'success'
    });
}

// ========================================
// GLOBAL FUNCTIONS (accesibles desde HTML)
// ========================================
window.createCompany = createCompany;
window.showCompany = showCompany;
window.editCompany = editCompany;
window.deleteCompany = deleteCompany;
window.renewLicense = renewLicense;
window.toggleStatus = toggleStatus;
window.previewLogo = previewLogo;
