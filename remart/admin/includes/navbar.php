<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="dashboard.php" class="text-xl font-bold text-gray-800">
                        <i class="fas fa-clock mr-2"></i>
                        Sit-in System
                    </a>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="announcements.php" class="<?php echo $current_page == 'announcements.php' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-bullhorn mr-2"></i>Announcements
                    </a>
                    <a href="students.php" class="<?php echo $current_page == 'students.php' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-users mr-2"></i>Students
                    </a>
                    <a href="search.php" class="<?php echo $current_page == 'search.php' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-search mr-2"></i>Search & Sit-in
                    </a>
                    <a href="current_sitins.php" class="<?php echo $current_page == 'current_sitins.php' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-user-clock mr-2"></i>Current Sit-ins
                    </a>
                    <a href="history.php" class="<?php echo $current_page == 'history.php' ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        <i class="fas fa-history mr-2"></i>History
                    </a>
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <div class="flex items-center space-x-4">
                    <a href="../logout.php" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="sm:hidden" id="mobile-menu">
        <div class="pt-2 pb-3 space-y-1">
            <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
            <a href="announcements.php" class="<?php echo $current_page == 'announcements.php' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                <i class="fas fa-bullhorn mr-2"></i>Announcements
            </a>
            <a href="students.php" class="<?php echo $current_page == 'students.php' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                <i class="fas fa-users mr-2"></i>Students
            </a>
            <a href="search.php" class="<?php echo $current_page == 'search.php' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                <i class="fas fa-search mr-2"></i>Search & Sit-in
            </a>
            <a href="current_sitins.php" class="<?php echo $current_page == 'current_sitins.php' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                <i class="fas fa-user-clock mr-2"></i>Current Sit-ins
            </a>
            <a href="history.php" class="<?php echo $current_page == 'history.php' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'; ?> block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                <i class="fas fa-history mr-2"></i>History
            </a>
            <a href="../logout.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenu.classList.toggle('hidden');
}
</script> 