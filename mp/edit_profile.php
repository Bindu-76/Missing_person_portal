<?php
session_start();
require_once "config.php";

// Check user login and role
if (!isset($_SESSION["user_email"]) || $_SESSION["user_type"] !== "user") {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$message = "";

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Update logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $profile_picture = $user['profile_picture'];

    // Handle profile image upload
    if (!empty($_FILES["photo"]["name"])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filename = basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . time() . "_" . $filename;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
            $profile_picture = $targetFile;
        }
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $email, $profile_picture, $userId);
    if ($stmt->execute()) {
        $_SESSION["user_name"] = $name;
        $_SESSION["user_email"] = $email;
        $message = "Profile updated successfully.";
        // Refresh data
        $user['name'] = $name;
        $user['email'] = $email;
        $user['profile_picture'] = $profile_picture;
    } else {
        $message = "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-form {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #3b82f6;
        }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <div class="profile-form">
        <h3 class="text-center mb-4">Edit Profile</h3>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="text-center mb-4">
                <img src="<?= !empty($user['profile_picture']) && file_exists($user['profile_picture']) ? $user['profile_picture'] : 'images/default_user.jpg' ?>" class="profile-img" alt="Profile Picture">
            </div>

            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Upload New Profile Picture</label>
                <input type="file" name="photo" class="form-control">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>

            <div class="text-center mt-3">
                <a href="profile.php" class="btn btn-secondary btn-sm">Back to Profile</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
