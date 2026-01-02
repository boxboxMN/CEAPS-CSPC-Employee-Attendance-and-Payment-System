🔐 **CEAPS Portal – Admin & Employee Login System**

The ***CSPC Employee Attendance and Payment System (CEAPS)*** is an internal web-based management system designed to streamline employee timekeeping, attendance monitoring, and payroll computation for college staff. The system emphasizes data accuracy, secure access control, and efficient record management through a role-based Admin and Employee portal.

Built using ***PHP, MySQL, HTML, CSS, and JavaScript***, CEAPS implements secure authentication with session handling and password hashing, alongside an intuitive landing page that routes users to the appropriate portal based on their role.

*This system was developed as the Final Project for the Advanced Database System Course SY 2024–2025, under the Bachelor of Science in Information Technology (BSIT) program.*
________________________________________

👥 **Project Members:**

Miguela Antonette Baluca

Jhyzzeel Dianela

Mariel Hernandez
________________________________________
📌 Features

  🧭 Landing Portal

  • Interactive homepage with animated logo
  
  • User routing to:
  
    o Admin Portal
    
    o Employee Portal
________________________________________
📊 Admin Dashboard

    Admin Dashboard Features:
    
    o Total number of employees
    
    o On-time attendance percentage
    
    o Employees on time today
    
    o Employees late today
    
    o Dynamic monthly attendance bar chart
    
    o Year-based attendance filtering
    

    👥 Employee Management

     o Employee list view
      
     o Work schedule management
      
     o Attendance tracking (on-time vs late)
      
     o Role and payroll navigation
     
      
    🖼️ Profile Management
      
    Admin can:
      
    o Update username and personal details
        
    o Change password
        
    o Upload and update profile photo
        
    o Modal-based editing with AJAX update (no page refresh)
    
  ________________________________________
👤 Employee Dashboard & Attendance System

    Employee Dashboard Features:

      o Real-time employee summary:
      
      o Total work hours rendered
      
      o Total work days
      
      o Average work hours per day
      
      o Assigned hourly rate
      
      o Personalized greeting using employee profile data
      
      o Attendance Recording
      
      o Attendance History
      
      o Salary & Payroll Summary
________________________________________

⚙️ System Logic Highlights

• Uses prepared statements for all database queries

• Attendance status is automatically calculated by comparing scheduled and actual time-in

• Payroll and attendance calculations are handled server-side for accuracy
________________________________________
🛠️ Technologies Used

•	Frontend:

    o	HTML5
    
    o	CSS3
  
    o	JavaScript

•	Backend:

    o	PHP (Sessions & Authentication)
    
    o	MySQL (via mysqli prepared statements)

•	Security:

    o	Password hashing & verification
    
    o	SQL injection prevention

