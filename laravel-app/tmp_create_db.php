<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$host = env('DB_HOST', '127.0.0.1');
$port = env('DB_PORT', '5432');
$user = env('DB_USERNAME', 'postgres');
$pass = env('DB_PASSWORD', 'postgres');
$dbName = env('DB_DATABASE', 'laravel');

try {
    // Connect to the default 'postgres' database
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=postgres", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the database exists
    $stmt = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '$dbName'");
    if (!$stmt->fetch()) {
        $pdo->exec("CREATE DATABASE \"$dbName\"");
        echo "Base de datos '$dbName' creada exitosamente.\n";
    } else {
        echo "La base de datos '$dbName' ya existe.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
