<div class="btn-group btn-group-sm" role="group">
  <a href="{{ $show }}" class="btn btn-outline-primary" title="Ver detalle">
    <i class="bi bi-eye"></i>
  </a>
  <form action="{{ $del }}" method="POST" onsubmit="return confirm('¿Eliminar registro?')">
    @csrf @method('DELETE')
    <button class="btn btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
  </form>
</div>
