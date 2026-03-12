@extends('layouts.admin')

@section('content')
<div class="container-fluid">
  <h3 class="mb-3">Detalle de Miembro</h3>

  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-3">C.I.</dt>            <dd class="col-sm-9">{{ $miembro->ci }}</dd>
        <dt class="col-sm-3">Nombres</dt>         <dd class="col-sm-9">{{ $miembro->nombres }}</dd>
        <dt class="col-sm-3">Apellido Paterno</dt><dd class="col-sm-9">{{ $miembro->app }}</dd>
        <dt class="col-sm-3">Apellido Materno</dt><dd class="col-sm-9">{{ $miembro->apm }}</dd>
        <dt class="col-sm-3">Género</dt>          <dd class="col-sm-9">{{ $miembro->genero }}</dd>
        <dt class="col-sm-3">Nacimiento</dt>      <dd class="col-sm-9">{{ optional($miembro->fecnac)->format('d/m/Y') }}</dd>
        <dt class="col-sm-3">Celular</dt>         <dd class="col-sm-9">{{ $miembro->celular }}</dd>
        <dt class="col-sm-3">Observaciones</dt>   <dd class="col-sm-9">{{ $miembro->obs }}</dd>
        <dt class="col-sm-3">Provincia</dt>       <dd class="col-sm-9">{{ $miembro->province->name ?? '—' }}</dd>
        <dt class="col-sm-3">Municipio</dt>       <dd class="col-sm-9">{{ $miembro->municipality->name ?? '—' }}</dd>
        <dt class="col-sm-3">Recinto</dt>         <dd class="col-sm-9">{{ $miembro->electoralPrecinct->name ?? '—' }}</dd>
      </dl>
      <a href="{{ route('postulaciones.index') }}" class="btn btn-secondary">Volver</a>
      <a href="{{ route('postulaciones.edit', $miembro) }}" class="btn btn-primary">Editar</a>
    </div>
  </div>
</div>
@endsection