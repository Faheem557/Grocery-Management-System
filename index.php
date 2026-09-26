<?php

session_start();

require_once "config/database.php";

$username_error = "";
$password_error = "";
$form_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if(isset($_POST['Login'])){
            $username = $_POST['username'] ?? "";
            $password = $_POST['password'] ?? "";

   if ($username === "" || $password === "") {

         if ($username === "") {
            $username_error = "Please enter username.";
        }

        if ($password === "") {
            $password_error = "Please enter password.";
        }
    }else{

            $sql = "SELECT id, shop_id, name, username, password, role, status FROM users WHERE username = ? LIMIT 1";

            $stmt = mysqli_prepare($conn,$sql);

            mysqli_stmt_bind_param($stmt, "s", $username);

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1){

                $user = mysqli_fetch_assoc($result);

            if ($user && $user["status"] === "active"){

                if (password_verify($password, $user["password"])){

                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["shop_id"] = $user["shop_id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["role"] = $user["role"];
                    
                   header("Location: success_login.php");
                    exit;
                }else{

                    $form_error = "Invalid username or password";
                }
            }else{
    
                $error = "Your account is inactive.";
            }
        }else{

            $form_error = "Invalid username or password";
        }
            mysqli_stmt_close($stmt);
    }
};
};


// if($username == $get_user['username'] && $password == $get_user['password'] and $is_active == $get_user['status']){
//    if($get_user['role'] == 'owner'){
//         echo "Owner is logged in";
//    }
//    if($get_user['role'] == 'customer'){
//         echo "customer is logged in";
//    }
// }else
//     echo "Login failed try again";
// }
// }

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

            <div class="logo-box">
                <img src="assets/IMAGES/mainLogo.png" alt="Grocery Management System Logo">
            </div>

            <div class="login-heading">
                <h1>Seller Login</h1>
                <p>Login to manage your grocery store</p>
            </div>

            <form action="#" method="POST">
                
                <?php if ($form_error !== ""): ?>
                <div class="form-error">
                <?php echo htmlspecialchars($form_error); ?>
                </div>
                <?php endif; ?>

                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>

                </div>


                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>

                </div>

                <button type="submit" name="Login" class="login-btn">Login</button>

            </form>

            <div class="login-footer">
                <p>Grocery Management System</p>
            </div>

        </div>

    </div>

</body>
</html>