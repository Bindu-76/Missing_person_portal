<?php
session_start();
require_once "config.php";

$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm  = $_POST["confirm_password"];
    $role     = $_POST["role"];

    if (empty($name) || empty($email) || empty($password) || empty($confirm) || empty($role)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!in_array($role, ['admin', 'police'])) {
        $error = "Invalid role selected.";
    } else {
        // Count existing admins/police
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $count = $result['count'];

        if (($role === 'admin' && $count >= 2) || ($role === 'police' && $count >= 6)) {
            $error = "The number of allowed $role accounts has been reached.";
        } else {
            // Check for existing email
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "An account with this email already exists.";
            } else {
                // Insert user
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $hashed, $role);
                if ($stmt->execute()) {
                    $success = "Registration successful! You can now log in.";
                } else {
                    $error = "Something went wrong. Try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin / Police Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }

        .bg-cover {
            background: url('images/signup_background.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signup-box {
            max-width: 500px;
            width: 100%;
            padding: 30px;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="bg-cover">
    <div class="signup-box">
        <h2 class="text-center mb-4">Admin / Police Signup</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_signup.php">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="toggle('password')">Show</button>
                </div>
            </div>

            <div class="mb-3">
                <label>Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="toggle('confirm')">Show</button>
                </div>
            </div>

            <div class="mb-3">
                <label>Register as</label>
                <select name="role" class="form-select" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="police">Police</option>
                </select>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success">Register</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggle(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
