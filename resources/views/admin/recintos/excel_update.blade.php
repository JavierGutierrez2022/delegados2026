@extends('layouts.admin')

@section('content')
<div class="content">
    <h1>ActualizaciÃ³n de recintos y cantidad de mesas (Excel)</h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-file-earmark-spreadsheet"></i> Carga masiva de cambios</h3>
            </div>
            <div class="card-body">
                                <div class="alert alert-info">
                    <strong>Columnas obligatorias del Excel:</strong>
                    <ul class="mb-0 mt-2">
                        <li><code>provincia</code></li>
                        <li><code>municipio</code></li>
                        <li><code>recinto</code></li>
                        <li><code>cantidad_mesas</code></li>
                        <li><code>state</code></li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <strong>Regla de seguridad:</strong> si intentas reducir mesas y esas mesas tienen asignaciones/vÃ­nculos, la fila se omite y se reporta error.
                </div>
                <div class="alert alert-secondary">
                    <strong>Modo lista maestra:</strong> los recintos que no estÃ©n en el Excel se marcarÃ¡n como <code>INACTIVO</code>.
                </div>

                <a href="{{ route('recintos.excel.template') }}" class="btn btn-success mb-3">
                    <i class="bi bi-download"></i> Descargar plantilla de actualizaciÃ³n
                </a>

                <form action="{{ route('recintos.excel.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Archivo Excel (.xlsx, .xls)</label>
                                <input type="file" name="archivo" class="form-control" accept=".xlsx,.xls" required>
                            </div>
                        </div>
                        <div class="col-md-5 d-flex align-items-end gap-2">
                            <button type="submit" formaction="{{ route('recintos.excel.preview') }}" class="btn btn-warning">
                                <i class="bi bi-search"></i> Prevalidar archivo
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Aplicar actualizaciÃ³n
                            </button>
                        </div>
                    </div>
                </form>

                @if(session('preview_summary'))
                    <hr>
                    <h5>Resumen de prevalidaciÃ³n</h5>
                    <div class="row">
                        <div class="col-md-4"><div class="alert alert-primary mb-2">Filas procesadas: <strong>{{ session('preview_summary.filas_procesadas') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-info mb-2">Recintos actualizados: <strong>{{ session('preview_summary.recintos_actualizados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-light mb-2">Recintos creados: <strong>{{ session('preview_summary.recintos_creados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-dark mb-2">Recintos inactivados: <strong>{{ session('preview_summary.recintos_inactivados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-success mb-2">Mesas agregadas: <strong>{{ session('preview_summary.mesas_agregadas') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-secondary mb-2">Mesas reducidas: <strong>{{ session('preview_summary.mesas_reducidas') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-warning mb-2">Filas omitidas: <strong>{{ session('preview_summary.filas_omitidas') }}</strong></div></div>
                    </div>
                @endif

                @if(session('import_summary'))
                    <hr>
                    <h5>Resumen de actualizaciÃ³n aplicada</h5>
                    <div class="row">
                        <div class="col-md-4"><div class="alert alert-primary mb-2">Filas procesadas: <strong>{{ session('import_summary.filas_procesadas') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-info mb-2">Recintos actualizados: <strong>{{ session('import_summary.recintos_actualizados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-light mb-2">Recintos creados: <strong>{{ session('import_summary.recintos_creados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-dark mb-2">Recintos inactivados: <strong>{{ session('import_summary.recintos_inactivados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-success mb-2">Mesas agregadas: <strong>{{ session('import_summary.mesas_agregadas') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-secondary mb-2">Mesas reducidas: <strong>{{ session('import_summary.mesas_reducidas') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-warning mb-2">Filas omitidas: <strong>{{ session('import_summary.filas_omitidas') }}</strong></div></div>
                    </div>
                @endif

                @if(session('preview_errors') && count(session('preview_errors')) > 0)
                    <hr>
                    <h5>Observaciones de prevalidaciÃ³n</h5>
                    <ul class="mb-0">
                        @foreach(session('preview_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <hr>
                    <h5>Observaciones de actualizaciÃ³n</h5>
                    <ul class="mb-0">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


