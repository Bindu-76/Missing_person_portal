<?php
require_once "config.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch person details
$sql = "SELECT * FROM missing_reports WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$person = $result->fetch_assoc();

if (!$person) {
    echo "<h2 class='text-center mt-5'>Person not found.</h2>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($person['name']) ?> - Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .profile-img {
            max-height: 400px;
            width: 100%;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .detail-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="container my-5">
    <a href="index.php" class="btn btn-secondary mb-4">← Back to Home</a>

    <div class="row">
        <!-- Left Side: Image -->
        <div class="col-md-5">
            <img src="uploads/<?= htmlspecialchars($person['photo']) ?>" alt="<?= htmlspecialchars($person['name']) ?>" class="profile-img img-fluid">
        </div>

        <!-- Right Side: Details -->
        <div class="col-md-7">
            <div class="detail-box">
                <h2><?= htmlspecialchars($person['name']) ?></h2>
                <hr>
                <p><strong>Age:</strong> <?= $person['age'] ?? 'N/A' ?></p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($person['gender']) ?></p>
                <p><strong>Last Seen Date:</strong> <?= date("d M Y", strtotime($person['last_seen_date'])) ?></p>
                <p><strong>Last Seen Location:</strong> <?= htmlspecialchars($person['last_seen_location']) ?></p>
                <p><strong>Contact Number:</strong> <?= htmlspecialchars($person['contact_number']) ?></p>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($person['description'])) ?></p>
                <p><strong>Submitted At:</strong> <?= date("d M Y, h:i A", strtotime($person['submitted_at'])) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($person['status']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
