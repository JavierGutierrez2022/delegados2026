@extends('layouts.admin')
@section('content')

    <div class="content">
        <h1>Creación de nuevo delegado</h1>
    </div>  
    
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
                    <h3 class="card-title"><i class="bi bi-journal-check"></i> ---Llene los datos---</h3>
                </div>
                <div class="card-body">

                    <form action="{{url('admin/delegados')}}" method="post">
                        @csrf
                                <div class="row">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">C.I.</label>
                                            <input type="text" name="ci" class="form-control">
                                        </div>
                                    </div>                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Nombres</label>
                                            <input type="text"name="nombres" class="form-control">
                                        </div>
                                        </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Apellido Paterno</label>
                                            <input type="text" name="app" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="">Apellido Materno</label>
                                            <input type="text" name="apm" class="form-control">
                                        </div>
                                    </div>
                                    
                                </div>  

                                        <div class="row">
                                            <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Género</label>
                                                    <select name="genero" class="form-control">
                                                        <option value="MASCULINO">MASCULINO</option>
                                                        <option value="FEMENINO">FEMENINO</option>
                                                    </select>
                                                    </div>
                                                </div>                                
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="">Fecha Nacimiento</label>
                                                    <input type="date" name="fecnac" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="">Celular</label>
                                                    <input type="number" name="celular" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="">Correo electronico</label>
                                                    <input type="email" name="correo_electronico" class="form-control" placeholder="correo@dominio.com">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="">Observaciones</label>
                                                    <input type="text" name="obs" class="form-control">
                                                </div>
                                            </div>

                                            
                                        </div>

                                            <div class="card-header">
                                            <h3 class="card-title"><i class="bi bi-geo-alt-fill"></i> ---Ubicación de votación---</h3>
                                            
                                            </div>
                            
                                 <div class="row">
                                        <!-- Provincia -->
                                        <div class="col-md-3">
                                            <label>Provincia</label>
                                            <select id="province_id" name="province_id" class="form-control select2">
                                                <option value="">Seleccione una provincia</option>
                                                @foreach($provinces as $province)
                                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Municipio -->
                                        <div class="col-md-3">
                                            <label>Municipio</label>
                                            <select id="municipality" name="municipality_id" class="form-control select2">
                                                <option value="">Seleccione un municipio</option>
                                            </select>
                                        </div>

                                        <!-- Recinto -->
                                        <div class="col-md-3">
                                            <label>Recinto</label>
                                            <select id="electoral_precinct_id" name="electoral_precinct_id" class="form-control select2">
                                                <option value="">Seleccione un recinto</option>
                                            </select>
                                        </div>

                                </div>                     

                            
                                    <hr>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <a href="{{ route('postulaciones.index') }}" class="btn btn-secondary"> Volver</a>
                                            <button type="submit" class="btn btn-primary"><i class="bi bi-floppy2"></i> Guardar registro</button>
                                        </div>

                                    </div>
                                </div>
                        
                    </form>



                </div>
                
            </div>
        </div>
    </div>
    
@endsection







@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

$('#province_id').on('change', function () {
    let provinceId = $(this).val();

    if (provinceId) {
        $.ajax({
            url: '/admin/municipios/por-provincia/' + provinceId,
            type: 'GET',
            success: function (data) {
                $('#municipality').empty().append('<option value="">Seleccione un municipio</option>');

                if (data.length > 0) {
                    data.forEach(function (municipio) {
                        $('#municipality').append('<option value="' + municipio.id + '">' + municipio.name + '</option>');
                    });
                }
            },
            error: function () {
                alert('Error al cargar municipios');
            }
        });
    } else {
        $('#municipality').empty().append('<option value="">Seleccione un municipio</option>');
    }
});

$('#municipality').on('change', function () {
    let municipalityId = $(this).val();

    if (municipalityId) {
        $.ajax({
            url: '/recintos/por-municipio/' + municipalityId,
            type: 'GET',
            success: function (data) {
                $('#electoral_precinct_id').empty().append('<option value="">Seleccione un recinto</option>');

                if (data.length > 0) {
                    data.forEach(function (recinto) {
                        $('#electoral_precinct_id').append('<option value="' + recinto.id + '">' + recinto.name + '</option>');
                    });
                } else {
                    $('#electoral_precinct_id').append('<option value="">No hay recintos disponibles</option>');
                }

                // Limpiar mesas también
                $('#table_ids').empty();
            },
            error: function () {
                alert('Error al cargar recintos');
            }
        });
    } else {
        $('#electoral_precinct_id').empty().append('<option value="">Seleccione un recinto</option>');
    }
});

$('#electoral_precinct_id').on('change', function () {
    let precinctId = $(this).val();

    if (precinctId) {
        $.ajax({
            url: '/mesas/por-recinto/' + precinctId,
            type: 'GET',
            success: function (data) {
                $('#table_ids').empty().append('<option value="">Seleccione una mesa</option>');

                if (data.length > 0) {
                    data.forEach(function (mesa) {
                        $('#table_ids').append('<option value="' + mesa.id + '">Mesa ' + mesa.table_number + '</option>');
                    });
                } else {
                    $('#table_ids').append('<option value="">No hay mesas disponibles</option>');
                }
            },
            error: function () {
                alert('Error al cargar mesas');
            }
        });
    } else {
        $('#table_ids').empty().append('<option value="">Seleccione una mesa</option>');
    }
});


</script>
@endsection

