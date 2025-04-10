<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get admin information
$stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

// Get all students
$stmt = $pdo->query("SELECT * FROM users ORDER BY lastname, firstname");
$students = $stmt->fetchAll();

// Get active sit-ins
$stmt = $pdo->query("SELECT sr.*, u.lastname, u.firstname, u.middlename, u.course, u.year 
                     FROM sit_in_records sr 
                     JOIN users u ON sr.user_id = u.idno 
                     WHERE sr.time_out IS NULL 
                     ORDER BY sr.time_in DESC");
$active_sitins = $stmt->fetchAll();

// Get sit-in history (last 5)
$stmt = $pdo->query("SELECT sr.*, u.lastname, u.firstname, u.middlename, u.course, u.year 
                     FROM sit_in_records sr 
                     JOIN users u ON sr.user_id = u.idno 
                     WHERE sr.time_out IS NOT NULL 
                     ORDER BY sr.time_out DESC 
                     LIMIT 5");
$sit_in_history = $stmt->fetchAll();

// Handle announcements
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_announcement'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $creator_id = $_SESSION['admin_id'];
    $creator_name = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];

    $stmt = $pdo->prepare("INSERT INTO announcements (title, content, creator_id, creator_name, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $content, $creator_id, $creator_name]);

    $_SESSION['success'] = "Announcement created successfully!";
    header("Location: dashboard.php");
    exit();
}

// Handle timeout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['timeout'])) {
    $record_id = $_POST['record_id'];
    
    $stmt = $pdo->prepare("UPDATE sit_in_records SET time_out = NOW() WHERE record_id = ?");
    $stmt->execute([$record_id]);

    $_SESSION['success'] = "Student timed out successfully!";
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sit-in System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Welcome, Admin!</h2>
                            <p class="text-gray-600">Here's what's happening today</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-lg font-semibold text-gray-800">Total Students</h2>
                                <p class="text-3xl font-bold text-gray-900"><?php echo count($students); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-user-clock text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-lg font-semibold text-gray-800">Active Sit-ins</h2>
                                <p class="text-3xl font-bold text-gray-900"><?php echo count($active_sitins); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-history text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-lg font-semibold text-gray-800">Total History</h2>
                                <p class="text-3xl font-bold text-gray-900"><?php echo count($sit_in_history); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Announcements -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold mb-6 text-gray-800">Recent Announcements</h2>
                        <div class="space-y-4">
                            <?php
                            // Fetch recent announcements
                            $announcementQuery = "SELECT a.* FROM announcements a ORDER BY a.created_at DESC LIMIT 5";
                            $announcementStmt = $pdo->query($announcementQuery);
                            $recentAnnouncements = $announcementStmt->fetchAll();

                            if (empty($recentAnnouncements)): ?>
                                <div class="text-center text-gray-500 py-4">
                                    No announcements found
                                </div>
                            <?php else:
                                foreach ($recentAnnouncements as $announcement): ?>
                                    <div class="border rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <?php echo htmlspecialchars($announcement['title']); ?>
                                                </h3>
                                                <p class="text-sm text-gray-500">
                                                    Posted by Admin on <?php echo date('M d, Y h:i A', strtotime($announcement['created_at'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-gray-700">
                                            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach;
                            endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold mb-6 text-gray-800">Recent History</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                    // Fetch recent sit-in history
                                    $historyQuery = "SELECT sr.*, u.idno 
                                                   FROM sit_in_records sr 
                                                   JOIN users u ON sr.user_id = u.id 
                                                   ORDER BY sr.time_in DESC 
                                                   LIMIT 5";
                                    $historyStmt = $pdo->query($historyQuery);
                                    $recentHistory = $historyStmt->fetchAll();

                                    if (empty($recentHistory)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                                No recent history found
                                            </td>
                                        </tr>
                                    <?php else:
                                        foreach ($recentHistory as $record): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($record['lastname'] . ', ' . $record['firstname']); ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($record['idno']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        <?php echo htmlspecialchars($record['course']); ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        Year <?php echo htmlspecialchars($record['year']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo date('M d, Y h:i A', strtotime($record['time_in'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $record['time_out'] ? date('M d, Y h:i A', strtotime($record['time_out'])) : 'Not timed out'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 