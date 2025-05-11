<?php
/**
 * Database Initialization Script
 * 
 * Creates the SQLite database and initializes the tables
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load the autoloader
require_once BASE_PATH . '/core/Autoloader.php';

// Initialize autoloader
$autoloader = new Core\Autoloader();

// Load config
$config = Core\Config::load();

// Get database config
$dbConfig = $config->getDatabaseConfig();

// Create data directory if it doesn't exist
$dataDir = BASE_PATH . '/data';
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
    echo "Created data directory at {$dataDir}\n";
}

// Initialize database connection
Core\Database::init($dbConfig);
$db = Core\Database::getInstance();

echo "Database connection initialized\n";

// Load schema file
$schemaFile = BASE_PATH . '/config/schema.sql';
$schema = file_get_contents($schemaFile);

echo "Loaded schema from {$schemaFile}\n";

// Split schema into individual statements
$statements = array_filter(array_map('trim', explode(';', $schema)));

// Begin transaction
$db->beginTransaction();

try {
    // Execute each statement
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        $db->exec($statement);
        echo "Executed: " . substr($statement, 0, 50) . "...\n";
    }
    
    // Commit transaction
    $db->commit();
    echo "Database schema created successfully\n";
    
    // Insert a log entry
    Core\Database::insert('logs', [
        'type' => 'system',
        'message' => 'Database initialized',
        'details' => 'Schema created successfully',
        'reference' => 'init_' . time(),
        'created_at' => time()
    ]);
    
    echo "Created initialization log entry\n";
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollBack();
    echo "Error creating database schema: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Database initialization completed successfully\n";