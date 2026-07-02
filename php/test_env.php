<?php
require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

header('Content-Type: text/plain');

echo "DB_USER: " . var_export($_ENV['DB_USER'] ?? 'NOT SET', true) . "\n";
echo "DB_HOST: " . var_export($_ENV['DB_HOST'] ?? 'NOT SET', true) . "\n";
echo "MONGO_URI set: " . (isset($_ENV['MONGO_URI']) ? 'YES' : 'NO') . "\n";
echo "REDIS_HOST: " . var_export($_ENV['REDIS_HOST'] ?? 'NOT SET', true) . "\n";
echo "REDIS_PORT: " . var_export($_ENV['REDIS_PORT'] ?? 'NOT SET', true) . "\n";