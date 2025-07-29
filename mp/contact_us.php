<?php
session_start();
require_once "config.php";

// Allow only logged-in users
if (!isset($_SESSION["user_email"]) || $_SESSION["user_type"] !== "user") {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"];
$userEmail = $_SESSION["user_email"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST["subject"]);
    $msg = trim($_POST["message"]);

    if (!empty($subject) && !empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, user_name, user_email, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $userName, $userEmail, $subject, $msg);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Message sent successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to send message.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>All fields are required.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-box {
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .back-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <div class="form-box">
        <!-- Back to Dashboard Button -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Contact Admin / Police</h3>
            <a href="dashboard.php" class="btn btn-outline-secondary back-btn">← Back</a>
        </div>

        <?= $message ?>

        <form method="POST">
            <div class="mb-3">
                <label>Your Name</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($userName) ?>" disabled>
            </div>

            <div class="mb-3">
                <label>Your Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($userEmail) ?>" disabled>
            </div>

            <div class="mb-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="5" required></textarea>
            </div>

            <div class="d-grid">
                <button class="btn btn-primary" type="submit">Send Message</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
