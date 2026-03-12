@extends('layouts.admin')

@section('content')



<div class="container">
    <h1 class="mb-4">Reporte: Mesas por Provincia, Municipio, Recinto y Agrupacion</h1>
    
            <div class="card card-outline card-primary">
                      <div class="card-header">
                        <h3 class="card-title"><b>Mesas por Provincia, Municipio y Recinto y Agrupacion</b></h3> 
                      </div>
    


                    <div class="card-body" style="display: block;">
                        
                        {{-- Filtro por Provincia --}}
                                <form method="GET" action="{{ route('reportes.mesas_por_provincia') }}" class="mb-4">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Provincia</label>
                                            <select name="province_id" class="form-control" onchange="this.form.submit()">
                                                <option value="">-- Todas --</option>
                                                @foreach($provincias as $id => $nombre)
                                                    <option value="{{ $id }}" {{ $provinciaSeleccionada == $id ? 'selected' : '' }}>
                                                        {{ $nombre }}
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
                                                <th>Nro Mesa</th>
                                                <th>Agrupación</th>
                                                <th>Nombre Miembro</th>
                                                
                                            </tr>
                                      </thead>
                                      <tbody>
                                      <?php $contador = 0;?>
                                      
                                     @foreach($datos as $fila)
                                            <tr>
                                                <td><?php echo $contador = $contador+1 ?></td>
                                                <td>{{ $fila->provincia }}</td>
                                                <td>{{ $fila->municipio }}</td>
                                                <td>{{ $fila->recinto }}</td>
                                                <td>{{ $fila->nro_mesa }}</td>
                                                <td>{{ $fila->agrupacion ?? '-' }}</td>
                                                <td>{{ $fila->nombre_miembro ?? '-' }}</td>
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

