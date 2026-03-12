@extends('layouts.admin')

@section('content')
<div class="container-fluid">

  <h3 class="mb-3">Reporte de Cobertura</h3>

  {{-- KPIs --}}
  <div class="row g-3 mb-3" id="kpis">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted">Mesas totales</div>
            <div class="h3 mb-0" id="kpi-total">—</div>
          </div>
          <i class="bi bi-grid-3x3-gap h1 text-secondary mb-0"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted">Mesas cubiertas</div>
            <div class="h3 mb-0 text-success" id="kpi-cubiertas">—</div>
          </div>
          <i class="bi bi-check2-circle h1 text-success mb-0"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted">Pendientes</div>
            <div class="h3 mb-0 text-danger" id="kpi-pendientes">—</div>
          </div>
          <i class="bi bi-exclamation-triangle h1 text-warning mb-0"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- Filtros --}}
  <div class="card mb-3">
    <div class="card-body">
      <form id="filtros" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Provincia</label>
          <select id="province_id" class="form-control">
            <option value="">TODAS</option>
            @foreach($provincias as $p)
              <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Municipio</label>
          <select id="municipality_id" class="form-control">
            <option value="">TODOS</option>
          </select>
        </div>
        <div class="col-md-3 d-none" id="wrap-district">
          <label class="form-label">Distrito</label>
          <select id="district_id" class="form-control">
            <option value="">TODOS</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Recinto</label>
          <select id="precinct_id" class="form-control">
            <option value="">TODOS</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Mesa</label>
          <select id="table_id" class="form-control">
            <option value="">TODAS</option>
          </select>
        </div>
      </form>
    </div>
  </div>

  {{-- Tabla --}}
  <div class="card">
    <div class="card-body">
      <div class="mb-3">
        <button type="button" id="btn-matriz" class="btn btn-outline-primary">
          <i class="bi bi-grid-3x3-gap"></i> Reporte matriz
        </button>
      </div>
      <table id="tabla-cobertura" class="table table-striped table-bordered w-100">
        <thead>
        <tr>
          <th>Provincia</th>
          <th>Municipio</th>
          <th>Recinto</th>
          <th>Mesa</th>
          <th>Delegado Prop.</th>
          <th>Delegado Supl.</th>
          <th>Jefe Recinto</th>
          <th>Estado</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  <div class="modal fade" id="modalMatriz" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reporte Matriz de Cobertura</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-end align-items-end flex-wrap mb-2" style="gap:.5rem;">
            <div style="min-width:220px;">
              <label for="matriz_distrito_export" id="lbl-matriz-grupo" class="form-label mb-1">Distrito</label>
              <select id="matriz_distrito_export" class="form-control form-control-sm">
                <option value="TODOS">TODOS</option>
              </select>
            </div>
            <a href="#" id="btn-matriz-excel-distrito" class="btn btn-outline-success btn-sm">
              <i class="bi bi-file-earmark-excel"></i> Exportar distrito (detalle)
            </a>
            <a href="#" id="btn-matriz-excel-faltantes" class="btn btn-outline-warning btn-sm">
              <i class="bi bi-file-earmark-excel"></i> Exportar faltantes
            </a>
            <a href="#" id="btn-matriz-excel-completos" class="btn btn-outline-info btn-sm">
              <i class="bi bi-file-earmark-excel"></i> Exportar 100%
            </a>
            <a href="#" id="btn-matriz-excel" class="btn btn-success btn-sm">
              <i class="bi bi-file-earmark-excel"></i> Exportar Excel
            </a>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-sm" id="tabla-matriz">
              <thead class="thead-light">
                <tr>
                  <th>N°</th>
                  <th id="th-matriz-grupo">Distrito</th>
                  <th>Reg. Recintos</th>
                  <th>Reg. Mesas</th>
                  <th>Req. Recintos</th>
                  <th>Dif. Recintos</th>
                  <th>Req. Mesas</th>
                  <th>Dif. Mesas</th>
                  <th>Req. Total</th>
                  <th>Cob. Total</th>
                  <th>%</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
$(function(){
  let precinctCache = [];

  function parseDistrictIds(raw) {
    const txt = (raw || '').toString().trim();
    if (!txt) return [];
    return txt.split(',')
      .map(x => parseInt(x.trim(), 10))
      .filter(x => !Number.isNaN(x) && x > 0);
  }

  function isTarijaMunicipioSelected() {
    const municipalityId = ($('#municipality_id').val() || '').toString().trim();
    const selectedText = ($('#municipality_id option:selected').text() || '').trim().toLowerCase();
    return municipalityId !== '' && selectedText === 'tarija';
  }

  function applyMatrizGroupLabels() {
    const isTarija = isTarijaMunicipioSelected();
    const groupTitle = isTarija ? 'Distrito' : 'Asiento electoral';
    const placeholder = 'TODOS';
    const btnLabel = isTarija ? 'Exportar distrito (detalle)' : 'Exportar asiento electoral (detalle)';

    $('#th-matriz-grupo').text(groupTitle);
    $('#lbl-matriz-grupo').text(groupTitle);
    $('#matriz_distrito_export option:first').text(placeholder);
    $('#btn-matriz-excel-distrito').html('<i class="bi bi-file-earmark-excel"></i> ' + btnLabel);
  }

  function applyPrecinctOptionsFromCache() {
    const ids = parseDistrictIds($('#district_id').val());
    let data = precinctCache;
    if (ids.length > 0) {
      data = precinctCache.filter(x => ids.includes(parseInt(x.district_id, 10)));
    }

    let opts = '<option value="">TODOS</option>';
    data.forEach(x => opts += `<option value="${x.id}">${x.name}</option>`);
    $('#precinct_id').html(opts);
  }

  // --- combos dependientes ---
  $('#province_id').on('change', function(){
    const id = $(this).val();
    $('#municipality_id').html('<option value="">TODOS</option>');
    $('#district_id').html('<option value="">TODOS</option>');
    $('#wrap-district').addClass('d-none');
    $('#precinct_id').html('<option value="">TODOS</option>');
    $('#table_id').html('<option value="">TODAS</option>');
    applyMatrizGroupLabels();
    if(!id){ table.ajax.reload(); kpis(); return; }
    $.get("{{ url('/admin/municipios/por-provincia') }}/"+id, function(res){
      let opts = '<option value="">TODOS</option>';
      res.forEach(x => opts += `<option value="${x.id}">${x.name}</option>`);
      $('#municipality_id').html(opts);
      table.ajax.reload(); kpis();
    });
  });

  $('#municipality_id').on('change', function(){
    const id = $(this).val();
    const selectedText = ($('#municipality_id option:selected').text() || '').trim().toLowerCase();
    const isTarija = selectedText === 'tarija';

    $('#district_id').html('<option value="">TODOS</option>');
    if (isTarija && id) {
      $('#wrap-district').removeClass('d-none');
      $.get("{{ url('/admin/distritos/por-municipio') }}/"+id, function(res){
        let opts = '<option value="">TODOS</option>';
        res.forEach(x => opts += `<option value="${x.id}">${x.name}</option>`);
        $('#district_id').html(opts);
      });
    } else {
      $('#wrap-district').addClass('d-none');
    }

    precinctCache = [];
    $('#precinct_id').html('<option value="">TODOS</option>');
    $('#table_id').html('<option value="">TODAS</option>');
    applyMatrizGroupLabels();
    if(!id){ table.ajax.reload(); kpis(); return; }
    $.get("{{ url('/recintos/por-municipio') }}/"+id, function(res){
      precinctCache = res || [];
      applyPrecinctOptionsFromCache();
      table.ajax.reload(); kpis();
    });
  });

  $('#precinct_id').on('change', function(){
    const id = $(this).val();
    $('#table_id').html('<option value="">TODAS</option>');
    if(!id){ table.ajax.reload(); kpis(); return; }
    $.get("{{ url('/mesas/por-recinto') }}/"+id, function(res){
      let opts = '<option value="">TODAS</option>';
      res.forEach(x => opts += `<option value="${x.id}">Mesa ${x.table_number}</option>`);
      $('#table_id').html(opts);
      table.ajax.reload(); kpis();
    });
  });

  $('#district_id').on('change', function(){
    applyPrecinctOptionsFromCache();
    $('#table_id').html('<option value="">TODAS</option>');
    table.ajax.reload();
    kpis();
  });
  $('#table_id').on('change', function(){ table.ajax.reload(); kpis(); });

  // --- helper para interpretar booleanos o HTML de íconos ---
  function ok(v){
    if (v === true || v === 1) return true;
    if (typeof v === 'number') return v > 0;
    if (!v) return false;
    if (typeof v === 'string') {
      const s = v.toLowerCase();
      if (s === '1' || s === 'si' || s === 'sí' || s === 'true') return true;
      // si tu backend manda HTML con "check"/"success", también lo detectamos
      if (s.indexOf('check') > -1 || s.indexOf('success') > -1) return true;
    }
    return false;
  }

  function currentFilters(){
    return {
      province_id: $('#province_id').val(),
      municipality_id: $('#municipality_id').val(),
      district_id: $('#district_id').val(),
      precinct_id: $('#precinct_id').val(),
      table_id: $('#table_id').val(),
    };
  }

 const table = $('#tabla-cobertura').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    order: [[0,'asc'],[1,'asc'],[2,'asc'],[3,'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    ajax: {
      url: "{{ route('cobertura.data') }}",
      data: function(d){ Object.assign(d, currentFilters()); }
    },

    // 👇 Cambiamos SOLO la definición de columnas con iconos
    columns: [
      { data:'provincia', name:'provincia' },
      { data:'municipio', name:'municipio' },
      { data:'recinto',   name:'recinto'   },
      { data:'mesa',      name:'mesa'      },

      // Delegado propietario
      {
        data: 'delegado_prop',
        orderable:false, searchable:false,
        render: function (data, type, row) {
          const isOk = ok(data);
          if (type === 'display') {
            return isOk
              ? '<i class="bi bi-check-circle-fill text-success"></i>'
              : '<i class="bi bi-exclamation-triangle-fill text-warning"></i>';
          }
          // para exportar/filtrar/ordenar
          return isOk ? 'SI' : 'NO';
        }
      },

      // Delegado suplente
      {
        data: 'delegado_supl',
        orderable:false, searchable:false,
        render: function (data, type, row) {
          const isOk = ok(data);
          if (type === 'display') {
            return isOk
              ? '<i class="bi bi-check-circle-fill text-success"></i>'
              : '<i class="bi bi-exclamation-triangle-fill text-warning"></i>';
          }
          return isOk ? 'SI' : 'NO';
        }
      },

      // Jefe de recinto
      {
        data: 'jefe_recinto',
        orderable:false, searchable:false,
        render: function (data, type, row) {
          const isOk = ok(data);
          if (type === 'display') {
            return isOk
              ? '<i class="bi bi-check-circle-fill text-success"></i>'
              : '<i class="bi bi-exclamation-triangle-fill text-warning"></i>';
          }
          return isOk ? 'SI' : 'NO';
        }
      },

      // Estado (mesas cubiertas)
      {
        data: 'cubierta',
        orderable:false, searchable:false,
        render: function (data, type, row) {
          const isOk = ok(data);
          if (type === 'display') {
            return isOk
              ? '<span class="badge bg-success">Cubierta</span>'
              : '<span class="badge bg-warning">Pendiente</span>';
          }
          return isOk ? 'SI' : 'NO';
        }
      },
    ],

    dom: 'Bfrtip',
    // 👇 Muy importante: orthogonal:'export' para que use el texto SI/NO en Excel/PDF/Imprimir
    buttons: [
      {
        extend: 'excelHtml5',
        text: 'Excel',
        exportOptions: { columns: ':visible', orthogonal: 'export' }
      },
      {
        extend: 'pdfHtml5',
        text: 'PDF',
        exportOptions: { columns: ':visible', orthogonal: 'export' }
      },
      {
        extend: 'print',
        text: 'Imprimir',
        exportOptions: { columns: ':visible', orthogonal: 'export' }
      },
      { extend: 'colvis', text: 'Visibilidad' }
    ]
  });

  // KPIs
  function kpis(){
    $.get("{{ route('cobertura.resumen') }}", currentFilters(), function(res){
      $('#kpi-total').text(res.total);
      $('#kpi-cubiertas').text(res.cubiertas);
      $('#kpi-pendientes').text(res.pendientes);
    });
  }

  function renderMatriz(rows){
    const $tb = $('#tabla-matriz tbody');
    $tb.empty();
    const $districtSelect = $('#matriz_distrito_export');
    $districtSelect.html('<option value="TODOS">TODOS</option>');
    applyMatrizGroupLabels();

    if (!rows || rows.length === 0) {
      $tb.append('<tr><td colspan="11" class="text-center text-muted">Sin datos</td></tr>');
      return;
    }

    const districtSeen = new Set();
    rows.forEach(r => {
      const district = (r.distrito || '').toString().trim();
      const districtUpper = district.toUpperCase();
      if (district && districtUpper !== 'TOTAL' && !districtSeen.has(districtUpper)) {
        districtSeen.add(districtUpper);
        $districtSelect.append(`<option value="${district}">${district}</option>`);
      }

      $tb.append(`
        <tr>
          <td>${r.nro ?? ''}</td>
          <td>${r.distrito ?? ''}</td>
          <td>${r.reg_recintos ?? 0}</td>
          <td>${r.reg_mesas ?? 0}</td>
          <td>${r.req_recintos ?? 0}</td>
          <td>${r.dif_recintos ?? 0}</td>
          <td>${r.req_mesas ?? 0}</td>
          <td>${r.dif_mesas ?? 0}</td>
          <td>${r.req_total ?? 0}</td>
          <td>${r.cob_total ?? 0}</td>
          <td>${r.porcentaje ?? '0,00%'}</td>
        </tr>
      `);
    });
  }

  $('#btn-matriz').on('click', function(){
    const f = currentFilters();
    const excelUrl = "{{ route('cobertura.matriz.excel') }}" + '?' + $.param(f);
    $('#btn-matriz-excel').attr('href', excelUrl);
    $('#btn-matriz-excel-distrito').attr('href', '#');
    $('#btn-matriz-excel-faltantes').attr('href', excelUrl + '&matriz_status=faltantes');
    $('#btn-matriz-excel-completos').attr('href', excelUrl + '&matriz_status=completos');
    $('#matriz_distrito_export').val('TODOS');
    applyMatrizGroupLabels();

    $.get("{{ route('cobertura.matriz') }}", f, function(res){
      renderMatriz(res.rows || []);
      $('#modalMatriz').modal('show');
    });
  });

  $('#btn-matriz-excel-distrito').on('click', function(e){
    const distrito = ($('#matriz_distrito_export').val() || '').toString().trim();
    const f = currentFilters();
    if (distrito && distrito !== 'TODOS') {
      f.matriz_distrito = distrito;
    } else {
      f.matriz_distrito = 'TODOS';
    }
    const excelUrl = "{{ route('cobertura.matriz.excel') }}" + '?' + $.param(f);
    $(this).attr('href', excelUrl);
  });

  // primera carga
  applyMatrizGroupLabels();
  kpis();
});
</script>
@endpush
