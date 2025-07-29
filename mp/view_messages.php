<?php
session_start();
require_once "config.php";

// Access control: Only admin or police
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin', 'police'])) {
    header("Location: admin_login.php");
    exit;
}

$sql = "SELECT * FROM messages ORDER BY sent_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f9fafb; }
        .container { margin-top: 40px; max-width: 900px; }
        .card {
            border-left: 5px solid #1e40af;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 6px 10px rgba(0,0,0,0.05);
        }
        .card h5 {
            margin-bottom: 10px;
            color: #1e3a8a;
        }
        .meta {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">User Messages</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card">
                <h5><?= htmlspecialchars($row['subject']) ?></h5>
                <div class="meta">
                    From: <?= htmlspecialchars($row['user_name']) ?> (<?= htmlspecialchars($row['user_email']) ?>) <br>
                    Sent: <?= date("d M Y, h:i A", strtotime($row['sent_at'])) ?>
                </div>
                <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">No messages found.</div>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="text-center mt-4">
        <a href="index_admin.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
