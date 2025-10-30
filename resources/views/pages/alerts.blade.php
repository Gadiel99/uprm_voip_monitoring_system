@extends('components.layout.app')

@section('content')
<style>
    /* === UPRM Theme === */
    .nav-pills .nav-link.active {
        background-color: #00844b !important;
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 0 6px rgba(0, 132, 75, 0.3);
    }

    .badge-critical { background-color: #dc3545 !important; color: #fff !important; }
    .badge-warning { background-color: #ffc107 !important; color: #000 !important; }
    .badge-normal  { background-color: #198754 !important; color: #fff !important; }

    .clickable-row { cursor: pointer; transition: background 0.15s ease; }
    .clickable-row:hover { background-color: #f3f7f3; }

    .card  { border-radius: 12px !important; }
    .table { border-radius: 8px !important; overflow: hidden; }
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">System Alerts</h4>

    {{-- ========== MAIN ALERT LIST VIEW ========== --}}
    <div id="alertOverview">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold">Critical Buildings</h6>
                <div>
                    <span class="badge bg-danger text-white me-2">1 Critical</span>
                </div>
            </div>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th>Building</th>
                        <th>Offline Devices</th>
                        <th>Severity</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- ======= Destacados ======= --}}
                    <tr class="clickable-row" onclick="showBuilding('Stefani', 121, 155)">
                        <td><i class="bi bi-exclamation-triangle text-danger"></i></td>
                        <td>Stefani</td>
                        <td>121 / 155</td>
                        <td><span class="badge badge-critical">CRITICAL</span></td>
                        <td>2 minutes ago</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Biblioteca', 8, 40)">
                        <td><i class="bi bi-exclamation-circle text-warning"></i></td>
                        <td>General Library</td>
                        <td>8 / 40</td>
                        <td><span class="badge badge-warning">WARNING</span></td>
                        <td>15 minutes ago</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Centro de Estudiantes', 0, 30)">
                        <td><i class="bi bi-check-circle text-success"></i></td>
                        <td>Student Center</td>
                        <td>0 / 30</td>
                        <td><span class="badge badge-normal">NORMAL</span></td>
                        <td>2 hours ago</td>
                    </tr>

                    {{-- =======  edificios (placeholders NORMAL) ======= --}}
                    <tr class="clickable-row" onclick="showBuilding('Celis', 0, 24)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Celis</td><td>0 / 24</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Biologia', 0, 18)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Biologia</td><td>0 / 18</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('DeDiego', 0, 20)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>DeDiego</td><td>0 / 20</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Luchetti', 0, 14)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Luchetti</td><td>0 / 14</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('ROTC', 0, 12)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>ROTC</td><td>0 / 12</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Adm.Empresas', 0, 16)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Adm.Empresas</td><td>0 / 16</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Musa', 0, 10)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Musa</td><td>0 / 10</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Chardon', 0, 22)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Chardon</td><td>0 / 22</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Monzon', 0, 17)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Monzon</td><td>0 / 17</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Sanchez Hidalgo', 0, 12)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Sanchez Hidalgo</td><td>0 / 12</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Fisica', 0, 14)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Fisica</td><td>0 / 14</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Geologia', 0, 9)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Geologia</td><td>0 / 9</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Ciencias Marinas', 0, 8)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Ciencias Marinas</td><td>0 / 8</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Quimica', 0, 20)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Quimica</td><td>0 / 20</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Piñero', 0, 6)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Piñero</td><td>0 / 6</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Enfermeria', 0, 10)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Enfermeria</td><td>0 / 10</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Vagones', 0, 7)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Vagones</td><td>0 / 7</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Natatorio', 0, 6)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Natatorio</td><td>0 / 6</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Centro Nuclear', 0, 6)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Centro Nuclear</td><td>0 / 6</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Coliseo', 0, 10)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Coliseo</td><td>0 / 10</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Gimnacio', 0, 8)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Gimnacio</td><td>0 / 8</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Servicios Medicos', 0, 12)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Servicios Medicos</td><td>0 / 12</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Decanato de Estudiantes', 0, 9)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Decanato de Estudiantes</td><td>0 / 9</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Oficina de Facultad', 0, 10)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Oficina de Facultad</td><td>0 / 10</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Adm.Finca Alzamora', 0, 5)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Adm.Finca Alzamora</td><td>0 / 5</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Biblioteca', 0, 25)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Biblioteca</td><td>0 / 25</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Centro de Estudiantes', 0, 20)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Centro de Estudiantes</td><td>0 / 20</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Terrats', 0, 4)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Terrats</td><td>0 / 4</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Ing.Civil', 0, 14)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Ing.Civil</td><td>0 / 14</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Ing.Industrial', 0, 18)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Ing.Industrial</td><td>0 / 18</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Ing.Quimica', 0, 16)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Ing.Quimica</td><td>0 / 16</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Ing.Agricola', 0, 12)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Ing.Agricola</td><td>0 / 12</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Edificio A (Hotel Colegial)', 0, 6)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Edificio A (Hotel Colegial)</td><td>0 / 6</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Edificio B (Adm.Peq.Negocios y Oficina Adm)', 0, 6)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Edificio B (Adm.Peq.Negocios y Oficina Adm)</td><td>0 / 6</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Edificio C (Oficina de Extension Agricola)', 0, 6)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Edificio C (Oficina de Extension Agricola)</td><td>0 / 6</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Edificio D', 0, 6)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Edificio D</td><td>0 / 6</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========== BUILDING DETAIL VIEW ========== --}}
    <div id="buildingDetails" class="d-none">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 id="buildingTitle" class="fw-semibold mb-0">Building Name</h5>
                    <small class="text-muted">Detailed device report</small>
                </div>
                <div class="d-flex align-items-center">
                    <span id="buildingCount" class="badge bg-secondary me-3">0 / 0</span>
                    <button class="btn btn-outline-secondary btn-sm" onclick="goBack()">
                        <i class="bi bi-arrow-left me-1"></i> Return
                    </button>
                </div>
            </div>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Server</th>
                        <th>ID</th>
                        <th>User</th>
                        <th>Phone</th>
                        <th>MAC Address</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="buildingTableBody">
                    {{-- Dynamic content --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
/* ====== Device data per building (placeholders) ====== */
const buildingData = {
    "Stefani": [
        { server: "STF-001", id: "DEV-1001", user: "admin",  phone: "787-555-0101", mac: "00:1B:44:11:3A:B7", ip: "192.168.1.10" },
        { server: "STF-002", id: "DEV-1002", user: "jdoe",   phone: "787-555-0102", mac: "00:1B:44:11:3A:B8", ip: "192.168.1.11" },
        { server: "STF-003", id: "DEV-1003", user: "msmith", phone: "787-555-0103", mac: "00:1B:44:11:3A:B9", ip: "192.168.1.12" }
    ],
    "Biblioteca": [
        { server: "LIB-001", id: "DEV-2001", user: "jsantos", phone: "787-555-0201", mac: "00:1B:44:11:4A:11", ip: "192.168.2.10" }
    ],
    "Centro de Estudiantes": [
        { server: "STD-001", id: "DEV-3001", user: "drios", phone: "787-555-0301", mac: "00:1B:44:11:5A:21", ip: "192.168.3.10" }
    ],
    "Celis": [{ server:"CEL-001", id:"DEV-4001", user:"acelis", phone:"787-555-0401", mac:"00:1B:44:11:6A:01", ip:"192.168.4.10" }],
    "Biologia": [{ server:"BIO-001", id:"DEV-6001", user:"dbio", phone:"787-555-0601", mac:"00:1B:44:11:6A:03", ip:"192.168.6.10" }],
    "DeDiego": [{ server:"DED-001", id:"DEV-7001", user:"ddiego", phone:"787-555-0701", mac:"00:1B:44:11:6A:04", ip:"192.168.7.10" }],
    "Luchetti": [{ server:"LUC-001", id:"DEV-8001", user:"rluch", phone:"787-555-0801", mac:"00:1B:44:11:6A:05", ip:"192.168.8.10" }],
    "ROTC": [{ server:"ROT-001", id:"DEV-9001", user:"rrot", phone:"787-555-0901", mac:"00:1B:44:11:6A:06", ip:"192.168.9.10" }],
    "Adm.Empresas": [{ server:"ADM-EMP-001", id:"DEV-16001", user:"aemp", phone:"787-555-1601", mac:"00:1B:44:11:6A:13", ip:"192.168.16.10" }],
    "Musa": [{ server:"MUS-001", id:"DEV-5001", user:"mmusa", phone:"787-555-0501", mac:"00:1B:44:11:6A:02", ip:"192.168.5.10" }],
    "Chardon": [{ server:"CHA-001", id:"DEV-10001", user:"jchardon", phone:"787-555-1001", mac:"00:1B:44:11:6A:07", ip:"192.168.10.10" }],
    "Monzon": [{ server:"MON-001", id:"DEV-11001", user:"lmonzon", phone:"787-555-1101", mac:"00:1B:44:11:6A:08", ip:"192.168.11.10" }],
    "Sanchez Hidalgo": [{ server:"SAN-001", id:"DEV-17001", user:"ssanchez", phone:"787-555-1701", mac:"00:1B:44:11:6A:14", ip:"192.168.17.10" }],
    "Fisica": [{ server:"FIS-001", id:"DEV-12001", user:"cfisica", phone:"787-555-1201", mac:"00:1B:44:11:6A:09", ip:"192.168.12.10" }],
    "Geologia": [{ server:"GEO-001", id:"DEV-18001", user:"mgeo", phone:"787-555-1801", mac:"00:1B:44:11:6A:15", ip:"192.168.18.10" }],
    "Ciencias Marinas": [{ server:"CIM-001", id:"DEV-19001", user:"cimar", phone:"787-555-1901", mac:"00:1B:44:11:6A:16", ip:"192.168.19.10" }],
    "Quimica": [{ server:"QUI-001", id:"DEV-13001", user:"mquimica", phone:"787-555-1301", mac:"00:1B:44:11:6A:10", ip:"192.168.13.10" }],
    "Piñero": [{ server:"PIN-001", id:"DEV-20001", user:"npin", phone:"787-555-2001", mac:"00:1B:44:11:6A:17", ip:"192.168.20.10" }],
    "Enfermeria": [{ server:"ENF-001", id:"DEV-21001", user:"enfer", phone:"787-555-2101", mac:"00:1B:44:11:6A:18", ip:"192.168.21.10" }],
    "Vagones": [{ server:"VAG-001", id:"DEV-22001", user:"vvag", phone:"787-555-2201", mac:"00:1B:44:11:6A:19", ip:"192.168.22.10" }],
    "Natatorio": [{ server:"NAT-001", id:"DEV-23001", user:"nat", phone:"787-555-2301", mac:"00:1B:44:11:6A:1A", ip:"192.168.23.10" }],
    "Centro Nuclear": [{ server:"CNU-001", id:"DEV-24001", user:"cnuc", phone:"787-555-2401", mac:"00:1B:44:11:6A:1B", ip:"192.168.24.10" }],
    "Coliseo": [{ server:"COL-001", id:"DEV-25001", user:"col", phone:"787-555-2501", mac:"00:1B:44:11:6A:1C", ip:"192.168.25.10" }],
    "Gimnacio": [{ server:"GIM-001", id:"DEV-26001", user:"gim", phone:"787-555-2601", mac:"00:1B:44:11:6A:1D", ip:"192.168.26.10" }],
    "Servicios Medicos": [{ server:"SEM-001", id:"DEV-27001", user:"smed", phone:"787-555-2701", mac:"00:1B:44:11:6A:1E", ip:"192.168.27.10" }],
    "Decanato de Estudiantes": [{ server:"DEC-001", id:"DEV-28001", user:"dec", phone:"787-555-2801", mac:"00:1B:44:11:6A:1F", ip:"192.168.28.10" }],
    "Oficina de Facultad": [{ server:"OFI-001", id:"DEV-29001", user:"ofac", phone:"787-555-2901", mac:"00:1B:44:11:6A:20", ip:"192.168.29.10" }],
    "Adm.Finca Alzamora": [{ server:"ALZ-001", id:"DEV-30001", user:"alz", phone:"787-555-3001", mac:"00:1B:44:11:6A:21", ip:"192.168.30.10" }],
    "Biblioteca": [{ server:"BIB-001", id:"DEV-15001", user:"biblio", phone:"787-555-1501", mac:"00:1B:44:11:6A:12", ip:"192.168.15.10" }],
    "Centro de Estudiantes": [{ server:"EST-001", id:"DEV-14001", user:"est", phone:"787-555-1401", mac:"00:1B:44:11:6A:11", ip:"192.168.14.10" }],
    "Terrats": [{ server:"TER-001", id:"DEV-31001", user:"terr", phone:"787-555-3101", mac:"00:1B:44:11:6A:22", ip:"192.168.31.10" }],
    "Ing.Civil": [{ server:"ICV-001", id:"DEV-32001", user:"icv", phone:"787-555-3201", mac:"00:1B:44:11:6A:23", ip:"192.168.32.10" }],
    "Ing.Industrial": [{ server:"IID-001", id:"DEV-33001", user:"iid", phone:"787-555-3301", mac:"00:1B:44:11:6A:24", ip:"192.168.33.10" }],
    "Ing.Quimica": [{ server:"IQM-001", id:"DEV-34001", user:"iqm", phone:"787-555-3401", mac:"00:1B:44:11:6A:25", ip:"192.168.34.10" }],
    "Ing.Agricola": [{ server:"IAG-001", id:"DEV-35001", user:"iag", phone:"787-555-3501", mac:"00:1B:44:11:6A:26", ip:"192.168.35.10" }],
    "Edificio A (Hotel Colegial)": [{ server:"EHA-001", id:"DEV-36001", user:"eha", phone:"787-555-3601", mac:"00:1B:44:11:6A:27", ip:"192.168.36.10" }],
    "Edificio B (Adm.Peq.Negocios y Oficina Adm)": [{ server:"EHB-001", id:"DEV-37001", user:"ehb", phone:"787-555-3701", mac:"00:1B:44:11:6A:28", ip:"192.168.37.10" }],
    "Edificio C (Oficina de Extension Agricola)": [{ server:"EHC-001", id:"DEV-38001", user:"ehc", phone:"787-555-3801", mac:"00:1B:44:11:6A:29", ip:"192.168.38.10" }],
    "Edificio D": [{ server:"EHD-001", id:"DEV-39001", user:"ehd", phone:"787-555-3901", mac:"00:1B:44:11:6A:2A", ip:"192.168.39.10" }]
};

/* ====== UI handlers ====== */
function showBuilding(name, notWorking, total) {
    document.getElementById('alertOverview').classList.add('d-none');
    document.getElementById('buildingDetails').classList.remove('d-none');

    document.getElementById('buildingTitle').innerText = name;
    const count = document.getElementById('buildingCount');
    count.innerText = `${notWorking} / ${total}`;

    let colorClass = 'bg-success';
    if (notWorking > 0 && notWorking < 10) colorClass = 'bg-warning text-dark';
    else if (notWorking >= 10) colorClass = 'bg-danger';
    count.className = `badge ${colorClass} me-3`;

    const tbody = document.getElementById('buildingTableBody');
    tbody.innerHTML = '';

    if (buildingData[name]) {
        buildingData[name].forEach(device => {
            tbody.innerHTML += `
                <tr>
                    <td>${device.server}</td>
                    <td>${device.id}</td>
                    <td>${device.user}</td>
                    <td>${device.phone}</td>
                    <td>${device.mac}</td>
                    <td>${device.ip}</td>
                </tr>`;
        });
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    No registered devices for <strong>${name}</strong>.
                </td>
            </tr>`;
    }
}

function goBack() {
    document.getElementById('buildingDetails').classList.add('d-none');
    document.getElementById('alertOverview').classList.remove('d-none');
}

/*  URL hook so markers can open a building: /alerts?building=Stefani */
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const building = params.get('building');
    if (building) {
        // Default counters when opened from URL; ajusta si tienes data real
        showBuilding(building, 0, (buildingData[building] || []).length || 10);
    }
});
</script>
@endsection
