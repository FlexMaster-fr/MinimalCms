<?php
// Format repository data for display
$repo = \App\Models\Repository::formatForDisplay($repository);
?>

<div class="repository-detail">
    <div class="repo-header">
        <div class="repo-title">
            <div class="repo-owner">
                <a href="<?= $owner['owner_type'] === 'Organization' ? '/org/' : '/user/' ?><?= $this->e($repo['owner_login']) ?>">
                    <?= $this->e($repo['owner_login']) ?>
                </a>
            </div>
            <h1>
                <a href="<?= $this->e($repo['url'] ?? '/repo/' . $repo['owner_login'] . '/' . $repo['name']) ?>">
                    <?= $this->e($repo['name']) ?>
                </a>
            </h1>
        </div>
        
        <div class="repo-stats">
            <div class="stat-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <span><?= $repo['stars_formatted'] ?></span>
            </div>
            
            <div class="stat-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-git-branch"><line x1="6" y1="3" x2="6" y2="15"></line><circle cx="18" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><path d="M18 9a9 9 0 0 1-9 9"></path></svg>
                <span><?= $repo['forks_formatted'] ?></span>
            </div>
            
            <div class="stat-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <span><?= $repo['watchers_formatted'] ?></span>
            </div>
            
            <div class="stat-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= $repo['issues_formatted'] ?></span>
            </div>
        </div>
    </div>
    
    <div class="repo-meta">
        <?php if (!empty($repo['language_name'])): ?>
        <div class="meta-item">
            <div class="meta-label"><?= $this->e($this->lang('repository.language')) ?>:</div>
            <div class="meta-value">
                <a href="/repositories/language/<?= $this->e($repo['language_name']) ?>" class="language-tag">
                    <?php if (!empty($repo['language_color'])): ?>
                    <span class="language-color" style="background-color: <?= $this->e($repo['language_color']) ?>"></span>
                    <?php endif; ?>
                    <?= $this->e($repo['language_name']) ?>
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($repo['license'])): ?>
        <div class="meta-item">
            <div class="meta-label"><?= $this->e($this->lang('repository.license')) ?>:</div>
            <div class="meta-value">
                <a href="/repositories/license/<?= $this->e($repo['license']['license_key']) ?>" class="license-tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <?= $this->e($repo['license']['name']) ?>
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="meta-item">
            <div class="meta-label"><?= $this->e($this->lang('repository.created')) ?>:</div>
            <div class="meta-value">
                <time datetime="<?= date('c', $repo['created_at']) ?>"><?= $repo['created_at_formatted'] ?></time>
            </div>
        </div>
        
        <div class="meta-item">
            <div class="meta-label"><?= $this->e($this->lang('repository.updated')) ?>:</div>
            <div class="meta-value">
                <time datetime="<?= date('c', $repo['updated_at']) ?>"><?= $repo['updated_at_formatted'] ?></time>
            </div>
        </div>
        
        <?php if (!empty($repo['pushed_at'])): ?>
        <div class="meta-item">
            <div class="meta-label"><?= $this->e($this->lang('repository.pushed')) ?>:</div>
            <div class="meta-value">
                <time datetime="<?= date('c', $repo['pushed_at']) ?>"><?= $repo['pushed_at_formatted'] ?></time>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($repo['topics'])): ?>
    <div class="repo-topics">
        <?php foreach ($repo['topics'] as $topic): ?>
        <a href="/search?q=<?= urlencode($topic) ?>" class="topic-tag">
            <?= $this->e($topic) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="repo-description">
        <?= $this->e($repo['description']) ?>
    </div>
    
    <div class="repo-content">
        <div class="content-tabs">
            <button id="tab-readme" class="tab active"><?= $this->e($this->lang('repository.readme')) ?></button>
            <button id="tab-owner" class="tab"><?= $this->e($this->lang('repository.owner')) ?></button>
        </div>
        
        <div id="content-readme" class="tab-content active">
            <div class="readme-loader">
                <div class="loader"><?= $this->e($this->lang('repository.loading')) ?></div>
            </div>
            <div class="readme-content"></div>
        </div>
        
        <div id="content-owner" class="tab-content">
            <div class="owner-card">
                <div class="owner-header">
                    <?php if (!empty($owner['avatar_url'])): ?>
                    <div class="owner-avatar">
                        <img src="<?= $this->e($owner['avatar_url']) ?>" alt="<?= $this->e($owner['name'] ?? $owner['login']) ?>">
                    </div>
                    <?php endif; ?>
                    
                    <div class="owner-info">
                        <h2><?= $this->e($owner['name'] ?? $owner['login']) ?></h2>
                        <h3><?= $this->e($owner['login']) ?></h3>
                        
                        <?php if (!empty($owner['location'])): ?>
                        <div class="owner-location">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="11" r="3"></circle></svg>
                            <?= $this->e($owner['location']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($owner['bio'])): ?>
                <div class="owner-bio">
                    <?= $this->e($owner['bio']) ?>
                </div>
                <?php endif; ?>
                
                <div class="owner-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($owner['public_repos']) ?></div>
                        <div class="stat-label"><?= $this->e($this->lang('repository.repositories')) ?></div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($owner['followers']) ?></div>
                        <div class="stat-label"><?= $this->e($this->lang('repository.followers')) ?></div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($owner['following']) ?></div>
                        <div class="stat-label"><?= $this->e($this->lang('repository.following')) ?></div>
                    </div>
                </div>
                
                <div class="owner-links">
                    <a href="<?= $owner['owner_type'] === 'Organization' ? '/org/' : '/user/' ?><?= $this->e($owner['login']) ?>" class="btn btn-secondary">
                        <?= $this->e($this->lang('repository.view_profile')) ?>
                    </a>
                    
                    <a href="https://github.com/<?= $this->e($owner['login']) ?>" target="_blank" rel="noopener" class="btn btn-outline">
                        <?= $this->e($this->lang('repository.view_on_github')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($related)): ?>
    <div class="related-repositories">
        <h2><?= $this->e($this->lang('repository.related')) ?></h2>
        
        <div class="repositories-grid">
            <?php foreach ($related as $relatedRepo): ?>
                <repo-card
                    name="<?= $this->e($relatedRepo['full_name']) ?>"
                    description="<?= $this->e($relatedRepo['description']) ?>"
                    language="<?= $this->e($relatedRepo['language_name'] ?? '') ?>"
                    language-color="<?= $this->e($relatedRepo['language_color'] ?? '') ?>"
                    stars="<?= $relatedRepo['stargazers_count'] ?>"
                    forks="<?= $relatedRepo['forks_count'] ?>"
                    updated="<?= date('Y-m-d', $relatedRepo['updated_at']) ?>"
                    url="/repo/<?= $this->e($relatedRepo['owner_login']) ?>/<?= $this->e($relatedRepo['name']) ?>"
                ></repo-card>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script type="module" src="/assets/js/components/repo-card.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const owner = '<?= $this->e($repo['owner_login']) ?>';
    const repoName = '<?= $this->e($repo['name']) ?>';
    
    // Load README
    fetch(`/api/repository/${owner}/${repoName}/readme`)
        .then(response => response.json())
        .then(data => {
            document.querySelector('.readme-loader').style.display = 'none';
            document.querySelector('.readme-content').innerHTML = data.html || '<div class="no-readme"><?= $this->e($this->lang('repository.no_readme')) ?></div>';
        })
        .catch(error => {
            document.querySelector('.readme-loader').style.display = 'none';
            document.querySelector('.readme-content').innerHTML = '<div class="error"><?= $this->e($this->lang('repository.error_loading_readme')) ?></div>';
            console.error('Error loading README:', error);
        });
    
    // Tab switching
    const tabs = document.querySelectorAll('.content-tabs .tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            const contentId = 'content-' + this.id.split('-')[1];
            document.getElementById(contentId).classList.add('active');
        });
    });
});
</script>
