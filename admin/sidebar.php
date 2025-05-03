<?php
// Get current page for highlighting active menu item
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar-container">
    <!-- Mobile Toggle Button -->
    <button id="sidebar-toggle-mobile" class="sidebar-toggle-mobile">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Sidebar Overlay (for mobile) -->
    <div id="sidebar-overlay" class="sidebar-overlay"></div>
    
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="https://play-lh.googleusercontent.com/zyANBcOcKYD-LTnA1Bkedxj3Gz37YXnlzeanJTF8Rt9LLp2JPWxlwA76FRoX4N6kVJo=w240-h480-rw" alt="Gym Logo" class="logo-img">
                <span class="logo-text">GYM POS</span>
            </div>
            <button id="sidebar-toggle" class="sidebar-toggle">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>
        

        
        <div class="sidebar-menu ">
            <div class="menu-category">
                <span class="category-title">MAIN</span>
                <ul class="menu-items">
                    <li class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                        <a href="dashboard.php">
                            <i class="bi bi-speedometer2"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $current_page == 'members.php' ? 'active' : ''; ?>">
                        <a href="members.php">
                            <i class="bi bi-people"></i>
                            <span class="menu-text">Members</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $current_page == 'add-member.php' ? 'active' : ''; ?>">
                        <a href="add-member.php">
                            <i class="bi bi-person-plus"></i>
                            <span class="menu-text">Add Member</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="menu-category">
                <span class="category-title">CAFE</span>
                <ul class="menu-items">
                    <li class="menu-item <?php echo $current_page == 'cafe-pos-system.php' ? 'active' : ''; ?>">
                        <a href="cafe-pos-system.php">
                            <i class="bi bi-cart-check"></i>
                            <span class="menu-text">POS System</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $current_page == 'cafe-products.php' ? 'active' : ''; ?>">
                        <a href="cafe-products.php">
                            <i class="bi bi-box-seam"></i>
                            <span class="menu-text">Products</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $current_page == 'cafe-orders.php' ? 'active' : ''; ?>">
                        <a href="cafe-orders.php">
                            <i class="bi bi-receipt"></i>
                            <span class="menu-text">Orders</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="menu-category">
                <span class="category-title">Communication</span>
                <ul class="menu-items">
                    <li class="menu-item <?php echo $current_page == 'send-sms.php' ? 'active' : ''; ?>">
                        <a href="send-sms.php">
                            <i class="bi bi-chat-dots"></i>
                            <span class="menu-text">Send SMS</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo $current_page == 'sms-settings.php' ? 'active' : ''; ?>">
                        <a href="sms-settings.php">
                            <i class="bi bi-gear"></i>
                            <span class="menu-text">SMS Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="menu-category">
                <span class="category-title">System</span>
                <ul class="menu-items">
                    <li class="menu-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                        <a href="settings.php">
                            <i class="bi bi-sliders"></i>
                            <span class="menu-text">Settings</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="menu-text">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>

<style>
:root {
    --sidebar-width: 260px;
    --sidebar-collapsed-width: 70px;
    --sidebar-bg: #2C2C2C;
    --sidebar-hover: #3a3a3a;
    --sidebar-active: #6c757d;
    --sidebar-text: #f8f9fa;
    --sidebar-muted: #adb5bd;
    --sidebar-transition: all 0.3s ease;
    --sidebar-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    --sidebar-border: 1px solid #3a3a3a;
    --sidebar-icon-size: 1.2rem;
    --sidebar-header-height: 10px;
    --sidebar-header-bg: #5D4037; /* Brown header background */
}

/* Sidebar Container */
.sidebar-container {
    position: relative;
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: var(--sidebar-width);
    background-color: var(--sidebar-bg);
    color: var(--sidebar-text);
    z-index: 1030;
    transition: var(--sidebar-transition);
    box-shadow: var(--sidebar-shadow);
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
    overflow-y: hidden; /* Hide overflow as requested */
}

/* Sidebar Header */
.sidebar-header {
    height: var(--sidebar-header-height);
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 10;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow: hidden;
}

.logo-img {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 8px;
}

.logo-text {
    font-size: 1.2rem;
    font-weight: 700;
    white-space: nowrap;
    transition: var(--sidebar-transition);
}

/* Sidebar Toggle Button */
.sidebar-toggle {
    background: transparent;
    border: none;
    color: var(--sidebar-text);
    cursor: pointer;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: var(--sidebar-transition);
}

.sidebar-toggle:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-toggle i {
    font-size: 1.2rem;
    transition: var(--sidebar-transition);
}

/* Mobile Toggle Button */
.sidebar-toggle-mobile {
    position: fixed;
    top: 10px;
    left: 10px;
    z-index: 1040;
    background-color: var(--sidebar-active);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.sidebar-toggle-mobile i {
    font-size: 1.5rem;
}

/* Sidebar Overlay */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1025;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

/* Profile Section */
.sidebar-profile {
    padding: 1.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: var(--sidebar-border);
}

.profile-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--sidebar-hover);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.profile-img i {
    font-size: 1.5rem;
    color: var(--sidebar-text);
}

.profile-info {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.profile-name {
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-role {
    font-size: 0.8rem;
    color: var(--sidebar-muted);
    white-space: nowrap;
}

/* Menu */
.sidebar-menu {
    flex: 1;
    padding: 1rem 0;
    overflow-y: hidden; /* Allow scrolling for menu items */
}

.menu-category {
    margin-bottom: 1.5rem;
}

.category-title {
    padding: 0 1.5rem;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: var(--sidebar-muted);
    margin-bottom: 0.5rem;
    display: block;
    white-space: nowrap;
    transition: var(--sidebar-transition);
}

.menu-items {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-item {
    position: relative;
}

.menu-item a {
    display: flex;
    align-items: center;
    padding: 0.8rem 1.5rem;
    color: var(--sidebar-text);
    text-decoration: none;
    transition: var(--sidebar-transition);
    position: relative;
    white-space: nowrap;
}

.menu-item a:hover {
    background-color: var(--sidebar-hover);
}

.menu-item.active a {
    background-color: rgba(108, 117, 125, 0.2);
    color: var(--sidebar-text);
    font-weight: 500;
}

.menu-item.active a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background-color: var(--sidebar-active);
}

.menu-item i {
    font-size: var(--sidebar-icon-size);
    margin-right: 10px;
    width: 20px;
    text-align: center;
    transition: var(--sidebar-transition);
}

.menu-text {
    transition: var(--sidebar-transition);
    opacity: 1;
}

.active-indicator {
    margin-left: auto;
    opacity: 0;
    transition: var(--sidebar-transition);
}

.menu-item.active .active-indicator {
    opacity: 1;
}

/* Collapsed State */
.sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}

.sidebar.collapsed .logo-text,
.sidebar.collapsed .profile-info,
.sidebar.collapsed .menu-text,
.sidebar.collapsed .category-title,
.sidebar.collapsed .active-indicator {
    opacity: 0;
    visibility: hidden;
}

.sidebar.collapsed .sidebar-toggle i {
    transform: rotate(180deg);
}

/* Main Content Adjustment */
main {
    margin-left: var(--sidebar-width);
    transition: var(--sidebar-transition);
    width: calc(100% - var(--sidebar-width));
}

main.expanded {
    margin-left: var(--sidebar-collapsed-width);
    width: calc(100% - var(--sidebar-collapsed-width));
}

/* Responsive Design */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        box-shadow: none;
    }
    
    .sidebar.show {
        transform: translateX(0);
        box-shadow: var(--sidebar-shadow);
    }
    
    .sidebar-toggle-mobile {
        display: flex;
    }
    
    .sidebar-overlay.show {
        display: block;
        opacity: 1;
    }
    
    main {
        margin-left: 0 !important;
        width: 100% !important;
        padding-top: 60px !important;
    }
}

/* Animations */
@keyframes slideIn {
    from { transform: translateX(-100%); }
    to { transform: translateX(0); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.sidebar.show {
    animation: slideIn 0.3s forwards;
}

.sidebar-overlay.show {
    animation: fadeIn 0.3s forwards;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarToggleMobile = document.getElementById('sidebar-toggle-mobile');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const mainContent = document.querySelector('main');
    
    // Check for saved state
    const sidebarState = localStorage.getItem('sidebarState');
    if (sidebarState === 'collapsed') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }
    
    // Desktop toggle
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        
        // Save state
        if (sidebar.classList.contains('collapsed')) {
            localStorage.setItem('sidebarState', 'collapsed');
        } else {
            localStorage.setItem('sidebarState', 'expanded');
        }
    }
    
    // Mobile toggle
    function toggleSidebarMobile() {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
    }
    
    // Event listeners
    sidebarToggle.addEventListener('click', toggleSidebar);
    sidebarToggleMobile.addEventListener('click', toggleSidebarMobile);
    sidebarOverlay.addEventListener('click', toggleSidebarMobile);
    
    // Close sidebar on menu item click (mobile only)
    const menuItems = document.querySelectorAll('.menu-item a');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
                toggleSidebarMobile();
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    });
});
</script>