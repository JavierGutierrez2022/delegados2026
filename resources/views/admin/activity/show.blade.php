@extends('layouts.admin')

@section('content')
<div class="container-fluid">
  <h4 class="mb-3">Detalle de actividad</h4>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">Información</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4">Fecha</dt>
            <dd class="col-sm-8">
              <span id="fecha-local" data-iso="{{ $actividad->created_at?->toIso8601String() }}">
                {{ $actividad->created_at->format('Y-m-d H:i:s') }}
              </span>
            </dd>

            <dt class="col-sm-4">Usuario</dt>
            <dd class="col-sm-8">{{ $actividad->user->name ?? '—' }} (ID: {{ $actividad->user_id ?? '—' }})</dd>

            <dt class="col-sm-4">Acción</dt>
            <dd class="col-sm-8">{{ $actividad->action }}</dd>

            <dt class="col-sm-4">Modelo</dt>
            <dd class="col-sm-8">{{ class_basename($actividad->model_type) ?? '—' }} (ID: {{ $actividad->model_id ?? '—' }})</dd>

            <dt class="col-sm-4">URL</dt>
            <dd class="col-sm-8"><small>{{ $actividad->url }}</small></dd>

            <dt class="col-sm-4">IP</dt>
            <dd class="col-sm-8">{{ $actividad->ip }}</dd>

            <dt class="col-sm-4">User-Agent</dt>
            <dd class="col-sm-8">
              <div><strong>{{ $uaInfo['label'] ?? '-' }}</strong></div>
              <small class="text-muted">{{ $uaInfo['raw'] ?? '-' }}</small>
            </dd>

            <dt class="col-sm-4">Descripción</dt>
            <dd class="col-sm-8">{{ $actividad->description ?? '—' }}</dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card">
        <div class="card-header">Cambios</div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <thead>
            <tr>
              <th>Campo</th>
              <th>Antes</th>
              <th>Después</th>
            </tr>
            </thead>
            <tbody>
            @forelse($diff as $row)
              <tr @class(['table-warning' => $row['changed']])>
                <td><code>{{ $row['key'] }}</code></td>
                <td><small>{{ is_scalar($row['before']) ? $row['before'] : json_encode($row['before']) }}</small></td>
                <td><small>{{ is_scalar($row['after']) ? $row['after'] : json_encode($row['after']) }}</small></td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted">Sin cambios para mostrar.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <a href="{{ route('actividad.index') }}" class="btn btn-secondary mt-3">Volver</a>
</div>
@endsection

@push('scripts')
<script>
$(function(){
  const $el = $('#fecha-local');
  if (!$el.length) return;
  const iso = $el.data('iso');
  if (!iso) return;
  const d = new Date(iso);
  if (isNaN(d.getTime())) return;
  $el.text(d.toLocaleString('es-BO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  }));
});
</script>
@endpush
