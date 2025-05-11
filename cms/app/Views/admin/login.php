<div class="admin-login">
    <div class="login-container">
        <h1><?= $this->e($this->lang('admin_login_title')) ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= $this->e($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/admin/login">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="form-group">
                <label for="username"><?= $this->e($this->lang('admin_username')) ?></label>
                <input type="text" id="username" name="username" required class="form-control" autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password"><?= $this->e($this->lang('admin_password')) ?></label>
                <input type="password" id="password" name="password" required class="form-control" autocomplete="current-password">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block"><?= $this->e($this->lang('admin_login_button')) ?></button>
            </div>
        </form>
        
        <div class="back-link">
            <a href="/">&larr; <?= $this->e($this->lang('admin_back_to_site')) ?></a>
        </div>
    </div>
</div>
