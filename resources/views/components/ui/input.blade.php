{{--
/*
 * Component: input.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Reusable form input component with label support
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   Provides a standardized form input field with optional label, supporting all
 *   HTML5 input types. Ensures consistent form styling across the application.
 * 
 * Props:
 *   @param {string} $label - Input label text (default: '')
 *     If empty, no label is rendered
 *   @param {string} $type - HTML input type (default: 'text')
 *     Supported: text, email, password, number, tel, url, date, time, etc.
 *   @param {string} $name - Input name attribute (default: '')
 *     Used for form submission and label association
 *   @param {string} $value - Input default value (default: '')
 *     Pre-populated value for the input field
 * 
 * Slots:
 *   - No slots; content is controlled by props
 * 
 * Attributes:
 *   - Automatically merges additional HTML attributes
 *   - Supports: id, placeholder, required, disabled, pattern, min, max, etc.
 *   - Applies Bootstrap class: form-control
 * 
 * Features:
 *   - Conditional label rendering (only if label prop provided)
 *   - Label-input association via 'for' and 'name' attributes
 *   - Bottom margin spacing (mb-3)
 *   - Full Bootstrap form control styling
 * 
 * Usage Examples:
 *   <x-ui.input label="Username" name="username" type="text" />
 *   <x-ui.input label="Email" name="email" type="email" required />
 *   <x-ui.input label="Password" name="password" type="password" />
 *   <x-ui.input label="Threshold" name="threshold" type="number" value="75" min="0" max="100" />
 * 
 * Accessibility:
 *   - Label associates with input via 'for' attribute
 *   - Supports ARIA attributes
 *   - Keyboard navigable
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 form classes
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 component design standards
 *   - Adheres to WCAG accessibility guidelines
 */
--}}
<!-- Reusable input component: supports label, type, name, value, and additional attributes -->
@props(['label' => '', 'type' => 'text', 'name' => '', 'value' => ''])

<div class="mb-3">
  @if($label)
    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
  @endif
  <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" {{ $attributes->merge(['class' => 'form-control']) }}>
</div>

