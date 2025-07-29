<?php
session_start();
require_once "config.php";

// Restrict access
if (!isset($_SESSION["user_email"]) || $_SESSION["user_type"] !== "user") {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION["user_name"];
$userEmail = $_SESSION["user_email"];

// Fetch recent notices
$noticeQuery = $conn->query("SELECT title, message, created_by, created_at FROM notices ORDER BY created_at DESC LIMIT 3");
$notices = $noticeQuery ? $noticeQuery->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard | Missing Person Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background-color: #1e3a8a;
            color: white;
            flex-shrink: 0;
            padding-top: 20px;
            position: fixed;
            height: 100vh;
        }

        .sidebar h2,
        .sidebar img {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar img {
            width: 100px;
            margin-bottom: 10px;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #374bbf;
        }

        .main {
            margin-left: 240px;
            padding: 20px;
            width: 100%;
        }

        .topbar {
            background-color: #2563eb;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .section-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.05);
        }

        .section-box h5 {
            color: #1e3a8a;
            margin-bottom: 10px;
        }

        .notice {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        iframe {
            width: 100%;
            height: 300px;
            border: 0;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .wrapper {
                flex-direction: column;
            }

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- Sidebar -->
    <div class="sidebar">
        <img src="images/logo.jpeg" alt="Portal Logo">
        <h2>MP Portal</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="report_missing.php">➕ Submit Report</a>
        <a href="my_reports.php">📋 My Reports</a>
        <a href="report_status.php">📊 Track Status</a>
        <a href="profile.php">🙍 My Profile</a>
        <a href="notices.php">📢 Notices</a>
        <a href="contact_us.php">✉️ Contact Admin</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="topbar">
            Welcome, <strong><?= htmlspecialchars($userName) ?></strong> (<?= htmlspecialchars($userEmail) ?>)
        </div>

        <!-- Emergency Notices -->
        <?php if (!empty($notices)): ?>
            <div class="section-box">
                <h5>📢 Emergency Notices</h5>
                <?php foreach ($notices as $notice): ?>
                    <div class="notice">
                        <strong><?= htmlspecialchars($notice['title']) ?></strong><br>
                        <?= nl2br(htmlspecialchars($notice['message'])) ?><br>
                        <small>By <?= htmlspecialchars($notice['created_by']) ?> on <?= date("d M Y", strtotime($notice['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- About the Portal -->
        <div class="section-box">
            <h5><i class="bi bi-info-circle-fill text-primary"></i> About the Missing Person Portal</h5>
            <p>
                The <strong>Missing Person Portal</strong> is a centralized online platform designed to help families, friends, and the public report and search for missing individuals with ease and urgency.
                This user-friendly portal bridges the gap between the public and law enforcement by providing a secure and accessible system for reporting missing persons and tracking updates.
            </p>

            <h6 class="text-success mt-3">🎯 Key Objectives:</h6>
            <ul>
                <li><strong>Immediate Reporting:</strong> Quickly submit missing person details including photo, age, last seen location, and contact info.</li>
                <li><strong>Status Tracking:</strong> Monitor the status and progress of submitted reports in real-time.</li>
                <li><strong>Public Awareness:</strong> View recently reported missing persons to encourage community assistance.</li>
                <li><strong>Direct Communication:</strong> Contact the admin or police securely for inquiries or updates.</li>
            </ul>

            <h6 class="text-success mt-3">🛡️ Who Can Use It?</h6>
            <p>
                This portal is built for concerned family members, friends, or any citizen wishing to report a missing person. Only registered users can submit or track reports, ensuring privacy and responsibility.
            </p>

            <h6 class="text-success mt-3">📌 Features at a Glance:</h6>
            <ul>
                <li>User Signup & Login</li>
                <li>Submit & Manage Reports</li>
                <li>Upload Images and Last Seen Details</li>
                <li>Secure Contact with Admin/Police</li>
                <li>Public Access to Recent Reports</li>
            </ul>

            <p>
                By combining technology with social responsibility, the Missing Person Portal speeds up the search process and improves outcomes through structured data and community support.
            </p>
        </div>

        <!-- Nearest Police Station -->
        <div class="section-box">
            <h5>🚓 Nearest Police Station</h5>
            <p>
                Station: Central Town Police Station<br>
                Phone: +91-9876543210 / 100<br>
                Email: centralpolice@example.com<br>
                Address: 123 Main Road, YourCity, YourState
            </p>
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.245184457555!2d106.70042451527646!3d10.776921292321308!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f3e90c7b02b%3A0x5fcfaea3ef09a6e6!2sCentral+Town+Police+Station!5e0!3m2!1sen!2sin!4v1620000000000!5m2!1sen!2sin"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <!-- How to Register -->
        <div class="section-box">
            <h5>📝 How to Register</h5>
            <p>To register, click <strong>Sign Up</strong> from the login page. Fill in your details, upload a profile picture, and create a password. After account creation, you can log in and access all features securely.</p>
        </div>
    </div>
</div>

</body>
</html>
