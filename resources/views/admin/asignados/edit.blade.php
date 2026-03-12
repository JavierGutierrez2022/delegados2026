@extends('layouts.admin')
@section('content')
<div class="container mt-3">
  <div class="card">
    <div class="card-header">Editar Asignación</div>
    <div class="card-body">
      <form method="POST" action="{{ route('asignados.update', $assignment->id) }}">
        @csrf @method('PUT')

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Ámbito</label>
            <select name="scope" id="scope" class="form-select">
              <option value="RECINTO" {{ $assignment->scope==='RECINTO'?'selected':'' }}>RECINTO</option>
              <option value="MESA"    {{ $assignment->scope==='MESA'?'selected':'' }}>MESA</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Rol</label>
            <select name="role" id="role" class="form-select">
              @if($assignment->scope==='RECINTO')
                <option value="JEFE_DE_RECINTO"  @selected($assignment->role==='JEFE_DE_RECINTO')>Jefe de Recinto</option>
                <option value="MONITOR_RADAR"    @selected($assignment->role==='MONITOR_RADAR')>Monitor / Radar</option>
              @else
                <option value="DELEGADO_PROPIETARIO" @selected($assignment->role==='DELEGADO_PROPIETARIO')>Delegado Propietario</option>
                <option value="DELEGADO_SUPLENTE"   @selected($assignment->role==='DELEGADO_SUPLENTE')>Delegado Suplente</option>
              @endif
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Miembro (ID)</label>
            <input type="number" name="miembro_id" class="form-control" value="{{ $assignment->miembro_id }}">
            <small class="text-muted">* puedes reemplazar por select2/ajax si quieres</small>
          </div>

          <div class="col-md-6">
            <label class="form-label">Recinto</label>
            <select name="electoral_precinct_id" id="electoral_precinct_id" class="form-select">
              @foreach($recintos as $r)
                <option value="{{ $r->id }}" @selected($r->id==$assignment->electoral_precinct_id)>{{ $r->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6" id="wrap-mesa" {{ $assignment->scope==='MESA' ? '' : 'style=display:none' }}>
            <label class="form-label">Mesa</label>
            <select name="table_id" id="table_id" class="form-select">
              <option value="">—</option>
              @foreach($mesas as $m)
                <option value="{{ $m->id }}" @selected(optional($assignment->table)->id==$m->id)>Mesa {{ $m->table_number }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12 mt-3">
            <a href="{{ route('asignados.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
  $('#electoral_precinct_id').on('change', function(){
    const id = $(this).val();
    $('#table_id').html('<option value="">—</option>');
    if(!id) return;
    $.get("{{ url('/mesas/por-recinto') }}/"+id, function(res){
      let opts = '<option value="">—</option>';
      res.forEach(x=>opts+=`<option value="${x.id}">Mesa ${x.table_number}</option>`);
      $('#table_id').html(opts);
    });
  });

  $('#scope').on('change', function(){
    const scope = $(this).val();
    $('#wrap-mesa').toggle(scope==='MESA');
    // roles del ámbito
    let opts = '';
    if(scope==='MESA'){
      opts += `<option value="DELEGADO_PROPIETARIO">Delegado Propietario</option>`;
      opts += `<option value="DELEGADO_SUPLENTE">Delegado Suplente</option>`;
    }else{
      opts += `<option value="JEFE_DE_RECINTO">Jefe de Recinto</option>`;
      opts += `<option value="MONITOR_RADAR">Monitor / Radar</option>`;
    }
    $('#role').html(opts);
  });
});
</script>
@endpush
