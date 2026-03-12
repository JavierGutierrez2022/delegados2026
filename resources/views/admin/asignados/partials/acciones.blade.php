@php /** @var object $row */ @endphp
<div class="btn-group btn-group-sm" role="group">
  <a href="{{ route('asignados.show', $row->id) }}" class="btn btn-outline-primary" title="Ver">
    <i class="far fa-eye"></i>
  </a>
  <a href="{{ route('asignados.edit', $row->id) }}" class="btn btn-outline-success" title="Editar">
    <i class="far fa-edit"></i>
  </a>
  <button type="button" class="btn btn-outline-danger btn-del-assignment" data-id="{{ $row->id }}" title="Eliminar">
    <i class="far fa-trash-alt"></i>
  </button>
</div>
