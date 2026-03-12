@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Reporte de Recintos</h1>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Provincia</th>
                <th>Municipio</th>
                <th>Recinto</th>
                <th>Total Mesas</th>
                <th>Delegados Mesa</th>
                <th>Jefes Recinto</th>
                <th>Miembros</th>
                <th>Mesas con Miembros</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultados as $fila)
                <tr>
                    <td>{{ $fila->provincia }}</td>
                    <td>{{ $fila->municipio }}</td>
                    <td>{{ $fila->recinto }}</td>
                    <td>{{ $fila->total_mesas }}</td>
                    <td>{{ $fila->cantidad_delegados_mesa }}</td>
                    <td>{{ $fila->cantidad_jefes_recinto }}</td>
                    <td>{{ $fila->miembros }}</td>
                    <td>{{ $fila->mesas_con_miembros }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
