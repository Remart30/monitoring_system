<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['idno'])) {
    header('Location: ../login.php');
    exit();
}

$idno = $_SESSION['idno'];

// Get student information
$stmt = $pdo->prepare("SELECT * FROM users WHERE idno = ?");
$stmt->execute([$idno]);
$row = $stmt->fetch();

if($row){
    $lastname = $row['lastname'];
    $firstname = $row['firstname'];
    $course = $row['course'];
    $year = $row['year'];
}

// Get all announcements
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Sit-in Monitoring System</title>
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
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            margin-top: 2rem;
            background: white;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-bottom: none;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        .card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        .card-body {
            padding: 0;
        }
        .announcement-item {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
            position: relative;
        }
        .announcement-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        .announcement-item:last-child {
            border-bottom: none;
        }
        .announcement-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .announcement-date {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .announcement-content {
            color: #2c3e50;
            line-height: 1.6;
            margin-bottom: 0;
        }
        .announcement-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--secondary-color);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .announcement-item:hover::before {
            opacity: 1;
        }
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="fas fa-bullhorn me-2"></i>
                    Announcements
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($announcements)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bullhorn"></i>
                        <p class="mb-0">No announcements available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement-item">
                            <h6 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                            <p class="announcement-date">
                                <i class="fas fa-clock"></i>
                                <i class="fas fa-user-shield"></i>
                                Posted by Admin on <?php echo date('M d, Y h:i A', strtotime($announcement['created_at'])); ?>
                            </p>
                            <p class="announcement-content"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 