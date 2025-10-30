<!-- Reusable button component: applies dynamic Bootstrap variant (primary, secondary, etc.) via 'variant' prop -->
@props(['variant' => 'primary'])

<button {{ $attributes->merge(['class' => "btn btn-$variant"]) }}>
  {{ $slot }}
</button>
