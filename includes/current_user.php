<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Get Current Logged-in User
|--------------------------------------------------------------------------
*/

function getCurrentUser($conn)
{
    if (!isset($_SESSION["user_id"], $_SESSION["shop_id"])) {
        return null;
    }

    $user_id = (int) $_SESSION["user_id"];
    $shop_id = (int) $_SESSION["shop_id"];

    $sql = "SELECT 
                id,
                shop_id,
                name,
                username,
                phone,
                email,
                role,
                status,
                profile_photo,
                created_at
            FROM users
            WHERE id = ?
            AND shop_id = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, "ii", $user_id, $shop_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        return null;
    }

    return mysqli_fetch_assoc($result);
}


/*
|--------------------------------------------------------------------------
| Profile Photo URL
|--------------------------------------------------------------------------
*/

function getProfilePhotoUrl($profile_photo)
{
    if (empty($profile_photo)) {
        return "";
    }

    return "/grocery-management/" . ltrim($profile_photo, "/");
}


/*
|--------------------------------------------------------------------------
| Generate Initials
|--------------------------------------------------------------------------
*/

function getUserInitials($name)
{
    $name = trim($name);

    if ($name === "") {
        return "U";
    }

    $words = preg_split('/\s+/', $name);

    if (count($words) >= 2) {
        return strtoupper(
            substr($words[0], 0, 1) .
            substr($words[1], 0, 1)
        );
    }

    return strtoupper(substr($name, 0, 2));
}
?>