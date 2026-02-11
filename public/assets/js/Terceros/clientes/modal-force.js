// SCRIPT DE FUERZA BRUTA PARA MODAL - SIMPLIFICADO
$(document).ready(function() {
    console.log('💪 Script de Fuerza Bruta cargado - versión simplificada');

    // Función de limpieza completa
    window.limpiarTodoElModal = function() {
        console.log('🧹 Limpiando todo el modal...');

        try {
            // PRESERVAR valores críticos antes de limpiar
            const tercerotipo_preserved = $('#tercerotipo_id').val();
            const user_preserved = $('#user_id').val();

            console.log('🔒 PRESERVANDO en limpiarTodoElModal:', {
                tercerotipo_id: tercerotipo_preserved,
                user_id: user_preserved
            });

// Limpiar campos principales (excepto tipopersona_id para mantener la selección)
            const campos = [
                'id', 'tipoidentificacion_id', 'identificacion', 'dv',
                'nombres', 'apellidos', 'nombre_establecimiento', 'telefono', 'celular',
                'correo', 'correo_fe', 'ciudad_id', 'direccion', 'vendedor_id',
                'contacto_id', 'sucursal_id'
            ];

            campos.forEach(campo => {
                $('#' + campo).val('');
            });

            // Limpiar selects específicos (excepto tipopersona_id)
            $('#tipoidentificacion_id').val('').trigger('change');
            $('#ciudad_id').val('').trigger('change');
            $('#vendedor_id').val('').trigger('change');
            $('#pais_id').val('').trigger('change');
            $('#departamento_id').val('').trigger('change');

            // Limpiar campos de contacto
            const camposContacto = ['nombres', 'apellidos', 'telefono', 'celular', 'correo', 'ext', 'cargo'];
            camposContacto.forEach(campo => {
                $('#contacto_' + campo).val('');
            });

            // Limpiar campos de sucursal
            const camposSucursal = [
                'nombre_sucursal', 'telefono', 'celular', 'correo', 'direccion',
                'persona_contacto', 'ciudad_id', 'departamento_id', 'pais_id'
            ];
            camposSucursal.forEach(campo => {
                $('#sucursal_' + campo).val('');
            });

            // RESTAURAR valores críticos después de limpiar
            $('#tercerotipo_id').val(tercerotipo_preserved);
            $('#user_id').val(user_preserved);

            console.log('🔓 RESTAURADOS en limpiarTodoElModal:', {
                tercerotipo_id: $('#tercerotipo_id').val(),
                user_id: $('#user_id').val()
            });

            console.log('✅ Todos los campos limpiados');

        } catch (error) {
            console.log('⚠️ Error limpiando campos:', error);
        }
    };

    // Override simple y directo
    window.regCliForced = function() {
        console.log('💪 regCliForced() ejecutándose...');

        try {
            // Marcar que NO está en modo edición
            $('#ModalCliente').data('edit-mode', false);

            // LIMPIAR PRIMERO, ANTES DE ABRIR
            window.limpiarTodoElModal();

            // Limpiar validaciones si existen las funciones
            if (typeof limpiarValidaciones === 'function') {
                limpiarValidaciones();
            }
            if (typeof limpiarValidacionesSucursal === 'function') {
                limpiarValidacionesSucursal();
            }
            if (typeof limpiarValidacionesContacto === 'function') {
                limpiarValidacionesContacto();
            }

            // Ejecutar resetModal si existe
            if (typeof resetModal === 'function') {
                resetModal();
            }

            // Ahora abrir el modal
            const $modal = $('#ModalCliente');
            console.log('Modal jQuery object:', $modal.length);

            $modal.modal('show');
            console.log('✅ Modal show ejecutado');

            // Configurar título
            $('#exampleModalLabel').html('<i class="fas fa-user-plus mr-2"></i>Registrar Cliente');

            // Cargar tablas vacías
            if (typeof CargarSucursales === 'function') {
                CargarSucursales(0);
            }
            if (typeof CargarContactos === 'function') {
                CargarContactos(0);
            }

            // Configurar evento para tipo de persona
            setTimeout(() => {
                $('#tipopersona_id').off('change.modalForce').on('change.modalForce', function() {
                    console.log('🔄 Tipo de persona cambió - ejecutando actualizarValidaciones');
                    if (typeof actualizarValidaciones === 'function') {
                        actualizarValidaciones();
                    }
                });

                // Ejecutar una vez inicialmente
                if (typeof actualizarValidaciones === 'function') {
                    actualizarValidaciones();
                }
            }, 500);

        } catch (error) {
            console.error('❌ Error en regCliForced:', error);
        }
    };

    // Reemplazar regCli completamente
    window.regCli = function() {
        console.log('🔄 regCli() override activado - con limpieza completa');
        window.regCliForced();
    };

    console.log('💪 Override completo de regCli aplicado');
});
