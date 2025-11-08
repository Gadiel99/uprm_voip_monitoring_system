{{--
/*
 * File: devices.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Device management interface with building overview and device details
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   This page provides a two-level interface for device management:
 *   Level 1: Building overview with device statistics
 *   Level 2: Detailed device listing per building
 *   Level 3: Individual device activity graphs (modal)
 * 
 * Features:
 *   - Building overview table showing device counts (total/online/offline)
 *   - Click-through to view all devices in a building
 *   - Device information: ID, User, Phone, MAC, IP
 *   - 30-day activity graph using Chart.js
 *   - Inline editing capabilities
 *   - Return navigation between views
 * 
 * Data Display:
 *   Buildings Table:
 *     - Building name
 *     - Total device count
 *     - Online device count
 *     - Offline device count
 *   
 *   Devices Table:
 *     - Device ID
 *     - Assigned user
 *     - Phone number
 *     - MAC address
 *     - IP address
 * 
 * Graphs:
 *   - Type: Line chart
 *   - X-axis: Days 1-30 (current month)
 *   - Y-axis: Binary (0=Inactive, 1=Active)
 *   - Point colors: Green (active), Red (inactive)
 *   - Library: Chart.js 3.x
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3
 *   - Chart.js (CDN)
 *   - Bootstrap Icons
 * 
 * Frontend Data:
 *   - Static demo data stored in JavaScript object
 *   - No backend database connection
 *   - Client-side navigation and filtering
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 software design description
 *   - Adheres to IEEE 829 test documentation standards
 */
--}}
@extends('components.layout.app')

@section('content')
<style>
    /* Card styling with rounded corners and soft shadow */
    .card {
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    /* Highlight table rows on hover */
    .table-hover tbody tr:hover {
        background-color: #f1f3f4;
        cursor: pointer;
    }

    /* Online badge styling */
    .badge-online {
        background-color: #e6f9ed;
        color: #00844b;
    }

    /* Offline badge styling */
    .badge-offline {
        background-color: #fdeaea;
        color: #c82333;
    }

    /* Small hint text for clickable rows */
    .click-hint {
        color: #00844b;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Device Management</h4>

    {{-- TABLE: CRITICAL DEVICES --}}
    {{-- Shows critical devices as a separate table --}}
    <div id="buildingOverview">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-semibold mb-3">Critical Devices</h5>
            <p class="text-muted mb-3">High-priority devices requiring special monitoring.</p>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Building</th>
                        <th>Total Devices</th>
                        <th>Online</th>
                        <th>Offline</th>
                    </tr>
                </thead>
                <tbody>
                    <tr onclick="showBuildingDevices('Critical Devices')">
                        <td><i class="bi bi-exclamation-triangle me-2 text-danger"></i> Critical Devices</td>
                        <td id="critical-total">0</td>
                        <td id="critical-online">0</td>
                        <td id="critical-offline">0</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- TABLE: BUILDINGS OVERVIEW --}}
        {{-- Shows a summary of all buildings and their device counts --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-semibold mb-3">Buildings Overview</h5>
            <p class="text-muted mb-3">Select a building to view all connected devices and their graphs.</p>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Building</th>
                        <th>Total Devices</th>
                        <th>Online</th>
                        <th>Offline</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- All buildings from the map --}}
                    <tr onclick="showBuildingDevices('Stefani')">
                        <td><i class="bi bi-building me-2 text-success"></i> Stefani</td>
                        <td>155</td>
                        <td>140</td>
                        <td>15</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Biblioteca')">
                        <td><i class="bi bi-building me-2 text-warning"></i> Biblioteca</td>
                        <td>40</td>
                        <td>35</td>
                        <td>5</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Centro de Estudiantes')">
                        <td><i class="bi bi-building me-2 text-success"></i> Centro de Estudiantes</td>
                        <td>30</td>
                        <td>30</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Celis')">
                        <td><i class="bi bi-building me-2 text-success"></i> Celis</td>
                        <td>24</td>
                        <td>24</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Biologia')">
                        <td><i class="bi bi-building me-2 text-success"></i> Biologia</td>
                        <td>18</td>
                        <td>18</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('DeDiego')">
                        <td><i class="bi bi-building me-2 text-success"></i> DeDiego</td>
                        <td>22</td>
                        <td>22</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Luchetti')">
                        <td><i class="bi bi-building me-2 text-success"></i> Luchetti</td>
                        <td>15</td>
                        <td>15</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('ROTC')">
                        <td><i class="bi bi-building me-2 text-success"></i> ROTC</td>
                        <td>12</td>
                        <td>12</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Adm.Empresas')">
                        <td><i class="bi bi-building me-2 text-success"></i> Adm.Empresas</td>
                        <td>28</td>
                        <td>28</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Musa')">
                        <td><i class="bi bi-building me-2 text-success"></i> Musa</td>
                        <td>16</td>
                        <td>16</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Chardon')">
                        <td><i class="bi bi-building me-2 text-success"></i> Chardon</td>
                        <td>25</td>
                        <td>25</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Monzon')">
                        <td><i class="bi bi-building me-2 text-success"></i> Monzon</td>
                        <td>20</td>
                        <td>20</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Sanchez Hidalgo')">
                        <td><i class="bi bi-building me-2 text-success"></i> Sanchez Hidalgo</td>
                        <td>19</td>
                        <td>19</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Fisica')">
                        <td><i class="bi bi-building me-2 text-success"></i> Fisica</td>
                        <td>32</td>
                        <td>32</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Geologia')">
                        <td><i class="bi bi-building me-2 text-success"></i> Geologia</td>
                        <td>14</td>
                        <td>14</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Ciencias Marinas')">
                        <td><i class="bi bi-building me-2 text-success"></i> Ciencias Marinas</td>
                        <td>11</td>
                        <td>11</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Quimica')">
                        <td><i class="bi bi-building me-2 text-success"></i> Quimica</td>
                        <td>35</td>
                        <td>35</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Piñero')">
                        <td><i class="bi bi-building me-2 text-success"></i> Piñero</td>
                        <td>21</td>
                        <td>21</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Enfermeria')">
                        <td><i class="bi bi-building me-2 text-success"></i> Enfermeria</td>
                        <td>17</td>
                        <td>17</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Vagones')">
                        <td><i class="bi bi-building me-2 text-success"></i> Vagones</td>
                        <td>8</td>
                        <td>8</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Natatorio')">
                        <td><i class="bi bi-building me-2 text-success"></i> Natatorio</td>
                        <td>6</td>
                        <td>6</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Centro Nuclear')">
                        <td><i class="bi bi-building me-2 text-success"></i> Centro Nuclear</td>
                        <td>9</td>
                        <td>9</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Coliseo')">
                        <td><i class="bi bi-building me-2 text-success"></i> Coliseo</td>
                        <td>13</td>
                        <td>13</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Gimnacio')">
                        <td><i class="bi bi-building me-2 text-success"></i> Gimnacio</td>
                        <td>10</td>
                        <td>10</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Servicios Medicos')">
                        <td><i class="bi bi-building me-2 text-success"></i> Servicios Medicos</td>
                        <td>7</td>
                        <td>7</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Decanato de Estudiantes')">
                        <td><i class="bi bi-building me-2 text-success"></i> Decanato de Estudiantes</td>
                        <td>12</td>
                        <td>12</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Oficina de Facultad')">
                        <td><i class="bi bi-building me-2 text-success"></i> Oficina de Facultad</td>
                        <td>15</td>
                        <td>15</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Adm.Finca Alzamora')">
                        <td><i class="bi bi-building me-2 text-success"></i> Adm.Finca Alzamora</td>
                        <td>5</td>
                        <td>5</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Terrats')">
                        <td><i class="bi bi-building me-2 text-success"></i> Terrats</td>
                        <td>18</td>
                        <td>18</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Ing.Civil')">
                        <td><i class="bi bi-building me-2 text-success"></i> Ing.Civil</td>
                        <td>23</td>
                        <td>23</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Ing.Industrial')">
                        <td><i class="bi bi-building me-2 text-success"></i> Ing.Industrial</td>
                        <td>27</td>
                        <td>27</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Ing.Quimica')">
                        <td><i class="bi bi-building me-2 text-success"></i> Ing.Quimica</td>
                        <td>31</td>
                        <td>31</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Ing.Agricola')">
                        <td><i class="bi bi-building me-2 text-success"></i> Ing.Agricola</td>
                        <td>14</td>
                        <td>14</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Edificio A (Hotel Colegial)')">
                        <td><i class="bi bi-building me-2 text-success"></i> Edificio A (Hotel Colegial)</td>
                        <td>8</td>
                        <td>8</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Edificio B (Adm.Peq.Negocios y Oficina Adm)')">
                        <td><i class="bi bi-building me-2 text-success"></i> Edificio B (Adm.Peq.Negocios y Oficina Adm)</td>
                        <td>9</td>
                        <td>9</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Edificio C (Oficina de Extension Agricola)')">
                        <td><i class="bi bi-building me-2 text-success"></i> Edificio C (Oficina de Extension Agricola)</td>
                        <td>6</td>
                        <td>6</td>
                        <td>0</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Edificio D')">
                        <td><i class="bi bi-building me-2 text-success"></i> Edificio D</td>
                        <td>7</td>
                        <td>7</td>
                        <td>0</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLE: DEVICES PER BUILDING --}}
    {{-- Displays all devices for the selected building --}}
    <div id="buildingDevices" class="d-none">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    {{-- Title will dynamically show building name --}}
                    <h5 class="fw-semibold mb-0" id="buildingTitle">Building Devices</h5>
                    <small class="click-hint">(Click row to view graph)</small>
                </div>
                {{-- Button to go back to overview --}}
                <button class="btn btn-outline-secondary btn-sm" onclick="goBack()">
                    <i class="bi bi-arrow-left me-1"></i> Return
                </button>
            </div>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Device ID</th>
                        <th>Assigned User</th>
                        <th>Phone Number</th>
                        <th>MAC Address</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="deviceTableBody">
                    {{-- Device rows will be populated dynamically via JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL: DEVICE GRAPH --}}
{{-- Modal displays activity chart for selected device --}}
<div class="modal fade" id="deviceGraphModal" tabindex="-1" aria-labelledby="deviceGraphModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        {{-- Device title dynamically updated --}}
        <h5 class="modal-title" id="deviceGraphModalLabel">Device Activity</h5>
      </div>
      <div class="modal-body">
        {{-- Chart.js canvas --}}
        <canvas id="deviceActivityChart" height="100"></canvas>
      </div>
      <div class="modal-footer">
        {{-- Close modal --}}
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-arrow-left me-1"></i> Return
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js library --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* Sample device data for each building */
const buildingDevicesData = {
    "Critical Devices": [], // Will be populated from localStorage
    "Stefani": [
        { id: "DEV-1001", user: "admin", phone: "787-555-0101", mac: "00:1B:44:11:3A:B7", ip: "192.168.1.10" },
        { id: "DEV-1002", user: "jdoe", phone: "787-555-0102", mac: "00:1B:44:11:3A:B8", ip: "192.168.1.11" },
        { id: "DEV-1003", user: "msmith", phone: "787-555-0103", mac: "00:1B:44:11:3A:B9", ip: "192.168.1.12" }
    ],
    "Biblioteca": [
        { id: "DEV-2001", user: "jsantos", phone: "787-555-0201", mac: "00:1B:44:11:4A:11", ip: "192.168.2.10" },
        { id: "DEV-2002", user: "acastro", phone: "787-555-0202", mac: "00:1B:44:11:4A:12", ip: "192.168.2.11" }
    ],
    "Centro de Estudiantes": [
        { id: "DEV-3001", user: "drios", phone: "787-555-0301", mac: "00:1B:44:11:5A:21", ip: "192.168.3.10" }
    ],
    "Celis": [
        { id: "DEV-4001", user: "alopez", phone: "787-555-0401", mac: "00:1B:44:11:6A:31", ip: "192.168.4.10" }
    ],
    "Biologia": [
        { id: "DEV-5001", user: "rperez", phone: "787-555-0501", mac: "00:1B:44:11:7A:41", ip: "192.168.5.10" }
    ],
    "DeDiego": [
        { id: "DEV-6001", user: "mgarcia", phone: "787-555-0601", mac: "00:1B:44:11:8A:51", ip: "192.168.6.10" }
    ],
    "Luchetti": [
        { id: "DEV-7001", user: "jrodriguez", phone: "787-555-0701", mac: "00:1B:44:11:9A:61", ip: "192.168.7.10" }
    ],
    "ROTC": [
        { id: "DEV-8001", user: "cfernandez", phone: "787-555-0801", mac: "00:1B:44:11:AA:71", ip: "192.168.8.10" }
    ],
    "Adm.Empresas": [
        { id: "DEV-9001", user: "lmartinez", phone: "787-555-0901", mac: "00:1B:44:11:BA:81", ip: "192.168.9.10" }
    ],
    "Musa": [
        { id: "DEV-10001", user: "dgonzalez", phone: "787-555-1001", mac: "00:1B:44:11:CA:91", ip: "192.168.10.10" }
    ],
    "Chardon": [
        { id: "DEV-11001", user: "shernandez", phone: "787-555-1101", mac: "00:1B:44:11:DA:A1", ip: "192.168.11.10" }
    ],
    "Monzon": [
        { id: "DEV-12001", user: "vramirez", phone: "787-555-1201", mac: "00:1B:44:11:EA:B1", ip: "192.168.12.10" }
    ],
    "Sanchez Hidalgo": [
        { id: "DEV-13001", user: "mtorres", phone: "787-555-1301", mac: "00:1B:44:11:FA:C1", ip: "192.168.13.10" }
    ],
    "Fisica": [
        { id: "DEV-14001", user: "jflores", phone: "787-555-1401", mac: "00:1B:44:12:0A:D1", ip: "192.168.14.10" }
    ],
    "Geologia": [
        { id: "DEV-15001", user: "rrivera", phone: "787-555-1501", mac: "00:1B:44:12:1A:E1", ip: "192.168.15.10" }
    ],
    "Ciencias Marinas": [
        { id: "DEV-16001", user: "acruz", phone: "787-555-1601", mac: "00:1B:44:12:2A:F1", ip: "192.168.16.10" }
    ],
    "Quimica": [
        { id: "DEV-17001", user: "lmorales", phone: "787-555-1701", mac: "00:1B:44:12:3A:01", ip: "192.168.17.10" }
    ],
    "Piñero": [
        { id: "DEV-18001", user: "dreyes", phone: "787-555-1801", mac: "00:1B:44:12:4A:11", ip: "192.168.18.10" }
    ],
    "Enfermeria": [
        { id: "DEV-19001", user: "mjimenez", phone: "787-555-1901", mac: "00:1B:44:12:5A:21", ip: "192.168.19.10" }
    ],
    "Vagones": [
        { id: "DEV-20001", user: "cdiaz", phone: "787-555-2001", mac: "00:1B:44:12:6A:31", ip: "192.168.20.10" }
    ],
    "Natatorio": [
        { id: "DEV-21001", user: "jruiz", phone: "787-555-2101", mac: "00:1B:44:12:7A:41", ip: "192.168.21.10" }
    ],
    "Centro Nuclear": [
        { id: "DEV-22001", user: "aortiz", phone: "787-555-2201", mac: "00:1B:44:12:8A:51", ip: "192.168.22.10" }
    ],
    "Coliseo": [
        { id: "DEV-23001", user: "rromero", phone: "787-555-2301", mac: "00:1B:44:12:9A:61", ip: "192.168.23.10" }
    ],
    "Gimnacio": [
        { id: "DEV-24001", user: "lvargas", phone: "787-555-2401", mac: "00:1B:44:12:AA:71", ip: "192.168.24.10" }
    ],
    "Servicios Medicos": [
        { id: "DEV-25001", user: "mcastillo", phone: "787-555-2501", mac: "00:1B:44:12:BA:81", ip: "192.168.25.10" }
    ],
    "Decanato de Estudiantes": [
        { id: "DEV-26001", user: "jgomez", phone: "787-555-2601", mac: "00:1B:44:12:CA:91", ip: "192.168.26.10" }
    ],
    "Oficina de Facultad": [
        { id: "DEV-27001", user: "ssantana", phone: "787-555-2701", mac: "00:1B:44:12:DA:A1", ip: "192.168.27.10" }
    ],
    "Adm.Finca Alzamora": [
        { id: "DEV-28001", user: "dramos", phone: "787-555-2801", mac: "00:1B:44:12:EA:B1", ip: "192.168.28.10" }
    ],
    "Terrats": [
        { id: "DEV-29001", user: "amendez", phone: "787-555-2901", mac: "00:1B:44:12:FA:C1", ip: "192.168.29.10" }
    ],
    "Ing.Civil": [
        { id: "DEV-30001", user: "lmedina", phone: "787-555-3001", mac: "00:1B:44:13:0A:D1", ip: "192.168.30.10" }
    ],
    "Ing.Industrial": [
        { id: "DEV-31001", user: "jvega", phone: "787-555-3101", mac: "00:1B:44:13:1A:E1", ip: "192.168.31.10" }
    ],
    "Ing.Quimica": [
        { id: "DEV-32001", user: "rcabrera", phone: "787-555-3201", mac: "00:1B:44:13:2A:F1", ip: "192.168.32.10" }
    ],
    "Ing.Agricola": [
        { id: "DEV-33001", user: "msoto", phone: "787-555-3301", mac: "00:1B:44:13:3A:01", ip: "192.168.33.10" }
    ],
    "Edificio A (Hotel Colegial)": [
        { id: "DEV-34001", user: "apagan", phone: "787-555-3401", mac: "00:1B:44:13:4A:11", ip: "192.168.34.10" }
    ],
    "Edificio B (Adm.Peq.Negocios y Oficina Adm)": [
        { id: "DEV-35001", user: "nnunez", phone: "787-555-3501", mac: "00:1B:44:13:5A:21", ip: "192.168.35.10" }
    ],
    "Edificio C (Oficina de Extension Agricola)": [
        { id: "DEV-36001", user: "dsilva", phone: "787-555-3601", mac: "00:1B:44:13:6A:31", ip: "192.168.36.10" }
    ],
    "Edificio D": [
        { id: "DEV-37001", user: "jleón", phone: "787-555-3701", mac: "00:1B:44:13:7A:41", ip: "192.168.37.10" }
    ]
};

let chartInstance = null;

// Load critical devices from localStorage
function loadCriticalDevices() {
    const criticalDevices = JSON.parse(localStorage.getItem('criticalDevices') || '[]');
    
    // Convert critical devices to the same format as building devices
    buildingDevicesData["Critical Devices"] = criticalDevices.map((device, index) => ({
        id: `CRIT-${1001 + index}`,
        user: device.owner || 'N/A',
        phone: 'N/A', // Critical devices don't have phone numbers in the admin table
        mac: device.mac,
        ip: device.ip
    }));
    
    // Update building overview counts
    const totalCritical = criticalDevices.length;
    const onlineCritical = criticalDevices.filter(d => d.status === 'Online').length;
    const offlineCritical = totalCritical - onlineCritical;
    
    document.getElementById('critical-total').textContent = totalCritical;
    document.getElementById('critical-online').textContent = onlineCritical;
    document.getElementById('critical-offline').textContent = offlineCritical;
}

// Load critical devices on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('Page loaded - DOMContentLoaded');
    
    loadCriticalDevices();
    
    // Listen for changes to critical devices
    window.addEventListener('storage', (e) => {
        if (e.key === 'criticalDevices') {
            loadCriticalDevices();
        }
    });
    
    // Check URL parameters for navigation from admin panel
    const urlParams = new URLSearchParams(window.location.search);
    const deviceIP = urlParams.get('ip');
    const building = urlParams.get('building');
    
    console.log('URL params:', { deviceIP, building });
    
    if (deviceIP && building) {
        console.log('Found navigation params - IP:', deviceIP, 'Building:', building);
        
        // Wait for critical devices to load
        setTimeout(() => {
            console.log('Opening building:', building);
            console.log('Critical devices data:', buildingDevicesData["Critical Devices"]);
            
            // Open the building
            showBuildingDevices(building);
            
            // Find and highlight the specific device
            setTimeout(() => {
                console.log('Looking for device with IP:', deviceIP);
                const rows = document.querySelectorAll('#deviceTableBody tr');
                console.log('Found rows:', rows.length);
                
                let found = false;
                rows.forEach((row, index) => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 5) {
                        const ipCell = cells[4]; // IP is the 5th column (index 4)
                        const ipText = ipCell.textContent.trim();
                        console.log(`Row ${index} IP:`, ipText);
                        
                        if (ipText === deviceIP) {
                            found = true;
                            const deviceId = cells[0].textContent.trim(); // Get device ID
                            console.log('✓ Found matching device:', deviceId);
                            
                            // Highlight the device row prominently
                            row.style.backgroundColor = '#ffeb3b'; // Bright yellow
                            row.style.border = '2px solid #ff9800'; // Orange border
                            row.style.transition = 'all 0.3s';
                            
                            // Scroll to center the device in view
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            
                            // Fade to lighter yellow after 2 seconds, stay highlighted
                            setTimeout(() => {
                                row.style.backgroundColor = '#fff3cd';
                                row.style.border = '';
                            }, 2000);
                        }
                    }
                });
                
                if (!found) {
                    console.log('✗ Device not found in table');
                }
                
                // Clean up URL parameters
                window.history.replaceState({}, document.title, '/devices');
            }, 500);
        }, 300);
    }
});



/* Show all devices for the selected building */
function showBuildingDevices(name) {
    // Hide overview and show devices table
    document.getElementById('buildingOverview').classList.add('d-none');
    document.getElementById('buildingDevices').classList.remove('d-none');

    // Update building title
    document.getElementById('buildingTitle').innerText = name + " — Devices";

    // Clear previous device rows
    const tbody = document.getElementById('deviceTableBody');
    tbody.innerHTML = '';

    // Add device rows dynamically
    buildingDevicesData[name].forEach(device => {
        tbody.innerHTML += `
            <tr onclick="openDeviceGraph('${device.id}')">
                <td>${device.id}</td>
                <td>${device.user}</td>
                <td>${device.phone}</td>
                <td>${device.mac}</td>
                <td>${device.ip}</td>
            </tr>`;
    });
}

/* Go back to the buildings overview */
function goBack() {
    document.getElementById('buildingDevices').classList.add('d-none');
    document.getElementById('buildingOverview').classList.remove('d-none');
}

/* Open modal and display device activity chart */
function openDeviceGraph(deviceId) {
    const modal = new bootstrap.Modal(document.getElementById('deviceGraphModal'));

    // Display current month in modal title
    const currentMonth = new Date().toLocaleString('default', { month: 'long', year: 'numeric' });
    document.getElementById('deviceGraphModalLabel').innerText = `Device Activity — ${deviceId} (${currentMonth})`;
    modal.show();

    // Generate sample labels (days 1–30) and sample data (all inactive)
    const labels = Array.from({length: 30}, (_, i) => i + 1);
    const data = Array.from({length: 30}, () => 0); // All inactive
    const pointColors = data.map(v => v === 0 ? 'red' : '#00844b');

    // Destroy previous chart instance to avoid duplication
    if (chartInstance) chartInstance.destroy();

    // Initialize new Chart.js line chart
    const ctx = document.getElementById('deviceActivityChart').getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Active (1) / Inactive (0)',
                data: data,
                borderColor: '#00844b',
                borderWidth: 2,
                pointBackgroundColor: pointColors,
                fill: false,
                tension: 0
            }]
        },
        options: {
            animation: false, // Disable animations for faster rendering
            plugins: {
                legend: { display: false } // Hide legend
            },
            scales: {
                y: {
                    min: 0,
                    max: 1,
                    ticks: { stepSize: 1 } // Only show 0 or 1
                },
                x: {
                    title: { display: true, text: 'Days (1–30)' } // X-axis label
                }
            }
        }
    });
}
</script>
@endsection
