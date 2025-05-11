<?php
/**
 * Base Controller Class
 * 
 * Abstract class that all controllers extend from
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

abstract class Controller
{
    /**
     * @var View View instance
     */
    protected View $view;
    
    /**
     * @var array Request parameters
     */
    protected array $params = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->view = new View();
    }
    
    /**
     * Set request parameters
     * 
     * @param array $params Parameters from the router
     * @return void
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
    
    /**
     * Get a request parameter
     * 
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed Parameter value or default
     */
    protected function getParam(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }
    
    /**
     * Redirect to a different URL
     * 
     * @param string $url URL to redirect to
     * @return void
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Return JSON response
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool True if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Generate CSRF token and store in session
     * 
     * @return string CSRF token
     */
    protected function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @return bool True if token is valid
     */
    protected function verifyCsrfToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        $valid = hash_equals($_SESSION['csrf_token'], $token);
        
        // Use once and regenerate
        unset($_SESSION['csrf_token']);
        
        return $valid;
    }
}
