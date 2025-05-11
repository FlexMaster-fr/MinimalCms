<?php
/**
 * Theme Manager
 * 
 * Handles theme management and assets
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

class ThemeManager
{
    /**
     * @var string Current theme name
     */
    private string $theme;
    
    /**
     * @var array Available themes
     */
    private array $availableThemes = ['default', 'dark'];
    
    /**
     * @var array Theme assets (CSS, JS)
     */
    private array $assets = [
        'css' => [],
        'js' => []
    ];
    
    /**
     * Constructor
     * 
     * @param string $theme Theme name (default: default)
     */
    public function __construct(string $theme = 'default')
    {
        // Validate theme
        $this->theme = in_array($theme, $this->availableThemes) ? $theme : 'default';
    }
    
    /**
     * Get the current theme
     * 
     * @return string Current theme name
     */
    public function getCurrentTheme(): string
    {
        return $this->theme;
    }
    
    /**
     * Set the current theme
     * 
     * @param string $theme Theme name
     * @return self For method chaining
     */
    public function setTheme(string $theme): self
    {
        if (in_array($theme, $this->availableThemes)) {
            $this->theme = $theme;
        }
        
        return $this;
    }
    
    /**
     * Get available themes
     * 
     * @return array Available theme names
     */
    public function getAvailableThemes(): array
    {
        return $this->availableThemes;
    }
    
    /**
     * Add a CSS asset
     * 
     * @param string $path CSS file path or URL
     * @param string $media Media type (default: all)
     * @return self For method chaining
     */
    public function addCss(string $path, string $media = 'all'): self
    {
        $this->assets['css'][] = [
            'path' => $this->assetPath($path),
            'media' => $media
        ];
        
        return $this;
    }
    
    /**
     * Add a JavaScript asset
     * 
     * @param string $path JavaScript file path or URL
     * @param bool $defer Whether to defer loading (default: false)
     * @return self For method chaining
     */
    public function addJs(string $path, bool $defer = false): self
    {
        $this->assets['js'][] = [
            'path' => $this->assetPath($path),
            'defer' => $defer
        ];
        
        return $this;
    }
    
    /**
     * Render CSS assets
     * 
     * @return string HTML for CSS assets
     */
    public function renderCss(): string
    {
        $html = '';
        
        foreach ($this->assets['css'] as $css) {
            $html .= '<link rel="stylesheet" href="' . $css['path'] . '" media="' . $css['media'] . '">' . PHP_EOL;
        }
        
        return $html;
    }
    
    /**
     * Render JavaScript assets
     * 
     * @return string HTML for JavaScript assets
     */
    public function renderJs(): string
    {
        $html = '';
        
        foreach ($this->assets['js'] as $js) {
            $defer = $js['defer'] ? ' defer' : '';
            $html .= '<script src="' . $js['path'] . '"' . $defer . '></script>' . PHP_EOL;
        }
        
        return $html;
    }
    
    /**
     * Get asset path based on the current theme
     * 
     * @param string $path Asset path
     * @return string Full asset path
     */
    private function assetPath(string $path): string
    {
        // If path is a URL, return as is
        if (strpos($path, '://') !== false) {
            return $path;
        }
        
        // If path starts with /, it's relative to assets directory
        if (substr($path, 0, 1) === '/') {
            return '/assets' . $path;
        }
        
        // Otherwise, prepend theme directory
        return '/assets/themes/' . $this->theme . '/' . $path;
    }
    
    /**
     * Get theme view path
     * 
     * @param string $view View path (relative to theme directory)
     * @return string Full view path
     */
    public function getViewPath(string $view): string
    {
        $themePath = dirname(__DIR__) . '/app/Views/themes/' . $this->theme . '/' . $view . '.php';
        
        // If theme-specific view exists, use it
        if (file_exists($themePath)) {
            return $themePath;
        }
        
        // Otherwise fallback to default view
        return dirname(__DIR__) . '/app/Views/' . $view . '.php';
    }
}