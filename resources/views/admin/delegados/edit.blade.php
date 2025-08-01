@extends('layouts.admin')
@section('content')

    <div class="content" style="margin-left:20px">
        <h1>Actualizar datos del miembro</h1>
    </div>  

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Actualice los datos</h3>
                </div>
                <div class="card-body">

                    <form action="{{url('admin/delegados',$miembro->id)}}" method="post">
                        @csrf
                        {{-- {{method_field('PATCH')}} --}}
                        @method('PUT')
                                <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Nombres</label>
                                        <input type="text"name="nombres" value="{{$miembro->nombres}}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Apellido Paterno</label>
                                        <input type="text" name="app" value="{{$miembro->app}}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Apellido Materno</label>
                                        <input type="text" name="apm" value="{{$miembro->apm}}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Género</label>
                                      <select name="genero"  class="form-control">
                                        @if($miembro->genero == 'MASCULINO')
                                        <option value="MASCULINO">MASCULINO</option>
                                        <option value="FEMENINO">FEMENINO</option>
                                        @else
                                        <option value="FEMENINO">FEMENINO</option>
                                        <option value="MASCULINO">MASCULINO</option>
                                        @endif                                        
                                      </select>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">C.I.</label>
                                        <input type="text" name="ci" value="{{$miembro->ci}}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Fecha Nacimiento</label>
                                        <input type="date" name="fecnac" value="{{$miembro->fecnac}}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Celular</label>
                                        <input type="number" name="celular" value="{{$miembro->celular}}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Recinto Votación</label>
                                        <input type="text" name="recintovot" value="{{$miembro->recintovot}}" class="form-control">
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="">Agrupación</label>
                                            <select name="agrupa" id="agrupa" class="form-control" required>
                                                    <option value="">Seleccione una agrupación</option>
                                                    <option value="UN" {{ old('agrupa', $miembro->agrupa ?? '') == 'UN' ? 'selected' : '' }}>UN</option>
                                                    <option value="CREEMOS" {{ old('agrupa', $miembro->agrupa ?? '') == 'CREEMOS' ? 'selected' : '' }}>CREEMOS</option>
                                                    <option value="MNR" {{ old('agrupa', $miembro->agrupa ?? '') == 'MNR' ? 'selected' : '' }}>MNR</option>
                                                    <option value="TODOS" {{ old('agrupa', $miembro->agrupa ?? '') == 'TODOS' ? 'selected' : '' }}>TODOS</option>
                                                    <option value="NASUR" {{ old('agrupa', $miembro->agrupa ?? '') == 'NASUR' ? 'selected' : '' }}>NASUR</option>
                                                    <option value="AIR" {{ old('agrupa', $miembro->agrupa ?? '') == 'AIR' ? 'selected' : '' }}>AIR</option>
                                                    <option value="CAMBIO25" {{ old('agrupa', $miembro->agrupa ?? '') == 'CAMBIO25' ? 'selected' : '' }}>CAMBIO25</option>
                                                    <option value="MSM" {{ old('agrupa', $miembro->agrupa ?? '') == 'MSM' ? 'selected' : '' }}>MSM</option>
                                                    <option value="MOVIMIENTO DIGNIDAD" {{ old('agrupa', $miembro->agrupa ?? '') == 'MOVIMIENTO DIGNIDAD' ? 'selected' : '' }}>MOVIMIENTO DIGNIDAD</option>
                                                    <option value="YO AMO TARIJA" {{ old('agrupa', $miembro->agrupa ?? '') == 'YO AMO TARIJA' ? 'selected' : '' }}>YO AMO TARIJA</option>
                                                    <option value="COPACIMIL" {{ old('agrupa', $miembro->agrupa ?? '') == 'COPACIMIL' ? 'selected' : '' }}>COPACIMIL</option>
                                            </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="">Estado</label>
                                        <select name="estado"  class="form-control">
                                         @if($miembro->estado == 'ACTIVO')
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
                                        @else
                                        <option value="INACTIVO">INACTIVO</option>
                                        <option value="ACTIVO">ACTIVO</option>
                                        @endif                                        
                                      </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="">Encargado</label>
                                        <select name="delegado"  class="form-control">
                                         @if($miembro->delegado == 'DELEGADO MESA')
                                        <option value="DELEGADO MESA"> DELEGADO MESA</option>
                                        <option value="JEFE RECINTO"> JEFE RECINTO</option>
                                        @else
                                        <option value="JEFE RECINTO">JEFE RECINTO</option>
                                        <option value="DELEGADO MESA">DELEGADO MESA</option>
                                        @endif                                        
                                      </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Observaciones</label>
                                        <input type="text" name="obs" value="{{$miembro->obs}}" class="form-control">
                                    </div>
                                </div>                        

                            </div>

                            <div class="row">
                               
                                <div class="col-md-3">
                                      <!-- Provincia -->
                                       <label for="province">Provincia:</label>
                                        <select name="province_id" id="province_id" class="form-select select2" required>
                                            <option value="">Seleccione una provincia</option>
                                            @foreach($provinces as $province)
                                                <option value="{{ $province->id }}" {{ $province->id == $miembro->province_id ? 'selected' : '' }}>
                                                    {{ $province->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                       
                                           
                                    
                                </div>
                                <div class="col-md-3">
                                    
                                        <!-- Municipio -->
                                        <label for="municipality" class="">Municipio:</label>
                                        <select name="municipality_id" id="municipality" class="form-select select2" required>
                                            @foreach($municipalities as $municipality)
                                                <option value="{{ $municipality->id }}" {{ $municipality->id == $miembro->municipality_id ? 'selected' : '' }}>
                                                    {{ $municipality->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    
                                </div>   
                                
                                <div class="col-md-3">
                                    
                                        <!-- Recinto electoral -->
                                        <label for="electoral_precint" class="">Recinto:</label>
                                            <select name="electoral_precinct_id" id="electoral_precinct_id" class="form-select" required>
                                                @foreach($recintos as $recinto)
                                                    <option value="{{ $recinto->id }}" {{ $recinto->id == $miembro->electoral_precinct_id ? 'selected' : '' }}>
                                                        {{ $recinto->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                    
                                </div>  

                            
                             <div class="col-md-3">
                                                                           
                                       <!-- Mesa -->
                                       <label for="table_number" class="">Mesa:</label>
                                      <select name="table_ids[]" id="table_ids" class="form-select" multiple required>
                                            @foreach($tables as $mesa)
                                                <option value="{{ $mesa->id }}"
                                                    {{ in_array($mesa->id, $miembro->mesas->pluck('id')->toArray()) ? 'selected' : '' }}>
                                                    Mesa {{ $mesa->table_number }}
                                                </option>
                                            @endforeach
                                        </select>                   
                                    
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <a href="{{url('admin/delegados')}}" class="btn btn-secondary">Volver</a>
                                        <button type="submit" class="btn btn-success"><i class="bi bi-floppy2"></i> Actualizar registro</button>
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
$(document).ready(function () {
    // MUNICIPIOS por PROVINCIA
    $('#province_id').on('change', function () {
        var provinceId = $(this).val();
        if (provinceId) {
            $.get('/admin/municipios/por-provincia/' + provinceId, function (data) {
                $('#municipality').empty().append('<option value="">Seleccione un municipio</option>');
                data.forEach(function (m) {
                    $('#municipality').append('<option value="' + m.id + '">' + m.name + '</option>');
                });
                $('#electoral_precinct_id').empty().append('<option value="">Seleccione un recinto</option>');
                $('#table_ids').empty();
            });
        }
    });

    // RECINTOS por MUNICIPIO
    $('#municipality').on('change', function () {
        var municipalityId = $(this).val();
        if (municipalityId) {
            $.get('/recintos/por-municipio/' + municipalityId, function (data) {
                $('#electoral_precinct_id').empty().append('<option value="">Seleccione un recinto</option>');
                data.forEach(function (r) {
                    $('#electoral_precinct_id').append('<option value="' + r.id + '">' + r.name + '</option>');
                });
                $('#table_ids').empty();
            });
        }
    });

    // MESAS por RECINTO
    $('#electoral_precinct_id').on('change', function () {
        var precinctId = $(this).val();
        if (precinctId) {
            $.get('/mesas/por-recinto/' + precinctId, function (data) {
                $('#table_ids').empty();
                data.forEach(function (mesa) {
                    $('#table_ids').append('<option value="' + mesa.id + '">Mesa ' + mesa.table_number + '</option>');
                });
            });
        }
    });
});
</script>
@endsection