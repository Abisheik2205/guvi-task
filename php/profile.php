<?php
// Include the Composer autoloader
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeload();
header('Content-Type: application/json');

try {
    // 1. Redis Session Check (Strictly NO PHP Session)
    $redis = new Predis\Client([
     'scheme' => 'tcp',
     'host'   => $_ENV['REDIS_HOST'] ?? '',
     'port'   => $_ENV['REDIS_PORT'] ?? '',

    ]);
    
    // Works for both GET (URL params) and POST (Form data)
    $token = $_REQUEST['token'] ?? ''; 
    $userId = $redis->get("session:" . $token);

    if (!$userId) {
        http_response_code(401); 
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    // 2. MySQL Connection (For Contact Number storage & validation)
    $dbHost = $_ENV['DB_HOST'] ?? '';
    $dbName = $_ENV['DB_NAME'] ?? ''; 
    $dbUser = $_ENV['DB_USER'] ?? '';
    $dbPass = $_ENV['DB_PASS'] ?? '';         // XAMPP default password is blank
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3. MongoDB Connection (Updated for MongoDB Atlas Cloud)
    // 3. MongoDB Connection (Production Ready)
    // We removed &tlsAllowInvalidCertificates=true
    // 3. MongoDB Connection (Secure Production Fix)
    $atlasUri = $_ENV['MONGO_URI'] ?? '';
    // Force the driver to use the specific certificate bundle you configured
    $mongo = new MongoDB\Client($atlasUri, [
    'tlsCAFile' => 'C:/xampp/php/cacert.pem'

    ]);
    
    $collection = $mongo->guvi_task->profiles;


    // --- HANDLE GET REQUEST (Loading the profile data when page opens) ---
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Fetch from Mongo
        $profile = $collection->findOne(['userId' => $userId]);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'name' => $profile['name'] ?? '',
                'age' => $profile['age'] ?? '',
                'dob' => $profile['dob'] ?? '',
                'contact' => $profile['contact'] ?? ''
            ]
        ]);
        exit;
    }


    // --- HANDLE POST REQUEST (Saving the profile data) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $age = $_POST['age'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $contact = $_POST['contact'] ?? '';

        $existingContact = $collection->findOne([
            'contact' => $contact,
            'userId' => ['$ne' => $userId] // Ensures it doesn't flag the user's own number
        ]);

        // Requirement: Store remaining profile details in MongoDB
        $collection->updateOne(
            ['userId' => $userId],
            ['$set' => [
                'name' => $name,
                'age' => $age,
                'dob' => $dob,
                'contact' => $contact,
                'timestamp' => time()
            ]],
            ['upsert' => true]
        );

        echo json_encode(['status' => 'success', 'message' => 'profile saved successfully']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
}
?>