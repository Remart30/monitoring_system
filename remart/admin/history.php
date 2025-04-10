<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get student details if viewing specific student
$student = null;
if (isset($_GET['student_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['student_id']]);
    $student = $stmt->fetch();
}

// Get sit-in history
$query = "SELECT sr.*, u.idno, u.lastname, u.firstname, u.middlename, u.course, u.year 
          FROM sit_in_records sr 
          JOIN users u ON sr.user_id = u.id 
          WHERE sr.time_out IS NOT NULL";

if ($student) {
    $query .= " AND sr.user_id = ?";
}

$query .= " ORDER BY sr.time_out DESC";

$stmt = $pdo->prepare($query);
if ($student) {
    $stmt->execute([$_GET['student_id']]);
} else {
    $stmt->execute();
}
$history = $stmt->fetchAll();

// Debug: Print the query results
error_log("History query: " . $query);
error_log("Number of records: " . count($history));
error_log("History data: " . print_r($history, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-in History - Sit-in System</title>
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
                        <h2 class="text-2xl font-bold text-gray-800">
                            <?php if ($student): ?>
                                Sit-in History for <?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?>
                            <?php else: ?>
                                Sit-in History
                            <?php endif; ?>
                        </h2>
                        <?php if ($student): ?>
                            <a href="students.php" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-arrow-left"></i> Back to Students
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($history as $record): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php 
                                                $lastname = isset($record['lastname']) ? htmlspecialchars($record['lastname']) : '';
                                                $firstname = isset($record['firstname']) ? htmlspecialchars($record['firstname']) : '';
                                                echo $lastname . ', ' . $firstname;
                                                ?>
                                            </div>
                                            <?php if (isset($record['middlename']) && !empty($record['middlename'])): ?>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($record['middlename']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo isset($record['course']) ? htmlspecialchars($record['course']) : ''; ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Year <?php echo isset($record['year']) ? htmlspecialchars($record['year']) : ''; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo isset($record['sit_in_purpose']) ? htmlspecialchars($record['sit_in_purpose']) : ''; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo isset($record['time_in']) ? date('M d, Y h:i A', strtotime($record['time_in'])) : ''; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo isset($record['time_out']) ? date('M d, Y h:i A', strtotime($record['time_out'])) : ''; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>