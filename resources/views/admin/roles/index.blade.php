@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Listado de Roles</h3>
    <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo rol</a>
  </div>
  <div class="card-body">
    <table id="tabla-roles" class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Rol</th>
          <th>Permisos</th>
          <th class="text-center" style="width:150px">Acciones</th>
        </tr>
      </thead>
      <tbody>
      @foreach($roles as $i => $role)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $role->name }}</td>
          <td>
            @forelse($role->permissions as $p)
              <span class="badge bg-secondary">{{ $p->name }}</span>
            @empty
              <span class="text-muted">Sin permisos</span>
            @endforelse
          </td>
          <td class="text-center">
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-success"><i class="fas fa-pen"></i></a>
            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('¿Eliminar este rol?')">
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
  $('#tabla-roles').DataTable({
    responsive:true,
    language:{ url:'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    pageLength:25,
    dom:'Bfrtip',
    buttons:['excel','pdf','print','colvis']
  });
});
</script>
@endpush