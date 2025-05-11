<?php
/**
 * Configuration Manager
 * 
 * Responsible for loading and providing access to configuration values
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

class Config
{
    /**
     * @var array Loaded configuration values
     */
    private static array $config = [];
    
    /**
     * Load configuration from the config file
     * 
     * @return self Returns an instance of Config for method chaining
     */
    public static function load(): self
    {
        $configFile = dirname(__DIR__) . '/config/config.php';
        
        if (file_exists($configFile)) {
            self::$config = require $configFile;
        } else {
            error_log('Configuration file not found: ' . $configFile);
        }
        
        return new self();
    }
    
    /**
     * Get a configuration value
     * 
     * @param string $key Configuration key (section.key or key)
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Configuration value or default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        // If key contains a dot, it references a section
        if (strpos($key, '.') !== false) {
            list($section, $sectionKey) = explode('.', $key, 2);
            
            if (isset(self::$config[$section]) && isset(self::$config[$section][$sectionKey])) {
                return self::$config[$section][$sectionKey];
            }
        } elseif (isset(self::$config[$key])) {
            return self::$config[$key];
        }
        
        return $default;
    }
    
    /**
     * Check if configuration has been loaded
     * 
     * @return bool True if configuration is loaded
     */
    public static function isLoaded(): bool
    {
        return !empty(self::$config);
    }
    
    /**
     * Get database connection configuration
     * 
     * @return array Database configuration
     */
    public static function getDatabaseConfig(): array
    {
        $dbType = self::get('database.type', 'sqlite');
        
        if ($dbType === 'mysql') {
            return [
                'type' => 'mysql',
                'host' => self::get('database.mysql.host', 'localhost'),
                'name' => self::get('database.mysql.database', 'github_crawler'),
                'user' => self::get('database.mysql.username', 'root'),
                'pass' => self::get('database.mysql.password', ''),
                'port' => self::get('database.mysql.port', 3306),
            ];
        } else {
            return [
                'type' => 'sqlite',
                'path' => self::get('database.sqlite.path', dirname(__DIR__) . '/data/database.sqlite'),
            ];
        }
    }
    
    /**
     * Get GitHub API configuration
     * 
     * @return array GitHub API configuration
     */
    public static function getGitHubConfig(): array
    {
        return [
            'token' => self::get('github.token', ''),
            'api_version' => self::get('github.api_version', '2022-11-28'),
            'user_agent' => self::get('github.user_agent', 'GitHub-Crawler-Bot'),
        ];
    }
}
