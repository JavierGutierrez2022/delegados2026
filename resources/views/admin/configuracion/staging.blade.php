@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h1>Staging: Datos de Prueba</h1>
        <p class="text-muted mb-3">Use este espacio para generar y limpiar datos de prueba sin tocar los datos reales del sistema.</p>
        <div class="alert alert-info">
            Reglas aplicadas en staging:
            1 jefe de recinto por recinto, 1 delegado titular y 1 suplente por mesa (si no existen aun),
            y multiples postulantes por recinto.
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="alert alert-primary mb-2">Miembros de prueba: <strong>{{ $stagingMiembros }}</strong></div>
    </div>
    <div class="col-md-4">
        <div class="alert alert-info mb-2">Asignaciones de prueba: <strong>{{ $stagingAsignaciones }}</strong></div>
    </div>
    <div class="col-md-4">
        <div class="alert alert-secondary mb-2">Vinculos de mesa (miembro_table): <strong>{{ $stagingMesasVinculadas }}</strong></div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Postulantes por recinto</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('staging.seed.postulantes') }}">
                    @csrf
                    <div class="form-group">
                        <label for="cantidad">Cantidad de postulantes</label>
                        <input type="number" min="1" max="1000" name="cantidad" id="cantidad" class="form-control" value="100" required>
                        <small class="text-muted">Crea postulantes de prueba distribuidos en recintos activos.</small>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-person-fill-add"></i> Insertar postulantes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Solo jefes de recinto</h3>
            </div>
            <div class="card-body">
                <p class="mb-3">Asigna un jefe por recinto activo (si falta).</p>
                <form method="POST" action="{{ route('staging.seed.jefes') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-badge-fill"></i> Insertar jefes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Solo delegados por mesa</h3>
            </div>
            <div class="card-body">
                <p class="mb-3">Asigna titular y suplente por mesa activa (si faltan).</p>
                <form method="POST" action="{{ route('staging.seed.delegados') }}">
                    @csrf
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-people-fill"></i> Insertar delegados
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Generar todo (postulantes + jefes + delegados)</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('staging.seed') }}">
                    @csrf
                    <div class="form-group">
                        <label for="cantidad_full">Cantidad de postulantes base</label>
                        <input type="number" min="1" max="1000" name="cantidad" id="cantidad_full" class="form-control" value="100" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-database-fill-add"></i> Generar staging completo
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Limpiar datos de prueba</h3>
            </div>
            <div class="card-body">
                <p class="mb-3">Elimina solo registros marcados con <code>[STAGING]</code> en observaciones.</p>
                <form method="POST" action="{{ route('staging.clear') }}" id="form-clear-staging">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Limpiar staging
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function(){
    $('#form-clear-staging').on('submit', function(e){
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Limpiar datos de prueba',
            text: 'Esta accion eliminara todos los registros de staging.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Si, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
