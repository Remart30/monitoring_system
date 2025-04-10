<?php
session_start();
require_once 'config/database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idno = $_POST['idno'];
    $firstname = $_POST['first_name'];
    $lastname = $_POST['last_name'];
    $midname = $_POST['middle_name'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Check if username or ID number already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR idno = ?");
        $stmt->execute([$username, $idno]);
        if ($stmt->rowCount() > 0) {
            $error = "Username or ID number already exists";
        } else {
            // Insert new user with default role as 'student'
            $stmt = $pdo->prepare("INSERT INTO users (idno, firstname, lastname, middlename, course, year, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'student')");
            $stmt->execute([$idno, $firstname, $lastname, $midname, $course, $year, $username, $password]);
            $_SESSION['registration_success'] = true;
            header('Location: login.php');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Registration failed. Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sit-in Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            width: 80%;
            margin: 0 auto;
        }
        .card-header {
            background-color: white;
            border-bottom: none;
            border-radius: 15px 15px 0 0 !important;
            padding: 2rem 2rem 1rem;
            text-align: center;
        }
        .card-header i {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        .card-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .login-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
        }
        .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-plus"></i>
                        <h3 class="mb-0">Create Account</h3>
                        <p class="text-muted mb-0">Please fill in your details</p>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="register.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="idno" class="form-label">ID Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-id-card text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control" id="idno" name="idno" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="username" class="form-label">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-user text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-user text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="middle_name" class="form-label">Middle Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-user text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control" id="middle_name" name="middle_name">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-user text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="course" class="form-label">Course</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-graduation-cap text-muted"></i>
                                            </span>
                                            <select class="form-control" id="course" name="course" required>
                                                <option value="">Select Course</option>
                                                <option value="BSIT">BSIT</option>
                                                <option value="BSCS">BSCS</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="year" class="form-label">Year Level</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-calendar text-muted"></i>
                                            </span>
                                            <select class="form-control" id="year" name="year" required>
                                                <option value="">Select Year</option>
                                                <option value="1st">1st Year</option>
                                                <option value="2nd">2nd Year</option>
                                                <option value="3rd">3rd Year</option>
                                                <option value="4th">4th Year</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-lock text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i> Register
                                </button>
                            </div>
                        </form>
                        <div class="login-link">
                            <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 