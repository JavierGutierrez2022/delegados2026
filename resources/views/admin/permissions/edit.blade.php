@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="h4 mb-3">Editar Permiso</h1>

    <form action="{{ route('permissions.update', $permission) }}" method="POST" class="card card-body">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Nombre del permiso</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $permission->name) }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('permissions.index') }}" class="btn btn-light">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection
