<?php
/**
 * Database Manager
 * 
 * Handles database connections and operations
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

use PDO;
use PDOException;

class Database
{
    /**
     * @var PDO|null PDO instance
     */
    private static ?PDO $instance = null;
    
    /**
     * @var array Database configuration
     */
    private static array $config = [];
    
    /**
     * Initialize the database with configuration
     * 
     * @param array $config Database configuration
     * @return void
     */
    public static function init(array $config): void
    {
        self::$config = $config;
    }
    
    /**
     * Get database connection (singleton pattern)
     * 
     * @return PDO Database connection
     * @throws PDOException If connection fails
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dbType = self::$config['type'] ?? 'sqlite';
            
            if ($dbType === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;port=%d;charset=utf8mb4',
                    self::$config['host'] ?? 'localhost',
                    self::$config['name'] ?? 'github_crawler',
                    self::$config['port'] ?? 3306
                );
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
                ];
                
                self::$instance = new PDO(
                    $dsn, 
                    self::$config['user'] ?? 'root',
                    self::$config['pass'] ?? '',
                    $options
                );
            } else {
                // SQLite as default
                $dbPath = self::$config['path'] ?? dirname(__DIR__) . '/data/crawler.sqlite';
                
                // Ensure directory exists
                $dbDir = dirname($dbPath);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
                
                self::$instance = new PDO(
                    'sqlite:' . $dbPath,
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                
                // Enable foreign keys for SQLite
                self::$instance->exec('PRAGMA foreign_keys = ON');
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Execute a SQL query with parameters
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return \PDOStatement PDO statement
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    /**
     * Fetch a single row from database
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|false Row data or false if not found
     */
    public static function fetchOne(string $sql, array $params = []): array|false
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows from database
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Array of rows
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert data into a table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last insert ID
     */
    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        self::query($sql, $data);
        return (int)self::getInstance()->lastInsertId();
    }
    
    /**
     * Update data in a table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause
     * @param array $whereParams WHERE parameters
     * @return int Number of affected rows
     */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = array_map(fn($col) => "$col = :$col", array_keys($data));
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $sets),
            $where
        );
        
        $stmt = self::query($sql, array_merge($data, $whereParams));
        return $stmt->rowCount();
    }
    
    /**
     * Delete data from a table
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params WHERE parameters
     * @return int Number of affected rows
     */
    public static function delete(string $table, string $where, array $params = []): int
    {
        $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True on success
     */
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True on success
     */
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool True on success
     */
    public static function rollback(): bool
    {
        return self::getInstance()->rollBack();
    }
}
