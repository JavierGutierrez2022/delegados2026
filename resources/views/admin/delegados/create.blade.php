@extends('layouts.admin')
@section('content')

    <div class="content">
        <h1>Creacion de nuevo delegado</h1>
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
                    <p class="text-muted mb-3"><span class="text-danger">*</span> Campos obligatorios</p>

                    <form action="{{ url('admin/delegados') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">C.I. <span class="text-danger">*</span></label>
                                    <input type="text" name="ci" value="{{ old('ci') }}" class="form-control js-uppercase">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" name="nombres" value="{{ old('nombres') }}" class="form-control js-uppercase">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Apellido Paterno</label>
                                    <input type="text" name="app" value="{{ old('app') }}" class="form-control js-uppercase">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Apellido Materno</label>
                                    <input type="text" name="apm" value="{{ old('apm') }}" class="form-control js-uppercase">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Genero <span class="text-danger">*</span></label>
                                    <select name="genero" class="form-control">
                                        <option value="MASCULINO" {{ old('genero') === 'MASCULINO' ? 'selected' : '' }}>MASCULINO</option>
                                        <option value="FEMENINO" {{ old('genero') === 'FEMENINO' ? 'selected' : '' }}>FEMENINO</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Fecha Nacimiento</label>
                                    <input type="date" name="fecnac" value="{{ old('fecnac') }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Celular</label>
                                    <input type="number" name="celular" value="{{ old('celular') }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Correo electronico</label>
                                    <input type="email" name="correo_electronico" value="{{ old('correo_electronico') }}" class="form-control js-lowercase" placeholder="correo@dominio.com">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Observaciones</label>
                                    <input type="text" name="obs" value="{{ old('obs') }}" class="form-control js-uppercase">
                                </div>
                            </div>
                        </div>

                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-geo-alt-fill"></i> ---Ubicacion de votacion---</h3>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <label>Provincia</label>
                                <select id="province_id" name="province_id" class="form-control select2">
                                    <option value="">Seleccione una provincia</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Municipio</label>
                                <select id="municipality" name="municipality_id" class="form-control select2">
                                    <option value="">Seleccione un municipio</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Recinto</label>
                                <select id="electoral_precinct_id" name="electoral_precinct_id" class="form-control">
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
<script>
$(function () {
    $('.js-uppercase').each(function () {
        this.value = (this.value || '').toLocaleUpperCase('es-BO');
    }).on('input', function () {
        this.value = (this.value || '').toLocaleUpperCase('es-BO');
    });

    $('.js-lowercase').each(function () {
        this.value = (this.value || '').toLocaleLowerCase('es-BO');
    }).on('input', function () {
        this.value = (this.value || '').toLocaleLowerCase('es-BO');
    });

    $('#electoral_precinct_id').select2({
        placeholder: 'Seleccione un recinto',
        allowClear: true,
        width: '100%'
    });

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

                    $('#electoral_precinct_id').val(null).trigger('change');
                    $('#table_ids').empty();
                },
                error: function () {
                    alert('Error al cargar recintos');
                }
            });
        } else {
            $('#electoral_precinct_id').empty().append('<option value="">Seleccione un recinto</option>');
            $('#electoral_precinct_id').val(null).trigger('change');
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
});
</script>
<style>
    .js-uppercase {
        text-transform: uppercase;
    }

    .js-lowercase {
        text-transform: lowercase;
    }

    #select2-electoral_precinct_id-container {
        line-height: calc(2.25rem + 2px);
    }

    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: 0;
        border: 1px solid #ced4da;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px);
    }
</style>
@endsection
