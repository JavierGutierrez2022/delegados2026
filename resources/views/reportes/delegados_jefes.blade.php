@extends('layouts.admin')

@section('content')

               <div class="container">
                <h1 class="mb-4">Reporte: Delegados y Jefes de Recinto</h1>
    
                 <div class="card card-outline card-primary">
                      <div class="card-header">
                        <h3 class="card-title"><b>Cantidad de "Delegados de Mesa" y "Jefes de Recinto"</b></h3> 
                      </div>
                      <div class="card-body" style="display: block;">
    
                                    {{-- Filtro por Municipio --}}
                                    <form method="GET" action="{{ route('reportes.delegados_jefes') }}" class="mb-4">
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <select name="municipio_id" class="form-select">
                                                    <option value="">-- Todos los Municipios --</option>
                                                    @foreach($municipios as $mun)
                                                        <option value="{{ $mun->id }}" {{ $municipioId == $mun->id ? 'selected' : '' }}>
                                                            {{ $mun->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                            </div>
                                        </div>
                                    </form>
                
                            <table id="example1" class="table table-bordered table-striped">
                                      <thead>
                                            <tr>
                                                <th>Nro</th>
                                                <th>Provincia</th>
                                                <th>Municipio</th>
                                                <th>Recinto</th>
                                                <th>Total Mesas</th> {{-- Nueva columna --}}
                                                <th>Cantidad Delegados Mesa</th>
                                                <th>Cantidad Jefes Recinto</th>
                                                
                                                
                                            </tr>
                                      </thead>
                                      <tbody>
                                      <?php $contador = 0;?>
                                       @forelse($datos as $fila)
                                                <tr>
                                                    <td><?php echo $contador = $contador+1 ?></td>
                                                    <td>{{ $fila->provincia }}</td>
                                                    <td>{{ $fila->municipio }}</td>
                                                    <td>{{ $fila->recinto }}</td>
                                                     <td>{{ $fila->total_mesas }}</td> {{-- Mostramos total --}}
                                                    <td>{{ $fila->cantidad_delegados_mesa }}</td>
                                                    <td>{{ $fila->cantidad_jefes_recinto }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No hay datos para mostrar</td>
                                                </tr>
                                            @endforelse
                                      </tbody>
                              
                            </table>
            
                            <script>
                                      $(function () {
                                        $("#example1").DataTable({
                                            "pageLength": 10,
                                            "language": {
                                                "emptyTable": "No hay información",
                                                "info": "Mostrando _START_ a _END_ de _TOTAL_ Miembros",
                                                "infoEmpty": "Mostrando 0 a 0 de 0 Miembros",
                                                "infoFiltered": "(Filtrado de MAX total Miembros)",
                                                "infoPostFix": "",
                                                "thousands": ",",
                                                "lengthMenu": "Mostrar MENU Miembros",
                                                "loadingRecords": "Cargando...",
                                                "processing": "Procesando...",
                                                "search": "Buscador:",
                                                "zeroRecords": "Sin resultados encontrados",
                                                "paginate": {
                                                    "first": "Primero",
                                                    "last": "Ultimo",
                                                    "next": "Siguiente",
                                                    "previous": "Anterior"
                                                }
                                            },
                                            "responsive": true, "lengthChange": true, "autoWidth": false,
                                            buttons: [{
                                                extend: 'collection',
                                                text: 'Reportes',
                                                orientation: 'landscape',
                                                buttons: [{
                                                    text: 'Copiar',
                                                    extend: 'copy',
                                                }, {
                                                    extend: 'pdf'
                                                },{
                                                    extend: 'csv'
                                                },{
                                                    extend: 'excel'
                                                },{
                                                    text: 'Imprimir',
                                                    extend: 'print'
                                                }
                                                ]
                                            },
                                                {
                                                    extend: 'colvis',
                                                    text: 'Visor de columnas',
                                                    collectionLayout: 'fixed three-column'
                                                }
                                            ],
                                        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
                                    });
                            </script>
                    </div>
            </div>
    </div>


@endsection