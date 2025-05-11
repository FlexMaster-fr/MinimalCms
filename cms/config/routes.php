<?php
/**
 * Route Definitions
 * 
 * Define all application routes here
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

use Core\Router;
use App\Controllers\HomeController;
use App\Controllers\RepositoryController;
use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\CrawlerController;

//----------------
// Public Routes
//----------------

// Home routes
Router::get('/', [HomeController::class, 'index'], 'home.index');
Router::get('/search', [HomeController::class, 'search'], 'home.search');
Router::get('/repositories/recent', [HomeController::class, 'recent'], 'home.recent');
Router::get('/repositories/popular', [HomeController::class, 'popular'], 'home.popular');
Router::get('/repositories/language/{language}', [HomeController::class, 'byLanguage'], 'home.by_language');
Router::get('/repositories/license/{license}', [HomeController::class, 'byLicense'], 'home.by_license');

// Repository routes
Router::get('/repo/{owner}/{repo}', [RepositoryController::class, 'detail'], 'repository.detail');
Router::get('/org/{org}', [RepositoryController::class, 'organization'], 'organization.detail');
Router::get('/user/{username}', [RepositoryController::class, 'user'], 'user.detail');

// API routes
Router::get('/api/search', [ApiController::class, 'search'], 'api.search');
Router::get('/api/repository/{owner}/{repo}', [ApiController::class, 'getRepository'], 'api.repository');
Router::get('/api/repository/{owner}/{repo}/readme', [RepositoryController::class, 'readme'], 'api.repository.readme');
Router::get('/api/organization/{org}', [ApiController::class, 'getOrganization'], 'api.organization');
Router::get('/api/user/{username}', [ApiController::class, 'getUser'], 'api.user');
Router::get('/api/docs', [ApiController::class, 'docs'], 'api.docs');

//----------------
// Admin Routes
//----------------

// Admin authentication
Router::get('/admin/login', [AdminController::class, 'login'], 'admin.login');
Router::post('/admin/login', [AdminController::class, 'login']);
Router::get('/admin/logout', [AdminController::class, 'logout'], 'admin.logout');

// Admin dashboard
Router::get('/admin', [AdminController::class, 'dashboard'], 'admin.dashboard');

// Crawler management
Router::post('/admin/crawler/run', [AdminController::class, 'runCrawler'], 'admin.crawler.run');
Router::post('/admin/crawler/schedule', [AdminController::class, 'scheduleCrawler'], 'admin.crawler.schedule');
Router::get('/admin/crawler/logs', [AdminController::class, 'getLogs'], 'admin.crawler.logs');
Router::get('/admin/crawler/schedules', [CrawlerController::class, 'schedules'], 'admin.crawler.schedules');
Router::post('/admin/crawler/schedules/create', [CrawlerController::class, 'createSchedule'], 'admin.crawler.schedules.create');
Router::get('/admin/crawler/{id}/status', [CrawlerController::class, 'status'], 'admin.crawler.status');
Router::post('/admin/crawler/{id}/stop', [CrawlerController::class, 'stop'], 'admin.crawler.stop');
Router::get('/admin/crawler/{id}/logs', [CrawlerController::class, 'logs'], 'admin.crawler.id.logs');

// Database management
Router::get('/admin/database', [AdminController::class, 'database'], 'admin.database');
Router::get('/admin/database/table', [AdminController::class, 'getTableData'], 'admin.database.table');
