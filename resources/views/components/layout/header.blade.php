{{--
/*
 * Component: header.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Main application navigation bar with branding and user menu
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   Provides the primary navigation bar displayed at the top of every page.
 *   Contains branding, responsive menu toggle, and user account links.
 * 
 * Features:
 *   - Responsive design (collapses on mobile)
 *   - Dark theme navbar
 *   - Brand logo/name on left
 *   - User menu on right
 *   - Mobile hamburger toggle button
 * 
 * Navigation Items:
 *   Left Side:
 *     - "Project AV" brand link (fw-bold)
 *   
 *   Right Side (User Menu):
 *     - Perfil (Profile) - User profile page
 *     - Cerrar sesión (Logout) - Sign out functionality
 * 
 * Responsive Behavior:
 *   - Desktop (≥992px): Horizontal layout, all items visible
 *   - Tablet/Mobile (<992px): Hamburger menu, collapsible navigation
 *   - Toggle button appears on smaller screens
 * 
 * Bootstrap Components:
 *   - navbar: Main navigation container
 *   - navbar-expand-lg: Breakpoint for responsive collapse
 *   - navbar-dark: Dark color scheme
 *   - bg-dark: Dark background
 *   - navbar-toggler: Mobile menu toggle button
 *   - collapse navbar-collapse: Collapsible menu container
 *   - justify-content-end: Aligns user menu to right
 * 
 * Collapse Target:
 *   - ID: navbarContent
 *   - Controlled by toggle button via data-bs-target
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 navbar component
 *   - Bootstrap JavaScript for collapse functionality
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 component design standards
 *   - Adheres to WCAG accessibility guidelines
 *   - Implements responsive design best practices
 */
--}}
<!-- Main navigation bar: brand, responsive toggle, and user links -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Project AV</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="#">Perfil</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Cerrar sesión</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
