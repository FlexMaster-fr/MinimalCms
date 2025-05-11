<?php
/**
 * Hook Manager
 * 
 * Implements a hook system for extensibility
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

class HookManager
{
    /**
     * @var array Registered hooks
     */
    private array $hooks = [];
    
    /**
     * @var array Registered filters
     */
    private array $filters = [];
    
    /**
     * @var self|null Singleton instance
     */
    private static ?self $instance = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        self::$instance = $this;
    }
    
    /**
     * Get the singleton instance
     * 
     * @return self Singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Add a hook
     * 
     * @param string $hookName Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority (lower numbers run first, default: 10)
     * @return void
     */
    public function addHook(string $hookName, callable $callback, int $priority = 10): void
    {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }
        
        if (!isset($this->hooks[$hookName][$priority])) {
            $this->hooks[$hookName][$priority] = [];
        }
        
        $this->hooks[$hookName][$priority][] = $callback;
    }
    
    /**
     * Run hooks
     * 
     * @param string $hookName Hook name
     * @param array $args Arguments to pass to hook callbacks
     * @return void
     */
    public function runHooks(string $hookName, array $args = []): void
    {
        if (!isset($this->hooks[$hookName])) {
            return;
        }
        
        // Sort by priority
        ksort($this->hooks[$hookName]);
        
        foreach ($this->hooks[$hookName] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }
    
    /**
     * Add a filter
     * 
     * @param string $filterName Filter name
     * @param callable $callback Callback function
     * @param int $priority Priority (lower numbers run first, default: 10)
     * @return void
     */
    public function addFilter(string $filterName, callable $callback, int $priority = 10): void
    {
        if (!isset($this->filters[$filterName])) {
            $this->filters[$filterName] = [];
        }
        
        if (!isset($this->filters[$filterName][$priority])) {
            $this->filters[$filterName][$priority] = [];
        }
        
        $this->filters[$filterName][$priority][] = $callback;
    }
    
    /**
     * Apply filters
     * 
     * @param string $filterName Filter name
     * @param mixed $value Value to filter
     * @param array $args Additional arguments to pass to filter callbacks
     * @return mixed Filtered value
     */
    public function applyFilter(string $filterName, $value, array $args = []): mixed
    {
        if (!isset($this->filters[$filterName])) {
            return $value;
        }
        
        // Sort by priority
        ksort($this->filters[$filterName]);
        
        // Add value to beginning of args
        array_unshift($args, $value);
        
        foreach ($this->filters[$filterName] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $args[0] = call_user_func_array($callback, $args);
            }
        }
        
        return $args[0];
    }
    
    /**
     * Check if a hook exists
     * 
     * @param string $hookName Hook name
     * @return bool True if hook exists
     */
    public function hasHook(string $hookName): bool
    {
        return isset($this->hooks[$hookName]) && !empty($this->hooks[$hookName]);
    }
    
    /**
     * Check if a filter exists
     * 
     * @param string $filterName Filter name
     * @return bool True if filter exists
     */
    public function hasFilter(string $filterName): bool
    {
        return isset($this->filters[$filterName]) && !empty($this->filters[$filterName]);
    }
    
    /**
     * Remove a hook
     * 
     * @param string $hookName Hook name
     * @param callable|null $callback Specific callback to remove (or all if null)
     * @param int|null $priority Specific priority to remove from (or all if null)
     * @return void
     */
    public function removeHook(string $hookName, ?callable $callback = null, ?int $priority = null): void
    {
        if (!isset($this->hooks[$hookName])) {
            return;
        }
        
        if ($callback === null && $priority === null) {
            // Remove all hooks for this name
            unset($this->hooks[$hookName]);
            return;
        }
        
        if ($callback === null) {
            // Remove all hooks for this priority
            unset($this->hooks[$hookName][$priority]);
            return;
        }
        
        if ($priority === null) {
            // Remove this callback from all priorities
            foreach ($this->hooks[$hookName] as $p => $callbacks) {
                $this->hooks[$hookName][$p] = array_filter($callbacks, function($cb) use ($callback) {
                    return $cb !== $callback;
                });
                
                if (empty($this->hooks[$hookName][$p])) {
                    unset($this->hooks[$hookName][$p]);
                }
            }
            
            return;
        }
        
        // Remove specific callback at specific priority
        if (isset($this->hooks[$hookName][$priority])) {
            $this->hooks[$hookName][$priority] = array_filter($this->hooks[$hookName][$priority], function($cb) use ($callback) {
                return $cb !== $callback;
            });
            
            if (empty($this->hooks[$hookName][$priority])) {
                unset($this->hooks[$hookName][$priority]);
            }
        }
    }
    
    /**
     * Remove a filter
     * 
     * @param string $filterName Filter name
     * @param callable|null $callback Specific callback to remove (or all if null)
     * @param int|null $priority Specific priority to remove from (or all if null)
     * @return void
     */
    public function removeFilter(string $filterName, ?callable $callback = null, ?int $priority = null): void
    {
        if (!isset($this->filters[$filterName])) {
            return;
        }
        
        if ($callback === null && $priority === null) {
            // Remove all filters for this name
            unset($this->filters[$filterName]);
            return;
        }
        
        if ($callback === null) {
            // Remove all filters for this priority
            unset($this->filters[$filterName][$priority]);
            return;
        }
        
        if ($priority === null) {
            // Remove this callback from all priorities
            foreach ($this->filters[$filterName] as $p => $callbacks) {
                $this->filters[$filterName][$p] = array_filter($callbacks, function($cb) use ($callback) {
                    return $cb !== $callback;
                });
                
                if (empty($this->filters[$filterName][$p])) {
                    unset($this->filters[$filterName][$p]);
                }
            }
            
            return;
        }
        
        // Remove specific callback at specific priority
        if (isset($this->filters[$filterName][$priority])) {
            $this->filters[$filterName][$priority] = array_filter($this->filters[$filterName][$priority], function($cb) use ($callback) {
                return $cb !== $callback;
            });
            
            if (empty($this->filters[$filterName][$priority])) {
                unset($this->filters[$filterName][$priority]);
            }
        }
    }
}