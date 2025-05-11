<?php
/**
 * Repository Model
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace App\Models;

use Core\Model;
use Core\Database;

class Repository extends Model
{
    /**
     * @var string Table name
     */
    protected static string $table = 'repositories';
    
    /**
     * @var string Primary key column
     */
    protected static string $primaryKey = 'id';
    
    /**
     * Get the owner of the repository
     * 
     * @return User|Organization|null Repository owner
     */
    public function getOwner()
    {
        if ($this->owner_type === 'User') {
            return User::find($this->owner_id);
        } elseif ($this->owner_type === 'Organization') {
            return Organization::find($this->owner_id);
        }
        
        return null;
    }
    
    /**
     * Get the language of the repository
     * 
     * @return array|null Language information
     */
    public function getLanguage(): ?array
    {
        if (empty($this->language_id)) {
            return null;
        }
        
        $query = "SELECT * FROM languages WHERE id = :id";
        return Database::fetchOne($query, ['id' => $this->language_id]);
    }
    
    /**
     * Get the license of the repository
     * 
     * @return array|null License information
     */
    public function getLicense(): ?array
    {
        if (empty($this->license_id)) {
            return null;
        }
        
        $query = "SELECT * FROM licenses WHERE id = :id";
        return Database::fetchOne($query, ['id' => $this->license_id]);
    }
    
    /**
     * Get topics for the repository
     * 
     * @return array Topics list
     */
    public function getTopics(): array
    {
        $query = "SELECT t.* 
                 FROM topics t
                 JOIN repository_topics rt ON t.id = rt.topic_id
                 WHERE rt.repository_id = :repo_id
                 ORDER BY t.name";
        
        return Database::fetchAll($query, ['repo_id' => $this->id]);
    }
    
    /**
     * Get README content for the repository
     * 
     * @return string|null README content
     */
    public function getReadme(): ?string
    {
        $query = "SELECT content FROM files 
                 WHERE entity_type = 'repository' 
                 AND entity_id = :id
                 AND filename = 'README.md'";
        
        $result = Database::fetchOne($query, ['id' => $this->id]);
        
        return $result ? $result['content'] : null;
    }
    
    /**
     * Get similar repositories
     * 
     * @param int $limit Maximum number of repositories
     * @return array Similar repositories
     */
    public function getSimilarRepositories(int $limit = 5): array
    {
        // Get by same language
        if (!empty($this->language_id)) {
            $query = "SELECT r.*, l.name as language_name, l.color as language_color, 
                      lic.name as license_name, lic.license_key as license_key
                     FROM repositories r
                     LEFT JOIN languages l ON r.language_id = l.id
                     LEFT JOIN licenses lic ON r.license_id = lic.id
                     WHERE r.language_id = :language_id
                     AND r.id != :id
                     ORDER BY r.stargazers_count DESC
                     LIMIT :limit";
            
            $results = Database::fetchAll($query, [
                'language_id' => $this->language_id,
                'id' => $this->id,
                'limit' => $limit
            ]);
            
            if (count($results) >= $limit) {
                return $results;
            }
        }
        
        // Fallback to popular repositories
        $query = "SELECT r.*, l.name as language_name, l.color as language_color, 
                  lic.name as license_name, lic.license_key as license_key
                 FROM repositories r
                 LEFT JOIN languages l ON r.language_id = l.id
                 LEFT JOIN licenses lic ON r.license_id = lic.id
                 WHERE r.id != :id
                 ORDER BY r.stargazers_count DESC
                 LIMIT :limit";
        
        return Database::fetchAll($query, [
            'id' => $this->id,
            'limit' => $limit
        ]);
    }
    
    /**
     * Search repositories
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
            $where[] = "(name LIKE :query OR description LIKE :query)";
            $params['query'] = "%$query%";
        }
        
        // Language filter
        if (!empty($options['language'])) {
            $where[] = "language_id = (SELECT id FROM languages WHERE name = :language)";
            $params['language'] = $options['language'];
        }
        
        // License filter
        if (!empty($options['license'])) {
            $where[] = "license_id = (SELECT id FROM licenses WHERE license_key = :license)";
            $params['license'] = $options['license'];
        }
        
        // Min stars filter
        if (!empty($options['min_stars'])) {
            $where[] = "stargazers_count >= :min_stars";
            $params['min_stars'] = (int)$options['min_stars'];
        }
        
        // Min forks filter
        if (!empty($options['min_forks'])) {
            $where[] = "forks_count >= :min_forks";
            $params['min_forks'] = (int)$options['min_forks'];
        }
        
        // Pagination
        $limit = $options['limit'] ?? 20;
        $offset = (($options['page'] ?? 1) - 1) * $limit;
        
        // SQL query
        $sql = "SELECT r.*, l.name as language_name, l.color as language_color, 
                   lic.name as license_name, lic.license_key as license_key
               FROM repositories r
               LEFT JOIN languages l ON r.language_id = l.id
               LEFT JOIN licenses lic ON r.license_id = lic.id";
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        // Sorting
        $sortField = $options['sort'] ?? 'stargazers_count';
        $sortDirection = $options['direction'] ?? 'DESC';
        
        $allowedSortFields = [
            'stargazers_count', 'forks_count', 'updated_at', 'created_at'
        ];
        
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'stargazers_count';
        }
        
        if (!in_array($sortDirection, ['ASC', 'DESC'])) {
            $sortDirection = 'DESC';
        }
        
        $sql .= " ORDER BY r.$sortField $sortDirection";
        
        // Add limit and offset
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        // Get repositories
        $results = Database::fetchAll($sql, $params);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM repositories r";
        
        if (!empty($where)) {
            $countSql .= " WHERE " . implode(' AND ', $where);
        }
        
        $countResult = Database::fetchOne($countSql, $params);
        $totalCount = (int)($countResult['count'] ?? 0);
        
        return [
            'repositories' => $results,
            'total' => $totalCount,
            'page' => $options['page'] ?? 1,
            'limit' => $limit,
            'pages' => ceil($totalCount / $limit)
        ];
    }
}