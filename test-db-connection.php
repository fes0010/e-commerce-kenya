<?php

echo "Testing database connection...\n\n";

// Load environment
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

echo "Configuration:\n";
echo "  Host: $host\n";
echo "  Port: $port\n";
echo "  Database: $database\n";
echo "  Username: $username\n\n";

// Test 1: Socket connection
echo "Test 1: Testing socket connection to $host:$port...\n";
$socket = @fsockopen($host, $port, $errno, $errstr, 5);
if ($socket) {
    echo "  ✓ Socket connection successful\n";
    fclose($socket);
} else {
    echo "  ✗ Socket connection failed: $errstr ($errno)\n";
    exit(1);
}

// Test 2: PDO connection
echo "\nTest 2: Testing PDO MySQL connection...\n";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "  ✓ PDO connection successful\n";
    
    // Test query
    $stmt = $pdo->query('SELECT VERSION() as version');
    $result = $stmt->fetch();
    echo "  ✓ MySQL version: " . $result['version'] . "\n";
    
    // Test database
    $stmt = $pdo->query('SELECT DATABASE() as db');
    $result = $stmt->fetch();
    echo "  ✓ Current database: " . $result['db'] . "\n";
    
    echo "\n✓ All tests passed! Database connection is working.\n";
    
} catch (PDOException $e) {
    echo "  ✗ PDO connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
