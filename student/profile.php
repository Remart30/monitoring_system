<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['idno'])) {
    header("Location: ../login.php");
    exit();
}

$idno = $_SESSION['idno'];  // Use idno instead of user_id

// Get student information
$stmt = $pdo->prepare("SELECT * FROM users WHERE idno = ?");
$stmt->execute([$idno]);
$row = $stmt->fetch();

if($row) {
    $lastname = $row['lastname'];
    $firstname = $row['firstname'];
    $midname = $row['middlename'];
    $course = $row['course'];
    $year = $row['year'];
    $username = $row['username'];
    $profile_pic = $row['profile_pic'] ?? 'default.jpg';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_pic']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                // Create uploads directory if it doesn't exist
                if (!file_exists('../uploads/profile_pics/')) {
                    mkdir('../uploads/profile_pics/', 0777, true);
                }

                // Create unique filename
                $new_filename = $idno . '_' . time() . '.' . $filetype;
                $upload_path = '../uploads/profile_pics/' . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    // Update profile picture in database
                    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE idno = ?");
                    $stmt->execute([$new_filename, $idno]);
                    $profile_pic = $new_filename;
                }
            }
        }

        // Update user information
        $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, middlename = ?, course = ?, year = ? WHERE idno = ?");
        $stmt->execute([
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['midname'],
            $_POST['course'],
            $_POST['year'],
            $idno
        ]);

        $_SESSION['success'] = "Profile updated successfully!";
        header('Location: profile.php');
        exit();
    } catch (PDOException $e) {
        $error = "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Sit-in Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            display: flex;
            justify-content: center;
        }
        .card {
            width: 60%;
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        @media (max-width: 768px) {
            .card {
                width: 90%;
            }
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary-color);
        }
        .profile-pic-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Edit Profile</h2>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="profile-pic-container">
                        <?php
                        $profile_pic_path = '../uploads/profile_pics/' . $profile_pic;
                        if (!file_exists($profile_pic_path)) {
                            $profile_pic_path = '../uploads/profile_pics/default.jpg';
                        }
                        ?>
                        <img src="<?php echo $profile_pic_path; ?>" 
                             alt="Profile Picture" 
                             class="profile-pic mb-3">
                        <div>
                            <input type="file" 
                                   name="profile_pic" 
                                   id="profile_pic" 
                                   class="form-control" 
                                   accept="image/*">
                            <small class="text-muted">Allowed formats: JPG, JPEG, PNG, GIF</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" 
                               class="form-control" 
                               name="firstname" 
                               value="<?php echo htmlspecialchars($firstname); ?>" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" 
                               class="form-control" 
                               name="midname" 
                               value="<?php echo htmlspecialchars($midname); ?>" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" 
                               class="form-control" 
                               name="lastname" 
                               value="<?php echo htmlspecialchars($lastname); ?>" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select class="form-select" name="course" required>
                            <option value="BSIT" <?php echo $course === 'BSIT' ? 'selected' : ''; ?>>BSIT</option>
                            <option value="BSCS" <?php echo $course === 'BSCS' ? 'selected' : ''; ?>>BSCS</option>
                            <option value="BSIS" <?php echo $course === 'BSIS' ? 'selected' : ''; ?>>BSIS</option>
                            <option value="BSCE" <?php echo $course === 'BSCE' ? 'selected' : ''; ?>>BSCE</option>
                            <option value="BSEE" <?php echo $course === 'BSEE' ? 'selected' : ''; ?>>BSEE</option>
                            <option value="BSCPE" <?php echo $course === 'BSCPE' ? 'selected' : ''; ?>>BSCPE</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select class="form-select" name="year" required>
                            <option value="1" <?php echo $year === '1' ? 'selected' : ''; ?>>1st Year</option>
                            <option value="2" <?php echo $year === '2' ? 'selected' : ''; ?>>2nd Year</option>
                            <option value="3" <?php echo $year === '3' ? 'selected' : ''; ?>>3rd Year</option>
                            <option value="4" <?php echo $year === '4' ? 'selected' : ''; ?>>4th Year</option>
                        </select>
                    </div>

                    <div class="text-end">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 