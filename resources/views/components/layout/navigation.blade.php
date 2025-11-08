{{--
/*
 * Component: navigation.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Horizontal tab navigation component with active state highlighting
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   Provides horizontal tab-based navigation for main application sections.
 *   Highlights the currently active section based on the 'active' prop.
 * 
 * Props:
 *   @param {string} $active - Currently active section identifier (default: 'home')
 *     Accepted values: 'home', 'alerts', 'devices', 'reports', 'admin'
 * 
 * Navigation Tabs:
 *   1. Inicio (Home) - active='home'
 *      Dashboard/home page with campus map
 *   
 *   2. Alertas (Alerts) - active='alerts'
 *      System alerts and notifications
 *   
 *   3. Dispositivos (Devices) - active='devices'
 *      Device management and monitoring
 *   
 *   4. Reportes (Reports) - active='reports'
 *      Search and reporting interface
 *   
 *   5. Admin - active='admin'
 *      Administrative control panel
 * 
 * Active State Logic:
 *   - If $active matches tab identifier → 'active' class applied
 *   - If $active does not match → no active class
 *   - Active class triggers Bootstrap tab highlighting
 * 
 * Styling:
 *   - nav nav-tabs: Bootstrap tabs component
 *   - mb-4: Bottom margin for spacing
 *   - nav-link: Bootstrap tab link styling
 *   - active: Highlights current section (Bootstrap blue underline)
 * 
 * Usage Example:
 *   <x-layout.navigation active="devices" />
 *   This will highlight the "Dispositivos" tab
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 nav-tabs component
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 component design standards
 *   - Adheres to WCAG navigation accessibility guidelines
 */
--}}
<!-- Top navigation tabs component: highlights the active section based on the passed 'active' prop -->
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