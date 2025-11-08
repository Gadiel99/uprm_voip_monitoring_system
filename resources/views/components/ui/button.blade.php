{{--
/*
 * Component: button.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Reusable button component with Bootstrap variant support
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   Provides a standardized button component supporting all Bootstrap button variants.
 *   Ensures consistent button styling across the application.
 * 
 * Props:
 *   @param {string} $variant - Bootstrap button variant (default: 'primary')
 *     Supported values: primary, secondary, success, danger, warning, info, light, dark
 * 
 * Slots:
 *   @slot default - Button text or content
 * 
 * Attributes:
 *   - Automatically merges additional HTML attributes
 *   - Supports: type, onclick, disabled, data-*, aria-*, etc.
 *   - Applies Bootstrap class: btn btn-{variant}
 * 
 * Features:
 *   - Dynamic variant assignment
 *   - Attribute merging for extensibility
 *   - Slot-based content flexibility
 * 
 * Usage Examples:
 *   <x-ui.button variant="success">Save</x-ui.button>
 *   <x-ui.button variant="danger" type="button" disabled>Delete</x-ui.button>
 *   <x-ui.button variant="primary" onclick="submitForm()">Submit</x-ui.button>
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 button classes
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 component design standards
 *   - Adheres to reusable component architecture
 */
--}}
<!-- Reusable button component: applies dynamic Bootstrap variant (primary, secondary, etc.) via 'variant' prop -->
@props(['variant' => 'primary'])

<button {{ $attributes->merge(['class' => "btn btn-$variant"]) }}>
  {{ $slot }}
</button>

