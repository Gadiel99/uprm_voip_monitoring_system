{{--
/*
 * Component: sidebar.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Vertical sidebar navigation with icon-based menu items
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   Provides vertical sidebar navigation for all main application sections.
 *   Highlights the currently active section and displays icons for visual clarity.
 * 
 * Props:
 *   @param {string} $active - Currently active section identifier (default: 'dashboard')
 *     Accepted values: 'dashboard', 'alerts', 'devices', 'reports', 'admin', 'settings', 'help'
 * 
 * Navigation Menu Items:
 *   1. Dashboard - active='dashboard'
 *      Icon: bi-house (house icon)
 *      Main dashboard/home page
 *   
 *   2. Alertas - active='alerts'
 *      Icon: bi-bell (bell icon)
 *      System alerts and notifications
 *   
 *   3. Dispositivos - active='devices'
 *      Icon: bi-cpu (CPU/device icon)
 *      Device management interface
 *   
 *   4. Reportes - active='reports'
 *      Icon: bi-bar-chart (chart icon)
 *      Search and reporting tools
 *   
 *   5. Administración - active='admin'
 *      Icon: bi-person-gear (admin icon)
 *      Administrative control panel
 *   
 *   6. Configuración - active='settings'
 *      Icon: bi-gear (settings icon)
 *      System configuration
 *   
 *   7. Ayuda - active='help'
 *      Icon: bi-question-circle (help icon)
 *      User documentation and help
 * 
 * Active State Logic:
 *   - If $active matches item identifier → 'active' class (Bootstrap blue highlight)
 *   - If $active does not match → 'link-dark' class (dark text, no highlight)
 * 
 * Styling:
 *   - Width: 250px fixed
 *   - Height: 100vh minimum (full viewport height)
 *   - Background: bg-light (light gray)
 *   - Border: border-end (right border separator)
 *   - Padding: p-3 (all sides)
 *   - Layout: flex-column (vertical stacking)
 * 
 * Icon Set:
 *   - Bootstrap Icons (bi bi-*)
 *   - All icons displayed inline before text
 * 
 * Usage Example:
 *   <x-layout.sidebar active="devices" />
 *   This will highlight the "Dispositivos" menu item
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 nav-pills component
 *   - Bootstrap Icons for menu icons
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 component design standards
 *   - Adheres to WCAG navigation accessibility guidelines
 *   - Implements consistent icon usage patterns
 */
--}}
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
