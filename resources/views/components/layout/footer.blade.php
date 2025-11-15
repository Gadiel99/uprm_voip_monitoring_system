{{--
/*
 * Component: footer.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Application footer with copyright information
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   Displays the application footer with dynamic copyright year and branding.
 *   Provides consistent footer across all pages.
 * 
 * Features:
 *   - Dynamic year display using PHP date() function
 *   - Centered text alignment
 *   - Top border separation
 *   - Auto margin top to push to bottom (mt-auto with flexbox)
 *   - Vertical padding for spacing
 * 
 * Content:
 *   - Copyright symbol (©)
 *   - Current year (dynamically generated)
 *   - Project name: "Project AV"
 *   - Rights reserved text in Spanish
 * 
 * Styling:
 *   - text-center: Centered alignment
 *   - py-3: Vertical padding
 *   - border-top: Top border separator
 *   - mt-auto: Pushes footer to bottom when used with flex layout
 *   - small: Smaller font size
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 utility classes
 *   - PHP date() function
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 component design standards
 *   - Adheres to UI consistency best practices
 */
--}}
<!-- Footer section: displays copyright -->
<footer class="text-center py-3 border-top mt-auto">
  <small>© {{ date('Y') }} Project AV — Todos los derechos reservados.</small>
</footer>
