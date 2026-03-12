@extends('layouts.admin')

@section('content')
<div class="container-fluid">
  <h3 class="mb-3">Reporte de actividad del sistema</h3>

  <div class="card mb-3">
    <div class="card-body">
      <form id="filtros" class="row g-3">
        <div class="col-md-2">
          <label class="form-label">Desde</label>
          <input type="date" id="from" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Hasta</label>
          <input type="date" id="to" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Usuario</label>
          <select id="user_id" class="form-control">
            <option value="">Todos</option>
            @foreach($usuarios as $u)
              <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Acción</label>
          <select id="action" class="form-control">
            <option value="">Todas</option>
            @foreach($acciones as $a)
              <option value="{{ $a }}">{{ $a }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Modelo</label>
          <select id="model_type" class="form-control">
            <option value="">Todos</option>
            @foreach($modelos as $m)
              <option value="{{ $m }}">{{ class_basename($m) }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">ID del modelo</label>
          <input type="text" id="model_id" class="form-control" placeholder="Opcional">
        </div>
        <div class="col-md-5">
          <label class="form-label">Buscar</label>
          <input type="text" id="term" class="form-control" placeholder="Descripción, URL, IP, User-Agent">
        </div>

        <div class="col-md-4 d-flex align-items-end justify-content-end gap-2">
          <button id="btn-limpiar" type="button" class="btn btn-secondary">Limpiar</button>

          <form id="form-purge" action="{{ route('actividad.purge') }}" method="POST" class="d-inline">
            @csrf
            <div class="input-group">
              <span class="input-group-text">Purgar &lt;</span>
              <input type="number" name="days" value="90" min="1" class="form-control" style="max-width:90px">
              <span class="input-group-text">días</span>
              <button class="btn btn-outline-danger" type="submit"
                onclick="return confirm('¿Eliminar registros anteriores al rango indicado?')">Ejecutar</button>
            </div>
          </form>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <table id="tabla-actividad" class="table table-striped table-bordered w-100">
        <thead>
        <tr>
          <th>#</th>
          <th>Fecha</th>
          <th>Usuario</th>
          <th>Acción</th>
          <th>Modelo</th>
          <th>ID</th>
          <th>Resumen</th>
          <th>Acciones</th>
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
  function fmtLocalDate(isoValue){
    if(!isoValue) return '';
    const d = new Date(isoValue);
    if (isNaN(d.getTime())) return isoValue;
    return d.toLocaleString('es-BO', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    });
  }

  const table = $('#tabla-actividad').DataTable({
    processing: true, serverSide: true, responsive: true,
    order: [[1,'desc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    ajax: {
      url: "{{ route('actividad.data') }}",
      data: function(d){
        d.from  = $('#from').val();
        d.to    = $('#to').val();
        d.user_id = $('#user_id').val();
        d.action  = $('#action').val();
        d.model_type = $('#model_type').val();
        d.model_id   = $('#model_id').val();
        d.term = $('#term').val();
      }
    },
    columns: [
      { data:'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false },
      {
        data:'created_at',
        name:'created_at',
        render: function(data, type){
          if (type === 'sort' || type === 'type') {
            const t = Date.parse(data);
            return isNaN(t) ? data : t;
          }
          return fmtLocalDate(data);
        }
      },
      { data:'usuario',    name:'usuario', orderable:false, searchable:false },
      { data:'action',     name:'action' },
      { data:'modelo',     name:'model_type' },
      { data:'model_id',   name:'model_id' },
      { data:'resumen',    name:'resumen', orderable:false, searchable:false },
      { data:'acciones',   orderable:false, searchable:false },
    ],
    dom: 'Bfrtip',
    buttons: ['excel','pdf','print','colvis']
  });

  $('#filtros input, #filtros select').on('change keyup', ()=> table.ajax.reload());

  $('#btn-limpiar').on('click', function(){
    $('#filtros')[0].reset();
    table.ajax.reload();
  });
});
</script>
@endpush
