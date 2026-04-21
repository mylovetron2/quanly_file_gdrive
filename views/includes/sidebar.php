<?php
/**
 * Sidebar component - Folder tree navigation
 */

// Require FolderManager if not already loaded
if (!class_exists('FolderManager')) {
    require_once APP_ROOT . '/includes/FolderManager.php';
}

$sidebarFolderManager = new FolderManager();

// Get folder tree (already includes file_count and children)
$folderTree = $sidebarFolderManager->getFolderTree();

// Count total folders
function countSidebarFolders($tree) {
    $count = count($tree);
    foreach ($tree as $folder) {
        if (!empty($folder['children'])) {
            $count += countSidebarFolders($folder['children']);
        }
    }
    return $count;
}
$totalFolders = countSidebarFolders($folderTree);

// Render folder tree recursively
function renderSidebarFolderTree($folders, $level = 0) {
    if (empty($folders)) return;
    
    $currentFolderId = isset($_GET['folder']) ? (int)$_GET['folder'] : 0;
    
    foreach ($folders as $folder) {
        $hasChildren = !empty($folder['children']);
        $isActive = $currentFolderId == $folder['id'];
        $indent = str_repeat('&nbsp;&nbsp;', $level);
        
        echo '<li class="sidebar-folder-item ' . ($isActive ? 'active' : '') . '" data-level="' . $level . '">';
        
        if ($hasChildren) {
            echo '<a href="#" class="folder-toggle" data-folder-id="' . $folder['id'] . '" onclick="toggleFolder(this); return false;">';
            echo '<i class="fas fa-chevron-right toggle-icon"></i>';
            echo '</a>';
        } else {
            echo '<span class="folder-spacer"></span>';
        }
        
        echo '<a href="' . APP_URL . '/views/dashboard/files.php?folder=' . $folder['id'] . '" class="folder-link">';
        echo '<i class="fas fa-folder text-warning me-1"></i>';
        echo '<span class="folder-name">' . htmlspecialchars($folder['folder_name']) . '</span>';
        
        if ($folder['file_count'] > 0) {
            echo ' <span class="badge bg-secondary rounded-pill">' . $folder['file_count'] . '</span>';
        }
        
        echo '</a>';
        echo '</li>';
        
        if ($hasChildren) {
            echo '<ul class="sidebar-folder-children" data-parent-id="' . $folder['id'] . '" style="display: none;">';
            renderSidebarFolderTree($folder['children'], $level + 1);
            echo '</ul>';
        }
    }
}
?>

<div class="sidebar" id="folderSidebar">
    <div class="sidebar-header">
        <h6 class="mb-0">
            <i class="fas fa-folder-tree me-2"></i>
            Thư mục
        </h6>
    </div>
    
    <div class="sidebar-body">
        <!-- Root / All Files -->
        <ul class="sidebar-folder-list">
            <li class="sidebar-folder-item <?php echo !isset($_GET['folder']) ? 'active' : ''; ?>">
                <span class="folder-spacer"></span>
                <a href="<?php echo APP_URL; ?>/views/dashboard/files.php" class="folder-link">
                    <i class="fas fa-home text-primary me-1"></i>
                    <span class="folder-name">Tất cả files</span>
                </a>
            </li>
            
            <?php renderSidebarFolderTree($folderTree); ?>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <small class="text-muted">
            <i class="fas fa-folder me-1"></i>
            <?php echo $totalFolders; ?> thư mục
        </small>
    </div>
</div>

<script>
function toggleFolder(element) {
    const icon = element.querySelector('.toggle-icon');
    const folderId = element.getAttribute('data-folder-id');
    const childrenList = document.querySelector('.sidebar-folder-children[data-parent-id="' + folderId + '"]');
    
    if (childrenList) {
        if (childrenList.style.display === 'none') {
            childrenList.style.display = 'block';
            icon.classList.add('rotated');
            element.classList.add('expanded');
        } else {
            childrenList.style.display = 'none';
            icon.classList.remove('rotated');
            element.classList.remove('expanded');
        }
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('folderSidebar');
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Mobile: Toggle show/hide
        sidebar.classList.toggle('show');
        
        // Add/remove overlay
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            overlay.onclick = toggleSidebar;
            document.body.appendChild(overlay);
        }
        overlay.classList.toggle('show');
    } else {
        // Desktop: Toggle collapsed state
        sidebar.classList.toggle('collapsed');
        
        // Save state to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }
}

// Restore sidebar state on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('folderSidebar');
    
    // Only restore collapsed state on desktop
    if (window.innerWidth > 768) {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed && sidebar) {
            sidebar.classList.add('collapsed');
        }
    }
    
    // Auto-expand to current folder
    const activeItem = document.querySelector('.sidebar-folder-item.active');
    if (activeItem) {
        let parent = activeItem.parentElement;
        while (parent) {
            if (parent.classList.contains('sidebar-folder-children')) {
                parent.style.display = 'block';
                const parentId = parent.getAttribute('data-parent-id');
                const toggleBtn = document.querySelector('.folder-toggle[data-folder-id="' + parentId + '"]');
                if (toggleBtn) {
                    const icon = toggleBtn.querySelector('.toggle-icon');
                    if (icon) {
                        icon.classList.add('rotated');
                    }
                    toggleBtn.classList.add('expanded');
                }
            }
            parent = parent.parentElement;
        }
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const isMobile = window.innerWidth <= 768;
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (!isMobile) {
            // Desktop: remove mobile classes
            sidebar.classList.remove('show');
            if (overlay) {
                overlay.classList.remove('show');
            }
        }
    });
});
</script>
