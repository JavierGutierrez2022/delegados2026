@extends('layouts.admin')

@section('content')
<div class="container-fluid">
  <h3 class="mb-3">Editar Miembro</h3>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('postulaciones.update', $miembro) }}">
        @csrf @method('PUT')

        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">C.I.</label>
            <input type="text" name="ci" value="{{ old('ci',$miembro->ci) }}" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Nombres</label>
            <input type="text" name="nombres" value="{{ old('nombres',$miembro->nombres) }}" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Apellido Paterno</label>
            <input type="text" name="app" value="{{ old('app',$miembro->app) }}" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Apellido Materno</label>
            <input type="text" name="apm" value="{{ old('apm',$miembro->apm) }}" class="form-control">
          </div>

          <div class="col-md-3">
            <div class="form-group">
            <label class="form-label">Género</label>
                <select name="genero" class="form-control">
                <option value="MASCULINO" {{ old('genero',$miembro->genero)=='MASCULINO'?'selected':'' }}>MASCULINO</option>
                <option value="FEMENINO"  {{ old('genero',$miembro->genero)=='FEMENINO'?'selected':'' }}>FEMENINO</option>
                </select>
            </div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Fecha Nacimiento</label>
            <input type="date" name="fecnac" value="{{ old('fecnac', optional($miembro->fecnac)->format('Y-m-d')) }}" class="form-control">
          </div>

          <div class="col-md-3">
            <label class="form-label">Celular</label>
            <input type="text" name="celular" value="{{ old('celular',$miembro->celular) }}" class="form-control">
          </div>

          <div class="col-md-3">
            <label class="form-label">Observaciones</label>
            <input type="text" name="obs" value="{{ old('obs',$miembro->obs) }}" class="form-control">
          </div>

          <div class="col-12"><hr><strong>Ubicación de votación</strong></div>

          <div class="col-md-4">
            <label class="form-label">Provincia</label>
            <select name="province_id" id="province_id" class="form-control">
              <option value="">Seleccione</option>
              @foreach($provincias as $p)
                <option value="{{ $p->id }}" {{ (old('province_id',$miembro->province_id)==$p->id)?'selected':'' }}>{{ $p->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Municipio</label>
            <select name="municipality_id" id="municipality_id" class="form-control">
              @foreach($municipios as $m)
                <option value="{{ $m->id }}" {{ (old('municipality_id',$miembro->municipality_id)==$m->id)?'selected':'' }}>{{ $m->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Recinto</label>
            <select name="electoral_precinct_id" id="precinct_id" class="form-control">
              @foreach($recintos as $r)
                <option value="{{ $r->id }}" {{ (old('electoral_precinct_id',$miembro->electoral_precinct_id)==$r->id)?'selected':'' }}>{{ $r->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <hr>
        <a href="{{ route('postulaciones.index') }}" class="btn btn-secondary">Volver</a>
        <button class="btn btn-success">Guardar cambios</button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$('#province_id').on('change', function(){
  let id = $(this).val();
  $('#municipality_id').html('<option value="">Cargando...</option>');
  $('#precinct_id').html('<option value="">Seleccione un recinto</option>');
  if(!id) return;
  $.get("{{ url('/admin/municipios/por-provincia') }}/"+id, function(res){
    let opts = res.map(m=>`<option value="${m.id}">${m.name}</option>`).join('');
    $('#municipality_id').html(opts);
  });
});

$('#municipality_id').on('change', function(){
  let id = $(this).val();
  $('#precinct_id').html('<option value="">Cargando...</option>');
  if(!id) return;
  $.get("{{ url('/recintos/por-municipio') }}/"+id, function(res){
    let opts = res.map(r=>`<option value="${r.id}">${r.name}</option>`).join('');
    $('#precinct_id').html(opts);
  });
});
</script>
@endpush
