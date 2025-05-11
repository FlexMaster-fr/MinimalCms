<!DOCTYPE html>
<html lang="<?= $this->lang('meta.lang_code', [], 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'GitHub Repository Crawler') ?></title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
                        </svg>
                        GitHub Repository Crawler
                    </a>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="/" class="<?= \Core\Router::isRoute('home.index') ? 'active' : '' ?>"><?= $this->lang('nav.home') ?></a></li>
                        <li><a href="/repositories/recent" class="<?= \Core\Router::isRoute('home.recent') ? 'active' : '' ?>"><?= $this->lang('nav.recent') ?></a></li>
                        <li><a href="/repositories/popular" class="<?= \Core\Router::isRoute('home.popular') ? 'active' : '' ?>"><?= $this->lang('nav.popular') ?></a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <form class="mini-search" action="/search" method="GET">
                        <input type="text" name="q" placeholder="<?= $this->lang('nav.search') ?>">
                        <button type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </button>
                    </form>
                    
                    <div class="language-selector">
                        <select onchange="window.location.href = '?lang=' + this.value">
                            <option value="en" <?= $this->getCurrentLanguage() === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="fr" <?= $this->getCurrentLanguage() === 'fr' ? 'selected' : '' ?>>Français</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <main>
        <?= $content ?>
    </main>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <div class="footer-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
                        </svg>
                        GitHub Repository Crawler
                    </div>
                    <p>
                        <?= $this->lang('footer.description', [], 'A tool to search and explore GitHub repositories') ?>
                    </p>
                </div>
                
                <div class="footer-links">
                    <div class="footer-nav">
                        <h3><?= $this->lang('footer.navigation') ?></h3>
                        <ul>
                            <li><a href="/"><?= $this->lang('footer.home') ?></a></li>
                            <li><a href="/repositories/recent"><?= $this->lang('footer.recent') ?></a></li>
                            <li><a href="/repositories/popular"><?= $this->lang('footer.popular') ?></a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-nav">
                        <h3><?= $this->lang('footer.resources') ?></h3>
                        <ul>
                            <li><a href="https://github.com" target="_blank"><?= $this->lang('footer.github') ?></a></li>
                            <li><a href="/api/docs"><?= $this->lang('footer.api_docs') ?></a></li>
                            <li><a href="/admin/login"><?= $this->lang('footer.admin') ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> GitHub Repository Crawler</p>
            </div>
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any JavaScript components here
        });
    </script>
</body>
</html>