<div class="btn-group" role="group">
  <a href="{{ route('postulaciones.show', $row->id) }}" class="btn btn-sm btn-info" title="Ver">
    <i class="fas fa-eye"></i>
  </a>
  <a href="{{ route('postulaciones.edit', $row->id) }}" class="btn btn-sm btn-success" title="Editar">
    <i class="fas fa-pen"></i>
  </a>
  <button class="btn btn-sm btn-danger btn-del" data-id="{{ $row->id }}" title="Eliminar">
    <i class="fas fa-trash"></i>
  </button>
</div>