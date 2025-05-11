<?php
/**
 * Language Manager
 * 
 * Handles translation and internationalization
 * 
 * @package GitHubCrawler
 * @author AI Assistant
 */

namespace Core;

class LanguageManager
{
    /**
     * @var string Current language code
     */
    private string $language;
    
    /**
     * @var array Loaded language strings
     */
    private array $strings = [];
    
    /**
     * @var array Available languages
     */
    private array $availableLanguages = ['en', 'fr'];
    
    /**
     * Constructor
     * 
     * @param string $language Language code (default: en)
     */
    public function __construct(string $language = 'en')
    {
        // Validate language
        $this->language = in_array($language, $this->availableLanguages) ? $language : 'en';
        
        // Load language file
        $this->loadLanguageFile();
    }
    
    /**
     * Get a translation string
     * 
     * @param string $key Translation key (dot notation supported)
     * @param array $params Parameters to replace in the string
     * @param string|null $fallback Fallback string if key not found
     * @return string Translated string
     */
    public function get(string $key, array $params = [], ?string $fallback = null): string
    {
        // Get string from the current language
        $string = $this->getString($key);
        
        // Use fallback if string not found
        if ($string === null) {
            // If no fallback provided, use the key
            if ($fallback === null) {
                return $key;
            }
            
            return $fallback;
        }
        
        // Replace parameters
        foreach ($params as $name => $value) {
            $string = str_replace(':' . $name, $value, $string);
        }
        
        return $string;
    }
    
    /**
     * Get a translation string (shorthand function)
     * 
     * @param string $key Translation key
     * @param array $params Parameters to replace in the string
     * @return string Translated string
     */
    public function __($key, array $params = []): string
    {
        return $this->get($key, $params);
    }
    
    /**
     * Get the current language
     * 
     * @return string Current language code
     */
    public function getCurrentLanguage(): string
    {
        return $this->language;
    }
    
    /**
     * Set the current language
     * 
     * @param string $language Language code
     * @return self For method chaining
     */
    public function setLanguage(string $language): self
    {
        if (in_array($language, $this->availableLanguages)) {
            $this->language = $language;
            $this->loadLanguageFile();
        }
        
        return $this;
    }
    
    /**
     * Get available languages
     * 
     * @return array Available language codes
     */
    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }
    
    /**
     * Load language file for the current language
     * 
     * @return void
     */
    private function loadLanguageFile(): void
    {
        $langFile = dirname(__DIR__) . '/languages/' . $this->language . '.php';
        
        if (file_exists($langFile)) {
            $this->strings = require $langFile;
        } else {
            // Fallback to English
            $fallbackFile = dirname(__DIR__) . '/languages/en.php';
            $this->strings = file_exists($fallbackFile) ? require $fallbackFile : [];
        }
    }
    
    /**
     * Get a string from the loaded language
     * 
     * @param string $key String key (dot notation supported)
     * @return string|null String value or null if not found
     */
    private function getString(string $key): ?string
    {
        // If key contains dots, traverse the array
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $value = $this->strings;
            
            foreach ($parts as $part) {
                if (!isset($value[$part])) {
                    return null;
                }
                
                $value = $value[$part];
            }
            
            return is_string($value) ? $value : null;
        }
        
        // Direct key lookup
        return $this->strings[$key] ?? null;
    }
}