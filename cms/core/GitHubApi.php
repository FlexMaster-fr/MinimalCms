<?php
/**
 * GitHub API Client
 * 
 * Handles communication with GitHub's REST and GraphQL APIs
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

class GitHubApi
{
    /**
     * @var string GitHub API token
     */
    private string $token;
    
    /**
     * @var string GitHub API version
     */
    private string $apiVersion;
    
    /**
     * @var string User agent for requests
     */
    private string $userAgent;
    
    /**
     * @var self|null Singleton instance
     */
    private static ?self $instance = null;
    
    /**
     * Constructor
     * 
     * @param array $config GitHub API configuration
     */
    public function __construct(array $config = [])
    {
        $this->token = $config['token'] ?? '';
        $this->apiVersion = $config['api_version'] ?? '2022-11-28';
        $this->userAgent = $config['user_agent'] ?? 'GitHub-Crawler-Bot';
        
        self::$instance = $this;
    }
    
    /**
     * Get singleton instance
     * 
     * @param array $config GitHub API configuration
     * @return self API client instance
     */
    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        
        return self::$instance;
    }
    
    /**
     * Make a request to GitHub's REST API
     * 
     * @param string $endpoint API endpoint (without base URL)
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array Response data
     */
    public function request(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $url = 'https://api.github.com/' . ltrim($endpoint, '/');
        
        $ch = curl_init();
        
        $headers = [
            'Accept: application/vnd.github+json',
            'X-GitHub-Api-Version: ' . $this->apiVersion,
            'User-Agent: ' . $this->userAgent
        ];
        
        // Add authorization header if token is set
        if (!empty($this->token)) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method
        ];
        
        // Add data for POST, PUT, PATCH requests
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($data)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            $headers[] = 'Content-Type: application/json';
        }
        
        // Add query parameters for GET requests
        if ($method === 'GET' && !empty($data)) {
            $options[CURLOPT_URL] = $url . '?' . http_build_query($data);
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Handle errors
        if ($error) {
            throw new \Exception('GitHub API request failed: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        // Handle error response
        if ($statusCode >= 400) {
            $message = $result['message'] ?? 'Unknown error';
            throw new \Exception('GitHub API error: ' . $message, $statusCode);
        }
        
        // Log API request
        $this->logApiRequest($endpoint, $method, $statusCode);
        
        return $result;
    }
    
    /**
     * Fetch repository data
     * 
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @return array Repository data
     */
    public function getRepository(string $owner, string $repo): array
    {
        return $this->request("repos/$owner/$repo");
    }
    
    /**
     * Fetch user data
     * 
     * @param string $username Username
     * @return array User data
     */
    public function getUser(string $username): array
    {
        return $this->request("users/$username");
    }
    
    /**
     * Fetch organization data
     * 
     * @param string $org Organization name
     * @return array Organization data
     */
    public function getOrganization(string $org): array
    {
        return $this->request("orgs/$org");
    }
    
    /**
     * Search repositories
     * 
     * @param string $query Search query
     * @param array $options Search options
     * @return array Search results
     */
    public function searchRepositories(string $query, array $options = []): array
    {
        $params = [
            'q' => $query,
            'per_page' => $options['per_page'] ?? 30,
            'page' => $options['page'] ?? 1
        ];
        
        if (isset($options['sort'])) {
            $params['sort'] = $options['sort'];
        }
        
        if (isset($options['order'])) {
            $params['order'] = $options['order'];
        }
        
        return $this->request('search/repositories', 'GET', $params);
    }
    
    /**
     * Search users
     * 
     * @param string $query Search query
     * @param array $options Search options
     * @return array Search results
     */
    public function searchUsers(string $query, array $options = []): array
    {
        $params = [
            'q' => $query,
            'per_page' => $options['per_page'] ?? 30,
            'page' => $options['page'] ?? 1
        ];
        
        if (isset($options['sort'])) {
            $params['sort'] = $options['sort'];
        }
        
        if (isset($options['order'])) {
            $params['order'] = $options['order'];
        }
        
        return $this->request('search/users', 'GET', $params);
    }
    
    /**
     * Fetch repository README
     * 
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param string $branch Branch name (default: main branch)
     * @return string README content or empty string if not found
     */
    public function getReadme(string $owner, string $repo, ?string $branch = null): string
    {
        try {
            $params = [];
            if ($branch !== null) {
                $params['ref'] = $branch;
            }
            
            $result = $this->request("repos/$owner/$repo/readme", 'GET', $params);
            
            if (isset($result['content']) && isset($result['encoding']) && $result['encoding'] === 'base64') {
                return base64_decode($result['content']);
            }
            
            return '';
        } catch (\Exception $e) {
            // If README not found, return empty string
            return '';
        }
    }
    
    /**
     * Fetch repository topics
     * 
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @return array Topics list
     */
    public function getRepositoryTopics(string $owner, string $repo): array
    {
        try {
            $headers = ['Accept: application/vnd.github.mercy-preview+json'];
            $result = $this->request("repos/$owner/$repo/topics", 'GET', [], $headers);
            
            return $result['names'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Fetch user repositories
     * 
     * @param string $username Username
     * @param array $options Filter options
     * @return array Repositories list
     */
    public function getUserRepositories(string $username, array $options = []): array
    {
        $params = [
            'per_page' => $options['per_page'] ?? 30,
            'page' => $options['page'] ?? 1
        ];
        
        if (isset($options['sort'])) {
            $params['sort'] = $options['sort'];
        }
        
        if (isset($options['type'])) {
            $params['type'] = $options['type'];
        }
        
        return $this->request("users/$username/repos", 'GET', $params);
    }
    
    /**
     * Fetch organization repositories
     * 
     * @param string $org Organization name
     * @param array $options Filter options
     * @return array Repositories list
     */
    public function getOrganizationRepositories(string $org, array $options = []): array
    {
        $params = [
            'per_page' => $options['per_page'] ?? 30,
            'page' => $options['page'] ?? 1
        ];
        
        if (isset($options['sort'])) {
            $params['sort'] = $options['sort'];
        }
        
        if (isset($options['type'])) {
            $params['type'] = $options['type'];
        }
        
        return $this->request("orgs/$org/repos", 'GET', $params);
    }
    
    /**
     * Log API request to database
     * 
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param int $statusCode HTTP status code
     * @return void
     */
    private function logApiRequest(string $endpoint, string $method, int $statusCode): void
    {
        // Only log if we have a database connection
        if (Database::getInstance()) {
            try {
                // Insert log record
                Database::insert('logs', [
                    'type' => 'github_api',
                    'message' => "GitHub API request: $method $endpoint",
                    'details' => "Status code: $statusCode",
                    'reference' => 'github_api_' . time(),
                    'created_at' => time()
                ]);
            } catch (\Exception $e) {
                // Silently fail if we can't log
            }
        }
    }
    
    /**
     * Execute a GraphQL query
     * 
     * @param string $query GraphQL query
     * @param array $variables Query variables
     * @return array Query results
     */
    public function graphql(string $query, array $variables = []): array
    {
        if (empty($this->token)) {
            throw new \Exception('GitHub API token is required for GraphQL queries');
        }
        
        $url = 'https://api.github.com/graphql';
        
        $data = [
            'query' => $query,
            'variables' => $variables
        ];
        
        $ch = curl_init();
        
        $headers = [
            'Accept: application/vnd.github+json',
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'X-GitHub-Api-Version: ' . $this->apiVersion,
            'User-Agent: ' . $this->userAgent
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Handle errors
        if ($error) {
            throw new \Exception('GitHub GraphQL API request failed: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        // Handle error response
        if ($statusCode >= 400 || isset($result['errors'])) {
            $message = $result['message'] ?? ($result['errors'][0]['message'] ?? 'Unknown error');
            throw new \Exception('GitHub GraphQL API error: ' . $message, $statusCode);
        }
        
        // Log API request
        $this->logApiRequest('graphql', 'POST', $statusCode);
        
        return $result;
    }
}