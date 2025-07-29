<?php
session_start();
require_once "config.php";

$searchTerm = '';
$results = [];

if (isset($_GET['query'])) {
    $searchTerm = trim($_GET['query']);

    if (!empty($searchTerm)) {
        $stmt = $conn->prepare("
            SELECT id, name, photo 
            FROM missing_reports 
            WHERE 
                name LIKE CONCAT('%', ?, '%') 
                OR last_seen_location LIKE CONCAT('%', ?, '%') 
                OR last_seen_date LIKE CONCAT('%', ?, '%')
                AND status != 'Rejected'
            ORDER BY submitted_at DESC
        ");
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - Missing Person Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f4f6f9;
        }

        .missing-card img {
            height: 250px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <a href="index.php" class="btn btn-secondary mb-4">← Back to Home</a>

    <h2 class="mb-4">Search Results for: <span class="text-primary"><?= htmlspecialchars($searchTerm) ?></span></h2>

    <?php if (!empty($results)): ?>
        <div class="row">
            <?php foreach ($results as $row): 
                $photo = $row['photo'] ?: 'default.jpg';
            ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <a href="person_details.php?id=<?= $row['id'] ?>" class="text-decoration-none text-dark">
                        <div class="card missing-card h-100 shadow-sm">
                            <img src="uploads/<?= htmlspecialchars($photo) ?>" class="card-img-top img-fluid" alt="<?= htmlspecialchars($row['name']) ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No matching records found.</div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
