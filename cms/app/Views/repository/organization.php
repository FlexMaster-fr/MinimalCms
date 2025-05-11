<div class="organization-detail">
    <div class="org-header">
        <div class="org-avatar">
            <?php if (!empty($organization['avatar_url'])): ?>
            <img src="<?= $this->e($organization['avatar_url']) ?>" alt="<?= $this->e($organization['name'] ?? $organization['login']) ?>">
            <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <?php endif; ?>
        </div>
        
        <div class="org-info">
            <h1><?= $this->e($organization['name'] ?? $organization['login']) ?></h1>
            <h2><?= $this->e($organization['login']) ?></h2>
            
            <?php if (!empty($organization['location'])): ?>
            <div class="org-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="11" r="3"></circle></svg>
                <?= $this->e($organization['location']) ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($organization['email'])): ?>
            <div class="org-email">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <a href="mailto:<?= $this->e($organization['email']) ?>"><?= $this->e($organization['email']) ?></a>
            </div>
            <?php endif; ?>
            
            <div class="org-stats">
                <div class="stat-item">
                    <div class="stat-value"><?= number_format($organization['public_repos']) ?></div>
                    <div class="stat-label"><?= $this->e($this->lang('organization.repositories')) ?></div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-value"><?= number_format($organization['followers']) ?></div>
                    <div class="stat-label"><?= $this->e($this->lang('organization.followers')) ?></div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-value"><?= number_format($organization['following']) ?></div>
                    <div class="stat-label"><?= $this->e($this->lang('organization.following')) ?></div>
                </div>
            </div>
            
            <div class="org-links">
                <a href="https://github.com/<?= $this->e($organization['login']) ?>" target="_blank" rel="noopener" class="btn btn-outline">
                    <?= $this->e($this->lang('organization.view_on_github')) ?>
                </a>
            </div>
        </div>
    </div>
    
    <?php if (!empty($organization['description'])): ?>
    <div class="org-description">
        <?= $this->e($organization['description']) ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($popular)): ?>
    <div class="popular-repositories">
        <h2><?= $this->e($this->lang('organization.popular_repositories')) ?></h2>
        
        <div class="repositories-grid">
            <?php foreach ($popular as $repo): ?>
                <repo-card
                    name="<?= $this->e($repo['full_name']) ?>"
                    description="<?= $this->e($repo['description']) ?>"
                    language="<?= $this->e($repo['language_name'] ?? '') ?>"
                    language-color="<?= $this->e($repo['language_color'] ?? '') ?>"
                    stars="<?= $repo['stargazers_count'] ?>"
                    forks="<?= $repo['forks_count'] ?>"
                    updated="<?= date('Y-m-d', $repo['updated_at']) ?>"
                    url="/repo/<?= $this->e($repo['owner_login']) ?>/<?= $this->e($repo['name']) ?>"
                ></repo-card>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="org-repositories">
        <h2><?= $this->e($this->lang('organization.all_repositories')) ?></h2>
        
        <div class="filters-bar">
            <div class="sort-options">
                <label for="sort-select"><?= $this->e($this->lang('organization.sort_by')) ?>:</label>
                <select id="sort-select" class="sort-select">
                    <option value="stars"><?= $this->e($this->lang('organization.sort_stars')) ?></option>
                    <option value="forks"><?= $this->e($this->lang('organization.sort_forks')) ?></option>
                    <option value="updated"><?= $this->e($this->lang('organization.sort_updated')) ?></option>
                    <option value="created"><?= $this->e($this->lang('organization.sort_created')) ?></option>
                </select>
                
                <div class="sort-direction">
                    <button id="sort-asc" class="sort-btn" title="<?= $this->e($this->lang('organization.sort_ascending')) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-up"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                    </button>
                    <button id="sort-desc" class="sort-btn active" title="<?= $this->e($this->lang('organization.sort_descending')) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-down"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="repositories-list" id="repositories-list">
            <?php foreach ($repositories as $repo): ?>
                <div class="repository-item" 
                     data-stars="<?= $repo['stargazers_count'] ?>"
                     data-forks="<?= $repo['forks_count'] ?>"
                     data-updated="<?= $repo['updated_at'] ?>"
                     data-created="<?= $repo['created_at'] ?>">
                    <div class="repo-header">
                        <h3 class="repo-name">
                            <a href="/repo/<?= $this->e($repo['owner_login']) ?>/<?= $this->e($repo['name']) ?>">
                                <?= $this->e($repo['name']) ?>
                            </a>
                        </h3>
                        
                        <div class="repo-stats">
                            <?php if (!empty($repo['language_name'])): ?>
                            <div class="repo-language">
                                <?php if (!empty($repo['language_color'])): ?>
                                <span class="language-color" style="background-color: <?= $this->e($repo['language_color']) ?>"></span>
                                <?php endif; ?>
                                <?= $this->e($repo['language_name']) ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="stat-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <span><?= number_format($repo['stargazers_count']) ?></span>
                            </div>
                            
                            <div class="stat-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-git-branch"><line x1="6" y1="3" x2="6" y2="15"></line><circle cx="18" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><path d="M18 9a9 9 0 0 1-9 9"></path></svg>
                                <span><?= number_format($repo['forks_count']) ?></span>
                            </div>
                            
                            <div class="stat-item">
                                <time datetime="<?= date('c', $repo['updated_at']) ?>">
                                    <?= $this->e($this->lang('organization.updated')) ?> <?= date('Y-m-d', $repo['updated_at']) ?>
                                </time>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($repo['description'])): ?>
                    <div class="repo-description">
                        <?= $this->e($repo['description']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($repositories)): ?>
            <div class="empty-state">
                <p><?= $this->e($this->lang('organization.no_repositories')) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="module" src="/assets/js/components/repo-card.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sort-select');
    const sortAsc = document.getElementById('sort-asc');
    const sortDesc = document.getElementById('sort-desc');
    const reposList = document.getElementById('repositories-list');
    
    // Current sort state
    let currentSort = 'stars';
    let currentDirection = 'desc';
    
    // Sort repositories function
    function sortRepositories() {
        const items = Array.from(reposList.querySelectorAll('.repository-item'));
        
        items.sort((a, b) => {
            let aValue = a.dataset[currentSort];
            let bValue = b.dataset[currentSort];
            
            // Convert to appropriate type for comparison
            if (currentSort === 'stars' || currentSort === 'forks') {
                aValue = parseInt(aValue);
                bValue = parseInt(bValue);
            } else if (currentSort === 'updated' || currentSort === 'created') {
                aValue = parseInt(aValue);
                bValue = parseInt(bValue);
            }
            
            // Compare based on direction
            if (currentDirection === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });
        
        // Reorder items in DOM
        items.forEach(item => {
            reposList.appendChild(item);
        });
    }
    
    // Event listeners
    sortSelect.addEventListener('change', function() {
        currentSort = this.value;
        sortRepositories();
    });
    
    sortAsc.addEventListener('click', function() {
        currentDirection = 'asc';
        sortAsc.classList.add('active');
        sortDesc.classList.remove('active');
        sortRepositories();
    });
    
    sortDesc.addEventListener('click', function() {
        currentDirection = 'desc';
        sortDesc.classList.add('active');
        sortAsc.classList.remove('active');
        sortRepositories();
    });
});
</script>
