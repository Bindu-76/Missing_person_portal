<?php
session_start();
require_once "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Missing Person Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .main-banner {
            background: url('banner.jpg') no-repeat center center;
            background-size: cover;
            height: 400px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            text-shadow: 2px 2px 5px #000;
        }

        .main-banner h1 {
            font-size: 3rem;
            font-weight: bold;
        }

        .missing-card img {
            height: 250px;
            object-fit: cover;
            border-radius: 5px 5px 0 0;
        }

        .how-it-works {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Missing Person Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_email'])): ?>
                    <?php if ($_SESSION['user_type'] === 'user'): ?>
                        <li class="nav-item"><a class="nav-link" href="report_missing.php">Report</a></li>
                        <li class="nav-item"><a class="nav-link" href="report_status.php">My Reports</a></li>
                    <?php elseif ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'police'): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="signup_user.php">Sign Up</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Banner -->
<div class="main-banner">
    <div>
        <h1>Help Us Bring Loved Ones Home</h1>
        <p class="lead">Your action can reunite families. Report or search cases now.</p>
        <?php if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] === 'user'): ?>
            <a href="report_missing.php" class="btn btn-primary btn-lg mt-3">Report a Missing Person</a>
        <?php endif; ?>
    </div>
</div>

<!-- Main Content -->
<div class="container my-5">

    <!-- Welcome Section -->
    <div class="text-center mb-5">
        <h2>Welcome to the Missing Person Reporting Portal</h2>
        <p class="lead">Use this platform to report missing individuals, view public alerts, and track case updates securely and efficiently.</p>
    </div>

    <!-- Search Bar -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <form action="search_results.php" method="GET">
                <div class="input-group input-group-lg">
                    <input type="text" name="query" class="form-control" placeholder="Search by name, location, or date..." required>
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recently Reported -->
    <h3 class="text-center mb-4">Recently Reported Missing Persons</h3>
    <div class="row">
        <?php
        $sql = "SELECT id, name, photo FROM missing_reports ORDER BY submitted_at DESC LIMIT 12";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $photo = (!empty($row['photo']) && file_exists("uploads/" . $row['photo'])) 
                    ? $row['photo'] 
                    : 'default.jpeg';
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
        <?php
            endwhile;
        else:
            echo "<p class='text-center'>No missing persons reported yet.</p>";
        endif;
        ?>
    </div>

    <!-- How It Works -->
    <div class="how-it-works my-5">
        <h4 class="text-center mb-4">How the Portal Works</h4>
        <div class="row text-center">
            <div class="col-md-4">
                <h5>1. Report</h5>
                <p>Anyone can submit a missing person report using the report form.</p>
            </div>
            <div class="col-md-4">
                <h5>2. Review</h5>
                <p>Admins or police verify and update the case status (Verified, Closed, Rejected).</p>
            </div>
            <div class="col-md-4">
                <h5>3. Track</h5>
                <p>View updates or search through cases using the portal at any time.</p>
            </div>
        </div>
    </div>

</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center p-3">
    &copy; <?= date("Y") ?> Missing Person Portal. All Rights Reserved.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
