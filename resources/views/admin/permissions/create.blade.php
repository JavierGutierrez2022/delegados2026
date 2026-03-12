@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="h4 mb-3">Crear Permiso</h1>

    <form action="{{ route('permissions.store') }}" method="POST" class="card card-body">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nombre del permiso</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('permissions.index') }}" class="btn btn-light">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection
