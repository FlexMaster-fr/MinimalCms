<?php
/**
 * Router
 * 
 * Handles routing of HTTP requests to controllers
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

class Router
{
    /**
     * @var array Registered routes
     */
    private static array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];
    
    /**
     * @var array Named routes
     */
    private static array $namedRoutes = [];
    
    /**
     * @var string|null Current route name
     */
    private static ?string $currentRouteName = null;
    
    /**
     * Register a GET route
     * 
     * @param string $pattern URL pattern
     * @param array|callable $action Controller and method or callback
     * @param string|null $name Route name
     * @return void
     */
    public static function get(string $pattern, array|callable $action, ?string $name = null): void
    {
        self::addRoute('GET', $pattern, $action, $name);
    }
    
    /**
     * Register a POST route
     * 
     * @param string $pattern URL pattern
     * @param array|callable $action Controller and method or callback
     * @param string|null $name Route name
     * @return void
     */
    public static function post(string $pattern, array|callable $action, ?string $name = null): void
    {
        self::addRoute('POST', $pattern, $action, $name);
    }
    
    /**
     * Register a PUT route
     * 
     * @param string $pattern URL pattern
     * @param array|callable $action Controller and method or callback
     * @param string|null $name Route name
     * @return void
     */
    public static function put(string $pattern, array|callable $action, ?string $name = null): void
    {
        self::addRoute('PUT', $pattern, $action, $name);
    }
    
    /**
     * Register a DELETE route
     * 
     * @param string $pattern URL pattern
     * @param array|callable $action Controller and method or callback
     * @param string|null $name Route name
     * @return void
     */
    public static function delete(string $pattern, array|callable $action, ?string $name = null): void
    {
        self::addRoute('DELETE', $pattern, $action, $name);
    }
    
    /**
     * Load routes from file
     * 
     * @param string $file Routes file path
     * @return void
     */
    public static function loadRoutes(string $file): void
    {
        if (file_exists($file)) {
            require $file;
        }
    }
    
    /**
     * Add a route to the router
     * 
     * @param string $method HTTP method
     * @param string $pattern URL pattern
     * @param array|callable $action Controller and method or callback
     * @param string|null $name Route name
     * @return void
     */
    private static function addRoute(string $method, string $pattern, array|callable $action, ?string $name = null): void
    {
        // Convert pattern to regex
        $patternRegex = self::patternToRegex($pattern);
        
        // Add route to routes array
        self::$routes[$method][$patternRegex] = [
            'pattern' => $pattern,
            'action' => $action
        ];
        
        // Add to named routes if name is provided
        if ($name !== null) {
            self::$namedRoutes[$name] = $pattern;
        }
    }
    
    /**
     * Convert URL pattern to regex
     * 
     * @param string $pattern URL pattern
     * @return string Regex pattern
     */
    private static function patternToRegex(string $pattern): string
    {
        // Convert parameters like {id} to regex
        $pattern = preg_replace('/{([a-zA-Z0-9_]+)}/', '(?P<$1>[^/]+)', $pattern);
        
        // Add start and end markers
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Dispatch request to appropriate controller
     * 
     * @return void
     */
    public static function dispatch(): void
    {
        // Get request method and URI
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = self::getUri();
        
        // Support for PUT and DELETE methods via POST
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
            if (!in_array($method, ['PUT', 'DELETE'])) {
                $method = 'POST';
            }
        }
        
        // Check if method is supported
        if (!isset(self::$routes[$method])) {
            self::notFound();
            return;
        }
        
        // Find matching route
        foreach (self::$routes[$method] as $pattern => $route) {
            if (preg_match($pattern, $uri, $matches)) {
                // Store current route name
                self::$currentRouteName = array_search($route['pattern'], self::$namedRoutes) ?: null;
                
                // Extract parameters
                $params = array_filter($matches, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                
                // Execute route action
                self::executeAction($route['action'], $params);
                return;
            }
        }
        
        // No matching route found
        self::notFound();
    }
    
    /**
     * Execute the route action
     * 
     * @param array|callable $action Controller and method or callback
     * @param array $params Route parameters
     * @return void
     */
    private static function executeAction(array|callable $action, array $params): void
    {
        if (is_callable($action)) {
            // Action is a closure
            call_user_func_array($action, $params);
        } elseif (is_array($action) && count($action) === 2) {
            // Action is [Controller::class, 'method']
            $controller = $action[0];
            $method = $action[1];
            
            if (class_exists($controller)) {
                $instance = new $controller();
                
                if (method_exists($instance, $method)) {
                    // Pass parameters to controller
                    $instance->setParams($params);
                    
                    // Execute controller method
                    call_user_func([$instance, $method]);
                } else {
                    self::notFound();
                }
            } else {
                self::notFound();
            }
        } else {
            self::notFound();
        }
    }
    
    /**
     * Get the current URI
     * 
     * @return string Cleaned URI
     */
    private static function getUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove script name from URI if running from script
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $uri = str_replace($scriptName, '', $uri);
        }
        
        // Remove trailing slash except for root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        return $uri;
    }
    
    /**
     * Handle 404 Not Found
     * 
     * @return void
     */
    private static function notFound(): void
    {
        header("HTTP/1.0 404 Not Found");
        include dirname(__DIR__) . '/app/Views/errors/404.php';
        exit;
    }
    
    /**
     * Generate URL for named route
     * 
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string|null Generated URL or null if route not found
     */
    public static function url(string $name, array $params = []): ?string
    {
        if (!isset(self::$namedRoutes[$name])) {
            return null;
        }
        
        $url = self::$namedRoutes[$name];
        
        // Replace parameters in URL
        foreach ($params as $key => $value) {
            $url = str_replace("{{$key}}", $value, $url);
        }
        
        return $url;
    }
    
    /**
     * Get current route name
     * 
     * @return string|null Current route name or null
     */
    public static function getCurrentRouteName(): ?string
    {
        return self::$currentRouteName;
    }
    
    /**
     * Check if current route matches name
     * 
     * @param string|array $name Route name or array of names
     * @return bool True if current route matches
     */
    public static function isRoute(string|array $name): bool
    {
        if (is_array($name)) {
            return in_array(self::$currentRouteName, $name);
        }
        
        return self::$currentRouteName === $name;
    }
}
