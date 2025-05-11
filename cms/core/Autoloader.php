<?php
/**
 * PSR-4 Autoloader Implementation
 * 
 * Responsible for autoloading classes based on PSR-4 standard
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

class Autoloader
{
    /**
     * Constructor - automatically registers the autoloader
     */
    public function __construct() 
    {
        spl_autoload_register([$this, 'loadClass']);
    }
    
    /**
     * Register the autoloader with SPL
     */
    public function register(): void
    {
        // This method is kept for backwards compatibility
        // Autoloader is now registered in the constructor
    }
    
    /**
     * Load a class based on PSR-4 standard
     * 
     * @param string $className Full class name including namespace
     * @return bool True if class was loaded, false otherwise
     */
    public function loadClass(string $className): bool
    {
        // Skip if class already exists
        if (class_exists($className, false)) {
            return true;
        }
        
        // Base directory
        $baseDir = dirname(__DIR__);
        
        // Namespace prefix mapping
        $prefixMap = [
            'Core\\' => $baseDir . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR,
            'App\\' => $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR,
            'App\\Controllers\\' => $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR,
            'App\\Models\\' => $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR,
            'App\\Views\\' => $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR,
        ];
        
        // Check each prefix against the class
        foreach ($prefixMap as $prefix => $dir) {
            // If the class has this prefix
            if (strpos($className, $prefix) === 0) {
                // Get the relative class name
                $relativeClass = substr($className, strlen($prefix));
                
                // Convert namespace separator to directory separator
                $relativeClass = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);
                
                // Build the file path
                $file = $dir . $relativeClass . '.php';
                
                // If file exists, load it
                if (file_exists($file)) {
                    require_once $file;
                    return true;
                }
            }
        }
        
        // Default handling for other namespaces
        // Convert namespace separator to directory separator
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        
        // Full path to the class file
        $classFile = $baseDir . DIRECTORY_SEPARATOR . $classPath . '.php';
        
        // Check if file exists and require it
        if (file_exists($classFile)) {
            require_once $classFile;
            return true;
        }
        
        return false;
    }
}
