<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle timeout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['timeout'])) {
    $record_id = $_POST['record_id'];
    
    // Debug: Print the received record_id
    error_log("Attempting to timeout record_id: " . $record_id);
    error_log("POST data: " . print_r($_POST, true));
    
    try {
        // First verify the record exists and is not already timed out
        $checkStmt = $pdo->prepare("SELECT * FROM sit_in_records WHERE id = ? AND time_out IS NULL");
        $checkStmt->execute([$record_id]);
        $record = $checkStmt->fetch();
        
        error_log("Check query result: " . print_r($record, true));
        
        if (!$record) {
            error_log("Record not found or already timed out: " . $record_id);
            $_SESSION['error'] = "Record not found or already timed out.";
            header("Location: current_sitins.php");
            exit();
        }

        // Update the sit-in record with time out
        $updateQuery = "UPDATE sit_in_records SET time_out = NOW() WHERE id = ?";
        error_log("Update query: " . $updateQuery);
        error_log("Update parameters: " . print_r([$record_id], true));
        
        $stmt = $pdo->prepare($updateQuery);
        $result = $stmt->execute([$record_id]);
        
        // Debug: Print the update result
        error_log("Update result: " . ($result ? "success" : "failed"));
        error_log("Rows affected: " . $stmt->rowCount());
        error_log("PDO error info: " . print_r($stmt->errorInfo(), true));

        $_SESSION['success'] = "Student timed out successfully!";
        header("Location: current_sitins.php");
        exit();
    } catch (PDOException $e) {
        error_log("Database error during timeout: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());
        error_log("Stack trace: " . $e->getTraceAsString());
        $_SESSION['error'] = "Error timing out student: " . $e->getMessage();
        header("Location: current_sitins.php");
        exit();
    }
}

// Fetch current sit-ins
$query = "SELECT sr.id, sr.time_in, sr.time_out, sr.sit_in_purpose, u.idno, u.lastname, u.firstname, u.middlename, u.course, u.year 
          FROM sit_in_records sr 
          JOIN users u ON sr.user_id = u.id 
          WHERE sr.time_out IS NULL 
          ORDER BY sr.time_in DESC";
$stmt = $pdo->query($query);
$current_sitins = $stmt->fetchAll();

// Debug: Print the query results
error_log("Current sit-ins query: " . $query);
error_log("Number of current sit-ins: " . count($current_sitins));
error_log("Current sit-ins data: " . print_r($current_sitins, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Sit-ins - Sit-in System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Current Sit-ins</h2>
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-user-clock mr-2"></i>
                            <?php echo count($current_sitins); ?> Active Students
                        </div>
                    </div>

                    <?php if (empty($current_sitins)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-user-clock text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg">No active sit-ins at the moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($current_sitins as $sit_in): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php 
                                                    $lastname = isset($sit_in['lastname']) ? htmlspecialchars($sit_in['lastname']) : '';
                                                    $firstname = isset($sit_in['firstname']) ? htmlspecialchars($sit_in['firstname']) : '';
                                                    echo $lastname . ', ' . $firstname;
                                                    ?>
                                                </div>
                                                <?php if (isset($sit_in['middlename']) && !empty($sit_in['middlename'])): ?>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($sit_in['middlename']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo isset($sit_in['course']) ? htmlspecialchars($sit_in['course']) : ''; ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Year <?php echo isset($sit_in['year']) ? htmlspecialchars($sit_in['year']) : ''; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo isset($sit_in['sit_in_purpose']) ? htmlspecialchars($sit_in['sit_in_purpose']) : 'N/A'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo isset($sit_in['time_in']) ? date('M d, Y h:i A', strtotime($sit_in['time_in'])) : ''; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <form action="" method="POST" class="inline">
                                                    <input type="hidden" name="record_id" value="<?php echo isset($sit_in['id']) ? htmlspecialchars($sit_in['id']) : ''; ?>">
                                                    <button type="submit" 
                                                            name="timeout"
                                                            class="text-red-600 hover:text-red-900"
                                                            onclick="return confirm('Are you sure you want to time out this student?')">
                                                        <i class="fas fa-clock"></i> Time Out
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="fixed bottom-0 right-0 m-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                <?php unset($_SESSION['success']); ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>