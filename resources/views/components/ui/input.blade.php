<!-- Reusable input component: supports label, type, name, value, and additional attributes -->
@props(['label' => '', 'type' => 'text', 'name' => '', 'value' => ''])

<div class="mb-3">
  @if($label)
    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
  @endif
  <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" {{ $attributes->merge(['class' => 'form-control']) }}>
</div>
