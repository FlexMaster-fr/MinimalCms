<?php
/**
 * Front Controller
 * 
 * This is the entry point for the application
 * All requests are routed through here
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

// Enable error reporting in development mode
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load the autoloader
require_once BASE_PATH . '/core/Autoloader.php';

// Initialize autoloader - this automatically registers it
$autoloader = new Core\Autoloader();

try {
    // Start Session
    session_start();

    // Load configuration
    $config = Core\Config::load();

    // Initialize the router
    $router = new Core\Router();

    // Register routes
    require_once BASE_PATH . '/config/routes.php';

    // Initialize language manager if class exists
    if (class_exists('Core\LanguageManager')) {
        // Check if language is set in URL or session
        $language = $_GET['lang'] ?? $_SESSION['language'] ?? $config->get('app.locale', 'en');
        
        // Validate language
        if (in_array($language, ['en', 'fr'])) {
            // Store in session
            $_SESSION['language'] = $language;
        } else {
            $language = 'en'; // Default to English if not valid
        }
        
        $languageManager = new Core\LanguageManager($language);
    }

    // Initialize theme manager if class exists
    if (class_exists('Core\ThemeManager')) {
        $themeManager = new Core\ThemeManager($config->get('app.theme', 'default'));
    }

    // Initialize hook manager if class exists
    if (class_exists('Core\HookManager')) {
        $hookManager = new Core\HookManager();
    }

    // Dispatch the request
    $router->dispatch();
    
} catch (Exception $e) {
    // Log error
    error_log($e->getMessage());
    
    // Display error page
    header("HTTP/1.1 500 Internal Server Error");
    echo "<h1>Application Error</h1>";
    echo "<p>The application encountered an error. Please try again later.</p>";
    
    // Show error details in development mode
    if (isset($config) && $config->get('app.debug', 0) == 1) {
        echo "<h2>Error Details:</h2>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    exit;
}