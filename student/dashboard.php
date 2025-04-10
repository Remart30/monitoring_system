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
    $midname = $row['middlename'];
    $course = $row['course'];
    $year = $row['year'];
    $profile_pic = $row['profile_pic'];
}

// Get announcements
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
$announcements = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Sit-in Monitoring System</title>
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
            max-height: 100%;
            overflow-y: auto;
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
        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .welcome-section h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .welcome-section p {
            margin-bottom: 0;
            opacity: 0.9;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary-color);
        }
        /* For the two-column layout */
        .column-box {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            height: 70vh;
        }
        .column {
            flex: 1;
            overflow-y: auto; /* Make the content scrollable */
            max-height: 100%;
        }
        /* Styling for the Rules and Regulations content */
        .rules-content p {
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .rules-content h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="row mb-4">
            <!-- Welcome Section (Row 1) -->
            <div class="col-lg-12 d-flex align-items-stretch">
                <div class="welcome-section w-100 d-flex align-items-center gap-4">
                    <!-- Profile Picture (Left Side) -->
                    <div>
                        <?php
                            $profile_pic_path = '../uploads/profile_pics/' . $profile_pic;
                            if (!file_exists($profile_pic_path)) {
                                $profile_pic_path = '../uploads/profile_pics/default.jpg';
                            }
                        ?>
                        <img src="<?php echo $profile_pic_path; ?>" 
                            alt="Profile Picture" 
                            class="profile-pic mb-3 rounded-circle">
                    </div>
                    
                    <!-- Information Section (Right Side) -->
                    <div>
                        <h1 style="font-size: 1.3rem;">Welcome, <?php echo htmlspecialchars($firstname . ' ' . $lastname); ?>!</h1>
                        <p><i class="fas fa-id-card me-2"></i><?php echo htmlspecialchars($idno); ?></p>
                        <p><i class="fas fa-user-graduate me-2"></i><?php echo htmlspecialchars($course); ?></p>
                        <p><i class="fas fa-calendar-alt me-2"></i>Year <?php echo htmlspecialchars($year); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcements and Rules Section (Row 2) -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="column-box">
                    <!-- Left Column: Announcements -->
                    <div class="column">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    <i class="fas fa-bullhorn me-2"></i>
                                    Recent Announcements
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

                    <!-- Right Column: Rules and Regulations -->
                    <div class="column">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    <i class="fas fa-gavel me-2"></i>
                                    Laboratory Rules and Regulations
                                </h5>
                            </div>
                            <div class="card-body rules-content" style="padding: 20px;">
                                <h2 style="font-size: 1.5rem; text-align: center; font-weight: bold;">University of Cebu</h2>
                                <h2 style="font-size: 1.3rem; text-align: center; font-weight: bold;">COLLEGE OF INFORMATION & COMPUTER STUDIES</h2>
                                <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
                                <p>1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans, and other personal pieces of equipment must be switched off.</p>
                                <p>2. Games are not allowed inside the lab. This includes computer-related games, card games, and other games that may disturb the operation of the lab.></p>
                                <p>3. Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>
                                <p>4. Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</p>
                                <p>5. Deleting computer files and changing the set-up of the computer is a major offense.</p>
                                <p>6. Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</p>
                                <p>7. Observe proper decorum while inside the laboratory.</p>
                                <p>- Do not get inside the lab unless the instructor is present.</p>
                                <p>- All bags, knapsacks, and the likes must be deposited at the counter.</p>
                                <p>- Follow the seating arrangement of your instructor.</p>
                                <p>- At the end of class, all software programs must be closed.</p>
                                <p>- Return all chairs to their proper places after using.</p>
                                <p>8. Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.<</p>
                                <p>9. Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</p>
                                <p>10. Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</p>
                                <p>11. For serious offenses, the lab personnel may call the Civil Security Office (CSU) for assistance.</p>
                                <p>12. Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant, or instructor immediately.</p>
                                <h1 style="font-size: 1.3rem; text-align: center; font-weight: bold;">DISCIPLINARY ACTION</h1>
                                <p>First Offense - The Head or the Dean or OIC recommends to the Guidance Center for a suspension from classes for each offender.</p>
                                <p>Second and Subsequent Offenses - A recommendation for a heavier sanction will be endorsed to the Guidance Center.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

i want to make the annoucnements and rules height to adjust to 70% andd make it scrollanle