<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle announcement deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_announcement'])) {
    $announcement_id = $_POST['announcement_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$announcement_id]);
        $_SESSION['success'] = "Announcement deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting announcement: " . $e->getMessage();
    }
    header("Location: announcements.php");
    exit();
}

// Handle announcement update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_announcement'])) {
    $announcement_id = $_POST['announcement_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    try {
        $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $announcement_id]);
        $_SESSION['success'] = "Announcement updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating announcement: " . $e->getMessage();
    }
    header("Location: announcements.php");
    exit();
}

// Handle announcement creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_announcement'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $created_by = $_SESSION['admin_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $created_by]);
        $_SESSION['success'] = "Announcement created successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error creating announcement: " . $e->getMessage();
    }
    header("Location: announcements.php");
    exit();
}

// Fetch all announcements
$stmt = $pdo->query("SELECT a.* FROM announcements a ORDER BY a.created_at DESC");
$announcements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements - Sit-in System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- Create Announcement Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Create New Announcement</h2>
                        <i class="fas fa-bullhorn text-blue-500 text-2xl"></i>
                    </div>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   required
                                   placeholder="Enter announcement title"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out">
                        </div>
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                            <textarea name="content" 
                                      id="content" 
                                      rows="6" 
                                      required
                                      placeholder="Enter announcement content"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out resize-none"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" 
                                    name="create_announcement"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center shadow-sm">
                                <i class="fas fa-plus mr-2"></i>
                                Create Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Announcements List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">All Announcements</h2>
                        <span class="text-sm text-gray-500"><?php echo count($announcements); ?> announcements</span>
                    </div>
                    <div class="space-y-4">
                        <?php if (empty($announcements)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-bullhorn text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500">No announcements found</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="border rounded-lg p-6 hover:shadow-md transition duration-150 ease-in-out">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                                <?php echo htmlspecialchars($announcement['title']); ?>
                                            </h3>
                                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                                <i class="fas fa-user-circle mr-2"></i>
                                                <span>Admin</span>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-clock mr-2"></i>
                                                <span><?php echo date('M d, Y h:i A', strtotime($announcement['created_at'])); ?></span>
                                            </div>
                                            <div class="text-gray-700 bg-gray-50 p-4 rounded-md">
                                                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2 ml-4">
                                            <!-- Edit Button -->
                                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($announcement)); ?>)"
                                                    class="text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <!-- Delete Button -->
                                            <form action="" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                                <input type="hidden" name="announcement_id" value="<?php echo $announcement['id']; ?>">
                                                <button type="submit" name="delete_announcement" class="text-red-600 hover:text-red-800 transition duration-150 ease-in-out">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Announcement</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="announcement_id" id="edit_announcement_id">
                <div>
                    <label for="edit_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" 
                           name="title" 
                           id="edit_title" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out">
                </div>
                <div>
                    <label for="edit_content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" 
                              id="edit_content" 
                              rows="6" 
                              required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out resize-none"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeEditModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-150 ease-in-out">
                        Cancel
                    </button>
                    <button type="submit" 
                            name="update_announcement"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(announcement) {
            document.getElementById('edit_announcement_id').value = announcement.id;
            document.getElementById('edit_title').value = announcement.title;
            document.getElementById('edit_content').value = announcement.content;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html> 