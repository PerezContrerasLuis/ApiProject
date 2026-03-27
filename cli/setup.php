<?php

// Load environment variables
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
        }
    }
}

// Database configuration
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$database = getenv('DB_DATABASE');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

echo "\n🔧 ApiProject Setup\n";
echo "═══════════════════════════════════════════\n\n";

// Connect to MySQL with PDO
echo "📡 Conectando a MySQL...\n";

try {
    $dsn = "mysql:host={$host}:{$port};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Conexión exitosa\n\n";
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

// Run migrations
echo "🚀 Ejecutando migraciones...\n";
$migrationsDir = dirname(__DIR__) . '/database/migrations';
$migrations = glob($migrationsDir . '/*.sql');
sort($migrations);

if (empty($migrations)) {
    echo "⚠️  No hay migraciones encontradas\n";
} else {
    foreach ($migrations as $migration) {
        $filename = basename($migration);
        $sql = file_get_contents($migration);

        try {
            $pdo->exec($sql);
            echo "  ✅ $filename\n";
        } catch (PDOException $e) {
            echo "  ❌ $filename - Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

echo "\n";

// Run seeders
echo "🌱 Ejecutando seeders...\n";
$seedersDir = dirname(__DIR__) . '/database/seeders';
$seeders = glob($seedersDir . '/*.sql');
sort($seeders);

if (empty($seeders)) {
    echo "⚠️  No hay seeders encontrados\n";
} else {
    foreach ($seeders as $seeder) {
        $filename = basename($seeder);
        $sql = file_get_contents($seeder);

        try {
            $pdo->exec($sql);
            echo "  ✅ $filename\n";
        } catch (PDOException $e) {
            echo "  ❌ $filename - Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

echo "\n";
echo "═══════════════════════════════════════════\n";
echo "✨ Setup completado correctamente\n\n";

$pdo = null;
