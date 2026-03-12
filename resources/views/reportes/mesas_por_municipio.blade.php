@extends('layouts.admin') {{-- O usa tu layout base --}}

@section('content')
<div class="container">
    <h1 class="mb-4">Reporte Mesas por Municipio y Recinto</h1>
    
            <div class="card card-outline card-primary">
                      <div class="card-header">
                        <h3 class="card-title"><b>Mesas por Municipio y Recinto</b></h3> 
                      </div>
    


                    <div class="card-body" style="display: block;">
                
                            <table id="example1" class="table table-bordered table-striped">
                                      <thead>
                                            <tr>
                                                <th>Nro</th>
                                                <th>Municipio</th>
                                                <th>Recinto</th>
                                                <th>Total Mesas</th>
                                                
                                            </tr>
                                      </thead>
                                      <tbody>
                                      <?php $contador = 0;?>
                                                 @foreach ($reportes as $reporte)
                                                        <tr>
                                                            <td><?php echo $contador = $contador+1 ?></td>
                                                            <td>{{ $reporte->municipality_name }}</td>
                                                            <td>{{ $reporte->precinct_name }}</td>
                                                            <td>{{ $reporte->total_tables }}</td>
                                                            
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