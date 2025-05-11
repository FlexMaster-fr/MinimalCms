<?php
/**
 * User Model
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace App\Models;

use Core\Model;
use Core\Database;

class User extends Model
{
    /**
     * @var string Table name
     */
    protected static string $table = 'users';
    
    /**
     * @var string Primary key column
     */
    protected static string $primaryKey = 'id';
    
    /**
     * Get repositories for this user
     * 
     * @param array $options Query options
     * @return array Repositories
     */
    public function getRepositories(array $options = []): array
    {
        $limit = $options['limit'] ?? 10;
        $offset = (($options['page'] ?? 1) - 1) * $limit;
        
        $orderBy = $options['sort'] ?? 'stargazers_count';
        $orderDir = $options['direction'] ?? 'DESC';
        
        $allowedOrderFields = ['stargazers_count', 'forks_count', 'updated_at', 'created_at', 'name'];
        if (!in_array($orderBy, $allowedOrderFields)) {
            $orderBy = 'stargazers_count';
        }
        
        if (!in_array($orderDir, ['ASC', 'DESC'])) {
            $orderDir = 'DESC';
        }
        
        $query = "SELECT r.*, l.name as language_name, l.color as language_color, 
                  lic.name as license_name, lic.license_key as license_key
                FROM repositories r
                LEFT JOIN languages l ON r.language_id = l.id
                LEFT JOIN licenses lic ON r.license_id = lic.id
                WHERE r.owner_id = :owner_id AND r.owner_type = 'User'
                ORDER BY r.$orderBy $orderDir
                LIMIT :limit OFFSET :offset";
        
        $repositories = Database::fetchAll($query, [
            'owner_id' => $this->github_id,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as count FROM repositories 
                      WHERE owner_id = :owner_id AND owner_type = 'User'";
        $countResult = Database::fetchOne($countQuery, ['owner_id' => $this->github_id]);
        $total = (int)($countResult['count'] ?? 0);
        
        return [
            'repositories' => $repositories,
            'total' => $total,
            'page' => $options['page'] ?? 1,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get popular repositories for this user
     * 
     * @param int $limit Maximum number of repositories
     * @return array Popular repositories
     */
    public function getPopularRepositories(int $limit = 5): array
    {
        $query = "SELECT r.*, l.name as language_name, l.color as language_color, 
                 lic.name as license_name, lic.license_key as license_key
                FROM repositories r
                LEFT JOIN languages l ON r.language_id = l.id
                LEFT JOIN licenses lic ON r.license_id = lic.id
                WHERE r.owner_id = :owner_id AND r.owner_type = 'User'
                ORDER BY r.stargazers_count DESC
                LIMIT :limit";
        
        return Database::fetchAll($query, [
            'owner_id' => $this->github_id,
            'limit' => $limit
        ]);
    }
    
    /**
     * Get profile README for this user
     * 
     * @return string|null README content
     */
    public function getProfileReadme(): ?string
    {
        $query = "SELECT content FROM files 
                 WHERE entity_type = 'user' 
                 AND entity_id = :id
                 AND filename = 'README.md'";
        
        $result = Database::fetchOne($query, ['id' => $this->id]);
        
        return $result ? $result['content'] : null;
    }
    
    /**
     * Find a user by GitHub login
     * 
     * @param string $login GitHub login
     * @return static|null User instance or null if not found
     */
    public static function findByLogin(string $login): ?static
    {
        $query = "SELECT * FROM users WHERE login = :login LIMIT 1";
        $result = Database::fetchOne($query, ['login' => $login]);
        
        if ($result === false) {
            return null;
        }
        
        return new static($result, true);
    }
    
    /**
     * Find a user by GitHub ID
     * 
     * @param int $githubId GitHub user ID
     * @return static|null User instance or null if not found
     */
    public static function findByGithubId(int $githubId): ?static
    {
        $query = "SELECT * FROM users WHERE github_id = :github_id LIMIT 1";
        $result = Database::fetchOne($query, ['github_id' => $githubId]);
        
        if ($result === false) {
            return null;
        }
        
        return new static($result, true);
    }
    
    /**
     * Get stats summary for this user
     * 
     * @return array User stats
     */
    public function getStats(): array
    {
        $repositoriesQuery = "SELECT 
                            COUNT(*) as total_repos,
                            SUM(stargazers_count) as total_stars,
                            SUM(forks_count) as total_forks
                          FROM repositories
                          WHERE owner_id = :owner_id AND owner_type = 'User'";
        
        $repoStats = Database::fetchOne($repositoriesQuery, ['owner_id' => $this->github_id]);
        
        $languagesQuery = "SELECT l.name, l.color, COUNT(*) as repo_count
                        FROM repositories r
                        JOIN languages l ON r.language_id = l.id
                        WHERE r.owner_id = :owner_id AND r.owner_type = 'User'
                        GROUP BY l.id
                        ORDER BY repo_count DESC
                        LIMIT 5";
        
        $languages = Database::fetchAll($languagesQuery, ['owner_id' => $this->github_id]);
        
        return [
            'repository_count' => (int)($repoStats['total_repos'] ?? 0),
            'star_count' => (int)($repoStats['total_stars'] ?? 0),
            'fork_count' => (int)($repoStats['total_forks'] ?? 0),
            'top_languages' => $languages
        ];
    }
    
    /**
     * Search users
     * 
     * @param string $query Search query
     * @param array $options Search options
     * @return array Search results
     */
    public static function search(string $query, array $options = []): array
    {
        $where = [];
        $params = [];
        
        // Build search query
        if (!empty($query)) {
            $where[] = "(login LIKE :query OR name LIKE :query OR bio LIKE :query)";
            $params['query'] = "%$query%";
        }
        
        // Location filter
        if (!empty($options['location'])) {
            $where[] = "location LIKE :location";
            $params['location'] = "%{$options['location']}%";
        }
        
        // Pagination
        $limit = $options['limit'] ?? 20;
        $offset = (($options['page'] ?? 1) - 1) * $limit;
        
        // SQL query
        $sql = "SELECT * FROM users";
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        // Sorting
        $sortField = $options['sort'] ?? 'followers';
        $sortDirection = $options['direction'] ?? 'DESC';
        
        $allowedSortFields = [
            'followers', 'public_repos', 'created_at', 'login'
        ];
        
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'followers';
        }
        
        if (!in_array($sortDirection, ['ASC', 'DESC'])) {
            $sortDirection = 'DESC';
        }
        
        $sql .= " ORDER BY $sortField $sortDirection";
        
        // Add limit and offset
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        // Get users
        $results = Database::fetchAll($sql, $params);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM users";
        
        if (!empty($where)) {
            $countSql .= " WHERE " . implode(' AND ', $where);
        }
        
        $countResult = Database::fetchOne($countSql, $params);
        $totalCount = (int)($countResult['count'] ?? 0);
        
        return [
            'users' => $results,
            'total' => $totalCount,
            'page' => $options['page'] ?? 1,
            'limit' => $limit,
            'pages' => ceil($totalCount / $limit)
        ];
    }
}