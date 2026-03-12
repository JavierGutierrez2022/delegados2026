@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Listado de Permisos</h3>
    <a href="{{ route('permissions.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo permiso</a>
  </div>
  <div class="card-body">
    <table id="tabla-permisos" class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Permiso</th>
          <th class="text-center" style="width:150px">Acciones</th>
        </tr>
      </thead>
      <tbody>
      @foreach($permissions as $i => $perm)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $perm->name }}</td>
          <td class="text-center">
            <a href="{{ route('permissions.edit', $perm) }}" class="btn btn-sm btn-success"><i class="fas fa-pen"></i></a>
            <form action="{{ route('permissions.destroy', $perm) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('¿Eliminar este permiso?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
  $('#tabla-permisos').DataTable({
    responsive:true,
    language:{ url:'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    pageLength:25,
    dom:'Bfrtip',
    buttons:['excel','pdf','print','colvis']
  });
});
</script>
@endpush
