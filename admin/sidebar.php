<?php
require_once 'config.php';
// Don't call checkAdminAuth() here as it might cause redirect loops for login page
?>

<!-- Sidebar Navigation -->
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="images/logo.png" alt="Logo" class="admin-logo">
        <h2>Admin Dashboard</h2>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                <a href="admin.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'institutions.php' ? 'active' : ''; ?>">
                <a href="institutions.php">
                    <i class="fas fa-building"></i>
                    <span>Institutions</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>">
                <a href="services.php">
                    <i class="fas fa-list"></i>
                    <span>Services</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>">
                <a href="reservations.php">
                    <i class="fas fa-calendar-check"></i>
                    <span>Réservations</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <?php if (isset($_SESSION['admin_id'])): ?>
        <div class="admin-profile">
            <div class="admin-avatar">
               <img src="images/admin.jpeg" alt="Admin">
            </div>
            <div>
                    <h4><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h4>
                    <span><?php echo $_SESSION['admin_role'] === 'super_admin' ? 'Super Administrateur' : 'Administrateur'; ?></span>
                </div>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
        </a>
        <?php endif; ?>
    </div>
</aside>

<style>
    .admin-avatar {
        width: 40px;
    height: 40px;
    border-radius: 50%;
    
    object-fit: cover;
    }
    
    .admin-profile {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .admin-profile div {
        flex: 1;
    }
    
    .admin-profile h4 {
        font-size: 0.9rem;
        margin-bottom: 3px;
    }
    
    .admin-profile span {
        font-size: 0.8rem;
        opacity: 0.8;
    }
</style>