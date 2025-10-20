## 📞 UPRM Registry Moniotring System with sipXcom

<p align="center"><img src="https://upload.wikimedia.org/wikipedia/en/thumb/6/65/UPR_at_Mayaguez_Seal.svg/250px-UPR_at_Mayaguez_Seal.svg.png" width="200" alt="UPRM logo" style="margin-right: 100px;">
<img src="https://www.iant.de/wp-content/uploads/2022/10/sipXcom-logo-image_green_blue.horizontal.png" margin-left= "100 px" width="300" alt="sipXcom logo">
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


## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Project Sponsors

We would like to extend our thanks to the following sponsors for lending us the development tools and the resources for this project. 

- **[CTI-RUM](https://www.uprm.edu/cti)**



## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
