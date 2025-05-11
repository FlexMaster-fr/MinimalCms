<?php
/**
 * Crawler Schedule Model
 * 
 * Manages crawler scheduling and status
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace App\Models;

use Core\Model;
use Core\Database;

class CrawlerSchedule extends Model
{
    /**
     * @var string Table name
     */
    protected static string $table = 'crawler_schedules';
    
    /**
     * @var string Primary key
     */
    protected static string $primaryKey = 'id';
    
    /**
     * Schedule a new crawler run
     * 
     * @param string $type Schedule type (auto or manual)
     * @return int Schedule ID
     */
    public function schedule(string $type = 'auto'): int
    {
        $now = time();
        
        $data = [
            'type' => $type,
            'status' => 'pending',
            'scheduled_at' => $now,
            'started_at' => null,
            'completed_at' => null,
            'created_at' => $now,
            'updated_at' => $now
        ];
        
        return Database::insert(static::$table, $data);
    }
    
    /**
     * Get current active schedule
     * 
     * @return array|null Schedule data or null if none active
     */
    public function getCurrent(): ?array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE status IN ('pending', 'running') ORDER BY scheduled_at ASC LIMIT 1";
        $data = Database::fetchOne($sql);
        
        return $data ?: null;
    }
    
    /**
     * Get a schedule by ID
     * 
     * @param int $id Schedule ID
     * @return array|null Schedule data or null if not found
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE id = :id";
        $data = Database::fetchOne($sql, ['id' => $id]);
        
        return $data ?: null;
    }
    
    /**
     * Get all schedules
     * 
     * @param int $limit Maximum number of schedules to return
     * @return array Array of schedules
     */
    public function getAll(int $limit = 50): array
    {
        $sql = "SELECT * FROM " . static::$table . " ORDER BY scheduled_at DESC LIMIT {$limit}";
        return Database::fetchAll($sql);
    }
    
    /**
     * Update schedule status
     * 
     * @param int $id Schedule ID
     * @param string $status New status
     * @return bool True if updated successfully
     */
    public function updateStatus(int $id, string $status): bool
    {
        $now = time();
        $data = [
            'status' => $status,
            'updated_at' => $now
        ];
        
        // Update timestamp based on status
        if ($status === 'running') {
            $data['started_at'] = $now;
        } elseif ($status === 'completed' || $status === 'failed' || $status === 'stopped') {
            $data['completed_at'] = $now;
        }
        
        return Database::update(static::$table, $data, 'id = :id', ['id' => $id]) > 0;
    }
    
    /**
     * Get next schedule time
     * 
     * @return int|null Timestamp for next schedule or null if none scheduled
     */
    public function getNextScheduleTime(): ?int
    {
        $sql = "SELECT scheduled_at FROM " . static::$table . " WHERE status = 'pending' ORDER BY scheduled_at ASC LIMIT 1";
        $data = Database::fetchOne($sql);
        
        return $data ? (int)$data['scheduled_at'] : null;
    }
    
    /**
     * Check if automatic scheduling is needed
     * 
     * @param int $interval Interval in seconds (default 48 hours)
     * @return bool True if scheduling is needed
     */
    public function needsScheduling(int $interval = 172800): bool
    {
        $now = time();
        
        // Check if there's a pending or running task
        $sql = "SELECT COUNT(*) as count FROM " . static::$table . " WHERE status IN ('pending', 'running')";
        $result = Database::fetchOne($sql);
        
        if ($result && $result['count'] > 0) {
            return false;
        }
        
        // Check when the last completed task finished
        $sql = "SELECT completed_at FROM {$this->table} WHERE status = 'completed' ORDER BY completed_at DESC LIMIT 1";
        $result = Database::fetchOne($sql);
        
        if (!$result || !$result['completed_at']) {
            return true; // No completed tasks, scheduling needed
        }
        
        // Check if interval has passed since last completed task
        return ($now - (int)$result['completed_at']) >= $interval;
    }
    
    /**
     * Create automatic schedule if needed
     * 
     * @param int $interval Interval in seconds (default 48 hours)
     * @return int|null Schedule ID or null if scheduling not needed
     */
    public function createAutoScheduleIfNeeded(int $interval = 172800): ?int
    {
        if ($this->needsScheduling($interval)) {
            return $this->schedule('auto');
        }
        
        return null;
    }
    
    /**
     * Log crawler progress
     * 
     * @param int $id Schedule ID
     * @param string $message Log message
     * @param string $type Log type
     * @return void
     */
    public function log(int $id, string $message, string $type = 'info'): void
    {
        $log = new Log();
        $log->createLog('crawler', $message, $type, 'crawler_' . $id);
    }
}
