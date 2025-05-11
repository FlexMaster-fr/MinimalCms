<?php
/**
 * Configuration File
 * 
 * Main configuration settings for the application
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

return [
    // Application settings
    'app' => [
        'name' => 'GitHub Repository Crawler',
        'version' => '1.0.0',
        'url' => getenv('APP_URL') ?: 'http://localhost:5000',
        'environment' => getenv('APP_ENV') ?: 'development',
        'debug' => getenv('APP_DEBUG') ?: true,
        'timezone' => 'UTC',
        'locale' => getenv('APP_LOCALE') ?: 'en', // Default locale
        'available_locales' => ['en', 'fr'], // Available locales
    ],
    
    // Database settings
    'database' => [
        'type' => getenv('DB_TYPE') ?: 'sqlite', // sqlite or mysql
        'sqlite' => [
            'path' => getenv('DB_PATH') ?: dirname(__DIR__) . '/data/database.sqlite',
        ],
        'mysql' => [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: 3306,
            'database' => getenv('DB_NAME') ?: 'github_crawler',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
    
    // GitHub API settings
    'github' => [
        'api_url' => 'https://api.github.com',
        'client_id' => getenv('GITHUB_CLIENT_ID') ?: '',
        'client_secret' => getenv('GITHUB_CLIENT_SECRET') ?: '',
        'token' => getenv('GITHUB_TOKEN') ?: '',
        'user_agent' => 'GitHub-Crawler/1.0.0', // User agent for API requests
        'per_page' => 100, // Max items per page (max allowed by GitHub: 100)
        'rate_limit_wait' => true, // Wait when rate limit is reached
    ],
    
    // Crawler settings
    'crawler' => [
        'schedule_interval' => 172800, // 48 hours in seconds
        'max_parallel_processes' => 5, // Maximum parallel crawler processes
        'rate_limit_delay' => 2, // Delay between API requests to avoid rate limiting (in seconds)
        'backoff_strategy' => 'exponential', // 'linear' or 'exponential'
        'max_retries' => 3, // Maximum number of retries when API request fails
        'timeout' => 30, // Timeout for API requests (in seconds)
        'repositories_per_crawl' => 1000, // Maximum repositories to crawl per run
        'organizations_per_crawl' => 100, // Maximum organizations to crawl per run
        'users_per_crawl' => 200, // Maximum users to crawl per run
    ],
    
    // Admin settings
    'admin' => [
        'username' => getenv('ADMIN_USERNAME') ?: 'admin',
        'password' => getenv('ADMIN_PASSWORD') ?: '$2y$10$1VfKIm7iK9Nr2YIFDpfdNefnIOiqxagjSV/pjcjxUz2tyATVPOxqO', // Default: 'password'
        'session_timeout' => 3600, // Session timeout in seconds (1 hour)
    ],
    
    // Cache settings
    'cache' => [
        'enabled' => true,
        'driver' => 'file', // 'file', 'redis', or 'memcached'
        'path' => dirname(__DIR__) . '/data/cache', // Path for file cache
        'lifetime' => 3600, // Default cache lifetime in seconds (1 hour)
    ],
    
    // View settings
    'view' => [
        'path' => dirname(__DIR__) . '/app/Views',
        'cache' => dirname(__DIR__) . '/data/cache/views',
        'cache_enabled' => false, // Enable view caching in production
    ],
    
    // Logger settings
    'logger' => [
        'path' => dirname(__DIR__) . '/logs',
        'level' => 'debug', // debug, info, warning, error, critical
        'max_files' => 30, // Maximum number of log files to keep
    ],
    
    // Security settings
    'security' => [
        'csrf_token_lifetime' => 3600, // CSRF token lifetime in seconds (1 hour)
        'session_name' => 'github_crawler_session',
        'auth_token_lifetime' => 86400, // API auth token lifetime in seconds (24 hours)
    ],
];