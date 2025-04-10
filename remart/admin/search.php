<?php
session_start();
require_once '../config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle sit-in registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_sitin'])) {
    $idno = $_POST['idno'];
    $sitin_purpose = isset($_POST['sit_in_purpose']) ? trim($_POST['sit_in_purpose']) : null;

    // Validate sit-in purpose
    if (empty($sitin_purpose)) {
        $_SESSION['error'] = "Sit-in purpose is required.";
        header("Location: search.php");
        exit();
    }

    try {
        // Debug: Print the received data
        error_log("Attempting to register sit-in for ID: " . $idno);
        error_log("Sit-in purpose: " . $sitin_purpose);

        // Get student details first
        $studentStmt = $pdo->prepare("SELECT id, lastname, firstname, middlename, course, year, session FROM users WHERE idno = ?");
        $studentStmt->execute([$idno]);
        $student = $studentStmt->fetch();

        // Debug: Print student details
        error_log("Student details: " . print_r($student, true));

        if (!$student) {
            error_log("Student not found with ID: " . $idno);
            $_SESSION['error'] = "Student not found.";
            header("Location: search.php");
            exit();
        }

        // Check if student has any sessions left
        if ($student['session'] <= 0) {
            $_SESSION['error'] = "Student has no sessions remaining. Please contact the administrator.";
            header("Location: search.php");
            exit();
        }

        // Check if student is already sitting in
        $checkStmt = $pdo->prepare("SELECT * FROM sit_in_records WHERE user_id = ? AND time_out IS NULL");
        $checkStmt->execute([$student['id']]);

        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = "Student is already sitting in and has not logged out yet.";
            header("Location: search.php");
            exit();
        }

        // Insert into sit_in_records
        $insertStmt = $pdo->prepare("INSERT INTO sit_in_records (user_id, lastname, firstname, middlename, course, year, time_in, sit_in_purpose) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");

        // Debug: Print the SQL and parameters
        error_log("SQL: " . $insertStmt->queryString);
        error_log("Parameters: " . print_r([
            $student['id'],
            $student['lastname'],
            $student['firstname'],
            $student['middlename'],
            $student['course'],
            $student['year'],
            $sitin_purpose // Ensure this is not null
        ], true));

        $result = $insertStmt->execute([
            $student['id'],
            $student['lastname'],
            $student['firstname'],
            $student['middlename'],
            $student['course'],
            $student['year'],
            $sitin_purpose // Ensure this is not null
        ]);

        // Update session count
        if ($result) {
            $updateSessionStmt = $pdo->prepare("UPDATE users SET session = session - 1 WHERE idno = ?");
            $updateSessionStmt->execute([$idno]);
            
            error_log("Sit-in record inserted successfully");
            $_SESSION['success'] = "Sit-in registered successfully!";
            header("Location: current_sitins.php");
            exit();
        } else {
            error_log("Failed to insert sit-in record");
            $_SESSION['error'] = "Failed to register sit-in. Please try again.";
            header("Location: search.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: search.php");
        exit();
    }
}

// Get search results
$search_results = [];
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search_query) {
    $query = "SELECT u.*, u.session as session_count
              FROM users u 
              WHERE u.idno LIKE ? OR u.lastname LIKE ? OR u.firstname LIKE ? OR u.course LIKE ? 
              ORDER BY u.lastname, u.firstname";
    $stmt = $pdo->prepare($query);
    $search_param = "%$search_query%";
    $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
    $search_results = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Students - Sit-in System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- Search Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Search Students</h2>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="GET" class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <input type="text" 
                                       name="q" 
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       placeholder="Search by ID, Name, or Course" 
                                       class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                <i class="fas fa-search mr-2"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Results -->
            <?php if ($search_query): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Search Results</h3>
                        
                        <?php if (empty($search_results)): ?>
                            <div class="text-center text-gray-600 py-8">
                                <i class="fas fa-search fa-3x mb-4"></i>
                                <p class="text-xl">No students found matching your search.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID No</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($search_results as $student): ?>
                                            <tr data-idno="<?php echo htmlspecialchars($student['idno']); ?>">
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($student['idno']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($student['course']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($student['year']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900 session-count">
                                                        <?php echo $student['session']; ?> sessions
                                                    </div>
                                                    <?php if ($student['session'] <= 0): ?>
                                                        <div class="text-sm text-red-600">No Sessions Left</div>
                                                    <?php else: ?>
                                                        <div class="text-sm text-green-600"><?php echo $student['session']; ?> sessions available</div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if ($student['session'] > 0): ?>
                                                        <button onclick="showSitInModal('<?php echo $student['idno']; ?>')" 
                                                                class="text-blue-600 hover:text-blue-900">
                                                            <i class="fas fa-plus-circle"></i> Register Sit-in
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-red-600">
                                                            <i class="fas fa-ban"></i> No Sessions Left
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sit-in Modal -->
    <div id="sitInModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Register Sit-in</h3>
                <form action="" method="POST">
                    <input type="hidden" name="idno" id="modal_idno">
                    
                    <div class="mb-4">
                        <div id="sessionInfo" class="text-sm text-gray-600 mb-4"></div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Sit-in Purpose</label>
                        <select name="sit_in_purpose" class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Programming">Programming</option>
                            <option value="C">C</option>
                            <option value="C++">C++</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="hideSitInModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                                name="register_sitin"
                                id="registerButton"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showSitInModal(idno) {
            document.getElementById('modal_idno').value = idno;
            
            // Get the session count for this student
            const row = document.querySelector(`tr[data-idno="${idno}"]`);
            const sessionCount = row.querySelector('.session-count').textContent;
            
            // Update the modal with session information
            const sessionInfo = document.getElementById('sessionInfo');
            sessionInfo.innerHTML = `
                <div class="bg-gray-50 p-3 rounded">
                    <div class="font-semibold">Session Information:</div>
                    <div>${sessionCount}</div>
                </div>
            `;
            
            // Show the modal
            document.getElementById('sitInModal').classList.remove('hidden');
        }

        function hideSitInModal() {
            document.getElementById('sitInModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('sitInModal');
            if (event.target == modal) {
                hideSitInModal();
            }
        }
    </script>
</body>
</html>