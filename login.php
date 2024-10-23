<?php
session_start(); // Start the session
require_once "config/db.php"; // Include the database connection

// Initialize variables
$username = "";
$error = ""; // Initialize error message

// Check if the login form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username']; // Store the entered username
    $password = $_POST['password'];

    // Prepare the SQL query to check the username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // If user exists
        if ($user) {
            // Check password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'name' => $user['name']
                ];

                // Redirect to the appropriate dashboard based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php"); // Correct the path to admin dashboard
                } else if ($user['role'] === 'staff') {
                    header("Location: staff_dashboard.php"); // Correct the path to staff dashboard
                }
                exit;
            }
        }
        // If we reach this point, it means the username or password is incorrect
        $error = "Invalid username or password!";
        $stmt->close();
    } else {
        $error = "Could not prepare statement!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }

        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
        }

        .login-container h2 {
            color: #2c3e50;
            font-size: 26px;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .login-container input:focus {
            border-color: #3498db;
            outline: none;
        }

        .toggle-password {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .toggle-password input {
            flex: 1;
            margin-right: 5px;
        }

        .toggle-password button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 10px; 
            width: 40px;
        }

        .toggle-password button:hover {
            color: #3498db;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .error-message {
            color: red;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-container::before {
            content: '';
            background: url('medical-icon.png') no-repeat center center;
            background-size: 100px 100px;
            opacity: 0.05;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            z-index: -1;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login to Surgical Equipment Management System</h2>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required aria-label="Username">
        
        <div class="toggle-password">
            <input type="password" id="password" name="password" placeholder="Password" required aria-label="Password">
            <button type="button" id="toggle-password" aria-label="Toggle password visibility">👁️</button>
        </div>
        
        <button type="submit">Login</button>
    </form>
    
    <!--sign up button-->
    <button onclick="window.location.href='sign_up.php'" style="margin-top: 15px;">Sign Up (Staff Only)</button>

    <!--forgot password-->
    <p>Forgot your password? <a href="update_password.php" style="color: #3498db;">Update it here</a></p>

    <?php if (!empty($error)): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
</div>

<script>
    document.getElementById('toggle-password').addEventListener('click', function() {
        const passwordField = document.getElementById('password');
        const passwordFieldType = passwordField.getAttribute('type');
        
        // Toggle password visibility
        if (passwordFieldType === 'password') {
            passwordField.setAttribute('type', 'text');
        } else {
            passwordField.setAttribute('type', 'password');
        }
    });
</script>

</body>
</html>
