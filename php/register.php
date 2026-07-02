<?php
// Include the Composer autoloader
require '../vendor/autoload.php';

// Ensure the response is always treated as JSON by the frontend AJAX
header('Content-Type: application/json');

// Retrieve and sanitize POST data
$email = $_POST['email'] ?? '';
$passwordInput = $_POST['password'] ?? '';

// Structural security: Reject empty payloads
if (empty($email) || empty($passwordInput)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit;
}

// Securely hash the password
$password = password_hash($passwordInput, PASSWORD_DEFAULT);

try {
    // Database connection using Environment Variables for Heroku/AWS hosting
    // It falls back to local credentials for your local XAMPP development
    // Ensure these exactly match your local XAMPP setup
    $dbHost = getenv('DB_HOST') ?: '127.0.0.1'; // Use 127.0.0.1 instead of localhost for XAMPP stability
    $dbName = getenv('DB_NAME') ?: 'guvi_task'; 
    $dbUser = getenv('DB_USER') ?: 'root';      // XAMPP default is 'root'
    $dbPass = getenv('DB_PASS') ?: '';          // XAMPP default password is blank

    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    
    // Enforce strict error handling on the PDO connection
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Uniqueness check using Prepared Statement
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    // If a record is found, return the exact requested error message
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'email already exists']);
        exit;
    }

    // 2. Insert new user using Prepared Statement
    $insertStmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $insertStmt->execute([$email, $password]);

    // Registration successful
    echo json_encode(['status' => 'success']);

} catch (PDOException $e) {
    // Catch database errors securely without exposing sensitive database architecture to the browser
    // Note: In a production environment, you would log $e->getMessage() to a server file here
    echo json_encode(['status' => 'error', 'message' => 'Database connection or execution failed.']);
}
?>