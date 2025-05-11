<?php
/**
 * Repository Controller
 * 
 * Handles repository, organization, and user detail pages
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace App\Controllers;

use Core\Controller;
use App\Models\Repository;
use App\Models\Organization;
use App\Models\User;

class RepositoryController extends Controller
{
    /**
     * Repository detail page
     * 
     * @return void
     */
    public function detail(): void
    {
        // Get owner and repo from parameters
        $owner = $this->getParam('owner');
        $repo = $this->getParam('repo');
        
        if (empty($owner) || empty($repo)) {
            $this->redirect('/');
            return;
        }
        
        // Get repository
        $repoModel = new Repository();
        $repository = $repoModel->getByOwnerAndName($owner, $repo);
        
        if (!$repository) {
            $this->view->render('errors/404');
            return;
        }
        
        // Get owner details
        $ownerDetails = [];
        
        if ($repository['owner_type'] === 'Organization') {
            $orgModel = new Organization();
            $ownerDetails = $orgModel->getByLogin($owner);
        } else {
            $userModel = new User();
            $ownerDetails = $userModel->getByLogin($owner);
        }
        
        // Get related repositories
        $relatedRepos = $repoModel->getRelated($repository['id'], 5);
        
        $this->view->setData([
            'repository' => $repository,
            'owner' => $ownerDetails,
            'related' => $relatedRepos
        ]);
        
        $this->view->render('repository/detail');
    }
    
    /**
     * Organization detail page
     * 
     * @return void
     */
    public function organization(): void
    {
        // Get organization name from parameters
        $org = $this->getParam('org');
        
        if (empty($org)) {
            $this->redirect('/');
            return;
        }
        
        // Get organization
        $orgModel = new Organization();
        $organization = $orgModel->getByLogin($org);
        
        if (!$organization) {
            $this->view->render('errors/404');
            return;
        }
        
        // Get repositories
        $repoModel = new Repository();
        $repositories = $repoModel->getByOrganization($org);
        
        // Get popular repositories
        $popularRepos = $repoModel->getByOrganizationSorted($org, 'stars', 'DESC', 5);
        
        $this->view->setData([
            'organization' => $organization,
            'repositories' => $repositories,
            'popular' => $popularRepos
        ]);
        
        $this->view->render('repository/organization');
    }
    
    /**
     * User detail page
     * 
     * @return void
     */
    public function user(): void
    {
        // Get username from parameters
        $username = $this->getParam('username');
        
        if (empty($username)) {
            $this->redirect('/');
            return;
        }
        
        // Get user
        $userModel = new User();
        $user = $userModel->getByLogin($username);
        
        if (!$user) {
            $this->view->render('errors/404');
            return;
        }
        
        // Get repositories
        $repoModel = new Repository();
        $repositories = $repoModel->getByUser($username);
        
        // Get popular repositories
        $popularRepos = $repoModel->getByUserSorted($username, 'stars', 'DESC', 5);
        
        $this->view->setData([
            'user' => $user,
            'repositories' => $repositories,
            'popular' => $popularRepos
        ]);
        
        $this->view->render('repository/user');
    }
    
    /**
     * Get repository README
     * 
     * @return void
     */
    public function readme(): void
    {
        // Get owner and repo from parameters
        $owner = $this->getParam('owner');
        $repo = $this->getParam('repo');
        
        if (empty($owner) || empty($repo)) {
            $this->json(['error' => 'Owner and repository name are required'], 400);
            return;
        }
        
        // Get repository
        $repoModel = new Repository();
        $repository = $repoModel->getByOwnerAndName($owner, $repo);
        
        if (!$repository) {
            $this->json(['error' => 'Repository not found'], 404);
            return;
        }
        
        // Get README content
        $readme = $repoModel->getReadme($repository['id']);
        
        $this->json([
            'html' => $readme['html_content'] ?? '',
            'raw' => $readme['content'] ?? ''
        ]);
    }
}
