<?php
/**
 * User Manager
 * 
 * Handles user authentication and session management
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

class UserManager
{
    /**
     * @var array|null Current admin user data
     */
    private static ?array $currentAdmin = null;
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['admin_user']);
    }
    
    /**
     * Get current admin user data
     * 
     * @return array|null Admin user data or null if not logged in
     */
    public static function getCurrentAdmin(): ?array
    {
        if (self::$currentAdmin === null) {
            if (isset($_SESSION['admin_user'])) {
                self::$currentAdmin = $_SESSION['admin_user'];
            }
        }
        
        return self::$currentAdmin;
    }
    
    /**
     * Authenticate admin user
     * 
     * @param string $username Username
     * @param string $password Password (plain text)
     * @return bool True if authentication successful
     */
    public static function authenticate(string $username, string $password): bool
    {
        // Get admin credentials from config
        $config = Config::load();
        $adminUsername = $config->get('admin.username');
        $adminPassword = $config->get('admin.password');
        
        // Verify credentials
        if ($username === $adminUsername && password_verify($password, $adminPassword)) {
            // Store user in session
            $_SESSION['admin_user'] = [
                'username' => $username,
                'logged_in_at' => time()
            ];
            
            // Store user in static property
            self::$currentAdmin = $_SESSION['admin_user'];
            
            // Log login event
            Database::insert('logs', [
                'type' => 'admin',
                'message' => 'Admin login successful',
                'reference' => 'admin_login_' . time(),
                'created_at' => time()
            ]);
            
            return true;
        }
        
        // Authentication failed
        return false;
    }
    
    /**
     * Logout admin user
     * 
     * @return void
     */
    public static function logout(): void
    {
        // Log logout event if user was logged in
        if (self::isLoggedIn()) {
            Database::insert('logs', [
                'type' => 'admin',
                'message' => 'Admin logout',
                'reference' => 'admin_logout_' . time(),
                'created_at' => time()
            ]);
        }
        
        // Remove user from session
        unset($_SESSION['admin_user']);
        
        // Reset static property
        self::$currentAdmin = null;
    }
    
    /**
     * Require admin login
     * 
     * Redirects to login page if not logged in
     * 
     * @param string|null $redirectTo URL to redirect to if not logged in
     * @return void
     */
    public static function requireLogin(?string $redirectTo = null): void
    {
        if (!self::isLoggedIn()) {
            $redirectUrl = $redirectTo ?? '/admin/login';
            
            // Use header redirect
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}