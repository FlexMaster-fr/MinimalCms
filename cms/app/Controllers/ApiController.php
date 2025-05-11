<?php
/**
 * API Controller
 * 
 * Handles API endpoints
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace App\Controllers;

use Core\Controller;
use App\Models\Repository;
use App\Models\Organization;
use App\Models\User;

class ApiController extends Controller
{
    /**
     * Search repositories, organizations, and users
     * 
     * @return void
     */
    public function search(): void
    {
        // Get search query
        $query = $_GET['q'] ?? '';
        
        // Get filters
        $filters = [
            'language' => $_GET['language'] ?? '',
            'license' => $_GET['license'] ?? '',
            'location' => $_GET['location'] ?? '',
            'stars' => (int)($_GET['stars'] ?? 0),
            'forks' => (int)($_GET['forks'] ?? 0),
        ];
        
        // Get sort options
        $sort = $_GET['sort'] ?? 'relevance';
        $direction = strtoupper($_GET['direction'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        
        // Get pagination
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(10, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        // Validate and sanitize query
        $query = trim($query);
        
        if (empty($query)) {
            $this->json([
                'status' => 'error',
                'message' => 'Search query is required'
            ], 400);
            return;
        }
        
        // Initialize models
        $repoModel = new Repository();
        $orgModel = new Organization();
        $userModel = new User();
        
        // Search repositories
        $repositories = $repoModel->search($query, $filters, $sort, $direction, $limit, $offset);
        $totalRepos = $repoModel->countSearch($query, $filters);
        
        // Search organizations
        $organizations = $orgModel->search($query, $filters, $sort, $direction, $limit, $offset);
        $totalOrgs = $orgModel->countSearch($query, $filters);
        
        // Search users
        $users = $userModel->search($query, $filters, $sort, $direction, $limit, $offset);
        $totalUsers = $userModel->countSearch($query, $filters);
        
        // Get available filter options
        $languages = $repoModel->getAvailableLanguages();
        $licenses = $repoModel->getAvailableLicenses();
        $locations = array_merge(
            $orgModel->getAvailableLocations(),
            $userModel->getAvailableLocations()
        );
        
        // Return results
        $this->json([
            'status' => 'success',
            'query' => $query,
            'repositories' => [
                'items' => $repositories,
                'total' => $totalRepos,
                'pages' => ceil($totalRepos / $limit)
            ],
            'organizations' => [
                'items' => $organizations,
                'total' => $totalOrgs,
                'pages' => ceil($totalOrgs / $limit)
            ],
            'users' => [
                'items' => $users,
                'total' => $totalUsers,
                'pages' => ceil($totalUsers / $limit)
            ],
            'filters' => [
                'languages' => $languages,
                'licenses' => $licenses,
                'locations' => $locations
            ],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalRepos + $totalOrgs + $totalUsers,
                'pages' => max(
                    ceil($totalRepos / $limit),
                    ceil($totalOrgs / $limit),
                    ceil($totalUsers / $limit)
                )
            ]
        ]);
    }
    
    /**
     * Get repository details
     * 
     * @return void
     */
    public function getRepository(): void
    {
        // Get owner and repo from parameters
        $owner = $this->getParam('owner');
        $repo = $this->getParam('repo');
        
        if (empty($owner) || empty($repo)) {
            $this->json([
                'status' => 'error',
                'message' => 'Owner and repository name are required'
            ], 400);
            return;
        }
        
        // Get repository
        $repoModel = new Repository();
        $repository = $repoModel->getByOwnerAndName($owner, $repo);
        
        if (!$repository) {
            $this->json([
                'status' => 'error',
                'message' => 'Repository not found'
            ], 404);
            return;
        }
        
        $this->json([
            'status' => 'success',
            'repository' => $repository
        ]);
    }
    
    /**
     * Get organization details
     * 
     * @return void
     */
    public function getOrganization(): void
    {
        // Get organization name from parameters
        $org = $this->getParam('org');
        
        if (empty($org)) {
            $this->json([
                'status' => 'error',
                'message' => 'Organization name is required'
            ], 400);
            return;
        }
        
        // Get organization
        $orgModel = new Organization();
        $organization = $orgModel->getByLogin($org);
        
        if (!$organization) {
            $this->json([
                'status' => 'error',
                'message' => 'Organization not found'
            ], 404);
            return;
        }
        
        // Get organization repositories
        $repoModel = new Repository();
        $repositories = $repoModel->getByOrganization($org);
        
        $this->json([
            'status' => 'success',
            'organization' => $organization,
            'repositories' => $repositories
        ]);
    }
    
    /**
     * Get user details
     * 
     * @return void
     */
    public function getUser(): void
    {
        // Get username from parameters
        $username = $this->getParam('username');
        
        if (empty($username)) {
            $this->json([
                'status' => 'error',
                'message' => 'Username is required'
            ], 400);
            return;
        }
        
        // Get user
        $userModel = new User();
        $user = $userModel->getByLogin($username);
        
        if (!$user) {
            $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
            return;
        }
        
        // Get user repositories
        $repoModel = new Repository();
        $repositories = $repoModel->getByUser($username);
        
        $this->json([
            'status' => 'success',
            'user' => $user,
            'repositories' => $repositories
        ]);
    }
    
    /**
     * API Documentation page
     * 
     * @return void
     */
    public function docs(): void
    {
        $this->view->setData([
            'title' => 'API Documentation',
        ])->render('api/docs');
    }
}
