@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Reporte: Cantidad de Miembros por Agrupación</h2>

    @if ($agrupaciones->isEmpty())
        <div class="alert alert-warning">No hay datos disponibles.</div>
    @else
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Agrupación</th>
                    <th>Cantidad de Miembros</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($agrupaciones as $agrupacion)
                    <tr>
                        <td>{{ $agrupacion->agrupa }}</td>
                        <td>{{ $agrupacion->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('delegados.index') }}" class="btn btn-secondary mt-3">Volver</a>
</div>
@endsection
