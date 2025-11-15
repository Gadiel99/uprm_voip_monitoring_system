{{--
/*
 * Component: alert.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Reusable alert component for user notifications
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   Provides a standardized alert component supporting all Bootstrap alert types.
 *   Used for displaying feedback messages, warnings, and informational content.
 * 
 * Props:
 *   @param {string} $type - Bootstrap alert type (default: 'info')
 *     Supported values: primary, secondary, success, danger, warning, info, light, dark
 * 
 * Slots:
 *   @slot default - Alert message content
 * 
 * Attributes:
 *   - Automatically merges additional HTML attributes
 *   - Supports: role, aria-*, data-*, etc.
 *   - Applies Bootstrap class: alert alert-{type}
 * 
 * Features:
 *   - Dynamic alert type assignment
 *   - Attribute merging for extensibility
 *   - Slot-based content flexibility
 *   - Compatible with Bootstrap alert-dismissible pattern
 * 
 * Usage Examples:
 *   <x-ui.alert type="success">Operation completed successfully!</x-ui.alert>
 *   <x-ui.alert type="danger">Error: Invalid credentials</x-ui.alert>
 *   <x-ui.alert type="warning">Warning: Threshold exceeded</x-ui.alert>
 *   <x-ui.alert type="info">Information: System update available</x-ui.alert>
 * 
 * Color Coding (Bootstrap Standard):
 *   - info: Blue (informational messages)
 *   - success: Green (successful operations)
 *   - warning: Yellow (caution/warning messages)
 *   - danger: Red (errors/critical alerts)
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 alert classes
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 component design standards
 *   - Adheres to accessibility guidelines
 */
--}}
<!-- Reusable alert component: displays a Bootstrap alert with dynamic type (info, success, warning, danger) via 'type' prop -->
@props(['type' => 'info'])

<div {{ $attributes->merge(['class' => "alert alert-$type"]) }}>
  {{ $slot }}
</div>

