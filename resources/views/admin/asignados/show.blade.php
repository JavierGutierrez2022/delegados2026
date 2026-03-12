@extends('layouts.admin')
@section('content')
<div class="container mt-3">
  <div class="card">
    <div class="card-header">Detalle de Asignación</div>
    <div class="card-body">
      <p><strong>Nombre:</strong> {{ $assignment->miembro->nombres }} {{ $assignment->miembro->app }} {{ $assignment->miembro->apm }}</p>
      <p><strong>Ámbito:</strong> {{ $assignment->scope }}</p>
      <p><strong>Rol:</strong> {{ $assignment->role }}</p>
      <p><strong>Recinto:</strong> {{ $assignment->precinct->name ?? '—' }}</p>
      <p><strong>Mesa:</strong> {{ $assignment->table->table_number ?? '—' }}</p>
      <a href="{{ route('asignados.index') }}" class="btn btn-secondary">Volver</a>
    </div>
  </div>
</div>
@endsection
