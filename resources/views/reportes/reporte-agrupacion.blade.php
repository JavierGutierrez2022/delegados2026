@extends('layouts.admin')

@section('content')
        <div class="container mt-4">
                <div class="card-header">
                    <h3 class="card-title"><b>Miembros por agrupacion</b></h3>
                </div>
                <div class="card-body" style="display: block;">

                    <div class="row">    
                                <div class="col-md-12">
                                    <div class="card card-outline card-primary">
                                              <div class="card-header">
                                                    <h3 class="card-title"><b>Miembros Registrados</b></h3> 
                                                
                                                
                                              </div>
                                      
                                      <!-- /.card-header -->
                                      <div class="card-body" style="display: block;">
                                        
                                                <table id="example1" class="table table-bordered table-striped">
                                                  <thead>
                                                        <tr>
                                                            <th>Nro</th>
                                                            <th>Agrupación</th>
                                                            <th>Cantidad de Miembros</th>
                                                            
                                                        </tr>
                                                  </thead>
                                                  <tbody>
                                                  <?php $contador = 0;?>
                                                  
                                                             @foreach ($agrupaciones as $agrupacion)
                                                                    <tr>
                                                                        <td><?php echo $contador = $contador+1 ?></td>
                                                                        <td>{{ $agrupacion->agrupa ?? 'Sin Agrupación' }}</td>
                                                                        <td>{{ $agrupacion->total }}</td>
                                                                        
                                                                        
                                                                    </tr>                  
                                                            @endforeach
                                                                    <tr>
                                                                        <td></td>
                                                                        <td><strong>Total General</strong></td>
                                                                        <td><strong>{{ $agrupaciones->sum('total') }}</strong></td>
                                                                    </tr>
                                                
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
                                        </div>                                 <!-- /.card-body -->
                               
                                     </div>
                                 </div>   
             
                     </div> 
               </div>
            </div> 
        


@endsection