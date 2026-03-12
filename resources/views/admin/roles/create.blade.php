@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="h4 mb-3">Crear Rol</h1>

    <form action="{{ route('roles.store') }}" method="POST" class="card card-body">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre del rol</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Permisos</label>
            <div class="row">
            @foreach($permissions as $perm)
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]"
                               value="{{ $perm->name }}" id="perm_{{ $perm->id }}">
                        <label class="form-check-label" for="perm_{{ $perm->id }}">{{ $perm->name }}</label>
                    </div>
                </div>
            @endforeach
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-light">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection
