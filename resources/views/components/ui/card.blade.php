{{--
/*
 * Component: card.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Reusable card component for consistent content display
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   Provides a standardized card layout following Bootstrap 5 card design patterns.
 *   Used throughout the application for consistent content presentation.
 * 
 * Props:
 *   @param {string} $title - Card title (required)
 *   @param {string|null} $description - Optional description text (default: null)
 * 
 * Slots:
 *   @slot default - Card body content
 * 
 * Attributes:
 *   - Automatically merges additional attributes
 *   - Supports all standard HTML/Bootstrap attributes
 * 
 * Features:
 *   - Shadow effect for depth perception
 *   - Bottom margin for spacing (mb-3)
 *   - Conditional description rendering
 *   - Flexible slot content
 * 
 * Usage Example:
 *   <x-ui.card title="Device Stats" description="Real-time statistics">
 *     <p>Total Devices: 150</p>
 *   </x-ui.card>
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 card classes
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 component design standards
 *   - Adheres to reusable component best practices
 */
--}}
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

