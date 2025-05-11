<?php
/**
 * Log Model
 * 
 * Handles system logs
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace App\Models;

use Core\Model;
use Core\Database;

class Log extends Model
{
    /**
     * @var string Table name
     */
    protected static string $table = 'logs';
    
    /**
     * @var string Primary key
     */
    protected static string $primaryKey = 'id';
    
    /**
     * Create a new log entry
     * 
     * @param string $type Log type
     * @param string $message Log message
     * @param string $details Additional details
     * @param string $reference Reference ID
     * @return int Log ID
     */
    public function createLog(string $type, string $message, string $details = '', string $reference = ''): int
    {
        $data = [
            'type' => $type,
            'message' => $message,
            'details' => $details,
            'reference' => $reference,
            'created_at' => time()
        ];
        
        return Database::insert(self::$table, $data);
    }
    
    /**
     * Get recent logs
     * 
     * @param int $limit Maximum number of logs to return
     * @return array Array of logs
     */
    public function getRecent(int $limit = 50): array
    {
        $sql = "SELECT * FROM " . self::$table . " ORDER BY created_at DESC LIMIT {$limit}";
        return Database::fetchAll($sql);
    }
    
    /**
     * Get logs by type
     * 
     * @param string $type Log type
     * @param int $limit Maximum number of logs to return
     * @return array Array of logs
     */
    public function getByType(string $type, int $limit = 50): array
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE type = :type ORDER BY created_at DESC LIMIT {$limit}";
        return Database::fetchAll($sql, ['type' => $type]);
    }
    
    /**
     * Get logs by reference
     * 
     * @param string $reference Reference ID
     * @param int $limit Maximum number of logs to return
     * @return array Array of logs
     */
    public function getByReference(string $reference, int $limit = 50): array
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE reference = :reference ORDER BY created_at DESC LIMIT {$limit}";
        return Database::fetchAll($sql, ['reference' => $reference]);
    }
    
    /**
     * Get log by ID
     * 
     * @param int $id Log ID
     * @return array|null Log data or null if not found
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE id = :id";
        $data = Database::fetchOne($sql, ['id' => $id]);
        
        return $data ?: null;
    }
    
    /**
     * Delete old logs
     * 
     * @param int $olderThan Delete logs older than this many seconds
     * @return int Number of deleted logs
     */
    public function deleteOld(int $olderThan = 2592000): int
    {
        $threshold = time() - $olderThan;
        $sql = "DELETE FROM " . self::$table . " WHERE created_at < :threshold";
        $stmt = Database::query($sql, ['threshold' => $threshold]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Format log timestamp
     * 
     * @param int $timestamp Unix timestamp
     * @return string Formatted date and time
     */
    public static function formatTimestamp(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    /**
     * Format log for display
     * 
     * @param array $log Log data
     * @return string Formatted log
     */
    public static function formatLog(array $log): string
    {
        $time = self::formatTimestamp($log['created_at']);
        $type = strtoupper($log['type']);
        
        return "[{$time}] [{$type}] {$log['message']}";
    }
}
