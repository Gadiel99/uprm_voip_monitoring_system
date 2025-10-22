@extends('components.layout.app')

@section('content')
    <h4 class="fw-semibold mb-3">UPRM Campus Map - System Status</h4>

    <div class="card border-0 shadow-sm p-3">
        <div class="position-relative">
            <div class="position-absolute top-0 start-0 bg-white rounded p-2 shadow-sm" style="font-size: 0.9rem;">
                <strong>Map Legend</strong>
                <ul class="list-unstyled mb-1 mt-2">
                    <li><span class="badge bg-success"> </span> Normal</li>
                    <li><span class="badge bg-warning text-dark"> </span> Warning</li>
                    <li><span class="badge bg-danger"> </span> Critical</li>
                </ul>
                <small class="text-muted fst-italic">Click markers for details</small>
            </div>

            <img src="{{ asset('images/EDIFICIOS_DEL_RUM_1.png') }}" alt="UPRM Map" class="img-fluid rounded">
        </div>
    </div>
@endsection
