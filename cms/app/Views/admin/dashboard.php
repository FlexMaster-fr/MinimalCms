<div class="admin-dashboard">
    <h1><?= $this->e($this->lang('admin.dashboard_title')) ?></h1>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['repositories']) ?></div>
            <div class="stat-label"><?= $this->e($this->lang('admin.stats_repositories')) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['organizations']) ?></div>
            <div class="stat-label"><?= $this->e($this->lang('admin.stats_organizations')) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['users']) ?></div>
            <div class="stat-label"><?= $this->e($this->lang('admin.stats_users')) ?></div>
        </div>
    </div>
    
    <div class="admin-sections">
        <div class="admin-section">
            <h2><?= $this->e($this->lang('admin.crawler_management')) ?></h2>
            
            <div class="crawler-status">
                <?php if (isset($schedule) && $schedule): ?>
                    <div class="status-card">
                        <h3><?= $this->e($this->lang('admin.crawler_status')) ?></h3>
                        <div class="status-info">
                            <p><strong><?= $this->e($this->lang('admin.status')) ?>:</strong> 
                                <span class="status-badge status-<?= $schedule['status'] ?>">
                                    <?= $this->e(ucfirst($schedule['status'])) ?>
                                </span>
                            </p>
                            <p><strong><?= $this->e($this->lang('admin.type')) ?>:</strong> <?= $schedule['type'] ?></p>
                            <p><strong><?= $this->e($this->lang('admin.scheduled_at')) ?>:</strong> <?= date('Y-m-d H:i:s', $schedule['scheduled_at']) ?></p>
                            
                            <?php if ($schedule['started_at']): ?>
                                <p><strong><?= $this->e($this->lang('admin.started_at')) ?>:</strong> <?= date('Y-m-d H:i:s', $schedule['started_at']) ?></p>
                            <?php endif; ?>
                            
                            <?php if ($schedule['completed_at']): ?>
                                <p><strong><?= $this->e($this->lang('admin.completed_at')) ?>:</strong> <?= date('Y-m-d H:i:s', $schedule['completed_at']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="status-card">
                        <h3><?= $this->e($this->lang('admin.crawler_status')) ?></h3>
                        <p><?= $this->e($this->lang('admin.no_active_crawler')) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="crawler-actions">
                <h3><?= $this->e($this->lang('admin.crawler_actions')) ?></h3>
                
                <form id="run-crawler-form" method="POST" action="/admin/crawler/run">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <button type="submit" class="btn btn-primary">
                        <?= $this->e($this->lang('admin.run_crawler')) ?>
                    </button>
                </form>
                
                <form id="schedule-crawler-form" method="POST" action="/admin/crawler/schedule">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="form-group">
                        <label for="schedule_type"><?= $this->e($this->lang('admin.schedule_type')) ?></label>
                        <select name="schedule_type" id="schedule_type">
                            <option value="auto"><?= $this->e($this->lang('admin.schedule_auto')) ?></option>
                            <option value="manual"><?= $this->e($this->lang('admin.schedule_manual')) ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <?= $this->e($this->lang('admin.schedule_crawler')) ?>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="admin-section">
            <h2><?= $this->e($this->lang('admin.logs')) ?></h2>
            
            <div class="logs-nav">
                <button class="btn btn-tab active" data-log-type="crawler"><?= $this->e($this->lang('admin.crawler_logs')) ?></button>
                <button class="btn btn-tab" data-log-type="github_api"><?= $this->e($this->lang('admin.api_logs')) ?></button>
                <button class="btn btn-tab" data-log-type="admin"><?= $this->e($this->lang('admin.admin_logs')) ?></button>
            </div>
            
            <div class="logs-container" id="logs-container">
                <div class="loader">Loading...</div>
                <div class="logs-list"></div>
            </div>
        </div>
    </div>
    
    <div class="recent-logs">
        <h2><?= $this->e($this->lang('admin.recent_activity')) ?></h2>
        
        <table class="logs-table">
            <thead>
                <tr>
                    <th><?= $this->e($this->lang('admin.time')) ?></th>
                    <th><?= $this->e($this->lang('admin.type')) ?></th>
                    <th><?= $this->e($this->lang('admin.message')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= date('Y-m-d H:i:s', $log['created_at']) ?></td>
                    <td><?= $this->e($log['type']) ?></td>
                    <td><?= $this->e($log['message']) ?></td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="3" class="text-center"><?= $this->e($this->lang('admin.no_logs')) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="/assets/js/admin.js"></script>
