<?php
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeload();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $dbHost = $_ENV['DB_HOST'] ?? '';
        $dbName = $_ENV['DB_NAME'] ?? ''; 
        $dbUser = $_ENV['DB_USER'] ?? '';
        $dbPass = $_ENV['DB_PASS'] ?? '';
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Find user by email
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        // REQUIREMENT: User is not registered yet
        if (!$userRecord) {
            echo json_encode(['status' => 'error', 'message' => 'User not registered, please register']);
            exit;
        }

        // REQUIREMENT: Password incorrect
        if (!password_verify($password, $userRecord['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'incorrect password']);
            exit;
        }

        // Secure Redis Session Generation
        $redis = new Predis\Client([
          'scheme' => 'tcp',
          'host'   => $_ENV['REDIS_HOST'] ?? '',
          'port'   => $_ENV['REDIS_PORT'] ?? '',
        ]);
        
        $sessionToken = bin2hex(random_bytes(32));
        $redis->setex("session:" . $sessionToken, 3600, $userRecord['id']);

        echo json_encode(['status' => 'success', 'message' => 'Login successful', 'token' => $sessionToken]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
    }
}
?>