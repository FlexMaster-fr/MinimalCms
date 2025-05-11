<div class="hero">
    <div class="container">
        <div class="hero-content">
            <h1><?= $this->e($title) ?></h1>
            <p class="hero-description"><?= $this->lang('home.description') ?></p>
            
            <form class="search-form" action="/search" method="GET">
                <div class="search-input-group">
                    <input type="text" name="q" class="search-input" placeholder="<?= $this->lang('home.search_placeholder') ?>">
                    <button type="submit" class="search-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
                
                <div class="search-filters">
                    <div class="filter-group">
                        <label for="language"><?= $this->lang('home.filter_language') ?>:</label>
                        <select name="language" id="language">
                            <option value=""><?= $this->lang('home.filter_all') ?></option>
                            <?php foreach ($languages ?? [] as $language): ?>
                                <option value="<?= $this->e($language['name']) ?>"><?= $this->e($language['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="license"><?= $this->lang('home.filter_license') ?>:</label>
                        <select name="license" id="license">
                            <option value=""><?= $this->lang('home.filter_all') ?></option>
                            <?php foreach ($licenses ?? [] as $license): ?>
                                <option value="<?= $this->e($license['license_key']) ?>"><?= $this->e($license['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container">
    <section class="popular-repositories">
        <div class="section-header">
            <h2><?= $this->lang('home.popular_repositories') ?></h2>
            <a href="/repositories/popular" class="view-all"><?= $this->lang('home.view_all') ?></a>
        </div>
        
        <div class="repositories-grid">
            <?php if (empty($popularRepos)): ?>
                <div class="empty-state">
                    <?= $this->lang('home.no_repositories') ?>
                </div>
            <?php else: ?>
                <?php foreach ($popularRepos as $repo): ?>
                    <div class="repository-card">
                        <div class="repo-card-header">
                            <div class="repo-owner">
                                <a href="<?= $repo['owner_type'] === 'User' ? '/user/' : '/org/' ?><?= $this->e($repo['owner_login']) ?>">
                                    <?= $this->e($repo['owner_login']) ?>
                                </a>
                            </div>
                            <h3 class="repo-name">
                                <a href="/repo/<?= $this->e($repo['owner_login']) ?>/<?= $this->e($repo['name']) ?>">
                                    <?= $this->e($repo['name']) ?>
                                </a>
                            </h3>
                        </div>
                        
                        <?php if (!empty($repo['description'])): ?>
                            <p class="repo-description"><?= $this->e($repo['description']) ?></p>
                        <?php endif; ?>
                        
                        <div class="repo-meta">
                            <?php if (!empty($repo['language_name'])): ?>
                                <div class="repo-language">
                                    <span class="language-color" style="background-color: <?= $this->e($repo['language_color'] ?? '#ccc') ?>"></span>
                                    <span><?= $this->e($repo['language_name']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="repo-stars">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <span><?= number_format($repo['stargazers_count']) ?></span>
                            </div>
                            
                            <div class="repo-forks">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M7 18l3-3H6a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3h-4l3 3"></path>
                                </svg>
                                <span><?= number_format($repo['forks_count']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="popular-repositories">
        <div class="section-header">
            <h2><?= $this->lang('home.recent_repositories') ?></h2>
            <a href="/repositories/recent" class="view-all"><?= $this->lang('home.view_all') ?></a>
        </div>
        
        <div class="repositories-grid">
            <?php if (empty($recentRepos)): ?>
                <div class="empty-state">
                    <?= $this->lang('home.no_repositories') ?>
                </div>
            <?php else: ?>
                <?php foreach ($recentRepos as $repo): ?>
                    <div class="repository-card">
                        <div class="repo-card-header">
                            <div class="repo-owner">
                                <a href="<?= $repo['owner_type'] === 'User' ? '/user/' : '/org/' ?><?= $this->e($repo['owner_login']) ?>">
                                    <?= $this->e($repo['owner_login']) ?>
                                </a>
                            </div>
                            <h3 class="repo-name">
                                <a href="/repo/<?= $this->e($repo['owner_login']) ?>/<?= $this->e($repo['name']) ?>">
                                    <?= $this->e($repo['name']) ?>
                                </a>
                            </h3>
                        </div>
                        
                        <?php if (!empty($repo['description'])): ?>
                            <p class="repo-description"><?= $this->e($repo['description']) ?></p>
                        <?php endif; ?>
                        
                        <div class="repo-meta">
                            <?php if (!empty($repo['language_name'])): ?>
                                <div class="repo-language">
                                    <span class="language-color" style="background-color: <?= $this->e($repo['language_color'] ?? '#ccc') ?>"></span>
                                    <span><?= $this->e($repo['language_name']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="repo-stars">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <span><?= number_format($repo['stargazers_count']) ?></span>
                            </div>
                            
                            <div class="repo-forks">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M7 18l3-3H6a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3h-4l3 3"></path>
                                </svg>
                                <span><?= number_format($repo['forks_count']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="explore">
        <div class="section-header">
            <h2><?= $this->lang('home.explore') ?></h2>
        </div>
        
        <div class="explore-grid">
            <a href="/repositories/popular" class="explore-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <h3><?= $this->lang('home.popular_repositories') ?></h3>
                <p class="count"><?= number_format($stats['repositories'] ?? 0) ?> <?= $this->lang('home.repositories') ?></p>
            </a>
            
            <?php
            // Get top languages
            $languages = \Core\Database::fetchAll("SELECT l.name, l.color, COUNT(*) as repo_count 
                                           FROM repositories r
                                           JOIN languages l ON r.language_id = l.id
                                           GROUP BY l.id
                                           ORDER BY repo_count DESC
                                           LIMIT 3");
            
            foreach ($languages as $language): 
            ?>
                <a href="/repositories/language/<?= $this->e($language['name']) ?>" class="explore-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?= $this->e($language['color'] ?? 'currentColor') ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                    <h3><?= $this->e($language['name']) ?></h3>
                    <p class="count"><?= number_format($language['repo_count']) ?> <?= $this->lang('home.repositories') ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>