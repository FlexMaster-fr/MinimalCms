<?php
/**
 * Home Controller
 * 
 * Handles main public pages
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Repository;
use App\Models\User;
use App\Models\Organization;

class HomeController extends Controller
{
    /**
     * Home page
     * 
     * @return void
     */
    public function index(): void
    {
        // Get popular repositories
        $popularRepos = Repository::findAll(
            [], 
            ['stargazers_count' => 'DESC'], 
            6
        );
        
        // Get recent repositories
        $recentRepos = Repository::findAll(
            [], 
            ['last_crawled_at' => 'DESC'], 
            6
        );
        
        // Get statistics
        $stats = [
            'repositories' => Repository::count(),
            'users' => User::count(),
            'organizations' => Organization::count()
        ];
        
        // Render view
        $this->view->setData([
            'title' => 'GitHub Repository Crawler',
            'popularRepos' => $popularRepos,
            'recentRepos' => $recentRepos,
            'stats' => $stats
        ])->render('home/index');
    }
    
    /**
     * Search page
     * 
     * @return void
     */
    public function search(): void
    {
        // Get query parameters
        $query = $_GET['q'] ?? '';
        $language = $_GET['language'] ?? '';
        $license = $_GET['license'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $where = [];
        $params = [];
        
        // Build search query
        if (!empty($query)) {
            $where[] = "(name LIKE :query OR description LIKE :query)";
            $params['query'] = "%$query%";
        }
        
        if (!empty($language)) {
            $where[] = "language_id = (SELECT id FROM languages WHERE name = :language)";
            $params['language'] = $language;
        }
        
        if (!empty($license)) {
            $where[] = "license_id = (SELECT id FROM licenses WHERE license_key = :license)";
            $params['license'] = $license;
        }
        
        // Get total count
        $totalQuery = "SELECT COUNT(*) as count FROM repositories";
        if (!empty($where)) {
            $totalQuery .= " WHERE " . implode(' AND ', $where);
        }
        $totalResult = Database::fetchOne($totalQuery, $params);
        $total = (int)($totalResult['count'] ?? 0);
        
        // Get repositories
        $reposQuery = "SELECT r.*, l.name as language_name, l.color as language_color, 
                        lic.name as license_name, lic.license_key as license_key
                      FROM repositories r
                      LEFT JOIN languages l ON r.language_id = l.id
                      LEFT JOIN licenses lic ON r.license_id = lic.id";
        
        if (!empty($where)) {
            $reposQuery .= " WHERE " . implode(' AND ', $where);
        }
        
        $reposQuery .= " ORDER BY stargazers_count DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        $repositories = Database::fetchAll($reposQuery, $params);
        
        // Calculate pagination
        $totalPages = max(1, ceil($total / $limit));
        $pagination = [
            'current' => $page,
            'total' => $totalPages,
            'prev' => $page > 1 ? $page - 1 : null,
            'next' => $page < $totalPages ? $page + 1 : null,
            'start' => ($page - 1) * $limit + 1,
            'end' => min($page * $limit, $total),
            'total_results' => $total
        ];
        
        // Get available languages and licenses for filters
        $languages = Database::fetchAll("SELECT id, name, color FROM languages ORDER BY name");
        $licenses = Database::fetchAll("SELECT id, license_key, name FROM licenses ORDER BY name");
        
        // Render view
        $this->view->setData([
            'title' => 'Search Results',
            'query' => $query,
            'language' => $language,
            'license' => $license,
            'repositories' => $repositories,
            'pagination' => $pagination,
            'languages' => $languages,
            'licenses' => $licenses
        ])->render('home/search');
    }
    
    /**
     * Recent repositories page
     * 
     * @return void
     */
    public function recent(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get repositories
        $query = "SELECT r.*, l.name as language_name, l.color as language_color, 
                  lic.name as license_name, lic.license_key as license_key
                FROM repositories r
                LEFT JOIN languages l ON r.language_id = l.id
                LEFT JOIN licenses lic ON r.license_id = lic.id
                ORDER BY r.last_crawled_at DESC
                LIMIT :limit OFFSET :offset";
        
        $repositories = Database::fetchAll($query, [
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        // Get total count
        $totalResult = Database::fetchOne("SELECT COUNT(*) as count FROM repositories");
        $total = (int)($totalResult['count'] ?? 0);
        
        // Calculate pagination
        $totalPages = max(1, ceil($total / $limit));
        $pagination = [
            'current' => $page,
            'total' => $totalPages,
            'prev' => $page > 1 ? $page - 1 : null,
            'next' => $page < $totalPages ? $page + 1 : null,
            'start' => ($page - 1) * $limit + 1,
            'end' => min($page * $limit, $total),
            'total_results' => $total
        ];
        
        // Render view
        $this->view->setData([
            'title' => 'Recent Repositories',
            'repositories' => $repositories,
            'pagination' => $pagination
        ])->render('home/recent');
    }
    
    /**
     * Popular repositories page
     * 
     * @return void
     */
    public function popular(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get repositories
        $query = "SELECT r.*, l.name as language_name, l.color as language_color, 
                  lic.name as license_name, lic.license_key as license_key
                FROM repositories r
                LEFT JOIN languages l ON r.language_id = l.id
                LEFT JOIN licenses lic ON r.license_id = lic.id
                ORDER BY r.stargazers_count DESC
                LIMIT :limit OFFSET :offset";
        
        $repositories = Database::fetchAll($query, [
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        // Get total count
        $totalResult = Database::fetchOne("SELECT COUNT(*) as count FROM repositories");
        $total = (int)($totalResult['count'] ?? 0);
        
        // Calculate pagination
        $totalPages = max(1, ceil($total / $limit));
        $pagination = [
            'current' => $page,
            'total' => $totalPages,
            'prev' => $page > 1 ? $page - 1 : null,
            'next' => $page < $totalPages ? $page + 1 : null,
            'start' => ($page - 1) * $limit + 1,
            'end' => min($page * $limit, $total),
            'total_results' => $total
        ];
        
        // Render view
        $this->view->setData([
            'title' => 'Popular Repositories',
            'repositories' => $repositories,
            'pagination' => $pagination
        ])->render('home/popular');
    }
    
    /**
     * Repositories by language page
     * 
     * @return void
     */
    public function byLanguage(): void
    {
        $language = $this->getParam('language');
        
        if (empty($language)) {
            $this->redirect('/');
            return;
        }
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get language info
        $languageInfo = Database::fetchOne(
            "SELECT * FROM languages WHERE name = :name",
            ['name' => $language]
        );
        
        if ($languageInfo === false) {
            $this->redirect('/');
            return;
        }
        
        // Get repositories
        $query = "SELECT r.*, l.name as language_name, l.color as language_color, 
                  lic.name as license_name, lic.license_key as license_key
                FROM repositories r
                JOIN languages l ON r.language_id = l.id
                LEFT JOIN licenses lic ON r.license_id = lic.id
                WHERE l.name = :language
                ORDER BY r.stargazers_count DESC
                LIMIT :limit OFFSET :offset";
        
        $repositories = Database::fetchAll($query, [
            'language' => $language,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        // Get total count
        $totalResult = Database::fetchOne(
            "SELECT COUNT(*) as count FROM repositories r 
             JOIN languages l ON r.language_id = l.id
             WHERE l.name = :language",
            ['language' => $language]
        );
        $total = (int)($totalResult['count'] ?? 0);
        
        // Calculate pagination
        $totalPages = max(1, ceil($total / $limit));
        $pagination = [
            'current' => $page,
            'total' => $totalPages,
            'prev' => $page > 1 ? $page - 1 : null,
            'next' => $page < $totalPages ? $page + 1 : null,
            'start' => ($page - 1) * $limit + 1,
            'end' => min($page * $limit, $total),
            'total_results' => $total
        ];
        
        // Render view
        $this->view->setData([
            'title' => "Repositories in $language",
            'language' => $languageInfo,
            'repositories' => $repositories,
            'pagination' => $pagination
        ])->render('home/by_language');
    }
    
    /**
     * Repositories by license page
     * 
     * @return void
     */
    public function byLicense(): void
    {
        $license = $this->getParam('license');
        
        if (empty($license)) {
            $this->redirect('/');
            return;
        }
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get license info
        $licenseInfo = Database::fetchOne(
            "SELECT * FROM licenses WHERE license_key = :key",
            ['key' => $license]
        );
        
        if ($licenseInfo === false) {
            $this->redirect('/');
            return;
        }
        
        // Get repositories
        $query = "SELECT r.*, l.name as language_name, l.color as language_color, 
                  lic.name as license_name, lic.license_key as license_key
                FROM repositories r
                LEFT JOIN languages l ON r.language_id = l.id
                JOIN licenses lic ON r.license_id = lic.id
                WHERE lic.license_key = :license
                ORDER BY r.stargazers_count DESC
                LIMIT :limit OFFSET :offset";
        
        $repositories = Database::fetchAll($query, [
            'license' => $license,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        // Get total count
        $totalResult = Database::fetchOne(
            "SELECT COUNT(*) as count FROM repositories r 
             JOIN licenses lic ON r.license_id = lic.id
             WHERE lic.license_key = :license",
            ['license' => $license]
        );
        $total = (int)($totalResult['count'] ?? 0);
        
        // Calculate pagination
        $totalPages = max(1, ceil($total / $limit));
        $pagination = [
            'current' => $page,
            'total' => $totalPages,
            'prev' => $page > 1 ? $page - 1 : null,
            'next' => $page < $totalPages ? $page + 1 : null,
            'start' => ($page - 1) * $limit + 1,
            'end' => min($page * $limit, $total),
            'total_results' => $total
        ];
        
        // Render view
        $this->view->setData([
            'title' => "Repositories with {$licenseInfo['name']} License",
            'license' => $licenseInfo,
            'repositories' => $repositories,
            'pagination' => $pagination
        ])->render('home/by_license');
    }
}