@props(['id' => 'modal', 'title' => 'Modal Title'])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
  
  {{-- Modal dialog wrapper --}}
  <div class="modal-dialog">
    
    {{-- Modal content container --}}
    <div class="modal-content">
      
      {{-- Modal header --}}
      <div class="modal-header">
        {{-- Title of the modal --}}
        <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
        {{-- Close button (top-right) --}}
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      {{-- Modal body: dynamic content slot --}}
      <div class="modal-body">
        {{ $slot }}
      </div>

      {{-- Modal footer with action buttons --}}
      <div class="modal-footer">
        {{-- Secondary button: closes the modal --}}
        <x-ui.button variant="secondary" data-bs-dismiss="modal">Cerrar</x-ui.button>

        {{-- Primary button: save or submit action --}}
        <x-ui.button variant="primary">Guardar</x-ui.button>
      </div>

    </div>
  </div>
</div>
