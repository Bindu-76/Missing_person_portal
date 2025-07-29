<?php
session_start();
require_once "config.php";

// Redirect if not logged in
if (!isset($_SESSION["user_email"]) || $_SESSION["user_type"] !== "user") {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

// Fetch user details
$stmt = $conn->prepare("SELECT name, email, role, profile_picture, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #dbeafe, #eef2ff);
            font-family: 'Segoe UI', sans-serif;
        }

        .profile-container {
            max-width: 600px;
            margin: 60px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
        }

        .profile-img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #3b82f6;
            margin-bottom: 20px;
        }

        .btn-outline {
            margin: 5px;
            border-radius: 30px;
        }

        .badge-role {
            font-size: 0.9rem;
            background-color: #3b82f6;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-container">
        <img src="<?= (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) ? $user['profile_picture'] : 'images/default_user.jpg' ?>"
             class="profile-img" alt="Profile Picture">

        <h3 class="mb-0"><?= htmlspecialchars($user['name']) ?></h3>
        <p class="text-muted mb-1"><?= htmlspecialchars($user['email']) ?></p>
        <span class="badge badge-role text-white px-3 py-2"><?= ucfirst($user['role']) ?></span>

        <div class="mt-3">
            <p><i class="bi bi-calendar-event"></i> Joined on <?= date("d M Y", strtotime($user['created_at'])) ?></p>
        </div>

        <div class="mt-4">
            <a href="edit_profile.php" class="btn btn-outline-primary btn-outline"><i class="bi bi-pencil-square"></i> Edit Profile</a>
            <a href="change_password.php" class="btn btn-outline-secondary btn-outline"><i class="bi bi-key-fill"></i> Change Password</a>
            <a href="dashboard.php" class="btn btn-outline-dark btn-outline"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
