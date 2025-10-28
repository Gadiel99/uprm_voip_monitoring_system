@extends('components.layout.app')

@section('content')
<h4 class="fw-semibold mb-4">UPRM Campus Map - System Status</h4>

<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="position-relative bg-white rounded-3 overflow-hidden" 
         style="border: 1px solid #dee2e6; height: 75vh;"> <!-- Reduced height -->

        <!-- Campus Map -->
        <img src="{{ asset('images/MapaRUM.jpeg') }}" 
             alt="UPRM Campus Map" 
             class="position-absolute top-0 start-0 w-100 h-100" 
             style="object-fit: contain;">

        <!-- Legend -->
        <div class="position-absolute top-0 start-0 bg-white bg-opacity-90 border rounded shadow-sm m-3 p-2 small text-start" 
             style="line-height: 1.3; border-left: 3px solid #00844b;">
            <strong class="text-dark">Map Legend</strong><br>
            <span class="text-success">● Normal</span><br>
            <span class="text-warning">● Warning</span><br>
            <span class="text-danger">● Critical</span><br>
            <small class="text-muted">Click markers for details</small>
        </div>

        <!-- Interactive markers in the map -->
        <div class="position-absolute" style="top: 60%; left: 77%;" data-bs-toggle="tooltip" title="Celis">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 52%; left: 82%;" data-bs-toggle="tooltip" title="Stefani">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 33.5%; left: 71%;" data-bs-toggle="tooltip" title="Biologia">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 63%; left: 76%;" data-bs-toggle="tooltip" title="DeDiego">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 59%; left: 86%;" data-bs-toggle="tooltip" title="Luchetti">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 56%; left: 84%;" data-bs-toggle="tooltip" title="ROTC">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 30%; left: 32%;" data-bs-toggle="tooltip" title="Adm.Empresas">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 63%; left: 66%;" data-bs-toggle="tooltip" title="Musa">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 53%; left: 75%;" data-bs-toggle="tooltip" title="Chardon">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 62%; left: 72%;" data-bs-toggle="tooltip" title="Monzon">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 47%; left: 69%;" data-bs-toggle="tooltip" title="Sanchez Hidalgo">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 45%; left: 75%;" data-bs-toggle="tooltip" title="Fisica">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 44%; left: 77%;" data-bs-toggle="tooltip" title="Geologia">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 44%; left: 79%;" data-bs-toggle="tooltip" title="Ciencias Marinas">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 44%; left: 62%;" data-bs-toggle="tooltip" title="Quimica">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top:44%; left: 62%;" data-bs-toggle="tooltip" title="Piñero">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 50%; left: 58%;" data-bs-toggle="tooltip" title="Enfermeria">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 48%; left: 53%;" data-bs-toggle="tooltip" title="Vagones">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 40%; left: 30%;" data-bs-toggle="tooltip" title="Natatorio">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 33%; left: 86%;" data-bs-toggle="tooltip" title="Centro Nuclear">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 56%; left: 45%;" data-bs-toggle="tooltip" title="Coliseo">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 58%; left: 53%;" data-bs-toggle="tooltip" title="Gimnacio">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 60%; left: 66%;" data-bs-toggle="tooltip" title="Servicios Medicos">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 64%; left: 80%;" data-bs-toggle="tooltip" title="Decanato de Estudiantes">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 49%; left: 65%;" data-bs-toggle="tooltip" title="Oficina de Facultad">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 55%; left: 7%;" data-bs-toggle="tooltip" title="Adm.Finca Alzamora">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 56%; left: 65%;" data-bs-toggle="tooltip" title="Biblioteca">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 57%; left: 72%;" data-bs-toggle="tooltip" title="Centro de Estudiantes">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 48%; left: 81%;" data-bs-toggle="tooltip" title="Terrats">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 28%; left: 59%;" data-bs-toggle="tooltip" title="Ing.Civil">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 49%; left: 78%;" data-bs-toggle="tooltip" title="Ing.Industrial">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 32%; left: 54%;" data-bs-toggle="tooltip" title="Ing.Quimica">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 43%; left: 50%;" data-bs-toggle="tooltip" title="Ing.Agricola">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 37.5%; left: 17%;" data-bs-toggle="tooltip" title="Edificio A (Hotel Colegial)">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 37.5%; left: 20.5%;" data-bs-toggle="tooltip" title="Edificio B (Adm.Peq.Negocios y Oficina Adm)">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 40.5%; left: 16.5%;" data-bs-toggle="tooltip" title="Edificio C (Oficina de Extension Agricola))">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        <div class="position-absolute" style="top: 39%; left: 19%;" data-bs-toggle="tooltip" title="Edificio D">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

        

    </div>
</div>

<script>
    // Enable tooltips for hover effect
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
    });
</script>
@endsection
