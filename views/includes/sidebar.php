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
        <button class="btn btn-sm btn-link" onclick="toggleSidebar()" title="Thu gọn">
            <i class="fas fa-chevron-left"></i>
        </button>
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

<style>
.sidebar {
    position: fixed;
    left: 0;
    top: 56px; /* Height of navbar */
    width: 280px;
    height: calc(100vh - 56px);
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
    overflow-y: auto;
    z-index: 1000;
    transition: transform 0.3s ease;
}

.sidebar.collapsed {
    transform: translateX(-280px);
}

.sidebar-header {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
    background: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
}

.sidebar-body {
    padding: 10px 0;
}

.sidebar-footer {
    padding: 10px 15px;
    border-top: 1px solid #dee2e6;
    background: white;
    position: sticky;
    bottom: 0;
}

.sidebar-folder-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-folder-item {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    margin: 2px 5px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.sidebar-folder-item:hover {
    background-color: #e9ecef;
}

.sidebar-folder-item.active {
    background-color: #e7f1ff;
    border-left: 3px solid #0d6efd;
}

.folder-toggle {
    width: 20px;
    height: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #6c757d;
    margin-right: 2px;
}

.folder-toggle:hover .toggle-icon {
    color: #0d6efd;
}

.toggle-icon {
    font-size: 10px;
    transition: transform 0.2s;
}

.toggle-icon.rotated {
    transform: rotate(90deg);
}

.folder-spacer {
    width: 20px;
    display: inline-block;
}

.folder-link {
    flex: 1;
    text-decoration: none;
    color: #212529;
    padding: 5px 8px;
    border-radius: 3px;
    display: flex;
    align-items: center;
    font-size: 14px;
}

.folder-link:hover {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.folder-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.sidebar-folder-children {
    list-style: none;
    padding: 0;
    margin: 0;
}

/* Main content adjustment when sidebar is visible */
.main-content-with-sidebar {
    margin-left: 290px;
    transition: margin-left 0.3s ease;
}

.main-content-with-sidebar.sidebar-collapsed {
    margin-left: 10px;
}

/* Toggle button for collapsed sidebar */
.sidebar-toggle-btn {
    position: fixed;
    left: 10px;
    top: 70px;
    z-index: 999;
    display: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    opacity: 0;
    transition: opacity 0.3s ease, transform 0.3s ease;
    transform: scale(0.8);
}

.sidebar-toggle-btn.show {
    display: block;
    opacity: 1;
    transform: scale(1);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(-20px) scale(0.8);
        opacity: 0;
    }
    to {
        transform: translateX(0) scale(1);
        opacity: 1;
    }
}

.sidebar-toggle-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-280px);
    }
    
    .sidebar.mobile-open {
        transform: translateX(0);
    }
    
    .main-content-with-sidebar {
        margin-left: 10px;
    }
    
    .sidebar-toggle-btn.show {
        display: block !important;
    }
}
</style>

<script>
function toggleFolder(element) {
    const icon = element.querySelector('.toggle-icon');
    const folderId = element.getAttribute('data-folder-id');
    const childrenList = document.querySelector('.sidebar-folder-children[data-parent-id="' + folderId + '"]');
    
    if (childrenList) {
        if (childrenList.style.display === 'none') {
            childrenList.style.display = 'block';
            icon.classList.add('rotated');
        } else {
            childrenList.style.display = 'none';
            icon.classList.remove('rotated');
        }
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('folderSidebar');
    const mainContent = document.querySelector('.main-content-with-sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
    const toggleIcon = toggleBtn ? toggleBtn.querySelector('i') : null;
    
    sidebar.classList.toggle('collapsed');
    if (mainContent) {
        mainContent.classList.toggle('sidebar-collapsed');
    }
    
    // Toggle button visibility, icon, and tooltip
    if (toggleBtn) {
        if (sidebar.classList.contains('collapsed')) {
            toggleBtn.classList.add('show');
            toggleBtn.setAttribute('title', 'Mở menu thư mục');
            if (toggleIcon) {
                toggleIcon.className = 'fas fa-chevron-right';
            }
        } else {
            toggleBtn.classList.remove('show');
            toggleBtn.setAttribute('title', 'Thu gọn menu');
            if (toggleIcon) {
                toggleIcon.className = 'fas fa-chevron-left';
            }
        }
    }
    
    // Save state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Restore sidebar state on page load
document.addEventListener('DOMContentLoaded', function() {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    const sidebar = document.getElementById('folderSidebar');
    const mainContent = document.querySelector('.main-content-with-sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
    
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        if (mainContent) {
            mainContent.classList.add('sidebar-collapsed');
        }
        if (toggleBtn) {
            toggleBtn.classList.add('show');
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
                    toggleBtn.querySelector('.toggle-icon').classList.add('rotated');
                }
            }
            parent = parent.parentElement;
        }
    }
});
</script>

<!-- Toggle button for collapsed sidebar -->
<button class="btn btn-primary sidebar-toggle-btn" onclick="toggleSidebar()" title="Mở menu thư mục">
    <i class="fas fa-chevron-right"></i>
</button>
