@extends('layouts.admin')

@section('content')
    <div class="row">
        <h1>Panel "Control PDC"</h1>
    </div>
    <hr>

        <div class="row">

            @can('menu.usuarios')
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            @php $contador_de_usuarios=0; @endphp
                            @foreach($usuarios as $usuario)
                                @php $contador_de_usuarios++; @endphp
                            @endforeach
                            <h3>{{$contador_de_usuarios}}</h3>
                            <p>Usuarios registrados</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <a href="{{url('/admin/usuarios')}}" class="small-box-footer">
                            Más información <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>   
            @endcan

            <div class="col-lg-3 col-6">

                <div class="small-box bg-info">
                    <div class="inner">
                        @php $contador_de_miembros=0; @endphp
                        @foreach($miembros as $miembro)
                            @php $contador_de_miembros++; @endphp
                        @endforeach
                        <h3>{{$contador_de_miembros}}</h3>
                        <p>Delegados registrados</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-person-arms-up"></i>
                    </div>
                    <a href="{{ route('postulaciones.index') }}" class="small-box-footer">
                        Más información <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>  
            
            <div class="col-lg-3 col-6">

                <div class="small-box bg-success">
                    <div class="inner">
                        @php $contador_de_provinces=0; @endphp
                        @foreach($provinces as $province)
                            @php $contador_de_provinces++; @endphp
                        @endforeach
                        <h3>{{$contador_de_provinces}}</h3>
                        <p>Provincias registradas</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <a href="" class="small-box-footer">
                        Más información <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>  
            
            <div class="col-lg-3 col-6">

                <div class="small-box bg-primary">
                    <div class="inner">
                        @php $contador_de_municipalities=0; @endphp
                        @foreach($municipalities as $municipalitie)
                            @php $contador_de_municipalities++; @endphp
                        @endforeach
                        <h3>{{$contador_de_municipalities}}</h3>
                        <p>Municipios registrados</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-pin-map-fill"></i>
                    </div>
                    <a href="" class="small-box-footer">
                        Más información <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>     
            
            


        </div>
         <div class="row">

            <div class="col-lg-3 col-6">

                <div class="small-box bg-secondary">
                    <div class="inner">
                        @php $contador_de_electoralprecincts=0; @endphp
                        @foreach($electoralprecincts as $electoralprecinct)
                            @php $contador_de_electoralprecincts++; @endphp
                        @endforeach
                        <h3>{{$contador_de_electoralprecincts}}</h3>
                        <p>Recintos registrados</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <a href="{{url('/admin/recintos/reporte')}}" class="small-box-footer">
                        Más información <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>     

            <div class="col-lg-3 col-6">

                <div class="small-box bg-danger">
                    <div class="inner">
                        @php $contador_de_tables=0; @endphp
                        @foreach($tables as $table)
                            @php $contador_de_tables++; @endphp
                        @endforeach
                        <h3>{{$contador_de_tables}}</h3>
                        <p>Mesas registradas</p>
                    </div>
                    <div class="icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <a href="{{url('/reportes/mesas-por-municipio')}}" class="small-box-footer">
                        Más información <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>     

              

        </div>

       

    @endsection
