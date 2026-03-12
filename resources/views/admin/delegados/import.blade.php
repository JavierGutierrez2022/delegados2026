@extends('layouts.admin')

@section('content')
<div class="content">
    <h1>Importar delegados por Excel</h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-file-earmark-excel"></i> Carga masiva de delegados</h3>
            </div>
            <div class="card-body">

                <div class="alert alert-info mb-4">
                    <strong>Flujo recomendado:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Descargue la plantilla oficial (incluye hojas <code>Plantilla</code> y <code>Catalogos</code>).</li>
                        <li>Llene las columnas obligatorias: <code>ci</code> y <code>nombres</code>.</li>
                        <li>Para ubicación use IDs (<code>province_id</code>, <code>municipality_id</code>, <code>electoral_precinct_id</code>) o nombres (<code>provincia</code>, <code>municipio</code>, <code>recinto</code>).</li>
                        <li>En <code>genero</code> use solo: <code>MASCULINO</code> o <code>FEMENINO</code>.</li>
                        <li><code>fecnac</code> debe ir en formato <code>YYYY-MM-DD</code> (ej: <code>1990-05-30</code>).</li>
                        <li><code>correo_electronico</code> es opcional, pero si se llena debe tener formato valido (ej: <code>persona@dominio.com</code>).</li>
                        <li>Para importar delegado ya asignado use: <code>assignment_scope</code> (<code>RECINTO</code> o <code>MESA</code>) y <code>assignment_role</code>.</li>
                        <li>Si <code>assignment_scope = MESA</code>, indique <code>table_id</code> o <code>mesa_numero</code> del mismo recinto.</li>
                        <li>Compatibilidad: si no llena <code>assignment_scope</code>/<code>assignment_role</code>, el sistema intentará mapear la columna antigua <code>delegado</code> (ej: JEFE RECINTO, DELEGADO MESA).</li>
                    </ol>
                </div>

                <div class="mb-4">
                    <a href="{{ route('delegados.import.template') }}" class="btn btn-success">
                        <i class="bi bi-download"></i> Descargar plantilla Excel
                    </a>
                </div>

                <form action="{{ route('delegados.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Archivo Excel (.xlsx, .xls)</label>
                                <input type="file" name="archivo" class="form-control" accept=".xlsx,.xls" required>
                            </div>
                        </div>
                        <div class="col-md-5 d-flex align-items-end gap-2">
                            <button type="submit" formaction="{{ route('delegados.import.preview') }}" class="btn btn-warning">
                                <i class="bi bi-search"></i> Prevalidar archivo
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Importar archivo
                            </button>
                        </div>
                    </div>
                </form>

                @if(session('preview_summary'))
                    <hr>
                    <h5>Resumen de prevalidación</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="alert alert-primary mb-2">Delegados nuevos: <strong>{{ session('preview_summary.delegados_nuevos') }}</strong></div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-primary mb-2">Delegados actualizados: <strong>{{ session('preview_summary.delegados_actualizados') }}</strong></div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-secondary mb-2">Delegados omitidos: <strong>{{ session('preview_summary.delegados_omitidos') }}</strong></div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-success mb-2">Asignaciones nuevas: <strong>{{ session('preview_summary.asig_nuevas') }}</strong></div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-success mb-2">Asignaciones actualizadas: <strong>{{ session('preview_summary.asig_actualizadas') }}</strong></div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-secondary mb-2">Asignaciones omitidas: <strong>{{ session('preview_summary.asig_omitidas') }}</strong></div>
                        </div>
                    </div>
                @endif

                @if(session('preview_errors') && count(session('preview_errors')) > 0)
                    <hr>
                    <h5>Observaciones de prevalidación</h5>
                    <div class="alert alert-warning">
                        Se muestran hasta 100 observaciones.
                    </div>
                    <ul class="mb-0">
                        @foreach(session('preview_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <hr>
                    <h5>Detalle de filas con observaciones (importación)</h5>
                    <div class="alert alert-warning">
                        Se muestran hasta 50 observaciones.
                    </div>
                    <ul class="mb-0">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif

                <hr>
                <a href="{{ route('delegados.create') }}" class="btn btn-secondary">Volver a creación manual</a>
            </div>
        </div>
    </div>
</div>
@endsection
