<?php

session_start();

require_once "config/database.php";

$username_error = "";
$password_error = "";
$form_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['Login'])) {

        $username = trim($_POST['username'] ?? "");
        $password = $_POST['password'] ?? "";

        // Empty field validation
        if ($username === "" || $password === "") {

            if ($username === "") {
                $username_error = "Please enter username.";
            }

            if ($password === "") {
                $password_error = "Please enter password.";
            }

        } else {

            // Get user from database
            $sql = "SELECT 
                        id,
                        shop_id,
                        name,
                        username,
                        phone,
                        email,
                        password,
                        role,
                        status,
                        profile_photo,
                        created_at,
                        updated_at
                    FROM users
                    WHERE username = ?
                    LIMIT 1";

            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {

                mysqli_stmt_bind_param($stmt, "s", $username);

                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) === 1) {

                    $user = mysqli_fetch_assoc($result);

                    // Check account status
                    if ($user["status"] === "active") {

                        // Check password
                        if (password_verify($password, $user["password"])) {

                            // Store user information in session
                            $_SESSION["user_id"] = $user["id"];
                            $_SESSION["shop_id"] = $user["shop_id"];
                            $_SESSION["user_name"] = $user["name"];
                            $_SESSION["username"] = $user["username"];
                            $_SESSION["role"] = $user["role"];
                            $_SESSION["status"] = $user["status"];
                            $_SESSION["photo"] = $user["profile_photo"];

                            // Login successful
                            header("Location: success_login.php");
                            exit;

                        } else {

                            $form_error = "Invalid username or password.";
                        }

                    } else {

                        $form_error = "Your account is inactive.";
                    }

                } else {

                    $form_error = "Invalid username or password.";
                }

                mysqli_stmt_close($stmt);

            } else {

                $form_error = "Something went wrong. Please try again.";
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Seller Login | Grocery Management System</title>

    <link rel="stylesheet" href="assets/css/login.css">

</head>

<body>

    <div class="login-container">

        <div class="login-card">

            <!-- Logo -->
            <div class="logo-box">

                <img
                    src="assets/IMAGES/mainLogo.png"
                    alt="Grocery Management System Logo"
                >

            </div>


            <!-- Heading -->
            <div class="login-heading">

                <h1>Seller Login</h1>

                <p>Login to manage your grocery store</p>

            </div>


            <!-- Login Form -->
            <form action="index.php" method="POST">

                <!-- General Error -->
                <?php if ($form_error !== ""): ?>

                    <div class="form-error">

                        <?php echo htmlspecialchars($form_error); ?>

                    </div>

                <?php endif; ?>


                <!-- Username -->
                <div class="input-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    >

                    <?php if ($username_error !== ""): ?>

                        <div class="input-error">

                            <?php echo htmlspecialchars($username_error); ?>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- Password -->
                <div class="input-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                    >

                    <?php if ($password_error !== ""): ?>

                        <div class="input-error">

                            <?php echo htmlspecialchars($password_error); ?>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- Login Button -->
                <button
                    type="submit"
                    name="Login"
                    class="login-btn"
                >
                    Login
                </button>

            </form>


            <!-- Footer -->
            <div class="login-footer">

                <p>Grocery Management System</p>

            </div>

        </div>

    </div>

</body>

</html>