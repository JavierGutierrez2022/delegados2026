@extends('layouts.admin')

@section('content')
<div class="container-fluid">

  <h3 class="mb-3">Lista de Postulacion</h3>

            {{-- Filtros --}}
            <div class="card mb-3">
              <div class="card-body">
                <form id="form-filtros">
            {{-- 1Âª fila: selects --}}
            <div class="row g-3 align-items-end mb-4">
              <div class="col-md-2">
                <label class="form-label">Departamento</label>
                <input type="text" class="form-control" value="Tarija" disabled>
              </div>


              <div class="col-md-3">
                <label class="form-label">Provincia</label>
                <select name="province_id" id="province_id" class="form-control">
                  <option value="">TODOS</option>
                  @foreach($provincias as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Municipio</label>
                <select name="municipality_id" id="municipality_id" class="form-control">
                  <option value="">TODOS</option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Recinto</label>
                <select name="precinct_id" id="precinct_id" class="form-control">
                  <option value="">TODOS</option>
                </select>
              </div>
            </div>

            {{-- 2Âª fila: bÃºsquedas y botÃ³n --}}
            <div class="row g-3 align-items-end">
              <div class="col-md-4">
                <label class="form-label">Buscar por Cédula</label>
                <div class="input-group">
                  <input type="text" class="form-control" name="cedula" id="cedula" placeholder="C.I.">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Buscar por Teléfono</label>
                <div class="input-group">
                  <input type="text" class="form-control" name="telefono" id="telefono" placeholder="Celular">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
              </div>

              <div class="col-md-4 d-flex justify-content-end align-items-end flex-wrap" style="gap: .5rem;">
                <a href="{{ route('postulaciones.export.excel') }}" id="btn-exportar-excel" class="btn btn-success mt-2 mt-md-0 shadow-sm">
                  <i class="fas fa-file-excel"></i> Descargar Excel
                </a>
                <button type="button" id="btn-limpiar" class="btn btn-primary mt-2 mt-md-0">Limpiar</button>
                @can('menu.usuarios')
                <button type="button" id="btn-purge-postulaciones" class="btn btn-danger mt-2 mt-md-0" data-url="{{ route('postulaciones.purge') }}">
                  Borrado general
                </button>
                @endcan
              </div>
            </div>
          </form>
          <hr>

  {{-- Tabla --}}
  <div class="card">
    <div class="card-body">
      <table id="tabla-postulaciones" class="table table-striped table-bordered w-100">
        <thead>
          <tr>
            <th>Nro</th>
            <th>Nombres</th>
            <th>Apellido Paterno</th>
            <th>Apellido Materno</th>
            <th>Género</th>
            <th>C.I.</th>
            <th>Nacimiento</th>
            <th>Celular</th>
            <th>Provincia</th>
            <th>Municipio</th>
            <th>Recinto</th>
            <th>Observaciones</th>
            <th style="width:120px">Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
$(function(){
  function buildExportUrl() {
    const params = new URLSearchParams({
      province_id: $('#province_id').val() || '',
      municipality_id: $('#municipality_id').val() || '',
      precinct_id: $('#precinct_id').val() || '',
      cedula: $('#cedula').val() || '',
      telefono: $('#telefono').val() || '',
    });

    return "{{ route('postulaciones.export.excel') }}?" + params.toString();
  }

  $('#btn-exportar-excel').on('click', function(e){
    e.preventDefault();
    window.location = buildExportUrl();
  });

  // Dependent combos
  $('#province_id').on('change', function(){
    let id = $(this).val();
    $('#municipality_id').html('<option value="">TODOS</option>');
    $('#precinct_id').html('<option value="">TODOS</option>');
    if(!id) { table.ajax.reload(); return; }

    $.get("{{ url('/admin/municipios/por-provincia') }}/"+id, function(res){
      let opts = '<option value="">TODOS</option>';
      res.forEach(x => opts += `<option value="${x.id}">${x.name}</option>`);
      $('#municipality_id').html(opts);
      table.ajax.reload();
    });
  });

  $('#municipality_id').on('change', function(){
    let id = $(this).val();
    $('#precinct_id').html('<option value="">TODOS</option>');
    if(!id) { table.ajax.reload(); return; }

    $.get("{{ url('/recintos/por-municipio') }}/"+id, function(res){
      let opts = '<option value="">TODOS</option>';
      res.forEach(x => opts += `<option value="${x.id}">${x.name}</option>`);
      $('#precinct_id').html(opts);
      table.ajax.reload();
    });
  });

  // DataTable
  const table = $('#tabla-postulaciones').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    order: [[1,'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    ajax: {
      url: "{{ route('postulaciones.data') }}",
      data: function (d) {
        d.province_id     = $('#province_id').val();
        d.municipality_id = $('#municipality_id').val();
        d.precinct_id     = $('#precinct_id').val();
        d.cedula          = $('#cedula').val();
        d.telefono        = $('#telefono').val();
      }
    },
    columns: [
      { data: 'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false },
      { data: 'nombres', name: 'm.nombres' },
      { data: 'app', name: 'm.app' },
      { data: 'apm', name: 'm.apm' },
      { data: 'genero', name: 'm.genero' },
      { data: 'ci', name: 'm.ci' },
      { data: 'fecnac', name: 'm.fecnac' },
      { data: 'celular', name: 'm.celular' },
      { data: 'provincia', name: 'p.name' },
      { data: 'municipio', name: 'mu.name' },
      { data: 'recinto', name: 'r.name' },
      { data: 'obs', name: 'm.obs' },
      { data: 'acciones', orderable:false, searchable:false }
    ],
    dom: 'Bfrtip',
    buttons: [
      {
        text: 'Excel',
        action: function () {
          window.location = buildExportUrl();
        }
      },
      'pdf',
      'print',
      'colvis'
    ]
  });

  // Triggers de recarga
  $('#form-filtros select, #form-filtros input').on('change keyup', function(){
    table.ajax.reload();
  });

  // Limpiar filtros
  $('#btn-limpiar').on('click', function(){
    $('#province_id').val('');
    $('#municipality_id').html('<option value="">TODOS</option>');
    $('#precinct_id').html('<option value="">TODOS</option>');
    $('#cedula, #telefono').val('');
    table.ajax.reload();
  });

  $('#btn-purge-postulaciones').on('click', function(){
    const url = $(this).data('url');
    Swal.fire({
      title: 'Borrado general de postulaciones',
      text: 'Se eliminaran TODOS los registros de esta tabla. Esta accion no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Si, borrar todo',
      cancelButtonText: 'Cancelar'
    }).then((res) => {
      if (!res.isConfirmed) return;

      $.ajax({
        url: url,
        type: 'POST',
        data: { _token: "{{ csrf_token() }}" },
        success: function(resp){
          table.ajax.reload();
          Swal.fire({
            icon:'success',
            title:'Borrado completado',
            text: resp?.message || 'Se eliminaron los registros.',
            timer: 1800,
            showConfirmButton: false
          });
        },
        error: function(xhr){
          Swal.fire({icon:'error', title:'No se pudo borrar', text: xhr.responseJSON?.message || 'Intente nuevamente'});
        }
      });
    });
  });

      // Eliminar
      $(document).on('click', '.btn-del', function(){
        const id = $(this).data('id');
        Swal.fire({
          title: 'Â¿Eliminar registro?',
          text: 'Esta acciÃ³n no se puede deshacer',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'SÃ­, eliminar',
          cancelButtonText: 'Cancelar'
        }).then((res)=>{
          if(!res.isConfirmed) return;

          $.ajax({
            url: "{{ url('admin/postulaciones') }}/" + id,
            type: 'POST',
            data: {
              _method: 'DELETE',
              _token: "{{ csrf_token() }}"
            },
            success: function(){
              table.ajax.reload(null, false);
              Swal.fire({icon:'success', title:'Eliminado', timer:1500, showConfirmButton:false});
            },
            error: function(xhr){
              Swal.fire({icon:'error', title:'No se pudo eliminar', text: xhr.responseJSON?.message || 'Intente nuevamente'});
            }
          });
        });
      });

});
</script>
<style>
  #btn-exportar-excel {
    min-width: 170px;
    font-weight: 600;
    white-space: nowrap;
    background-color: #1f9d55;
    border-color: #1f9d55;
    color: #fff;
  }

  #btn-exportar-excel:hover,
  #btn-exportar-excel:focus {
    background-color: #18864a;
    border-color: #18864a;
    color: #fff;
  }
</style>
@endpush
