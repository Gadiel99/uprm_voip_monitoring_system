@props(['id' => 'modal', 'title' => 'Modal Title'])

{{-- Componente UI: Modal Bootstrap reutilizable.
   Props:
   - id: identificador único del modal
   - title: título del encabezado
   Uso:
   <x-ui.modal id="myModal" title="Ejemplo">Contenido</x-ui.modal> --}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {{ $slot }}
      </div>
      <div class="modal-footer">
        <x-ui.button variant="secondary" data-bs-dismiss="modal">Cerrar</x-ui.button>
        <x-ui.button variant="primary">Guardar</x-ui.button>
      </div>
    </div>
  </div>
</div>
