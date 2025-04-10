<?php
// Get the current page name to highlight active nav item
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
    }
    .navbar {
        background-color: var(--primary-color);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky; /* Ensure the navbar stays in place */
        top: 0; /* Stick to the top of the viewport */
        z-index: 1030; /* Ensure it stays above other elements */
    }
    .navbar-brand {
        font-weight: 600;
        color: white !important;
    }
    .nav-link {
        color: rgba(255,255,255,0.8) !important;
        transition: color 0.3s ease;
    }
    .nav-link:hover {
        color: white !important;
    }
    .nav-link.active {
        color: white !important;
        font-weight: 500;
    }
</style>
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-clock me-2"></i>
            Sit-in Monitoring System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'announcements.php' ? 'active' : ''; ?>" href="announcements.php">
                        <i class="fas fa-bullhorn me-1"></i> Announcements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'rules.php' ? 'active' : ''; ?>" href="rules.php">
                        <i class="fas fa-book me-1"></i> Sit-in Rules
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                        <i class="fas fa-user me-1"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>