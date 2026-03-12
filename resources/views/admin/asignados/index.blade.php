@extends('layouts.admin')

@section('content')
<div class="container-fluid">

  <div class="card mb-3">
    <div class="card-header bg-gradient-primary text-white">
      <strong>LISTA DE POSTULACIÓN &nbsp;|&nbsp; Asignaciones</strong>
    </div>
    <div class="card-body">

      {{-- Filtros superiores --}}
      <div class="row g-3 mb-3">
        <div class="col-md-2">
          <label class="form-label">Departamento</label>
          <input type="text" class="form-control" value="Tarija" disabled>
        </div>

        <div class="col-md-3">
          <label class="form-label">Provincia</label>
          <select id="province_id" class="form-control">
            <option value="">TODOS</option>
            @foreach($provincias as $p)
              <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Municipio</label>
          <select id="municipality_id" class="form-control">
            <option value="">TODOS</option>
          </select>
        </div>

        <div class="col-md-2 d-none" id="wrap-district">
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

        <div class="col-md-2">
          <label class="form-label">Mesa</label>
          <select id="table_id" class="form-control">
            <option value="">TODAS</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Buscar por Cédula</label>
          <div class="input-group">
            <input id="cedula" type="text" class="form-control" placeholder="C.I.">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Buscar por Teléfono</label>
          <div class="input-group">
            <input id="telefono" type="text" class="form-control" placeholder="Celular">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
          </div>
        </div>

        <div class="col-md-6 d-flex align-items-end justify-content-end">
          <button id="btn-limpiar" class="btn btn-secondary">Limpiar</button>
        </div>
      </div>

      {{-- Tabla --}}
      <div class="table-responsive">
        <table id="tabla-asignados" class="table table-striped table-bordered w-100">
          <thead class="table-primary">
            <tr>
              <th style="width:80px">Acciones</th> {{-- NUEVO --}}
              <th>Nro</th>
              <th>Nº Documento</th>
              <th>Nombre Completo</th>
              <th>Celular</th>
              <th>Provincia</th>
              <th>Municipio</th>
              <th>Recinto</th>
              <th>Mesa</th>
              <th>Tipo de Rol</th>
              <th>Rol de Recinto</th>
              <th>Ámbito</th>
              <th>Circunscripción</th>
            </tr>
          </thead>
        </table>
      </div>

    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
<script>
$(function(){
  let precinctCache = [];

  $('#precinct_id').select2({
    placeholder: 'TODOS',
    allowClear: true,
    width: '100%'
  });

  function isTarijaMunicipioSelected() {
    const municipalityId = ($('#municipality_id').val() || '').toString().trim();
    const selectedText = ($('#municipality_id option:selected').text() || '').trim().toLowerCase();
    return municipalityId !== '' && selectedText === 'tarija';
  }

  function applyPrecinctOptionsFromCache() {
    const selectedDistrict = ($('#district_id').val() || '').toString().trim();
    let data = precinctCache;

    if (selectedDistrict) {
      data = precinctCache.filter(x => String(x.district_id || '') === selectedDistrict);
    }

    let opts = '<option value="">TODOS</option>';
    data.forEach(x => opts += `<option value="${x.id}">${x.name}</option>`);
    $('#precinct_id').html(opts);
    $('#precinct_id').val('').trigger('change');
  }

  // Combos dependientes
  $('#province_id').on('change', function(){
    $('#municipality_id').html('<option value="">TODOS</option>');
    $('#district_id').html('<option value="">TODOS</option>');
    $('#wrap-district').addClass('d-none');
    $('#precinct_id').html('<option value="">TODOS</option>');
    $('#precinct_id').val('').trigger('change');
    $('#table_id').html('<option value="">TODAS</option>');
    precinctCache = [];
    const id = $(this).val();
    if(!id){ table.ajax.reload(); return; }
    $.get("{{ url('/admin/municipios/por-provincia') }}/"+id, function(res){
      let opts = '<option value="">TODOS</option>';
      res.forEach(x=>opts+=`<option value="${x.id}">${x.name}</option>`);
      $('#municipality_id').html(opts);
      table.ajax.reload();
    });
  });

  $('#municipality_id').on('change', function(){
    const id = $(this).val();
    const isTarija = isTarijaMunicipioSelected();

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

    $('#precinct_id').html('<option value="">TODOS</option>');
    $('#precinct_id').val('').trigger('change');
    $('#table_id').html('<option value="">TODAS</option>');
    precinctCache = [];
    if(!id){ table.ajax.reload(); return; }
    $.get("{{ url('/recintos/por-municipio') }}/"+id, function(res){
      precinctCache = res || [];
      applyPrecinctOptionsFromCache();
      table.ajax.reload();
    });
  });

  $('#district_id').on('change', function(){
    applyPrecinctOptionsFromCache();
    $('#table_id').html('<option value="">TODAS</option>');
    table.ajax.reload();
  });

  $('#precinct_id').on('change', function(){
    $('#table_id').html('<option value="">TODAS</option>');
    const id = $(this).val();
    if(!id){ table.ajax.reload(); return; }
    $.get("{{ url('/mesas/por-recinto') }}/"+id, function(res){
      let opts = '<option value="">TODAS</option>';
      res.forEach(x=>opts+=`<option value="${x.id}">Mesa ${x.table_number}</option>`);
      $('#table_id').html(opts);
      table.ajax.reload();
    });
  });

  // DataTable
  const table = $('#tabla-asignados').DataTable({
    processing:true, serverSide:true, responsive:true,
    order:[[2,'asc']], // ordena por Nº Doc
    language:{ url:'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    ajax:{
      url:"{{ route('asignados.data') }}",
      data:function(d){
        d.province_id     = $('#province_id').val();
        d.municipality_id = $('#municipality_id').val();
        d.district_id     = $('#district_id').val();
        d.precinct_id     = $('#precinct_id').val();
        d.table_id        = $('#table_id').val();
        d.cedula          = $('#cedula').val();
        d.telefono        = $('#telefono').val();
      }
    },
    columns:[
      // ACCIONES (usa el id de la asignación que debe venir en cada fila)
      {
        data:'id', name:'a.id', orderable:false, searchable:false,
        render:function(id){
          return `
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-danger btn-del-assignment" data-id="${id}" title="Eliminar">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>`;
        }
      },
      { data:'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false },
      { data:'ci', name:'m.ci' },
      { data:'nombre_completo', name:'m.nombres' },
      { data:'celular', name:'m.celular' },
      { data:'provincia', name:'p.name' },
      { data:'municipio', name:'mu.name' },
      { data:'recinto', name:'r.name' },
      { data:'mesa', name:'t.table_number' },
      { data:'tipo_rol', name:'a.role' },
      { data:'rol_recinto', name:'ar.role' },
      { data:'ambito', searchable:false },
      { data:'circuns', name:'r.circuns' },
    ],
    dom:'Bfrtip',
    buttons:['excel','pdf','print','colvis']
  });

  // Triggers de filtros
  $('#province_id, #municipality_id, #district_id, #precinct_id, #table_id').on('change', function(){ table.ajax.reload(); });
  $('#cedula, #telefono').on('keyup', _.debounce(()=>table.ajax.reload(), 300));

  // Limpiar filtros
  $('#btn-limpiar').on('click', function(){
    $('#province_id').val('');
    $('#municipality_id').html('<option value="">TODOS</option>');
    $('#district_id').html('<option value="">TODOS</option>');
    $('#wrap-district').addClass('d-none');
    $('#precinct_id').html('<option value="">TODOS</option>');
    $('#precinct_id').val('').trigger('change');
    $('#table_id').html('<option value="">TODAS</option>');
    $('#cedula, #telefono').val('');
    precinctCache = [];
    table.ajax.reload();
  });

  // Eliminar (AJAX)
  $(document).on('click','.btn-del-assignment',function(){
    const id = $(this).data('id');
    Swal.fire({
      title:'¿Eliminar asignación?',
      text:'Esta acción no se puede deshacer',
      icon:'warning', showCancelButton:true,
      confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'
    }).then(res=>{
      if(!res.isConfirmed) return;
      $.ajax({
        url:"{{ url('admin/asignados') }}/"+id,
        type:'POST',
        data:{ _method:'DELETE', _token:"{{ csrf_token() }}" },
        success:()=>{
          table.ajax.reload(null,false);
          Swal.fire({icon:'success', title:'Eliminado', timer:1300, showConfirmButton:false});
        },
        error:(xhr)=>{
          Swal.fire({icon:'error', title:'No se pudo eliminar', text: xhr.responseJSON?.message || 'Intente nuevamente'});
        }
      });
    });
  });

});
</script>
<style>
  #select2-precinct_id-container {
    line-height: calc(2.25rem + 2px);
  }

  .select2-container .select2-selection--single {
    height: calc(2.25rem + 2px);
    border: 1px solid #ced4da;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: calc(2.25rem + 2px);
  }
</style>
@endpush
