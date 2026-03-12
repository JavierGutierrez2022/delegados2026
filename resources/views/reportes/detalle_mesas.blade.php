@extends('layouts.admin')

@section('content')

<div class="container">
    <h1 class="mb-4">Reporte: Mesas por Provincia, Municipio, Recinto y Agrupacion</h1>
    
            <div class="card card-outline card-primary">
                      <div class="card-header">
                        <h3 class="card-title"><b>Mesas por Provincia, Municipio y Recinto y Agrupacion</b></h3> 
                      </div>
    


                    <div class="card-body" style="display: block;">
                        
                        <form method="GET" action="{{ route('reportes.detalle_mesas') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="municipio_id"><strong>Filtrar por Municipio:</strong></label>
                                    <select name="municipio_id" id="municipio_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">-- Todos los municipios --</option>
                                        @foreach($municipios as $municipio)
                                            <option value="{{ $municipio->id }}" {{ ($municipio_id == $municipio->id) ? 'selected' : '' }}>
                                                {{ $municipio->name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                                                <th>N° Mesa</th>
                                                <th>Cantidad Miembros</th>
                                                <th>Agrupaciones</th>
                                                <th>Miembros</th>
                                                
                                            </tr>
                                      </thead>
                                      <tbody>
                                      <?php $contador = 0;?>
                                      
                                      @foreach ($datos as $dato)
                                            <tr>
                                                <td><?php echo $contador = $contador+1 ?></td>
                                                <td>{{ $dato->provincia }}</td>
                                                <td>{{ $dato->municipio }}</td>
                                                <td>{{ $dato->recinto }}</td>
                                                <td>{{ $dato->numero_mesa }}</td>
                                                <td>{{ $dato->cantidad_miembros }}</td>
                                                <td>{{ $dato->agrupacion }}</td>
                                                <td>{{ $dato->miembros_nombres }}</td>
                                            </tr>
                                        @endforeach
                                                
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