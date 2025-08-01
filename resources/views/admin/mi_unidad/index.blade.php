@extends('layouts.admin')

@section('content')
<div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0">Mi Unidad</h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        
        <!-- Button trigger modal -->
            <button type="button" class="btn btn-block btn-outline-success" data-toggle="modal" data-target="#exampleModal">
                <i class="bi bi-folder-fill"></i> Nueva Carpeta
            </button>
            
            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Nombre de la carpeta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    </div>
                    <div class="modal-body">

                        <form action="{{url('/admin/mi_unidad')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="nombre" required>
                                    </div>
                                </div>
                            </div>
     
                    
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                    </div>
                </form>
                </div>
                </div>
            </div>

        
      </ol>
    </div><!-- /.col -->
  </div>
  <hr>
  <h5>Carpetas</h5>
  <hr>
  <div class="row">
    @foreach($carpetas as $carpeta)

    <div class="col-md-3 col-sm-6 col-12">
        <a href="{{url('/admin/mi_unidad/carpeta',$carpeta->id)}}" style="color:black">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="far"><i class="bi bi-folder"></i></i></span>
                <div class="info-box-content">
                 <h6>{{$carpeta->nombre}}</h6>
                </div>
                
              </div>
        </a>
        
      </div>
    @endforeach
    
  </div>

@endsection
