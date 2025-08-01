@extends('layouts.admin')

@section('content')

<div class="content" style="margin-left:2px">
<h1>Listado de Delegados de Mesa index</h1>

@if($message = Session::get('mensaje'))
            <script>
                Swal.fire({
                        title: "Registro Exitoso!",
                        text: "{{$message}}",
                        icon: "success"
                });
            </script>        
        @endif


    <div class="row">    
        <div class="col-md-12">
            <div class="card card-outline card-primary">
              <div class="card-header">
                <h3 class="card-title"><b>Miembros Registrados</b></h3> 
                
                <!-- boton agregar -->
              <div class="card-tools">
                  <a href="{{url('admin/delegados/create')}}" class="btn btn-primary">

                    <i class="bi bi-person-fill-add"></i> Agregar Miembro
                  </a>
                </div>
                <!-- /. boton agregar -->

              </div>
              
              <!-- /.card-header -->
              <div class="card-body" style="display: block;">
                
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                        <tr>
                            <th>Nro</th>
                            <th>Nombres</th>
                            <th>Apellido Paterno</th>
                            <th>Apellido Materno</th>
                            <th>Genero</th>
                            <th>C.I.</th>
                            <th>Fecha Nacimiento</th>
                            <th>Celular</th>
                            <th>Recinto Votación</th>
                            <th>Agrupación</th>
                            <th>Observación</th>
                            <th>Estado</th>
                            <th>Encargado</th>
                            <th>Provincia</th>
                            <th>Municipio</th>
                            <th>Recinto</th>
                            <th>Mesa</th>
                            <th>Acción</th>
                            
                        </tr>
                  </thead>
                  <tbody>
                  <?php $contador = 0;?>
                             @foreach ($miembros as $miembro)
                                    <tr>
                                        <td><?php echo $contador = $contador+1 ?></td>
                                        <td> {{$miembro->nombres}}</td>
                                        <td> {{$miembro->app}}</td>
                                        <td> {{$miembro->apm}}</td>
                                        <td> {{$miembro->genero}}</td>
                                        <td> {{$miembro->ci}}</td>
                                        <td> {{$miembro->fecnac}}</td>
                                        <td> {{$miembro->celular}}</td>
                                        <td> {{$miembro->recintovot}}</td>
                                        <td> {{$miembro->agrupa}}</td>
                                        <td> {{$miembro->obs}}</td>
                                        <td> {{$miembro->estado}}</td>
                                        <td> {{$miembro->delegado}}</td>
                                       <td>{{ $miembro->province->name ?? 'Sin provincia' }}</td>
                                        <td>{{ $miembro->municipality->name ?? 'Sin municipio' }}</td>
                                        <td>{{ $miembro->electoralPrecinct->name ?? 'Sin recinto' }}</td>
                                        <td>
                                            @if(!empty($miembro->tables) && $miembro->tables->isNotEmpty())
                                                {{ $miembro->tables->pluck('table_number')->unique()->implode(', ') }}
                                            @else
                                                Sin mesas
                                            @endif
                                        </td>


                                        <td> 
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="{{route('delegados.edit',$miembro->id)}}" type="button" class="btn btn-success"><i class="bi bi-pencil-square"></i> Editar</a>
                                                <form action="{{url('delegados', $miembro->id)}}" method="post">
                                                    @csrf
                                                    {{method_field('DELETE')}}
                                                    <button type="submit" onclick="return confirm('¿Esta seguro de eliminar este registro?')" class="btn btn-danger"><i class="bi bi-trash3-fill"></i> Eliminar</button> 
                                                </form>
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

{{-- $('#electoral_precinct_id').on('change', function () {
    var precinctId = $(this).val();

    if (precinctId) {
        $.ajax({
            url: '/mesas/por-recinto/' + precinctId,
            type: 'GET',
            success: function (data) {
                $('#table_ids').empty();
                if (data.length > 0) {
                    data.forEach(function (mesa) {
                        $('#table_ids').append(`<option value="${mesa.id}">${mesa.table_number}</option>`);
                    });
                } else {
                    $('#table_ids').append('<option disabled>No hay mesas disponibles</option>');
                }
            },
            error: function () {
                alert('Error al cargar mesas');
            }
        });
    } else {
        $('#table_ids').empty();
    }
}); --}}

