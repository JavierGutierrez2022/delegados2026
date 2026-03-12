@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Reporte de Recintos Electorales</h2>

 <div class="card-body" style="display: block;">
                
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                        <tr>
                            <th>Asiento Electoral</th>
                            <th>Número de Distrito</th>
                            <th>Nombre del Recinto</th>
                            <th>Cantidad de Mesas</th>
                            
                        </tr>
                  </thead>
                  <tbody>
                    @foreach($recintos as $recinto)
                        <tr>
                            <td>{{ $recinto->electoral_seat }}</td>
                            <td>{{ $recinto->distric_number }}</td>
                            <td>{{ $recinto->name }}</td>
                            <td>{{ $recinto->table }}</td>
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
@endsection
