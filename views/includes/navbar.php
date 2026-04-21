<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>">
            <i class="fab fa-google-drive me-2"></i><?php echo APP_NAME; ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/views/dashboard/index.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                
                <?php if ($permission->can('file.view')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/views/dashboard/files.php">
                        <i class="fas fa-file"></i> Files
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($permission->can('folder.manage')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/views/dashboard/folders.php">
                        <i class="fas fa-folder"></i> Folders
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($auth->isAdmin()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/views/admin/users.php">Quản lý người dùng</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/views/admin/roles.php">Quản lý vai trò</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/views/admin/permissions.php">Phân quyền</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/views/admin/drive-account.php"><i class="fab fa-google-drive text-primary"></i> Google Drive Account</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/views/admin/logs.php">Lịch sử hoạt động</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- Search Form -->
            <form class="d-flex me-3" action="<?php echo APP_URL; ?>/views/dashboard/search.php" method="GET">
                <input class="form-control me-2" type="search" name="q" placeholder="Tìm kiếm file..." required>
                <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <!-- User Menu -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['full_name'] ?? $_SESSION['username']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/views/auth/profile.php">Thông tin cá nhân</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/views/auth/change-password.php">Đổi mật khẩu</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/api/logout.php">Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
