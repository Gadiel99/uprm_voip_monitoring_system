{{--
/*
 * Component: modal.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Reusable Bootstrap modal dialog component
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   Provides a standardized modal dialog following Bootstrap 5 modal patterns.
 *   Used throughout the application for dialogs, confirmations, and forms.
 * 
 * Props:
 *   @param {string} $id - Modal DOM ID for targeting (default: 'modal')
 *     Must be unique if multiple modals exist on same page
 *   @param {string} $title - Modal header title (default: 'Modal Title')
 *     Displayed in the modal header bar
 * 
 * Slots:
 *   @slot default - Modal body content
 *     Accepts any HTML/Blade content
 * 
 * Structure:
 *   1. Modal Header
 *      - Title (h5 with id="{id}Label")
 *      - Close button (X icon, top-right)
 *   
 *   2. Modal Body
 *      - Slot content (dynamic)
 *      - Can contain forms, text, tables, etc.
 *   
 *   3. Modal Footer
 *      - "Cerrar" (Close) button - dismisses modal
 *      - "Guardar" (Save) button - primary action
 * 
 * Features:
 *   - Fade animation on show/hide
 *   - Backdrop click to close
 *   - Escape key to dismiss
 *   - Keyboard trap (tab navigation contained)
 *   - ARIA attributes for accessibility
 *   - Uses Bootstrap modal JavaScript plugin
 * 
 * Triggering the Modal:
 *   - Via data attribute: data-bs-toggle="modal" data-bs-target="#{id}"
 *   - Via JavaScript: new bootstrap.Modal(document.getElementById('{id}')).show()
 * 
 * Usage Example:
 *   <x-ui.modal id="editDeviceModal" title="Edit Device">
 *     <form>
 *       <x-ui.input label="Device Name" name="name" />
 *       <x-ui.input label="IP Address" name="ip" />
 *     </form>
 *   </x-ui.modal>
 * 
 *   <!-- Trigger button -->
 *   <button data-bs-toggle="modal" data-bs-target="#editDeviceModal">Edit</button>
 * 
 * Button Actions:
 *   - Cerrar (Close): data-bs-dismiss="modal" - closes modal without action
 *   - Guardar (Save): No default action - implement custom onclick or form submission
 * 
 * Accessibility:
 *   - tabindex="-1" prevents focus on modal container
 *   - aria-labelledby links to modal title
 *   - aria-hidden="true" when modal is closed
 *   - Focus management handled by Bootstrap
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 modal classes and JavaScript
 *   - x-ui.button component for footer buttons
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 component design standards
 *   - Adheres to WCAG modal accessibility guidelines
 */
--}}
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

