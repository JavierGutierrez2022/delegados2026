@extends('layouts.admin')

@section('content')
<div class="content">
    <h1>Actualizacion de distritos por recinto (Excel)</h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-map"></i> Carga de distritos para municipio Tarija</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Columnas obligatorias del Excel:</strong>
                    <ul class="mb-0 mt-2">
                        <li><code>distrito</code></li>
                        <li><code>recinto</code></li>
                    </ul>
                </div>

                <div class="alert alert-secondary">
                    Esta carga actualiza solo recintos del municipio <strong>Tarija</strong>.
                    Si el distrito no existe, se crea automaticamente y se asigna al recinto.
                </div>

                <a href="{{ route('distritos.excel.template') }}" class="btn btn-success mb-3">
                    <i class="bi bi-download"></i> Descargar plantilla de distritos
                </a>

                <form action="{{ route('distritos.excel.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Archivo Excel (.xlsx, .xls)</label>
                                <input type="file" name="archivo" class="form-control" accept=".xlsx,.xls" required>
                            </div>
                        </div>
                        <div class="col-md-5 d-flex align-items-end gap-2">
                            <button type="submit" formaction="{{ route('distritos.excel.preview') }}" class="btn btn-warning">
                                <i class="bi bi-search"></i> Prevalidar archivo
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Aplicar actualizacion
                            </button>
                        </div>
                    </div>
                </form>

                @if(session('preview_summary'))
                    <hr>
                    <h5>Resumen de prevalidacion</h5>
                    <div class="row">
                        <div class="col-md-4"><div class="alert alert-primary mb-2">Filas procesadas: <strong>{{ session('preview_summary.filas_procesadas') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-info mb-2">Distritos creados: <strong>{{ session('preview_summary.distritos_creados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-success mb-2">Distritos activados/actualizados: <strong>{{ session('preview_summary.distritos_actualizados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-dark mb-2">Recintos actualizados: <strong>{{ session('preview_summary.recintos_actualizados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-warning mb-2">Filas omitidas: <strong>{{ session('preview_summary.filas_omitidas') }}</strong></div></div>
                    </div>
                @endif

                @if(session('import_summary'))
                    <hr>
                    <h5>Resumen de actualizacion aplicada</h5>
                    <div class="row">
                        <div class="col-md-4"><div class="alert alert-primary mb-2">Filas procesadas: <strong>{{ session('import_summary.filas_procesadas') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-info mb-2">Distritos creados: <strong>{{ session('import_summary.distritos_creados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-success mb-2">Distritos activados/actualizados: <strong>{{ session('import_summary.distritos_actualizados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-dark mb-2">Recintos actualizados: <strong>{{ session('import_summary.recintos_actualizados') }}</strong></div></div>
                        <div class="col-md-4"><div class="alert alert-warning mb-2">Filas omitidas: <strong>{{ session('import_summary.filas_omitidas') }}</strong></div></div>
                    </div>
                @endif

                @if(session('preview_errors') && count(session('preview_errors')) > 0)
                    <hr>
                    <h5>Observaciones de prevalidacion</h5>
                    <ul class="mb-0">
                        @foreach(session('preview_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <hr>
                    <h5>Observaciones de actualizacion</h5>
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

