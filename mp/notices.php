<?php
session_start();
require_once "config.php";

// Allow only logged-in users
if (!isset($_SESSION["user_email"]) || $_SESSION["user_type"] !== "user") {
    header("Location: login.php");
    exit;
}

$notices = [];
$sql = "SELECT title, message, posted_by, created_at FROM notices ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notices[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Public Notices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f3f4f6;
            font-family: 'Segoe UI', sans-serif;
        }
        .notice-container {
            max-width: 900px;
            margin: 40px auto;
        }
        .notice-card {
            background: white;
            border-left: 6px solid #1e40af;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        }
        .notice-card h5 {
            margin-bottom: 10px;
            color: #1e3a8a;
        }
        .notice-meta {
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>

<div class="container notice-container">
    <!-- ✅ Back Button -->
    <a href="dashboard.php" class="btn btn-secondary mb-4">← Back to Dashboard</a>

    <h2 class="text-center mb-4">Public Notices</h2>

    <?php if (count($notices) > 0): ?>
        <?php foreach ($notices as $notice): ?>
            <div class="notice-card">
                <h5><?= htmlspecialchars($notice['title']) ?></h5>
                <p><?= nl2br(htmlspecialchars($notice['message'])) ?></p>
                <div class="notice-meta">
                    Posted by <?= ucfirst(htmlspecialchars($notice['posted_by'])) ?> on <?= date("d M Y, h:i A", strtotime($notice['created_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">No notices available at the moment.</div>
    <?php endif; ?>
</div>

</body>
</html>
