<!-- Reusable card component: displays a title, optional description, and slot content -->
@props(['title', 'description' => null])

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h5 class="card-title fw-bold">{{ $title }}</h5>
    @if($description)
      <p class="text-muted">{{ $description }}</p>
    @endif
    {{ $slot }}
  </div>
</div>
