@extends('components.layout.app')

@section('content')
    <h4 class="fw-semibold mb-3">Registered Devices</h4>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Location</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>01</td><td>Camera A1</td><td>Library</td><td><span class="badge bg-success">Online</span></td></tr>
            <tr><td>02</td><td>Router G2</td><td>Student Center</td><td><span class="badge bg-warning text-dark">Warning</span></td></tr>
            <tr><td>03</td><td>Sensor T3</td><td>Admin Building</td><td><span class="badge bg-danger">Offline</span></td></tr>
        </tbody>
    </table>
@endsection
