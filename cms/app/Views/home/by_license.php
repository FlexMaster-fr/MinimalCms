<div class="container">
    <div class="page-header">
        <h1><?= $this->lang('search.title') ?> - <?= $this->e($license['name'] ?? '') ?></h1>
    </div>
    
    <div class="search-results">
        <?php if (empty($repositories)): ?>
            <div class="no-results">
                <p><?= $this->lang('search.no_results') ?></p>
            </div>
        <?php else: ?>
            <div class="repositories-list">
                <?php foreach ($repositories as $repo): ?>
                    <div class="repository-card">
                        <div class="repository-header">
                            <h3>
                                <a href="/repo/<?= $this->e($repo['owner_login']) ?>/<?= $this->e($repo['name']) ?>">
                                    <?= $this->e($repo['full_name']) ?>
                                </a>
                            </h3>
                            <div class="repository-stats">
                                <span class="stars" title="Stars">
                                    <i class="icon-star"></i> <?= number_format($repo['stargazers_count']) ?>
                                </span>
                                <span class="forks" title="Forks">
                                    <i class="icon-fork"></i> <?= number_format($repo['forks_count']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if (!empty($repo['description'])): ?>
                            <p class="repository-description"><?= $this->e($repo['description']) ?></p>
                        <?php endif; ?>
                        
                        <div class="repository-meta">
                            <?php if (!empty($repo['language_name'])): ?>
                                <span class="language">
                                    <span class="language-color" style="background-color: <?= $this->e($repo['language_color'] ?? '#ccc') ?>"></span>
                                    <?= $this->e($repo['language_name']) ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($repo['license_name'])): ?>
                                <span class="license">
                                    <i class="icon-law"></i> <?= $this->e($repo['license_name']) ?>
                                </span>
                            <?php endif; ?>
                            
                            <span class="updated-at">
                                <?= $this->lang('repository.updated') ?> <?= date('Y-m-d', $repo['updated_at'] ?? time()) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (isset($pagination)): ?>
                <div class="pagination">
                    <?php if ($pagination['current'] > 1): ?>
                        <a href="?page=<?= $pagination['current'] - 1 ?>" class="prev-page">
                            <?= $this->lang('search.prev_page') ?>
                        </a>
                    <?php endif; ?>
                    
                    <span class="page-info">
                        <?= str_replace(
                            [':start', ':end', ':total'],
                            [
                                $pagination['start'],
                                $pagination['end'],
                                $pagination['total_results']
                            ],
                            $this->lang('search.showing_results')
                        ) ?>
                    </span>
                    
                    <?php if ($pagination['current'] < $pagination['total']): ?>
                        <a href="?page=<?= $pagination['current'] + 1 ?>" class="next-page">
                            <?= $this->lang('search.next_page') ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>