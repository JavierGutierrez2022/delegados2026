@extends('layouts.admin')
@section('content')

<div class="content" style="margin-left:2px">
<h1>Listado de provincias</h1>
    <div class="row">    
        <div class="col-md-12">
            <div class="card card-outline card-primary">
              <div class="card-header">
                <h3 class="card-title"><b>Provincias Registradas</b></h3>           
              </div>
              <!-- /.card-header -->
              <div class="card-body" style="display: block;">
                
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                        <tr>
                            <th>Nro</th>
                            <th>Nombres</th>
                            <th>Estado</th>
                            <th>Acción</th>
                            
                        </tr>
                  </thead>
                  <tbody>
                  <?php $contador = 0;?>
                             @foreach ($provincias as $provincia)
                                    <tr>
                                        <td><?php echo $contador = $contador+1 ?></td>
                                        <td> {{$provincia->name}}</td>
                                        <td> {{$provincia->state}}</td>
                                        <td style="text-align:center">
                                                <div class="btn-group" role="group" aria-label="Basic example">
                                                    <a href="{{route('provincias.showmunicipio')}}" type="button" class="btn btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                        
                                                </div>
                                        </td>
                                        
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
                                        "info": "Mostrando START a END de TOTAL Miembros",
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
              <!-- /.card-body -->
            </div>
            
        </div>       
       
    </div>
  
 </div>

@endsection