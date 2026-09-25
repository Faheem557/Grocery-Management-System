<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION["user_name"];

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Successful | Grocery Management System</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7f5;
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .success-container {
            width: 100%;
            max-width: 430px;
            padding: 20px;
        }

        .success-card {
            background: #ffffff;
            border: 1px solid #e4e8e5;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;

            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);

            animation: cardShow 0.5s ease;
        }

        .success-icon {
            width: 75px;
            height: 75px;

            margin: 0 auto 22px;

            border-radius: 50%;

            background: #e8f7ee;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #176b3a;

            font-size: 38px;
            font-weight: bold;

            animation: iconShow 0.5s ease;
        }

        .success-card h1 {
            color: #176b3a;
            font-size: 26px;
            margin-bottom: 10px;
        }

        .success-card p {
            color: #777;
            font-size: 14px;
            line-height: 1.6;
        }

        .welcome {
            color: #333 !important;
            font-size: 15px !important;
            margin-bottom: 22px;
        }

        .loader-wrapper {
            margin-top: 25px;

            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .loader {
            width: 20px;
            height: 20px;

            border: 3px solid #dce9e1;
            border-top-color: #176b3a;

            border-radius: 50%;

            animation: spin 0.8s linear infinite;
        }

        .loader-text {
            color: #666;
            font-size: 13px;
        }

        @keyframes spin {

            to {
                transform: rotate(360deg);
            }

        }

        @keyframes cardShow {

            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        @keyframes iconShow {

            from {
                transform: scale(0.6);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }

        }

    </style>

</head>

<body>

    <div class="success-container">

        <div class="success-card">

            <div class="success-icon">
                ✓
            </div>

            <h1>Login Successful</h1>

            <p class="welcome">
                Welcome, <?php echo htmlspecialchars($user_name); ?>!
            </p>

            <p>
                You have successfully logged in to the
                Grocery Management System.
            </p>

            <div class="loader-wrapper">

                <div class="loader"></div>

                <span class="loader-text">
                    Opening your dashboard...
                </span>

            </div>

        </div>

    </div>


    <script>

       setTimeout(function () {

    window.location.replace("seller/dashboard.php");

    }, 2000);


    </script>

</body>
</html>
