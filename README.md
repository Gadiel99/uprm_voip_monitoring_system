## 📞 UPRM Registry Moniotring System with sipXcom

<p align="center"><img src="https://upload.wikimedia.org/wikipedia/en/thumb/6/65/UPR_at_Mayaguez_Seal.svg/250px-UPR_at_Mayaguez_Seal.svg.png" width="200" alt="UPRM logo" style="margin-right:100px;">
<img src="https://www.iant.de/wp-content/uploads/2022/10/sipXcom-logo-image_green_blue.horizontal.png" width="300" alt="sipXcom logo" style=" margin-right:100px;">
</p>

## 🧾 About Project
The auxiliary services department manages the campus telephone service, for which it has implemented a voice over IP system using the SipXcon platform. A call logging monitoring system is needed to identify service problems on campus.

## 🎯 System Objectives

The system aims to:
- Monitor active and inactive device registrations in real-time.
- Trigger alerts when thresholds of inactive devices are exceeded.
- Store historical data in a MariaDB database for visualization and reporting.
- Integrate data from PostgreSQL and MongoDB (from SipXcom) using an ETL pipeline.
- Provide a web dashboard with metrics, alerts, and backup management tools.

## 🧩 System Architecture
The system follows a **Monolithic MVC-inspired architecture**:

### Architecture Diagram 
<p align="center"><img src="images\TRIATEK Design Diagram.png" alt="UPRM logo" style="margin-right: 30px;">

### 1. User Interface Layer (View)
- Interactive Map  
- Alert Panel  
- Device Table  

### 2. Business Layer (Controller)
- Authentication Controller  
- Alert Controller  
- Backup Manager  

### 3. Data Interface Layer (Model)
- Eloquent ORM (Laravel)  
- ETL Pipeline (for data extraction and transformation)  

**Databases:**
- **PostgreSQL** and **MongoDB** (SipXcom data sources)  
- **MariaDB** (application data repository)


## Setup Instructions
<div align="center">
    <h3>To get the web page of this repository to run, you'll need to follow this steps.</h3>
</div>


    
<summary><strong> 📄Step 1: Clone the repository</strong></summary>
   

    git clone <repository-url>

    cd uprm_viop_monitoring system

<summary><strong> ⚙️Step 2: Install Dependencies</strong></summary>
    
    
    
    composer install --ignore-platform-req=ext-mongodb

<summary><strong> 🏕️Step 3: Setup enviroment configuration </strong><summary>


    cp .env.example .env

<summary><strong> 🔑Step 4: Generate application key</strong><summary>




    php artisan key:generate

<summary><strong> 🧹Step 5: Clear configuration cache</strong><summary>

    

    php artisan config:clear



## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Project Sponsors

We would like to extend our thanks to the following sponsors for lending us the development tools and the resources for this project. 

- **[CTI-RUM](https://www.uprm.edu/cti)**



## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Controllers

This project follows thin-controller conventions; controllers validate inputs, call model/service methods, and return views or redirects.

- DevicesController
  - GET /devices → index(): Devices overview page (buildings table and guidance).
  - GET /devices/building/{building} → byBuilding(building): Filtered listing for a building.
  - Notes: Read-only mock data currently; wire to DB when ETL is ready.

- AdminUserController (admin-only)
  - GET /admin/users → index(): Renders Admin page and loads the Users tab with real data.
  - POST /admin/users → store(): Create user. Required: name, email, password. Role: user|admin (super_admin creation guarded).
  - PATCH /admin/users/{user}/role → updateRole(): Change role. Restrictions: cannot change own role; cannot demote super admins unless policy allows.
  - DELETE /admin/users/{user} → destroy(): Delete user. Restrictions: cannot delete self; cannot delete super admins.
  - Flash: status and validation errors are shown in the Users tab.

- ProfileController (authenticated)
  - GET /profile → edit(): Account settings modal/tabs.
  - PATCH /profile → update(): Update name/email. Respects return_to and tab flash to reopen the modal at the correct tab.
  - PATCH /profile/password → updatePassword(): Update password with current_password check and confirmation.
  - DELETE /profile → destroy(): Delete current user after password confirmation (Breeze pattern).

- AccountController
  - Reserved for future account UX flows (not wired).

Middleware
- 'auth' protects dashboard/routes.
- 'admin' restricts Admin routes (see App\Http\Middleware\AdminOnly).

Routing
- See routes/web.php for grouped routes and inline documentation.
