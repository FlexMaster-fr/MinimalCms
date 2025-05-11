#!/usr/bin/env php
<?php
/**
 * GitHub Repository Crawler CLI Script
 * 
 * This script handles crawling GitHub repositories, organizations, and users
 * via the GitHub API and stores the data in the local database.
 * 
 * Usage: php crawler.php [schedule_id]
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

// Change to project root directory
chdir(dirname(__DIR__));

// Load autoloader
require_once 'core/Autoloader.php';
\Core\Autoloader::register();

// Load configuration
\Core\Config::load();

// Initialize database
\Core\Database::init(\Core\Config::getDatabaseConfig());

// Get schedule ID from command line argument
$scheduleId = isset($argv[1]) ? (int)$argv[1] : null;

// If no schedule ID provided, create a new schedule
if (!$scheduleId) {
    $crawlerSchedule = new \App\Models\CrawlerSchedule();
    $scheduleId = $crawlerSchedule->schedule('cli');
}

// Main crawler class
class Crawler
{
    /**
     * @var \Core\GitHubApi GitHub API client
     */
    private \Core\GitHubApi $api;
    
    /**
     * @var \App\Models\Repository Repository model
     */
    private \App\Models\Repository $repoModel;
    
    /**
     * @var \App\Models\Organization Organization model
     */
    private \App\Models\Organization $orgModel;
    
    /**
     * @var \App\Models\User User model
     */
    private \App\Models\User $userModel;
    
    /**
     * @var \App\Models\CrawlerSchedule Crawler schedule model
     */
    private \App\Models\CrawlerSchedule $scheduleModel;
    
    /**
     * @var int Current schedule ID
     */
    private int $scheduleId;
    
    /**
     * @var int Maximum number of repositories to crawl per run
     */
    private int $maxRepos = 100;
    
    /**
     * @var int Maximum number of organizations to crawl per run
     */
    private int $maxOrgs = 50;
    
    /**
     * @var int Maximum number of users to crawl per run
     */
    private int $maxUsers = 50;
    
    /**
     * @var int Time threshold (in seconds) for re-crawling (default: 48 hours)
     */
    private int $crawlThreshold = 172800; // 48 hours
    
    /**
     * @var array Statistics for current crawler run
     */
    private array $stats = [
        'repositories_crawled' => 0,
        'organizations_crawled' => 0,
        'users_crawled' => 0,
        'files_saved' => 0,
        'errors' => 0,
        'start_time' => 0,
        'end_time' => 0
    ];
    
    /**
     * Constructor
     * 
     * @param int $scheduleId Schedule ID
     */
    public function __construct(int $scheduleId)
    {
        $this->scheduleId = $scheduleId;
        
        // Initialize API client
        $config = \Core\Config::getGitHubConfig();
        $this->api = new \Core\GitHubApi(
            $config['token'],
            $config['api_version'],
            $config['user_agent']
        );
        
        // Initialize models
        $this->repoModel = new \App\Models\Repository();
        $this->orgModel = new \App\Models\Organization();
        $this->userModel = new \App\Models\User();
        $this->scheduleModel = new \App\Models\CrawlerSchedule();
        
        // Initialize stats
        $this->stats['start_time'] = time();
    }
    
    /**
     * Run the crawler
     * 
     * @return void
     */
    public function run(): void
    {
        try {
            // Update schedule status
            $this->scheduleModel->updateStatus($this->scheduleId, 'running');
            $this->log("Starting crawler (Schedule ID: {$this->scheduleId})");
            
            // Crawl repositories
            $this->crawlRepositories();
            
            // Crawl organizations
            $this->crawlOrganizations();
            
            // Crawl users
            $this->crawlUsers();
            
            // Cleanup and finalize
            $this->finalize();
        } catch (\Exception $e) {
            $this->log("Crawler error: " . $e->getMessage(), 'error');
            $this->scheduleModel->updateStatus($this->scheduleId, 'failed');
            
            // Update stats
            $this->stats['errors']++;
            $this->stats['end_time'] = time();
            $this->logStats();
        }
    }
    
    /**
     * Crawl repositories
     * 
     * @return void
     */
    private function crawlRepositories(): void
    {
        $this->log("Fetching repositories to crawl");
        
        // Get repositories that need to be crawled
        $repositories = $this->repoModel->getRepositoriesToCrawl($this->maxRepos, $this->crawlThreshold);
        
        $total = count($repositories);
        $this->log("Found {$total} repositories to crawl");
        
        foreach ($repositories as $index => $repo) {
            try {
                $this->log("Crawling repository {$index+1}/{$total}: {$repo['full_name']}");
                
                // Fetch repository details from GitHub API
                $repoData = $this->api->getRepository($repo['owner_login'], $repo['name']);
                
                // Save repository data
                $repoId = $this->repoModel->saveFromApi($repoData, $repoData['owner']['type'] ?? 'User');
                
                // Fetch and save README
                $this->fetchAndSaveReadme($repo['owner_login'], $repo['name'], $repoId);
                
                // Update last crawled timestamp
                $this->repoModel->updateLastCrawled($repo['id']);
                
                // Update stats
                $this->stats['repositories_crawled']++;
                
                // Add a small delay to prevent API rate limiting
                usleep(100000); // 100ms
            } catch (\Exception $e) {
                $this->log("Error crawling repository {$repo['full_name']}: " . $e->getMessage(), 'error');
                $this->stats['errors']++;
            }
        }
        
        $this->log("Completed repository crawling: {$this->stats['repositories_crawled']} repositories processed");
    }
    
    /**
     * Crawl organizations
     * 
     * @return void
     */
    private function crawlOrganizations(): void
    {
        $this->log("Fetching organizations to crawl");
        
        // Get organizations that need to be crawled
        $organizations = $this->orgModel->getOrganizationsToCrawl($this->maxOrgs, $this->crawlThreshold);
        
        $total = count($organizations);
        $this->log("Found {$total} organizations to crawl");
        
        foreach ($organizations as $index => $org) {
            try {
                $this->log("Crawling organization {$index+1}/{$total}: {$org['login']}");
                
                // Fetch organization details from GitHub API
                $orgData = $this->api->getOrganization($org['login']);
                
                // Save organization data
                $orgId = $this->orgModel->saveFromApi($orgData);
                
                // Fetch repositories for this organization
                $this->fetchOrganizationRepositories($org['login']);
                
                // Update last crawled timestamp
                $this->orgModel->updateLastCrawled($org['id']);
                
                // Update stats
                $this->stats['organizations_crawled']++;
                
                // Add a small delay to prevent API rate limiting
                usleep(100000); // 100ms
            } catch (\Exception $e) {
                $this->log("Error crawling organization {$org['login']}: " . $e->getMessage(), 'error');
                $this->stats['errors']++;
            }
        }
        
        $this->log("Completed organization crawling: {$this->stats['organizations_crawled']} organizations processed");
    }
    
    /**
     * Crawl users
     * 
     * @return void
     */
    private function crawlUsers(): void
    {
        $this->log("Fetching users to crawl");
        
        // Get users that need to be crawled
        $users = $this->userModel->getUsersToCrawl($this->maxUsers, $this->crawlThreshold);
        
        $total = count($users);
        $this->log("Found {$total} users to crawl");
        
        foreach ($users as $index => $user) {
            try {
                $this->log("Crawling user {$index+1}/{$total}: {$user['login']}");
                
                // Fetch user details from GitHub API
                $userData = $this->api->getUser($user['login']);
                
                // Save user data
                $userId = $this->userModel->saveFromApi($userData);
                
                // Fetch repositories for this user
                $this->fetchUserRepositories($user['login']);
                
                // Update last crawled timestamp
                $this->userModel->updateLastCrawled($user['id']);
                
                // Update stats
                $this->stats['users_crawled']++;
                
                // Add a small delay to prevent API rate limiting
                usleep(100000); // 100ms
            } catch (\Exception $e) {
                $this->log("Error crawling user {$user['login']}: " . $e->getMessage(), 'error');
                $this->stats['errors']++;
            }
        }
        
        $this->log("Completed user crawling: {$this->stats['users_crawled']} users processed");
    }
    
    /**
     * Fetch repositories for an organization
     * 
     * @param string $org Organization login
     * @return void
     */
    private function fetchOrganizationRepositories(string $org): void
    {
        try {
            $this->log("Fetching repositories for organization: {$org}");
            
            // Use GraphQL for efficient repository fetching
            $after = null;
            $hasNextPage = true;
            $totalRepos = 0;
            
            while ($hasNextPage && $totalRepos < 100) { // Limit to 100 repos per org
                $response = $this->api->getRepositoriesGraphQL($org, 'organization', 30, $after);
                
                if (isset($response['data']['organization']['repositories'])) {
                    $repos = $response['data']['organization']['repositories'];
                    $pageInfo = $repos['pageInfo'];
                    $hasNextPage = $pageInfo['hasNextPage'];
                    $after = $pageInfo['endCursor'];
                    
                    foreach ($repos['nodes'] as $repoData) {
                        try {
                            // Convert GraphQL format to REST-like format
                            $normalizedData = $this->normalizeGraphQLRepository($repoData, $org);
                            
                            // Save repository data
                            $repoId = $this->repoModel->saveFromApi($normalizedData, 'Organization');
                            
                            // Fetch and save README
                            $this->fetchAndSaveReadme($org, $repoData['name'], $repoId);
                            
                            $totalRepos++;
                        } catch (\Exception $e) {
                            $this->log("Error processing repository {$org}/{$repoData['name']}: " . $e->getMessage(), 'error');
                            $this->stats['errors']++;
                        }
                    }
                    
                    $this->log("Processed {$totalRepos} repositories for {$org}");
                } else {
                    $hasNextPage = false;
                }
            }
        } catch (\Exception $e) {
            $this->log("Error fetching repositories for organization {$org}: " . $e->getMessage(), 'error');
            $this->stats['errors']++;
        }
    }
    
    /**
     * Fetch repositories for a user
     * 
     * @param string $username User login
     * @return void
     */
    private function fetchUserRepositories(string $username): void
    {
        try {
            $this->log("Fetching repositories for user: {$username}");
            
            // Use GraphQL for efficient repository fetching
            $after = null;
            $hasNextPage = true;
            $totalRepos = 0;
            
            while ($hasNextPage && $totalRepos < 100) { // Limit to 100 repos per user
                $response = $this->api->getRepositoriesGraphQL($username, 'user', 30, $after);
                
                if (isset($response['data']['user']['repositories'])) {
                    $repos = $response['data']['user']['repositories'];
                    $pageInfo = $repos['pageInfo'];
                    $hasNextPage = $pageInfo['hasNextPage'];
                    $after = $pageInfo['endCursor'];
                    
                    foreach ($repos['nodes'] as $repoData) {
                        try {
                            // Convert GraphQL format to REST-like format
                            $normalizedData = $this->normalizeGraphQLRepository($repoData, $username);
                            
                            // Save repository data
                            $repoId = $this->repoModel->saveFromApi($normalizedData, 'User');
                            
                            // Fetch and save README
                            $this->fetchAndSaveReadme($username, $repoData['name'], $repoId);
                            
                            $totalRepos++;
                        } catch (\Exception $e) {
                            $this->log("Error processing repository {$username}/{$repoData['name']}: " . $e->getMessage(), 'error');
                            $this->stats['errors']++;
                        }
                    }
                    
                    $this->log("Processed {$totalRepos} repositories for {$username}");
                } else {
                    $hasNextPage = false;
                }
            }
        } catch (\Exception $e) {
            $this->log("Error fetching repositories for user {$username}: " . $e->getMessage(), 'error');
            $this->stats['errors']++;
        }
    }
    
    /**
     * Fetch and save README for a repository
     * 
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param int $repoId Repository ID
     * @return void
     */
    private function fetchAndSaveReadme(string $owner, string $repo, int $repoId): void
    {
        try {
            $content = $this->api->getReadme($owner, $repo);
            
            if ($content) {
                // Convert Markdown to HTML
                $view = new \Core\View();
                $htmlContent = $view->markdown($content);
                
                // Save README
                $this->repoModel->saveFile($repoId, 'README.md', $content, $htmlContent);
                $this->stats['files_saved']++;
            }
        } catch (\Exception $e) {
            $this->log("Error fetching README for {$owner}/{$repo}: " . $e->getMessage(), 'error');
            $this->stats['errors']++;
        }
    }
    
    /**
     * Normalize GraphQL repository data to REST-like format
     * 
     * @param array $graphqlRepo Repository data from GraphQL
     * @param string $owner Repository owner
     * @return array Normalized repository data
     */
    private function normalizeGraphQLRepository(array $graphqlRepo, string $owner): array
    {
        $normalized = [
            'id' => $graphqlRepo['id'],
            'name' => $graphqlRepo['name'],
            'full_name' => $owner . '/' . $graphqlRepo['name'],
            'description' => $graphqlRepo['description'] ?? '',
            'html_url' => $graphqlRepo['url'],
            'stargazers_count' => $graphqlRepo['stargazerCount'],
            'forks_count' => $graphqlRepo['forkCount'],
            'created_at' => $graphqlRepo['createdAt'],
            'updated_at' => $graphqlRepo['updatedAt'],
            'owner' => [
                'login' => $owner,
                'id' => 0, // Will be populated from existing data
                'type' => 'User' // Default, will be overridden if known
            ],
            'fork' => $graphqlRepo['isFork'] ?? false,
            'archived' => $graphqlRepo['isArchived'] ?? false,
            'topics' => []
        ];
        
        // Add language
        if (isset($graphqlRepo['primaryLanguage']) && $graphqlRepo['primaryLanguage']) {
            $normalized['language'] = $graphqlRepo['primaryLanguage']['name'];
        }
        
        // Add license
        if (isset($graphqlRepo['licenseInfo']) && $graphqlRepo['licenseInfo']) {
            $normalized['license'] = [
                'key' => strtolower($graphqlRepo['licenseInfo']['spdxId'] ?? ''),
                'name' => $graphqlRepo['licenseInfo']['name'],
                'spdx_id' => $graphqlRepo['licenseInfo']['spdxId'] ?? '',
                'url' => ''
            ];
        }
        
        // Add topics
        if (isset($graphqlRepo['topics']['nodes']) && !empty($graphqlRepo['topics']['nodes'])) {
            foreach ($graphqlRepo['topics']['nodes'] as $topicNode) {
                $normalized['topics'][] = $topicNode['topic']['name'];
            }
        }
        
        return $normalized;
    }
    
    /**
     * Finalize crawler run
     * 
     * @return void
     */
    private function finalize(): void
    {
        // Update stats
        $this->stats['end_time'] = time();
        $executionTime = $this->stats['end_time'] - $this->stats['start_time'];
        
        $this->log("Crawler completed in {$executionTime} seconds");
        $this->logStats();
        
        // Update schedule status
        $this->scheduleModel->updateStatus($this->scheduleId, 'completed');
    }
    
    /**
     * Log crawler progress
     * 
     * @param string $message Log message
     * @param string $type Log type (info, error, etc.)
     * @return void
     */
    private function log(string $message, string $type = 'info'): void
    {
        $this->scheduleModel->log($this->scheduleId, $message, $type);
        
        // Also output to console
        $timestamp = date('Y-m-d H:i:s');
        echo "[{$timestamp}] [{$type}] {$message}" . PHP_EOL;
    }
    
    /**
     * Log crawler statistics
     * 
     * @return void
     */
    private function logStats(): void
    {
        $executionTime = $this->stats['end_time'] - $this->stats['start_time'];
        
        $statsMessage = "Crawler statistics: " .
            "{$this->stats['repositories_crawled']} repositories, " .
            "{$this->stats['organizations_crawled']} organizations, " .
            "{$this->stats['users_crawled']} users, " .
            "{$this->stats['files_saved']} files saved, " .
            "{$this->stats['errors']} errors, " .
            "{$executionTime} seconds execution time";
        
        $this->log($statsMessage);
    }
}

// Run the crawler
try {
    $crawler = new Crawler($scheduleId);
    $crawler->run();
} catch (Exception $e) {
    // Log fatal error
    $log = new \App\Models\Log();
    $log->createLog('crawler', 'Fatal crawler error: ' . $e->getMessage(), 'error', 'crawler_' . $scheduleId);
    
    // Update schedule status
    $crawlerSchedule = new \App\Models\CrawlerSchedule();
    $crawlerSchedule->updateStatus($scheduleId, 'failed');
    
    // Output to console
    echo '[' . date('Y-m-d H:i:s') . '] [FATAL] ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

exit(0);
