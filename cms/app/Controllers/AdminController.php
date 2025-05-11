<?php
/**
 * Admin Controller
 * 
 * Handles admin panel functionality
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace App\Controllers;

use Core\Controller;
use Core\UserManager;
use Core\Database;
use Core\Config;
use App\Models\Log;
use App\Models\CrawlerSchedule;

class AdminController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // Set admin layout
        $this->view->setLayout('admin');
    }
    
    /**
     * Login page
     * 
     * @return void
     */
    public function login(): void
    {
        // Redirect if already logged in
        if (UserManager::isLoggedIn()) {
            $this->redirect('/admin');
            return;
        }
        
        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Verify CSRF token
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->verifyCsrfToken($token)) {
                $this->view->set('error', 'Invalid CSRF token. Please try again.');
                $this->view->set('csrf_token', $this->generateCsrfToken());
                $this->view->render('admin/login');
                return;
            }
            
            if (UserManager::authenticate($username, $password)) {
                // Log successful login
                $log = new Log();
                $log->createLog('admin', 'Admin login successful', 'User: ' . $username);
                
                $this->redirect('/admin');
            } else {
                // Log failed login attempt
                $log = new Log();
                $log->createLog('admin', 'Admin login failed', 'User: ' . $username);
                
                $this->view->set('error', 'Invalid username or password');
            }
        }
        
        $this->view->set('csrf_token', $this->generateCsrfToken());
        $this->view->render('admin/login');
    }
    
    /**
     * Logout action
     * 
     * @return void
     */
    public function logout(): void
    {
        UserManager::logout();
        $this->redirect('/admin/login');
    }
    
    /**
     * Admin dashboard
     * 
     * @return void
     */
    public function dashboard(): void
    {
        // Require admin login
        UserManager::requireLogin('/admin/login');
        
        // Get database stats
        $stats = [
            'repositories' => Database::fetchOne("SELECT COUNT(*) as count FROM repositories")['count'] ?? 0,
            'organizations' => Database::fetchOne("SELECT COUNT(*) as count FROM organizations")['count'] ?? 0,
            'users' => Database::fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
        ];
        
        // Get recent logs
        $logModel = new Log();
        $logs = $logModel->getRecent(10);
        
        // Get crawler schedule
        $crawler = new CrawlerSchedule();
        $schedule = $crawler->getCurrent();
        
        $this->view->setData([
            'stats' => $stats,
            'logs' => $logs,
            'schedule' => $schedule,
            'csrf_token' => $this->generateCsrfToken()
        ]);
        
        $this->view->render('admin/dashboard');
    }
    
    /**
     * Run crawler manually
     * 
     * @return void
     */
    public function runCrawler(): void
    {
        // Require admin login
        UserManager::requireLogin('/admin/login');
        
        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($token)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid CSRF token']);
            } else {
                $this->redirect('/admin');
            }
            return;
        }
        
        // Create a new crawler schedule
        $crawler = new CrawlerSchedule();
        $id = $crawler->schedule('manual');
        
        // Execute crawler in background
        $phpPath = PHP_BINARY;
        $crawlerPath = dirname(dirname(__DIR__)) . '/bin/crawler.php';
        $logPath = dirname(dirname(__DIR__)) . '/logs/crawler.log';
        
        // Execute crawler script
        $command = "{$phpPath} {$crawlerPath} {$id} >> {$logPath} 2>&1 &";
        exec($command);
        
        // Log crawler execution
        $log = new Log();
        $log->createLog('crawler', 'Manual crawler execution started', 'Schedule ID: ' . $id);
        
        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Crawler started', 'id' => $id]);
        } else {
            $this->redirect('/admin');
        }
    }
    
    /**
     * Get crawler logs
     * 
     * @return void
     */
    public function getLogs(): void
    {
        // Require admin login
        UserManager::requireLogin();
        
        // Get log type from request
        $type = $this->getParam('type', 'crawler');
        
        // Get logs
        $logModel = new Log();
        $logs = $logModel->getByType($type, 50);
        
        $this->json(['logs' => $logs]);
    }
    
    /**
     * Schedule crawler
     * 
     * @return void
     */
    public function scheduleCrawler(): void
    {
        // Require admin login
        UserManager::requireLogin();
        
        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($token)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid CSRF token']);
            } else {
                $this->redirect('/admin');
            }
            return;
        }
        
        // Get schedule type
        $type = $_POST['schedule_type'] ?? 'auto';
        
        // Create a new crawler schedule
        $crawler = new CrawlerSchedule();
        $id = $crawler->schedule($type);
        
        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Crawler scheduled', 'id' => $id]);
        } else {
            $this->redirect('/admin');
        }
    }
    
    /**
     * Database management
     * 
     * @return void
     */
    public function database(): void
    {
        // Require admin login
        UserManager::requireLogin();
        
        // Get table list
        $tables = [];
        
        // For SQLite
        if (Config::get('database.type', 'sqlite') === 'sqlite') {
            $result = Database::fetchAll("SELECT name FROM sqlite_master WHERE type='table'");
            foreach ($result as $row) {
                $tables[] = $row['name'];
            }
        } else {
            // For MySQL
            $result = Database::fetchAll("SHOW TABLES");
            foreach ($result as $row) {
                $tables[] = reset($row);
            }
        }
        
        $this->view->set('tables', $tables);
        $this->view->set('csrf_token', $this->generateCsrfToken());
        $this->view->render('admin/database');
    }
    
    /**
     * Get table data
     * 
     * @return void
     */
    public function getTableData(): void
    {
        // Require admin login
        UserManager::requireLogin();
        
        // Get table name
        $table = $_GET['table'] ?? '';
        
        // Validate table name (prevent SQL injection)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            $this->json(['error' => 'Invalid table name']);
            return;
        }
        
        // Get page and limit
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 50);
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Get total rows
        $countResult = Database::fetchOne("SELECT COUNT(*) as count FROM {$table}");
        $total = $countResult['count'] ?? 0;
        
        // Get table data
        $data = Database::fetchAll("SELECT * FROM {$table} LIMIT {$limit} OFFSET {$offset}");
        
        // Get column names
        $columns = [];
        if (!empty($data)) {
            $columns = array_keys($data[0]);
        }
        
        $this->json([
            'table' => $table,
            'columns' => $columns,
            'data' => $data,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit)
        ]);
    }
}
