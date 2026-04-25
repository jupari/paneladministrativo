// Copiar filas de un cargo origen a uno o varios cargos destino en DataTable
async function copiarFilaACargos(rowIdx) {
    if (!tablaNovedadesDT) return;
    let rowData = typeof rowIdx === 'object' && rowIdx.getData ? rowIdx.getData() : tablaNovedadesDT.row(rowIdx).data();
    const cargoOrigen = rowData?.cargo_id;
    const todasFilas = tablaNovedadesDT.rows().data().toArray();

    // Construir opciones de cargos (excluye el origen)
    const opciones = (Array.isArray(cargos) ? cargos : Object.entries(cargos || {}).map(([id, nombre]) => ({ id, nombre })))
        .filter(o => String(o.id) !== String(cargoOrigen));

    const selectHtml = `
        <select id="copiar_cargos" class="swal2-select" multiple style="height:180px">
            ${opciones.map(o => `<option value="${o.id}">${o.nombre}</option>`).join('')}
        </select>
        <small class="text-muted">Copiará todas las filas del cargo origen al/los cargos destino.</small>
    `;

    const res = await Swal.fire({
        title: 'Copiar cargo completo',
        html: selectHtml,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Copiar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusConfirm: false,
        didOpen: () => {
            const el = Swal.getHtmlContainer().querySelector('#copiar_cargos');
            if (el) el.focus();
        },
        preConfirm: () => {
            const el = document.getElementById('copiar_cargos');
            const values = Array.from(el?.selectedOptions || []).map(o => o.value);
            if (!values.length) {
                Swal.showValidationMessage('Selecciona al menos un cargo destino');
            }
            return values;
        }
    });

    if (res.dismiss || !res.value?.length) return;
    const cargosDestino = res.value;

    // Filas base: todas las que pertenezcan al cargo origen
    const filasOrigen = todasFilas.filter(f => String(f.cargo_id) === String(cargoOrigen));
    if (!filasOrigen.length) {
        await Swal.fire('Sin filas', 'No hay filas para copiar desde el cargo origen.', 'info');
        return;
    }

    const nuevasFilas = [];
    let saltadas = 0;

    cargosDestino.forEach(cId => {
        filasOrigen.forEach(fila => {
            // Evitar duplicar si ya existe misma combinacion cargo+novedad_detalle_id
            const existe = todasFilas.some(r => String(r.cargo_id) === String(cId) && String(r.novedad_detalle_id) === String(fila.novedad_detalle_id));
            if (existe) {
                saltadas++;
                return;
            }
            nuevasFilas.push({
                // __rid: genRidN(), // Si tienes generador de id, descomenta
                categoria_id: fila.categoria_id ?? null,
                cargo_id: cId,
                novedad_detalle_id: fila.novedad_detalle_id ?? null,
                valor_porcentaje: fila.valor_porcentaje ?? '',
                valor_admon: Number(fila.valor_admon) === 1 ? 1 : 0,
                valor_obra: Number(fila.valor_obra) === 1 ? 1 : 0
            });
        });
    });

    if (nuevasFilas.length) {
        tablaNovedadesDT.rows.add(nuevasFilas).draw(false);
    }
    const mensaje = nuevasFilas.length
        ? `Se copiaron ${nuevasFilas.length} fila(s).` + (saltadas ? ` ${saltadas} fila(s) ya existían y se omitieron.` : '')
        : 'No se copiaron filas porque todas ya existían en los cargos destino.';
    await Swal.fire('Copia completada', mensaje, 'success');
}
let tablaNovedadesDT = null;

// Utilidades para selects
function renderSelect(options, selected, nameAttr) {
    let html = `<select class="form-control form-control-sm dt-editable"${nameAttr ? ` name=\"${nameAttr}\"` : ''}>`;
    html += '<option value=""></option>';
    // Si options es un array de objetos (id, nombre)
    if (Array.isArray(options)) {
        for (const opt of options) {
            // Solo seleccionar si selected es estrictamente igual (no 0, null, undefined)
            html += `<option value="${opt.id}"${(selected !== undefined && selected !== null && selected !== '' && opt.id == selected) ? ' selected' : ''}>${opt.nombre}</option>`;
        }
    } else {
        for (const [val, label] of Object.entries(options)) {
            html += `<option value="${val}"${(selected !== undefined && selected !== null && selected !== '' && val == selected) ? ' selected' : ''}>${label}</option>`;
        }
    }
    html += '</select>';
    return html;
}

function renderInput(value) {
    return `<input type="text" class="form-control form-control-sm dt-editable text-end" value="${value ?? ''}">`;
}

function renderSwitch(value) {
    return `<div class="d-flex justify-content-center"><input type="checkbox" class="dt-editable" ${value == 1 ? 'checked' : ''}></div>`;
}

// Mostrar la DataTable de novedades al renderizar la página
$(document).ready(function() { CargarNovedadesDT(); });

function CargarNovedadesDT() {
    if ( $.fn.DataTable.isDataTable('#tabla-parametrizacion-dt') ) {
        $('#tabla-parametrizacion-dt').DataTable().destroy();
        $('#tabla-parametrizacion-dt tbody').empty();
    }
    // Usar la variable global parametrizacion para poblar la tabla
    let data = Array.isArray(parametrizacion) ? parametrizacion : [];
    // Preparar arrays para selects de categorías y novedades
    let categoriasArr = [];
    if (typeof categorias === 'object' && !Array.isArray(categorias)) {
        for (const [id, nombre] of Object.entries(categorias)) {
            if (nombre === 'NOMINA') categoriasArr.push({id, nombre});
        }
    } else if (Array.isArray(categorias)) {
        categoriasArr = categorias.filter(c => c.nombre === 'NOMINA');
    }
    let novedadesArr = [];
    if (typeof novedadesCombo === 'object' && !Array.isArray(novedadesCombo)) {
        for (const [id, nombre] of Object.entries(novedadesCombo)) {
            novedadesArr.push({id, nombre});
        }
    } else if (Array.isArray(novedadesCombo)) {
        novedadesArr = novedadesCombo;
    }

    // Ordenar cargos alfabéticamente
    let cargosArr = [];
    if (typeof cargos === 'object' && !Array.isArray(cargos)) {
        for (const [id, nombre] of Object.entries(cargos)) {
            cargosArr.push({id, nombre});
        }
        cargosArr.sort((a, b) => a.nombre.localeCompare(b.nombre));
    } else if (Array.isArray(cargos)) {
        cargosArr = [...cargos].sort((a, b) => a.nombre.localeCompare(b.nombre));
    }
    tablaNovedadesDT = $('#tabla-parametrizacion-dt').DataTable({
        data: data,
        columns: [
            { data: 'categoria_id', name: 'categoria_id', render: function(data, type, row, meta) {
                if (type === 'display') return renderSelect(categoriasArr, data, 'categoria_id');
                let found = categoriasArr.find(c => c.id == data);
                return found ? found.nombre : '';
            } },
            { data: 'cargo_id', name: 'cargo_id', render: function(data, type, row, meta) {
                // Si es display y es un registro nuevo (data vacío, null, undefined o 0), no seleccionar ninguno
                let selected = (data === undefined || data === null || data === '' || data === 0) ? '' : data;
                if (type === 'display') return renderSelect(cargosArr, selected, 'cargo_id');
                let found = cargosArr.find(c => c.id == data);
                return found ? found.nombre : '';
            } },
            { data: 'novedad_detalle_id', name: 'novedad_detalle_id', render: function(data, type, row, meta) {
                if (type === 'display') return renderSelect(novedadesArr, data, 'novedad_detalle_id');
                let found = novedadesArr.find(n => n.id == data);
                return found ? found.nombre : '';
            } },
            { data: 'valor_admon', name: 'valor_admon', render: function(data, type, row, meta) {
                if (type === 'display') return renderSwitch(data);
                return data == 1 ? '✔️' : '';
            } },
            { data: 'valor_obra', name: 'valor_obra', render: function(data, type, row, meta) {
                if (type === 'display') return renderSwitch(data);
                return data == 1 ? '✔️' : '';
            } },
            { data: 'valor_porcentaje', name: 'valor_porcentaje', render: function(data, type, row, meta) {
                if (type === 'display') return renderInput(data);
                return data;
            } },
            { data: null, orderable: false, name: 'acciones', render: function(data, type, row, meta) {
                return `<div class="d-flex justify-content-center">
                    <button class="btn btn-danger btn-xs" onclick="eliminarFilaNovedadesDT(${meta.row})" title="Eliminar fila">
                        <i class="fa fa-trash"></i>
                    </button>
                    <button class="btn btn-primary btn-xs ms-1" onclick="copiarFilaACargos(${meta.row})" title="Copiar a cargos">
                        <i class="fa fa-copy"></i>
                    </button>
                </div>`;
            }}
        ],
        paging: true,
        searching: true,
        ordering: true,
        responsive: true,
        language: { url: '/assets/js/spanish.json' },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Exportar a Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: function(idx, data, node) {
                        // Excluye la columna de acciones (última)
                        return idx !== tablaNovedadesDT.columns().nodes().length - 1;
                    },
                    format: {
                        body: function (data, row, column, node) {
                            // Si es un input, extraer el valor (para valor_porcentaje)
                            if (typeof data === 'string' && data.indexOf('input') !== -1) {
                                // Extraer value del input manualmente
                                let match = data.match(/value=["']?([^"'> ]+)["']?/);
                                if (match && match[1] !== undefined) return match[1];
                            }
                            // Si es un checkbox, exportar 1 si está checked, 0 si no
                            if ($(data).find('input[type="checkbox"]').length) {
                                return $(data).find('input[type="checkbox"]').is(':checked') ? '1' : '0';
                            }
                            // Si es un select, exportar el texto seleccionado
                            if ($(data).find('select').length) {
                                return $(data).find('select option:selected').text();
                            }
                            // Si es un div centrado (checkbox), extraer el valor del input
                            if ($(data).is('div')) {
                                let $chk = $(data).find('input[type="checkbox"]');
                                if ($chk.length) return $chk.is(':checked') ? '1' : '0';
                            }
                            // Si es texto plano
                            if ($(data).text && $(data).text().trim() !== '') return $(data).text();
                            // Si no, devolver el valor tal cual
                            return data;
                        }
                    }
                }
            }
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).find('.dt-editable').on('change', function() {
                let colIdx = $(this).closest('td').index();
                let field = tablaNovedadesDT.column(colIdx).dataSrc();
                let val = $(this).is(':checkbox') ? ($(this).is(':checked') ? 1 : 0) : $(this).val();
                // Si cambia la novedad_detalle_id, limpiar/adaptar valores
                if (field === 'novedad_detalle_id') {
                    // Limpiar los valores de valor_admon y valor_obra en modelo y DOM
                    let idxAdmon = tablaNovedadesDT.column('valor_admon:name').index();
                    let idxObra = tablaNovedadesDT.column('valor_obra:name').index();
                    let idxValor = tablaNovedadesDT.column('valor_porcentaje:name').index();
                    tablaNovedadesDT.cell(dataIndex, idxAdmon).data(0);
                    tablaNovedadesDT.cell(dataIndex, idxObra).data(0);
                    tablaNovedadesDT.cell(dataIndex, idxValor).data('');
                    // Desmarcar checkboxes en el DOM
                    $(row).find('td').eq(idxAdmon).find('input[type="checkbox"]').prop('checked', false);
                    $(row).find('td').eq(idxObra).find('input[type="checkbox"]').prop('checked', false);
                    // Limpiar input valor_porcentaje en el DOM
                    $(row).find('td').eq(idxValor).find('input').val('');
                    // Forzar sincronización
                    tablaNovedadesDT.row(dataIndex).invalidate();
                }
                tablaNovedadesDT.cell(dataIndex, colIdx).data(val);
            });
        }
    });

    // Delegated event para checkboxes (valor_admon y valor_obra)
    $('#tabla-parametrizacion-dt tbody').off('change', 'input[type="checkbox"]').on('change', 'input[type="checkbox"]', function() {
        let $td = $(this).closest('td');
        let $row = $(this).closest('tr');
        let colIdx = $td.index();
        let dataIdx = tablaNovedadesDT.row($row).index();
        let idxAdmon = tablaNovedadesDT.column('valor_admon:name').index();
        let idxObra = tablaNovedadesDT.column('valor_obra:name').index();
        let idxValor = tablaNovedadesDT.column('valor_porcentaje:name').index();
        // Tomar SOLO el valor del select novedad_detalle_id en la fila
        let novedadId = $row.find('select[name="novedad_detalle_id"]').val() || tablaNovedadesDT.row($row).data().novedad_detalle_id;
        // Referencias a los checkboxes
        let $chkAdmon = $row.find('td').eq(idxAdmon).find('input[type="checkbox"]');
        let $chkObra = $row.find('td').eq(idxObra).find('input[type="checkbox"]');
        let $inputValor = $row.find('td').eq(idxValor).find('input');

        // Si se marca uno, bloquear el otro
        if (colIdx === idxAdmon && $(this).is(':checked')) {
            $chkObra.prop('disabled', true);
            if (novedadId) {
                $.get('/admin/admin.novedaddetalle.show/' + novedadId, function(resp) {
                    if (resp && resp.valor_admon !== undefined) {
                        tablaNovedadesDT.cell(dataIdx, colIdx).data(1);
                        tablaNovedadesDT.cell(dataIdx, idxValor).data(resp.valor_admon);
                        $inputValor.val(resp.valor_admon);
                    }
                });
            }
        } else if (colIdx === idxObra && $(this).is(':checked')) {
            $chkAdmon.prop('disabled', true);
            if (novedadId) {
                $.get('/admin/admin.novedaddetalle.show/' + novedadId, function(resp) {
                    if (resp && resp.valor_operativo !== undefined) {
                        tablaNovedadesDT.cell(dataIdx, colIdx).data(1);
                        tablaNovedadesDT.cell(dataIdx, idxValor).data(resp.valor_operativo);
                        $inputValor.val(resp.valor_operativo);
                    }
                });
            }
        }

        // Si se desmarca cualquiera, habilitar ambos y poner valor en cero
        if ((colIdx === idxAdmon || colIdx === idxObra) && !$(this).is(':checked')) {
            $chkAdmon.prop('disabled', false);
            $chkObra.prop('disabled', false);
            tablaNovedadesDT.cell(dataIdx, idxValor).data(0);
            $inputValor.val(0);
        }
    });
}

function agregarFilaNovedadesDT() {
    if (!tablaNovedadesDT) CargarNovedadesDT();
    tablaNovedadesDT.row.add({
        categoria_id: '',
        cargo_id: '', // No seleccionar ninguno por defecto
        novedad_detalle_id: '',
        valor_porcentaje: '',
        valor_admon: 0,
        valor_obra: 0
    }).draw(false);
}


function eliminarFilaNovedadesDT(idx) {
    if (!tablaNovedadesDT) return;
    let rowData = tablaNovedadesDT.row(idx).data();
    if (!rowData || !rowData.id) {
        toastr.error('No se encontró el ID del registro para eliminar.');
        return;
    }
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción eliminará el registro de forma permanente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: '/admin/admin.parametrizacion.deletenovedad/' + rowData.id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    tablaNovedadesDT.row(idx).remove().draw(false);
                    toastr.success('Registro eliminado correctamente');
                },
                error: function(err) {
                    toastr.error('Error al eliminar el registro');
                }
            });
        }
    });
}

function saveDataNovedadesDT() {
    if (!tablaNovedadesDT) return;

    // Índices de columnas por nombre para no depender del orden
    const idxCat     = tablaNovedadesDT.column('categoria_id:name').index();
    const idxCargo   = tablaNovedadesDT.column('cargo_id:name').index();
    const idxNovedad = tablaNovedadesDT.column('novedad_detalle_id:name').index();
    const idxAdmon   = tablaNovedadesDT.column('valor_admon:name').index();
    const idxObra    = tablaNovedadesDT.column('valor_obra:name').index();
    const idxValor   = tablaNovedadesDT.column('valor_porcentaje:name').index();

    const payload = [];

    tablaNovedadesDT.rows().every(function () {
        const rowData = this.data();
        const $tr = $(this.node());

        // Leer siempre desde el DOM para capturar valores que aún no dispararon
        // el evento 'change' (p. ej. valor_porcentaje recién escrito sin hacer blur).
        const selVal  = idx => $tr.find('td').eq(idx).find('select').val()                       ?? rowData[tablaNovedadesDT.column(idx).dataSrc()];
        const inpVal  = idx => $tr.find('td').eq(idx).find('input[type="text"]').val()            ?? rowData[tablaNovedadesDT.column(idx).dataSrc()];
        const chkVal  = idx => ($tr.find('td').eq(idx).find('input[type="checkbox"]').is(':checked') ? 1 : 0);

        payload.push({
            id:                 rowData.id ?? null,
            categoria_id:       selVal(idxCat),
            cargo_id:           selVal(idxCargo),
            novedad_detalle_id: selVal(idxNovedad),
            valor_porcentaje:   inpVal(idxValor),
            valor_admon:        chkVal(idxAdmon),
            valor_obra:         chkVal(idxObra),
        });
    });

    // Validación básica
    const invalida = payload.find(r => !r.categoria_id || !r.cargo_id || !r.novedad_detalle_id);
    if (invalida) {
        toastr.warning('Hay filas incompletas. Complete Categoría, Cargo y Novedad antes de guardar.');
        return;
    }

    $.ajax({
        url: '/admin/admin.parametrizacion.storenovedades',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        contentType: 'application/json',
        data: JSON.stringify({ parametrizacion: payload }),
        success: function (res) {
            toastr.success('Guardado correctamente');
            // Recargar la página para obtener los IDs de los registros recién creados
            window.location.reload();
        },
        error: function (err) {
            let msg = err.responseJSON?.message || 'Error al guardar';
            toastr.error(msg);
        }
    });
}

// Inicializar al cargar la pestaña (opcional)
// $(document).ready(function() { CargarNovedadesDT(); });
