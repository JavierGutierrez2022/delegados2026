@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="card-header"><h3 class="card-title">Asignar roles a: {{ $user->name }}</h3></div>
  <div class="card-body">
    <form method="POST" action="{{ route('usuarios.roles.update', $user) }}">
      @csrf @method('PUT')
      <div class="row">
        @foreach($roles as $role)
          <div class="col-md-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="roles[]"
                     value="{{ $role->name }}" id="r{{ $role->id }}"
                     {{ $user->hasRole($role->name) ? 'checked' : '' }}>
              <label class="form-check-label" for="r{{ $role->id }}">{{ $role->name }}</label>
            </div>
          </div>
        @endforeach
      </div>
      <div class="mt-3">
        <a href="{{ route('usuarios.index') }}" class="btn btn-light">Volver</a>
        <button class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>
@endsection
