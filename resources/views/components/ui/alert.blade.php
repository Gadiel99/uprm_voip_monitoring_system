<!-- Reusable alert component: displays a Bootstrap alert with dynamic type (info, success, warning, danger) via 'type' prop -->
@props(['type' => 'info'])

<div {{ $attributes->merge(['class' => "alert alert-$type"]) }}>
  {{ $slot }}
</div>
