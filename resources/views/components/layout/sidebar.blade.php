<!-- Sidebar navigation component: displays vertical menu and highlights active section using the 'active' prop -->
@props(['active' => 'dashboard'])

<div class="d-flex flex-column flex-shrink-0 p-3 bg-light border-end" style="width: 250px; min-height: 100vh;">
  <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
      <a href="#" class="nav-link {{ $active === 'dashboard' ? 'active' : 'link-dark' }}">
        <i class="bi bi-house"></i> Dashboard
      </a>
    </li>
    <li><a href="#" class="nav-link {{ $active === 'alerts' ? 'active' : 'link-dark' }}"><i class="bi bi-bell"></i> Alertas</a></li>
    <li><a href="#" class="nav-link {{ $active === 'devices' ? 'active' : 'link-dark' }}"><i class="bi bi-cpu"></i> Dispositivos</a></li>
    <li><a href="#" class="nav-link {{ $active === 'reports' ? 'active' : 'link-dark' }}"><i class="bi bi-bar-chart"></i> Reportes</a></li>
    <li><a href="#" class="nav-link {{ $active === 'admin' ? 'active' : 'link-dark' }}"><i class="bi bi-person-gear"></i> Administración</a></li>
    <li><a href="#" class="nav-link {{ $active === 'settings' ? 'active' : 'link-dark' }}"><i class="bi bi-gear"></i> Configuración</a></li>
    <li><a href="#" class="nav-link {{ $active === 'help' ? 'active' : 'link-dark' }}"><i class="bi bi-question-circle"></i> Ayuda</a></li>
  </ul>
</div>
