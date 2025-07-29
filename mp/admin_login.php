<?php
session_start();
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // ✅ Check if role is admin or police and password is correct
            if (in_array($user['role'], ['admin', 'police']) && password_verify($password, $user['password'])) {
                $_SESSION["user_id"] = $user['id'];
                $_SESSION["user_email"] = $user['email'];
                $_SESSION["user_name"] = $user['name']; // ✅ fixed syntax
                $_SESSION["user_type"] = $user['role'];

                header("Location: index_admin.php");
                exit;
            } else {
                $error = "Access denied. Only Admin or Police can login here.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin / Police Login - Missing Person Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body, html {
            height: 100%;
            margin: 0;
        }

        .bg-cover {
            background: url('images/admin_background.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            max-width: 450px;
            width: 100%;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.25);
        }

        .btn-toggle {
            border-radius: 0 5px 5px 0;
        }

        .text-center a {
            display: inline-block;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<div class="bg-cover">
    <div class="login-box">
        <h2 class="text-center mb-4">Admin / Police Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <div class="mb-3">
                <label>Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary btn-toggle" onclick="togglePassword()">Show</button>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>

            <div class="text-center">
                <a href="forgot_password.php">Forgot Password?</a><br>
                <span>Don't have an account? </span><a href="admin_signup.php">Sign up here</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
