# UPRM VoIP Monitoring System - Architecture Diagram (Mermaid)

## Complete System Architecture

```mermaid
graph TB
    subgraph External["External Systems - SipXcom Platform (CentOS)"]
        PG[(PostgreSQL<br/>Phone Config)]
        MG[(MongoDB<br/>SIP Registrations)]
        CSV[CSV Export<br/>phone.csv + users.csv]
    end

    subgraph ETL["ETL Pipeline - Ubuntu Server"]
        CRON[Cron Job<br/>Every 5 minutes]
        SCP[SCP Transfer<br/>Remote → Local]
        ETL[ETLService<br/>Data Processing]
        IMPORT[DataImportService<br/>CSV Parser]
    end

    subgraph AppDB["Application Database - MariaDB (Ubuntu)"]
        DB[(MariaDB - InnoDB<br/>devices, extensions, networks<br/>buildings, users, alert_settings<br/>device_activity)]
    end

    subgraph Model["Model Layer - Business Logic"]
        MODELS[Eloquent Models<br/>User, Devices, Extensions<br/>Network, Building<br/>AlertSettings, DeviceActivity]
        SERVICES[Business Services<br/>NotificationService<br/>BackupService<br/>DeviceActivityService<br/>FileCleanupService]
    end

    subgraph Controller["Controller Layer - Request Orchestration"]
        MIDDLEWARE[Middleware Pipeline<br/>auth - Authentication<br/>AdminOnly - Authorization<br/>CacheManager - Cache Control<br/>MarkUserOnline - Activity<br/>CSRF Protection]
        CONTROLLERS[Controllers<br/>HomeController<br/>DevicesController<br/>AlertsController<br/>AdminController<br/>ReportsController<br/>BuildingController]
        API[API Endpoints<br/>/api/critical-devices/status<br/>/api/device-activity/*<br/>Protected from direct access]
    end

    subgraph View["View Layer - Presentation"]
        BLADE[Blade Templates<br/>home.blade.php - Dashboard<br/>devices.blade.php - Tables<br/>alerts.blade.php - Notifications<br/>reports.blade.php - Search<br/>admin.blade.php - Management]
        ASSETS[Client Assets<br/>Bootstrap 5<br/>Bootstrap Icons<br/>Alpine.js<br/>Vite Build]
    end

    subgraph Infra["Infrastructure - Supporting Systems"]
        EMAIL[SMTP Email<br/>Critical Alerts<br/>Password Resets<br/>Notifications]
        STORAGE[File Storage<br/>Import Archives<br/>Database Backups<br/>Application Logs]
        SCHEDULER[Task Scheduler<br/>ETL Automation<br/>File Cleanup<br/>Weekly Backups<br/>Activity Rotation]
    end

    USER[End User<br/>Web Browser]

    %% External to ETL Flow
    PG -->|Export| CSV
    MG -->|Export| CSV
    CSV -->|Trigger| CRON
    CRON -->|Execute| SCP
    SCP -->|Download| ETL
    ETL -->|Parse| IMPORT
    IMPORT -->|Store| DB

    %% Database to Model
    DB <-->|Eloquent ORM| MODELS
    MODELS <-->|Business Logic| SERVICES

    %% Model to Controller
    MODELS -->|Data| CONTROLLERS
    SERVICES -->|Operations| CONTROLLERS

    %% Request Flow
    USER -->|HTTP Request| MIDDLEWARE
    MIDDLEWARE -->|Authenticated| CONTROLLERS
    MIDDLEWARE -->|Unauthorized| USER
    CONTROLLERS -->|API Response| API
    API -->|JSON| USER
    CONTROLLERS -->|Render| BLADE

    %% View to User
    BLADE -->|HTML| ASSETS
    ASSETS -->|Rendered Page| USER

    %% Infrastructure Connections
    SERVICES -->|Send| EMAIL
    EMAIL -->|Notify| USER
    SERVICES -->|Write| STORAGE
    SCHEDULER -->|Execute| ETL
    SCHEDULER -->|Trigger| SERVICES
    CONTROLLERS -->|Read/Write| STORAGE

    %% Styling
    classDef external fill:#ff6b6b,stroke:#c92a2a,color:#fff
    classDef etl fill:#fd7e14,stroke:#d9480f,color:#fff
    classDef database fill:#51cf66,stroke:#2f9e44,color:#fff
    classDef model fill:#4dabf7,stroke:#1971c2,color:#fff
    classDef controller fill:#339af0,stroke:#1864ab,color:#fff
    classDef view fill:#9775fa,stroke:#6741d9,color:#fff
    classDef infra fill:#f06595,stroke:#c2255c,color:#fff
    classDef user fill:#ffd43b,stroke:#fab005,color:#000

    class PG,MG,CSV external
    class CRON,SCP,ETL,IMPORT etl
    class DB database
    class MODELS,SERVICES model
    class MIDDLEWARE,CONTROLLERS,API controller
    class BLADE,ASSETS view
    class EMAIL,STORAGE,SCHEDULER infra
    class USER user
```

## Detailed Component Flow

### 1. Data Ingestion Flow
```mermaid
sequenceDiagram
    participant SipXcom as SipXcom (CentOS)
    participant Cron as Cron Job
    participant SCP as SCP Transfer
    participant ETL as ETLService
    participant Import as DataImportService
    participant DB as MariaDB

    SipXcom->>SipXcom: Export PostgreSQL/MongoDB to CSV
    Note over SipXcom: phone.csv + users.csv
    Cron->>Cron: Trigger every 5 minutes
    Cron->>SCP: Execute SCP command
    SCP->>SipXcom: Download CSV files
    SCP->>ETL: Pass file paths
    ETL->>Import: Parse CSV data
    Import->>DB: Insert/Update records
    Note over DB: devices, extensions tables updated
```

### 2. User Request Flow
```mermaid
sequenceDiagram
    participant User as Web Browser
    participant MW as Middleware
    participant Cache as CacheManager
    participant Auth as AdminOnly
    participant Ctrl as Controller
    participant Model as Eloquent Model
    participant DB as MariaDB
    participant View as Blade Template

    User->>MW: HTTP Request
    MW->>Cache: Check authentication
    alt Not authenticated
        Cache->>User: Redirect to login
    else Authenticated
        Cache->>Auth: Check role (if admin route)
        alt Not admin
            Auth->>User: Redirect with error
        else Authorized
            Auth->>Ctrl: Forward request
            Ctrl->>Model: Query data
            Model->>DB: Execute SQL
            DB->>Model: Return results
            Model->>Ctrl: Return data
            Ctrl->>View: Pass data
            View->>User: Render HTML
        end
    end
```

### 3. Real-time Notifications Flow
```mermaid
graph LR
    subgraph Frontend["Frontend - JavaScript"]
        JS[JavaScript Fetch<br/>Every 15 seconds]
    end

    subgraph Backend["Backend - Laravel"]
        API[API Controller<br/>getCriticalDevicesStatus]
        MODEL[Devices Model<br/>is_critical = true]
        DB[(MariaDB)]
    end

    subgraph UIDisplay["UI Display"]
        BELL[Bell Icon]
        DROPDOWN[Notification Dropdown]
        BADGE[Red Badge Count]
    end

    JS -->|AJAX Request| API
    API -->|Query| MODEL
    MODEL -->|SELECT| DB
    DB -->|Offline Devices| MODEL
    MODEL -->|JSON Response| API
    API -->|Critical Devices| JS
    JS -->|Update| BELL
    JS -->|Populate| DROPDOWN
    JS -->|Show Count| BADGE

    classDef frontend fill:#9775fa,stroke:#6741d9,color:#fff
    classDef backend fill:#4dabf7,stroke:#1971c2,color:#fff
    classDef ui fill:#ffd43b,stroke:#fab005,color:#000

    class JS frontend
    class API,MODEL,DB backend
    class BELL,DROPDOWN,BADGE ui
```

### 4. Admin Panel Access Control
```mermaid
flowchart TD
    START[User clicks Admin tab]
    CHECK_AUTH{Authenticated?}
    CHECK_ROLE{Admin or<br/>Super Admin?}
    LOAD_PAGE[Load Admin Panel]
    REDIRECT_LOGIN[Redirect to Login<br/>'Please login to continue']
    REDIRECT_HOME[Redirect to Dashboard<br/>'You are not an admin']

    START --> CHECK_AUTH
    CHECK_AUTH -->|No| REDIRECT_LOGIN
    CHECK_AUTH -->|Yes| CHECK_ROLE
    CHECK_ROLE -->|No| REDIRECT_HOME
    CHECK_ROLE -->|Yes| LOAD_PAGE

    style START fill:#4dabf7,color:#fff
    style LOAD_PAGE fill:#51cf66,color:#fff
    style REDIRECT_LOGIN fill:#ff6b6b,color:#fff
    style REDIRECT_HOME fill:#ff6b6b,color:#fff
```

### 5. Backup & Restore Flow
```mermaid
sequenceDiagram
    participant Admin as Admin User
    participant UI as Admin Panel UI
    participant Ctrl as AdminController
    participant Backup as BackupService
    participant FS as File System
    participant DB as MariaDB

    Note over Admin,DB: Manual Backup Creation
    Admin->>UI: Click "Create Backup"
    UI->>Ctrl: POST /admin/backup/create
    Ctrl->>Backup: triggerManualBackup()
    Backup->>DB: mysqldump command
    DB->>Backup: SQL dump
    Backup->>FS: Save backup_YYYY-MM-DD_HHMMSS.zip
    FS->>Backup: Confirm saved
    Backup->>Ctrl: Success message
    Ctrl->>UI: Redirect with flash message
    UI->>Admin: Show "Backup created successfully"

    Note over Admin,DB: Backup Download
    Admin->>UI: Click "Download Latest Backup"
    UI->>Ctrl: GET /admin/backup/download
    Ctrl->>FS: Read latest backup file
    FS->>Ctrl: File stream
    Ctrl->>Admin: Download file

    Note over Admin,DB: Database Restore
    Admin->>UI: Click "Restore from Backup"
    UI->>Ctrl: POST /admin/backup/restore
    Ctrl->>Backup: restoreBackup()
    Backup->>FS: Read backup file
    FS->>Backup: SQL content
    Backup->>DB: Execute SQL statements
    alt Restore Success
        DB->>Backup: Restore complete
        Backup->>Ctrl: Success
        Ctrl->>UI: Redirect to login
        UI->>Admin: Show success notification
    else Restore Failed
        DB->>Backup: Error
        Backup->>Ctrl: Failure
        Ctrl->>UI: Redirect to login
        UI->>Admin: Show error notification
    end
```

### 6. Device Activity Tracking
```mermaid
graph TB
    subgraph DataCollection["Data Collection - Every 5 Minutes"]
        CRON[Cron Scheduler]
        ACTIVITY[DeviceActivityService]
        DEVICES[Query All Devices]
    end

    subgraph Storage["Storage - MariaDB"]
        DA[(device_activity table<br/>288 samples per day<br/>per device)]
    end

    subgraph APIAccess["API Access"]
        API_TODAY[GET /api/device-activity/{id}?day=1<br/>Today's activity]
        API_YESTERDAY[GET /api/device-activity/{id}?day=2<br/>Yesterday's activity]
        API_BOTH[GET /api/device-activity/{id}/both<br/>Both days comparison]
    end

    subgraph FrontendDisplay["Frontend Display"]
        CHART[24-hour Timeline Chart<br/>Green = Online<br/>Red = Offline<br/>Gray = No data]
    end

    CRON -->|Every 5 min| ACTIVITY
    ACTIVITY -->|Sample status| DEVICES
    DEVICES -->|Record sample| DA
    
    API_TODAY -->|Query| DA
    API_YESTERDAY -->|Query| DA
    API_BOTH -->|Query| DA
    
    DA -->|JSON Response| API_TODAY
    DA -->|JSON Response| API_YESTERDAY
    DA -->|JSON Response| API_BOTH
    
    API_TODAY -->|Data| CHART
    API_YESTERDAY -->|Data| CHART
    API_BOTH -->|Data| CHART

    classDef cron fill:#fd7e14,color:#fff
    classDef storage fill:#51cf66,color:#fff
    classDef api fill:#4dabf7,color:#fff
    classDef display fill:#9775fa,color:#fff

    class CRON,ACTIVITY,DEVICES cron
    class DA storage
    class API_TODAY,API_YESTERDAY,API_BOTH api
    class CHART display
```

## Technology Stack Summary

```mermaid
mindmap
  root((UPRM VoIP<br/>Monitoring))
    Backend
      Laravel 11
        PHP 8.x
        Eloquent ORM
        Blade Templates
        Artisan CLI
      MariaDB
        InnoDB Engine
        7 Main Tables
    Frontend
      Bootstrap 5
        Responsive Grid
        Components
        Icons
      Alpine.js
        Minimal Interactivity
      Vite
        Asset Bundling
    Infrastructure
      Ubuntu Server
        Application Host
        Cron Jobs
        File Storage
      CentOS Server
        SipXcom Platform
        PostgreSQL
        MongoDB
    Security
      Middleware
        Authentication
        Authorization
        Cache Control
      CSRF Protection
      API Guards
      Role-Based Access
```

## Key Features & Flows

### Authentication Flow
- User logs in → Credentials validated → Session created → Middleware checks auth on each request
- Logout → Session destroyed → CacheManager prevents back button access

### Authorization Flow  
- AdminOnly middleware checks user role
- Non-admin users redirected with friendly error message
- Admin routes: /admin, /admin/users, /admin/backup, etc.

### ETL Pipeline
- Runs every 5 minutes via cron
- Downloads CSV from SipXcom (CentOS)
- Parses phone.csv and users.csv
- Updates devices and extensions tables in MariaDB
- Maintains data synchronization

### Cache Management
- CacheManager middleware prevents browser caching
- After logout, back button redirects to login
- No-cache headers on all authenticated pages
- 419 Page Expired handler for CSRF token expiration

### API Protection
- API endpoints check for AJAX requests
- Direct browser access blocked
- Returns 403 or redirects to dashboard
- Prevents API URLs in browser history
