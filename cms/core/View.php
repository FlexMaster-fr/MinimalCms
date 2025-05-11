<?php
/**
 * View Manager
 * 
 * Handles rendering of views
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

class View
{
    /**
     * @var array View data
     */
    private array $data = [];
    
    /**
     * @var string Default layout
     */
    private string $layout = 'main';
    
    /**
     * Set view data
     * 
     * @param string $key Data key
     * @param mixed $value Data value
     * @return self
     */
    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }
    
    /**
     * Set multiple view data values
     * 
     * @param array $data Associative array of data
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    /**
     * Set the layout
     * 
     * @param string $layout Layout name
     * @return self
     */
    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Render a view
     * 
     * @param string $view View path
     * @param array $data Additional data
     * @return void
     */
    public function render(string $view, array $data = []): void
    {
        // Merge data
        $viewData = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($viewData);
        
        // Get content
        ob_start();
        include dirname(__DIR__) . "/app/Views/$view.php";
        $content = ob_get_clean();
        
        // Render with layout
        include dirname(__DIR__) . "/app/Views/layouts/{$this->layout}.php";
    }
    
    /**
     * Render a view without layout
     * 
     * @param string $view View path
     * @param array $data Additional data
     * @return void
     */
    public function renderPartial(string $view, array $data = []): void
    {
        // Merge data
        $viewData = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($viewData);
        
        // Render view
        include dirname(__DIR__) . "/app/Views/$view.php";
    }
    
    /**
     * Get rendered view content
     * 
     * @param string $view View path
     * @param array $data Additional data
     * @return string Rendered content
     */
    public function getContent(string $view, array $data = []): string
    {
        // Merge data
        $viewData = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($viewData);
        
        // Get content
        ob_start();
        include dirname(__DIR__) . "/app/Views/$view.php";
        return ob_get_clean();
    }
    
    /**
     * Escape HTML for safe output
     * 
     * @param mixed $data Data to escape
     * @return string Escaped string
     */
    public function escape(mixed $data): string
    {
        if (is_array($data)) {
            return implode(', ', array_map([$this, 'escape'], $data));
        }
        
        return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Shorthand for escape function
     * 
     * @param mixed $data Data to escape
     * @return string Escaped string
     */
    public function e(mixed $data): string
    {
        return $this->escape($data);
    }
    
    /**
     * Convert Markdown to HTML
     * 
     * @param string $markdown Markdown content
     * @return string HTML content
     */
    public function markdown(string $markdown): string
    {
        // Simple Markdown conversion
        $patterns = [
            '/^#\s+(.+)$/m' => '<h1>$1</h1>',
            '/^##\s+(.+)$/m' => '<h2>$1</h2>',
            '/^###\s+(.+)$/m' => '<h3>$1</h3>',
            '/^####\s+(.+)$/m' => '<h4>$1</h4>',
            '/^#####\s+(.+)$/m' => '<h5>$1</h5>',
            '/^######\s+(.+)$/m' => '<h6>$1</h6>',
            '/\*\*(.+?)\*\*/s' => '<strong>$1</strong>',
            '/\*(.+?)\*/s' => '<em>$1</em>',
            '/\[(.+?)\]\((.+?)\)/' => '<a href="$2">$1</a>',
            '/^-\s+(.+)$/m' => '<li>$1</li>',
            '/^\d+\.\s+(.+)$/m' => '<li>$1</li>',
            '/```(.+?)```/s' => '<pre><code>$1</code></pre>',
            '/`(.+?)`/' => '<code>$1</code>',
        ];
        
        $html = preg_replace(array_keys($patterns), array_values($patterns), $markdown);
        
        // Process lists
        $html = preg_replace_callback('/((?:<li>.+?<\/li>\s*)+)/', function($matches) {
            if (preg_match('/^\d+\./', $matches[0])) {
                return '<ol>' . $matches[0] . '</ol>';
            } else {
                return '<ul>' . $matches[0] . '</ul>';
            }
        }, $html);
        
        // Process paragraphs
        $html = preg_replace('/(?:(?!<h|<p|<ul|<ol|<li|<pre|<code).+?)(?:\n\n|\z)/s', '<p>$0</p>', $html);
        
        return $html;
    }
    
    /**
     * Include a partial view
     * 
     * @param string $partial Partial view path
     * @param array $data Additional data
     * @return void
     */
    public function partial(string $partial, array $data = []): void
    {
        $this->renderPartial($partial, $data);
    }
    
    /**
     * Get language string
     * 
     * @param string $key Language key
     * @param array $replacements Replacements for placeholders
     * @return string Translated string
     */
    public function lang(string $key, array $replacements = []): string
    {
        global $languageManager;
        
        if (isset($languageManager) && $languageManager instanceof LanguageManager) {
            return $languageManager->get($key, $replacements);
        } else {
            // Fallback if language manager is not available
            return $key;
        }
    }
    
    /**
     * Get the current language code
     * 
     * @return string Current language code (default: en)
     */
    public function getCurrentLanguage(): string
    {
        global $languageManager;
        
        if (isset($languageManager) && $languageManager instanceof LanguageManager) {
            return $languageManager->getCurrentLanguage();
        }
        
        // Fallback to default language
        return 'en';
    }
}
