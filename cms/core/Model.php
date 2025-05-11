<?php
/**
 * Base Model Class
 * 
 * Abstract class that all models extend from
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

abstract class Model
{
    /**
     * @var string Table name
     */
    protected static string $table = '';
    
    /**
     * @var string Primary key column
     */
    protected static string $primaryKey = 'id';
    
    /**
     * @var array Model attributes
     */
    protected array $attributes = [];
    
    /**
     * @var array Original attributes
     */
    protected array $original = [];
    
    /**
     * @var bool Whether the model exists in the database
     */
    protected bool $exists = false;
    
    /**
     * Constructor
     * 
     * @param array $attributes Model attributes
     * @param bool $exists Whether the model exists in the database
     */
    public function __construct(array $attributes = [], bool $exists = false)
    {
        $this->attributes = $attributes;
        $this->original = $attributes;
        $this->exists = $exists;
    }
    
    /**
     * Get model attribute
     * 
     * @param string $key Attribute name
     * @return mixed Attribute value
     */
    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }
    
    /**
     * Set model attribute
     * 
     * @param string $key Attribute name
     * @param mixed $value Attribute value
     * @return void
     */
    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }
    
    /**
     * Check if attribute exists
     * 
     * @param string $key Attribute name
     * @return bool True if attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Find model by primary key
     * 
     * @param mixed $id Primary key value
     * @return static|null Model instance or null if not found
     */
    public static function find($id): ?static
    {
        $query = sprintf(
            "SELECT * FROM %s WHERE %s = :id LIMIT 1",
            static::$table,
            static::$primaryKey
        );
        
        $result = Database::fetchOne($query, ['id' => $id]);
        
        if ($result === false) {
            return null;
        }
        
        return new static($result, true);
    }
    
    /**
     * Find all models
     * 
     * @param array $where Where conditions
     * @param array $order Order by columns
     * @param int|null $limit Limit
     * @param int|null $offset Offset
     * @return array Array of model instances
     */
    public static function findAll(array $where = [], array $order = [], ?int $limit = null, ?int $offset = null): array
    {
        $query = sprintf("SELECT * FROM %s", static::$table);
        $params = [];
        
        // Add where clauses
        if (!empty($where)) {
            $whereClauses = [];
            
            foreach ($where as $column => $value) {
                $whereClauses[] = "$column = :$column";
                $params[$column] = $value;
            }
            
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        // Add order by
        if (!empty($order)) {
            $orderClauses = [];
            
            foreach ($order as $column => $direction) {
                $orderClauses[] = "$column $direction";
            }
            
            $query .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        // Add limit and offset
        if ($limit !== null) {
            $query .= " LIMIT :limit";
            $params['limit'] = $limit;
            
            if ($offset !== null) {
                $query .= " OFFSET :offset";
                $params['offset'] = $offset;
            }
        }
        
        $results = Database::fetchAll($query, $params);
        $models = [];
        
        foreach ($results as $result) {
            $models[] = new static($result, true);
        }
        
        return $models;
    }
    
    /**
     * Count models
     * 
     * @param array $where Where conditions
     * @return int Number of models
     */
    public static function count(array $where = []): int
    {
        $query = sprintf("SELECT COUNT(*) as count FROM %s", static::$table);
        $params = [];
        
        // Add where clauses
        if (!empty($where)) {
            $whereClauses = [];
            
            foreach ($where as $column => $value) {
                $whereClauses[] = "$column = :$column";
                $params[$column] = $value;
            }
            
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $result = Database::fetchOne($query, $params);
        
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Save model to database
     * 
     * @return bool True on success
     */
    public function save(): bool
    {
        if ($this->exists) {
            // Update existing record
            $changed = array_diff_assoc($this->attributes, $this->original);
            
            if (empty($changed)) {
                return true; // Nothing changed
            }
            
            $id = $this->attributes[static::$primaryKey];
            $result = Database::update(static::$table, $changed, static::$primaryKey . ' = :id', ['id' => $id]);
            
            if ($result > 0) {
                $this->original = $this->attributes;
                return true;
            }
            
            return false;
        }
        
        // Insert new record
        $id = Database::insert(static::$table, $this->attributes);
        
        if ($id > 0) {
            $this->attributes[static::$primaryKey] = $id;
            $this->original = $this->attributes;
            $this->exists = true;
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete model from database
     * 
     * @return bool True on success
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $id = $this->attributes[static::$primaryKey];
        $result = Database::delete(static::$table, static::$primaryKey . ' = :id', ['id' => $id]);
        
        if ($result > 0) {
            $this->exists = false;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get model attributes
     * 
     * @return array Model attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Set model attributes
     * 
     * @param array $attributes Model attributes
     * @return static Model instance
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }
    
    /**
     * Check if model exists in database
     * 
     * @return bool True if model exists
     */
    public function doesExist(): bool
    {
        return $this->exists;
    }
}