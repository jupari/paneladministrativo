// Utilidad para importar registros desde un archivo Excel y agregarlos a la tabla de Costos (DataTable o Tabulator)
// Requiere: SheetJS (xlsx), SweetAlert2, y que la tabla esté inicializada como tablaCostosDT o tablaCostosTabulator

async function importarCostosDesdeExcel() {
    // Crear input file oculto
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.xlsx,.xls';
    input.style.display = 'none';
    document.body.appendChild(input);

    input.onchange = async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        try {
            const data = await file.arrayBuffer();
            const workbook = XLSX.read(data, { type: 'array' });
            const sheet = workbook.Sheets[workbook.SheetNames[0]];
            const rows = XLSX.utils.sheet_to_json(sheet, { defval: '' });
            if (!rows.length) throw new Error('El archivo está vacío o no tiene datos.');

            // Adaptar los nombres de columnas de Excel a los campos internos de Tabulator
            // Excel esperado: Categoria (nombre), Item_Propio (código), Nombre (item_nombre), Unidad_Medida (sigla), Costo_dia, Costo_hora, Costo_Unitario
            // Mapear nombre de categoría a id y validar item
            const categoriasArr = typeof window.categorias !== 'undefined' ? window.categorias : [];
            const itemsArr = typeof window.itemsPropios !== 'undefined' ? window.itemsPropios : [];
            const buscarCategoria = (nombre) => {
                if (!nombre) return null;
                return categoriasArr.find(c => c.nombre && c.nombre.trim().toUpperCase() === String(nombre).trim().toUpperCase()) || null;
            };
            const buscarItem = (codigo) => {
                if (!codigo) return null;
                return itemsArr.find(i => i.codigo && String(i.codigo).trim().toUpperCase() === String(codigo).trim().toUpperCase()) || null;
            };

            console.log('📥 Filas importadas desde Excel:', rows);
            const filasImportadas = rows.map(r => {
                const cat = buscarCategoria(r['Categoria'] || r['categoria']);
                const item = buscarItem(r['Item_Propio'] || r['item_propio']);
                return {
                    categoria_id: cat ? cat.id : '',
                    item: item ? item.codigo : (r['Item_Propio'] || r['item_propio'] || ''),
                    item_nombre: item ? item.nombre : (r['Nombre'] || r['nombre'] || ''),
                    unidad_medida: item ? item.unidad_medida : (r['Unidad_Medida'] || r['unidad_medida'] || ''),
                    costo_dia: r['Costo_dia'] || r['costo_dia'] || '',
                    costo_hora: r['Costo_hora'] || r['costo_hora'] || '',
                    costo_unitario: r['Costo_Unitario'] || r['costo_unitario'] || ''
                };
            });

            // Agregar a la tabla (ajusta según tu tabla: DataTable o Tabulator)
            if (window.tablaCostosDT) {
                window.tablaCostosDT.rows.add(filasImportadas).draw(false);
            } else if (window.tablaCostos) {
                window.tablaCostos.addData(filasImportadas, true);
            } else {
                throw new Error('No se encontró la tabla de Costos.');
            }
            console.log('✅ Filas importadas:', filasImportadas);
            Swal.fire('Importación exitosa', `Se importaron ${filasImportadas.length} registros.`, 'success');
        } catch (err) {
            Swal.fire('Error', err.message || 'No se pudo importar el archivo.', 'error');
        } finally {
            input.remove();
        }
    };
    input.click();
}

// Para usar: agrega un botón en la pestaña de Costos que llame a importarCostosDesdeExcel()
// <button onclick="importarCostosDesdeExcel()">Importar Excel</button>
// Asegúrate de tener cargado SheetJS (xlsx.full.min.js) y SweetAlert2 en tu plantilla.
