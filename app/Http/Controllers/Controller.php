<?php

namespace App\Http\Controllers;

/**
 * Controlador Base
 *
 * Convenciones del proyecto:
 * - Controladores delgados: validación con FormRequest/validator(), lógica de negocio en Models/Services.
 * - Respuestas: vistas Blade en GET; redirects con flash en POST/PATCH/DELETE.
 * - Enlaces de modelos en rutas para parámetros tipados (p.ej. {user}).
 *
 * Seguridad:
 * - Middleware 'auth' protege páginas internas.
 * - Middleware 'admin' restringe rutas del panel de administración.
 */
abstract class Controller
{
    //
}
