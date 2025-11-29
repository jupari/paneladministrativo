// mapas id=>nombre para editores "list"
const categoriasMap = categorias.reduce((a,c)=> (a[c.id]=c.nombre, a), {});
// Para el editor list: guarda la SIGLA, muestra "SIGLA - Nombre"
const unidadOptions = unidades.map(u => ({ label: `${u.sigla} - ${u.nombre}`, value: u.sigla }));

// Si alguna vez quieres resolver nombre por sigla:
const unidadesMap = unidades.reduce((acc, u) => (acc[u.sigla] = u.nombre, acc), {});

// helper robusto
const upperEs = (v) => {
  if (v == null) return v;
  const s = String(v).trim();
  // Si quieres aplicar solo si hay letras:
  // if (!/[A-Za-z\u00C0-\u024F]/.test(s)) return s;
  return s.normalize('NFC').toUpperCase();
};

// formateador miles
const fmtMiles = new Intl.NumberFormat('es-CO',{minimumFractionDigits:2, maximumFractionDigits:2});
const toNumber = v => {
  if (v===null || v===undefined || v==='') return NaN;
  if (typeof v==='number') return v;
  const n = Number(String(v).replace(',','.'));
  return Number.isFinite(n) ? n : NaN;
};

// Tabla
let tabla;
function cargarTabla() {
  if (tabla) { tabla.destroy(); }

  tabla = new Tabulator("#tabla-items-propios", {
    height: "520px",
    layout: "fitColumns",
    ajaxURL: "admin.items-propios/list",
    placeholder: "No hay registros...",
    columns: [
      { title: "Categoría", field: "categoria_id", editor: "list",
        editorParams: { values: categoriasMap, autocomplete:true, listOnEmpty:true },
        formatter: cell => categoriasMap[cell.getValue()] || ""
      },

      { title: "Código", field: "codigo", editor: "input", headerSort:true,editorParams: {
                    elementAttributes: {
                    style: "text-transform:uppercase;"
                }
           },
        mutatorEdit: (value) => upperEs(value),
      },

      { title: "Nombre", field: "nombre", editor: "input",
        editorParams: {
                    elementAttributes:
                    {
                        style: "text-transform:uppercase;" // visual mientras escribe
                    }
                },
        mutatorEdit: (value) => upperEs(value),
      },

      { title: "Unidad", field: "unidad_medida", editor: "list",
        editorParams: { values: unidadesMap, autocomplete:true, listOnEmpty:true },
        formatter: cell => unidadesMap[cell.getValue()] || cell.getValue()
      },

      { title: "Activo", field: "active", hozAlign:"center", width:90,
        formatter: "tickCross",
        editor: true, // true = checkbox boolean
        mutatorEdit: (value)=> !!value ? 1 : 0,
      },

      { title: "Creado", field: "created_at", width: 140 },
      { title: "Actualizado", field: "updated_at", width: 140 },

      { title: "Acciones", field: "acciones", headerSort:false, width:140,
        formatter: (_, row) => {
          return `
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-success btn-guardar">Guardar</button>
              <button class="btn btn-sm btn-danger btn-eliminar">Eliminar</button>
            </div>`;
        },
        cellClick: async (e, cell) => {
          const row = cell.getRow();
          const data = row.getData();

          if (e.target.classList.contains('btn-guardar')) {
            await saveDataItemsPropios(row);
          }
          if (e.target.classList.contains('btn-eliminar')) {
            eliminarFila(row);
          }
        }
      },
    ],
    cellEdited: function(cell){
      // opción: guardar automáticamente al editar
      // guardarFila(cell.getRow());
    }
  });
}

async function saveDataItemsPropios(row){
  const data = row.getData();
  const payload = {
    categoria_id:  Number(data.categoria_id),
    codigo:        data.codigo?.trim(),
    nombre:        data.nombre?.trim(),
    unidad_medida: data.unidad_medida,   // sigla
    active:        Number(data.active ? 1 : 0),
  };

  const isNew = !data.id;
  const url   = isNew ? "admin.items-propios" : "admin.items-propios/"+data.id;
  const method= isNew ? "POST" : "PUT";

  try{
    const res = await fetch(url, {
      method,
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(payload)
    });

    if(!res.ok){
      const err = await res.json().catch(()=>({message:'Error'}));
      throw new Error(err.message || 'Error al guardar');
    }

    const saved = await res.json();
    row.update(saved);
    Swal.fire('OK', 'Registro guardado', 'success');
  }catch(err){
    console.error(err);
    Swal.fire('Error', err.message || 'No se pudo guardar', 'error');
  }
}

function eliminarFila(row){
  const data = row.getData();
  if(!data.id){
    row.delete();
    return;
  }
  Swal.fire({
    title: '¿Eliminar?',
    text: `${data.codigo} - ${data.nombre}`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
  }).then(async (r)=>{
    if(!r.value) return;

    try{
      const res = await fetch("admin.items-propios/"+data.id, {
        method: "DELETE",
        headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content }
      });
      if(!res.ok){
        const err = await res.json().catch(()=>({message:'Error'}));
        throw new Error(err.message || 'Error al eliminar');
      }

      row.delete();
      Swal.fire('Eliminado','Registro eliminado','success');
    }catch(err){
      Swal.fire('Error', err.message || 'No se pudo eliminar','error');
    }
  });
}

// Botón Nuevo
document.addEventListener('DOMContentLoaded', ()=>{
  cargarTabla();

  document.getElementById('btn-refresh').addEventListener('click', ()=> tabla.replaceData());
  document.getElementById('btn-nuevo').addEventListener('click', ()=>{
    tabla.addRow({
      categoria_id: categorias[0]?.id ?? null,
      codigo: "",
      nombre: "",
      unidad_medida: unidades[0]?.id ?? "",
      active: 1,
    }, false);
  });
});
