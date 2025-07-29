<?php
session_start();
require_once "config.php";

// Access control
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'police'])) {
    header("Location: admin_login.php");
    exit;
}

$notice_error = "";
$notice_success = "";

// Handle new notice submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['title'], $_POST['message'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);

    if (!empty($title) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO notices (title, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $message);
        if ($stmt->execute()) {
            $notice_success = "Notice added successfully.";
        } else {
            $notice_error = "Error adding notice.";
        }
    } else {
        $notice_error = "Please fill in both title and message.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM notices WHERE id = $id");
    header("Location: manage_notices.php");
    exit;
}

// Fetch all notices
$notices = $conn->query("SELECT * FROM notices ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Notices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 900px; margin-top: 40px; }
        .notice-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .notice-card h5 { margin-bottom: 10px; }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <h2 class="mb-4 text-center">Manage Public Notices</h2>

    <?php if ($notice_error): ?>
        <div class="alert alert-danger"><?= $notice_error ?></div>
    <?php elseif ($notice_success): ?>
        <div class="alert alert-success"><?= $notice_success ?></div>
    <?php endif; ?>

    <!-- Add Notice Form -->
    <div class="card p-4 mb-4">
        <h5>Add New Notice</h5>
        <form method="POST">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Message</label>
                <textarea name="message" rows="4" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Notice</button>
        </form>
    </div>

    <!-- All Notices -->
    <?php while ($row = $notices->fetch_assoc()): ?>
        <div class="notice-card">
            <h5><?= htmlspecialchars($row['title']) ?></h5>
            <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
            <small class="text-muted">Posted on <?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></small>
            <div class="text-end">
                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger mt-2" onclick="return confirm('Delete this notice?')">Delete</a>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="index_admin.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
