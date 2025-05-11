<?php
/**
 * Crawler Controller
 * 
 * Handles crawler functions via web interface
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace App\Controllers;

use Core\Controller;
use Core\UserManager;
use App\Models\CrawlerSchedule;
use App\Models\Log;

class CrawlerController extends Controller
{
    /**
     * Get crawler status
     * 
     * @return void
     */
    public function status(): void
    {
        // Require admin login
        UserManager::requireLogin();
        
        // Get crawler ID from parameters
        $id = $this->getParam('id');
        
        if (empty($id)) {
            $this->json([
                'status' => 'error',
                'message' => 'Crawler ID is required'
            ], 400);
            return;
        }
        
        // Get crawler schedule
        $crawlerModel = new CrawlerSchedule();
        $crawler = $crawlerModel->getById($id);
        
        if (!$crawler) {
            $this->json([
                'status' => 'error',
                'message' => 'Crawler not found'
            ], 404);
            return;
        }
        
        // Get crawler logs
        $logModel = new Log();
        $logs = $logModel->getByReference('crawler_' . $id, 50);
        
        $this->json([
            'status' => 'success',
            'crawler' => $crawler,
            'logs' => $logs
        ]);
    }
    
    /**
     * Stop crawler
     * 
     * @return void
     */
    public function stop(): void
    {
        // Require admin login
        UserManager::requireLogin();
        
        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($token)) {
            $this->json([
                'status' => 'error',
                'message' => 'Invalid CSRF token'
            ], 403);
            return;
        }
        
        // Get crawler ID from parameters
        $id = $this->getParam('id');
        
        if (empty($id)) {
            $this->json([
                'status' => 'error',
                'message' => 'Crawler ID is required'
            ], 400);
            return;
        }
        
        // Get crawler schedule
        $crawlerModel = new CrawlerSchedule();
        $crawler = $crawlerModel->getById($id);
        
        if (!$crawler) {
            $this->json([
                'status' => 'error',
                'message' => 'Crawler not found'
            ], 404);
            return;
        }
        
        // Update crawler status
        $crawlerModel->updateStatus($id, 'stopped');
        
        // Log crawler stop
        $logModel = new Log();
        $logModel->createLog('crawler', 'Crawler stopped', 'Crawler ID: ' . $id);
        
        $this->json([
            'status' => 'success',
            'message' => 'Crawler stopped successfully'
        ]);
    }
    
    /**
     * Get crawler logs
     * 
     * @return void
     */
    public function logs(): void
    {
        // Require admin login
        UserManager::requireLogin();
        
        // Get crawler ID from parameters
        $id = $this->getParam('id');
        
        // Get logs
        $logModel = new Log();
        
        if (empty($id)) {
            // Get all crawler logs
            $logs = $logModel->getByType('crawler', 100);
        } else {
            // Get logs for specific crawler
            $logs = $logModel->getByReference('crawler_' . $id, 100);
        }
        
        $this->json([
            'status' => 'success',
            'logs' => $logs
        ]);
    }
    
    /**
     * Manage crawler schedules
     * 
     * @return void
     */
    public function schedules(): void
    {
        // Require admin login
        UserManager::requireLogin();
        
        // Get schedules
        $crawlerModel = new CrawlerSchedule();
        $schedules = $crawlerModel->getAll();
        
        $this->view->set('schedules', $schedules);
        $this->view->set('csrf_token', $this->generateCsrfToken());
        $this->view->render('admin/crawler/schedules');
    }
    
    /**
     * Create crawler schedule
     * 
     * @return void
     */
    public function createSchedule(): void
    {
        // Require admin login
        UserManager::requireLogin();
        
        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verifyCsrfToken($token)) {
            $this->json([
                'status' => 'error',
                'message' => 'Invalid CSRF token'
            ], 403);
            return;
        }
        
        // Get schedule type
        $type = $_POST['type'] ?? 'auto';
        
        // Create schedule
        $crawlerModel = new CrawlerSchedule();
        $id = $crawlerModel->schedule($type);
        
        // Log schedule creation
        $logModel = new Log();
        $logModel->createLog('crawler', 'Schedule created', 'Schedule ID: ' . $id . ', Type: ' . $type);
        
        $this->json([
            'status' => 'success',
            'message' => 'Schedule created successfully',
            'id' => $id
        ]);
    }
}
