@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="h4 mb-3">Editar Rol</h1>

    <form action="{{ route('roles.update', $role) }}" method="POST" class="card card-body">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nombre del rol</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Permisos</label>
            <div class="row">
            @foreach($permissions as $perm)
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]"
                               value="{{ $perm->name }}" id="perm_{{ $perm->id }}"
                               {{ $role->permissions->contains('id',$perm->id) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ $perm->id }}">{{ $perm->name }}</label>
                    </div>
                </div>
            @endforeach
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-light">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection
