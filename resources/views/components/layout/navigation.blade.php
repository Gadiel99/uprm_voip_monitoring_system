@props(['active' => 'home'])

{{-- Navegación superior por pestañas (demo). 
   - La variable $active resalta la pestaña actual. --}}
<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link {{ $active === 'home' ? 'active' : '' }}" href="#">Inicio</a></li>
  <li class="nav-item"><a class="nav-link {{ $active === 'alerts' ? 'active' : '' }}" href="#">Alertas</a></li>
  <li class="nav-item"><a class="nav-link {{ $active === 'devices' ? 'active' : '' }}" href="#">Dispositivos</a></li>
  <li class="nav-item"><a class="nav-link {{ $active === 'reports' ? 'active' : '' }}" href="#">Reportes</a></li>
  <li class="nav-item"><a class="nav-link {{ $active === 'admin' ? 'active' : '' }}" href="#">Admin</a></li>
</ul>

{{-- Bloque condicional para enlace Admin cuando el usuario es admin --}}
@admin
<li class="nav-item">
    <a href="{{ route('admin') }}"
       class="nav-link {{ request()->is('admin*') ? 'active' : '' }}">
       <i class="bi bi-shield-lock me-1"></i> Admin
    </a>
</li>
@endadmin