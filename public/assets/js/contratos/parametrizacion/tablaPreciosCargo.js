(function () {
  // usa token desde meta siempre, sin variable global
  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  let tablaPreciosCargo = null;

  function moneyFormatter(cell) {
    const v = Number(cell.getValue() ?? 0);
    return v.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function pctFormatter(cell) {
    const v = Number(cell.getValue() ?? 0);
    return (v * 100).toFixed(2) + '%';
  }

  async function fetchTablaPrecios() {
    const res = await fetch(TABLA_PRECIOS_GET_URL);
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'No se pudo cargar la tabla de precios');
    return json.data || [];
  }

  function buildTablaPrecios(data) {
    if (tablaPreciosCargo) {
      tablaPreciosCargo.replaceData(data);
      return;
    }

    tablaPreciosCargo = new Tabulator("#tabla-precios-cargo", {
      data,
      layout: "fitDataStretch",
      height: "520px",
      pagination: "local",
      paginationSize: 20,
      movableColumns: true,
      columns: [
        { title: "Cargo", field: "cargo", widthGrow: 2, headerFilter: "input" },
        { title: "Costo día (S)", field: "base_costo_dia", hozAlign: "right", formatter: moneyFormatter },
        { title: "Costo hora (T)", field: "base_costo_hora", hozAlign: "right", formatter: moneyFormatter },
        { title: "Hora ordinaria", field: "hora_ordinaria", hozAlign: "right", formatter: moneyFormatter },
        { title: "Recargo nocturno", field: "recargo_nocturno", hozAlign: "right", formatter: moneyFormatter },
        { title: "Extra diurna", field: "hora_extra_diurna", hozAlign: "right", formatter: moneyFormatter },
        { title: "Extra nocturna", field: "hora_extra_nocturna", hozAlign: "right", formatter: moneyFormatter },
        { title: "Dominical", field: "hora_dominical", hozAlign: "right", formatter: moneyFormatter },
        { title: "Extra dom. diurna", field: "hora_extra_dominical_diurna", hozAlign: "right", formatter: moneyFormatter },
        { title: "Extra dom. nocturna", field: "hora_extra_dominical_nocturna", hozAlign: "right", formatter: moneyFormatter },
        { title: "Valor día ordinario", field: "valor_dia_ordinario", hozAlign: "right", formatter: moneyFormatter },
        { title: "Utilidad", field: "utilidad_pct", hozAlign: "right", formatter: pctFormatter },
        { title: "Horas día", field: "horas_diarias", hozAlign: "center" },
        { title: "Actualizado", field: "updated_at", hozAlign: "center" },
      ],
    });
  }

  async function cargarTablaPrecios() {
    try {
      const data = await fetchTablaPrecios();
      buildTablaPrecios(data);

      const lbl = document.getElementById('lbl-updated-tabla-precios');
      const last = data?.[0]?.updated_at;
      if (lbl) lbl.textContent = last ? `Última actualización: ${last}` : '';
    } catch (e) {
      Swal.fire('Error', e.message, 'error');
    }
  }

  async function generarTablaPrecios() {
    try {
      const res = await fetch(TABLA_PRECIOS_POST_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({}),
      });

      const json = await res.json();
      if (!json.success) throw new Error(json.message || 'No se pudo generar la tabla');

      Swal.fire('OK', `${json.message} (Cargos: ${json.count})`, 'success');
      buildTablaPrecios(json.data || []);
    } catch (e) {
      Swal.fire('Error', e.message, 'error');
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btn-refresh-tabla-precios')?.addEventListener('click', cargarTablaPrecios);
    document.getElementById('btn-gen-tabla-precios')?.addEventListener('click', generarTablaPrecios);

    document.getElementById('tabla-precios-tab')?.addEventListener('shown.bs.tab', () => {
      if (!tablaPreciosCargo) cargarTablaPrecios();
    });
  });
})();
