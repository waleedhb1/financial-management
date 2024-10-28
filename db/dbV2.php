<?php
$servername = "localhost";
$username = "root";
$password = "";


try {
    
    // Connect to MySQL as root user (no dbname provided)
    $conn = new PDO("mysql:host=$servername", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL to create database
    $sqlCreateDatabase = "CREATE DATABASE IF NOT EXISTS financial_management";

    // Execute the database creation statement
    $conn->exec($sqlCreateDatabase);
    echo "Database financial_management created successfully.<br>";

    // Switch to the newly created database
    $conn->exec("USE financial_management");

    // SQL to create budgets table
    $sqlbudgets = "CREATE TABLE budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_budget DECIMAL(15, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

    // SQL to create budget_years table
    $sqlbudgetyears = "CREATE TABLE budget_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_id INT,
    year INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE
)";

    // SQL to create contracts table
    $sqlcontracts = "CREATE TABLE contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_year_id INT,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_year_id) REFERENCES budget_years(id) ON DELETE CASCADE
)";

    // SQL to create logs table
    $sqllogs = "CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

    // SQL to create users table

    $sqlusers = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        role ENUM('employee', 'department_manager', 'admin_manager', 'general_manager', 'admin') DEFAULT 'employee',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

"INSERT INTO users (
    username,
    password,
    email,
    role,
    created_at
) VALUES (
    'admin',              -- اسم المستخدم
    'admin123',          -- كلمة المرور
    'admin@example.com', -- البريد الإلكتروني
    'admin',             -- الصلاحية
    NOW()                -- تاريخ الإنشاء
)";
    // SQL to create login logs table
    $sqlloginlogs= "CREATE TABLE login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(50),
    login_time DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

    // SQL to create pending changes table
    $sqlPendingChanges= "CREATE TABLE pending_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    change_type ENUM('insert', 'update', 'delete') NOT NULL,
    budget_year_id INT,
    year INT,
    amount DECIMAL(15, 2),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (budget_year_id) REFERENCES budget_years(id)
)";

    // SQL to create projects table
    $sqlProjects = "CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50),
    contract_name VARCHAR(255) NOT NULL,
    tender_type ENUM('منافسة عامة', 'شراء مباشر', 'سوق الكتروني') NOT NULL,
    project_nature ENUM('توريد', 'تشغيل', '-') DEFAULT '-',
    start_date DATE,
    end_date DATE,
    contract_sign_date DATE,
    start_minutes_date DATE,
    work_completion_date DATE,
    vendor VARCHAR(255),
    notes TEXT,
    payment_method VARCHAR(50),
    owning_department ENUM('خدمات تقنية', 'التكامل', 'البنية التحتية', 'التطبيقات') NOT NULL,
    connection_status ENUM('التزام', 'تخطيط', 'حجز', 'منتهي') NOT NULL,
    total_estimated_value DECIMAL(15, 2) DEFAULT 0,
    total_contractual_value DECIMAL(15, 2) DEFAULT 0,
    total_reserved_value DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

    // SQL to create project_years table
    $sqlProjectYears = "CREATE TABLE project_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    year INT NOT NULL,
    estimated_value DECIMAL(15, 2) DEFAULT 0,
    contractual_value DECIMAL(15, 2) DEFAULT 0,
    reserved_value DECIMAL(15, 2) DEFAULT 0,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
)";

    // SQL to create project_payments table
    $sqlProjectPayments = "CREATE TABLE project_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    year INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
)";

// SQL to create used_years table
$sqlUsedYears = "CREATE TABLE IF NOT EXISTS `used_years` (
    `year` INT NOT NULL UNIQUE,
    PRIMARY KEY (`year`)
  )";



    // Execute each table creation statement
    $conn->exec($sqlbudgets);
    echo "Table budgets created successfully.<br>";

    $conn->exec($sqlbudgetyears);
    echo "Table budget years created successfully.<br>";

    $conn->exec($sqlcontracts);
    echo "Table contracts created successfully.<br>";

    $conn->exec($sqllogs);
    echo "Table logs created successfully.<br>";
    
    $conn->exec($sqlusers);
    echo "Table users created successfully.<br>";

    $conn->exec($sqlloginlogs);
    echo "Table login logs created successfully.<br>";

    $conn->exec($sqlPendingChanges);
    echo "Table pending changes created successfully.<br>";

    $conn->exec($sqlProjects);
    echo "Table projects created successfully.<br>";

    $conn->exec($sqlProjectYears);
    echo "Table project_years created successfully.<br>";

    $conn->exec($sqlProjectPayments);
    echo "Table project_payments created successfully.<br>";

    $conn->exec($sqlUsedYears);
    echo "Table used_years created successfully.<br>";
    

} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}

// Close connection
$conn = null;