<?php
// config.php
if (session_status() === PHP_SESSION_NONE) session_start();

$host = 'localhost';
$user = 'root';
$pass = ''; 
$dbname = 'sns_solution_db';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");

    // 1. Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50), password VARCHAR(255)
    )");

    // 2. Clients
    $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
        client_id INT AUTO_INCREMENT PRIMARY KEY,
        client_name VARCHAR(150), address TEXT, 
        contact_number VARCHAR(20), alt_contact_number VARCHAR(20),
        client_origin ENUM('INHOUSE', 'OUTHOUSE') DEFAULT 'INHOUSE',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Projects
    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        project_id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT,
        project_name VARCHAR(200), project_type VARCHAR(50),
        amc_base_amount DECIMAL(10, 2) DEFAULT 0.00,
        next_renewal_date DATE,
        manager_name VARCHAR(100), manager_contact_no VARCHAR(20),
        tech_name VARCHAR(100), current_version VARCHAR(20) DEFAULT '1.0',
        FOREIGN KEY (client_id) REFERENCES clients(client_id)
    )");        

    // 4. SMM Services
    $pdo->exec("CREATE TABLE IF NOT EXISTS smm_services (
        smm_id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT,
        base_charge DECIMAL(10, 2) DEFAULT 0.00,
        ad_description TEXT, 
        next_renewal_date DATE,
        FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE
    )");

    // 5. Invoices (Updated with service_type and billing_period)
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoices(
         invoice_id INT AUTO_INCREMENT PRIMARY KEY, 
         invoice_number VARCHAR(50), client_id INT, 
         invoice_date DATE, total_amount DECIMAL(10,2),
         service_type VARCHAR(10),
         billing_period VARCHAR(20))"); // Added billing_period column

    // 6. Invoice Items
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoice_items (
         item_id INT AUTO_INCREMENT PRIMARY KEY, 
         invoice_id INT, description VARCHAR(255), 
         amount DECIMAL(10,2), qty INT DEFAULT 1)");

    // 7. Receipts
    $pdo->exec("CREATE TABLE IF NOT EXISTS receipts (
        receipt_id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT,
        receipt_number VARCHAR(50),
        receipt_date DATE,
        amount_paid DECIMAL(10,2),
        payment_mode VARCHAR(50),
        transaction_ref VARCHAR(100),
        receipt_file VARCHAR(255),
        FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE
    )");

    // 8. Reminders
    $pdo->exec("CREATE TABLE IF NOT EXISTS reminders (
        reminder_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        remark TEXT,
        reminder_date DATE NOT NULL,
        reminder_type ENUM('ONETIME', 'WEEKLY', 'MONTHLY', 'YEARLY') DEFAULT 'ONETIME',
        status ENUM('PENDING', 'COMPLETED') DEFAULT 'PENDING',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // AUTO-MIGRATION (Updates existing tables if columns are missing)
    $updates = [
        'clients' => ['alt_contact_number' => 'VARCHAR(20)'],
        'projects' => ['manager_contact_no' => 'VARCHAR(20)', 'tech_name' => 'VARCHAR(100)', 'current_version' => 'VARCHAR(20)'],
        'invoice_items' => ['qty' => 'INT DEFAULT 1'],
        'receipts' => ['receipt_file' => 'VARCHAR(255)'],
        'invoices' => [
            'service_type' => 'VARCHAR(10)', 
            'billing_period' => 'VARCHAR(20)' // New migration entry for billing_period
        ]
    ];
    foreach($updates as $tbl => $cols) {
        foreach($cols as $col => $def) {
            $check = $pdo->query("SHOW COLUMNS FROM $tbl LIKE '$col'");
            if($check->rowCount() == 0) $pdo->exec("ALTER TABLE $tbl ADD COLUMN $col $def");
        }
    }

    // 9. Service Types
    $pdo->exec("CREATE TABLE IF NOT EXISTS service_type_tbl (id INT AUTO_INCREMENT PRIMARY KEY, service_name VARCHAR(100) NOT NULL)");
    if ($pdo->query("SELECT COUNT(*) FROM service_type_tbl")->fetchColumn() == 0) {
        $default_services = ['Website', 'Mobile App', 'Desktop App', 'Web App', 'Digital Marketing'];
        $insert_svc = $pdo->prepare("INSERT INTO service_type_tbl (service_name) VALUES (?)");
        foreach ($default_services as $svc) $insert_svc->execute([$svc]);
    }
    
    // Default Admin
    if($pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO users (username, password) VALUES (?,?)")->execute(['admin', password_hash('password', PASSWORD_DEFAULT)]);
    }

    // Create a View for easier Client Data access
    $pdo->exec("CREATE OR REPLACE VIEW client_view AS 
        SELECT c.*, 
        (SELECT COUNT(*) FROM projects p WHERE p.client_id = c.client_id) as no_of_project,
        (SELECT COUNT(*) FROM smm_services s WHERE s.client_id = c.client_id) as no_of_sm
        FROM clients c ORDER BY c.client_id DESC");

} catch (PDOException $e) { die("DB Error: " . $e->getMessage()); }
?>