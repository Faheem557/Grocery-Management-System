<?php

session_start();

require_once "config/database.php";
require_once "includes/current_user.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"], $_SESSION["shop_id"])) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

$user = getCurrentUser($conn);

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Default Values
|--------------------------------------------------------------------------
*/

$user_id = $user["id"];

$name = $user["name"] ?? "";
$username = $user["username"] ?? "";
$phone = $user["phone"] ?? "";
$email = $user["email"] ?? "";
$role = $user["role"] ?? "";
$status = $user["status"] ?? "";
$profile_photo = $user["profile_photo"] ?? "";
$created_at = $user["created_at"] ?? "";

$initials = getUserInitials($name);
$photo_url = getProfilePhotoUrl($profile_photo);

$success_message = "";
$error_message = "";


/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_profile"])) {

    $new_name = trim($_POST["name"] ?? "");
    $new_username = trim($_POST["username"] ?? "");
    $new_phone = trim($_POST["phone"] ?? "");
    $new_email = trim($_POST["email"] ?? "");

    $new_photo_path = $profile_photo;
    $uploaded_new_photo = false;
    $new_photo_full_path = "";
    $old_photo_full_path = "";


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($new_name === "") {

        $error_message = "Name is required.";

    } elseif (strlen($new_name) < 3) {

        $error_message = "Name must contain at least 3 characters.";

    } elseif ($new_username === "") {

        $error_message = "Username is required.";

    } elseif (strlen($new_username) < 3) {

        $error_message = "Username must contain at least 3 characters.";

    } elseif ($new_email !== "" && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {

        $error_message = "Please enter a valid email address.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check Username
    |--------------------------------------------------------------------------
    */

    if ($error_message === "") {

        $sql = "SELECT id
                FROM users
                WHERE username = ?
                AND shop_id = ?
                AND id != ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            $error_message = "Database error while checking username.";

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "sii",
                $new_username,
                $_SESSION["shop_id"],
                $user_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $error_message = "This username is already in use.";
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Photo Upload
    |--------------------------------------------------------------------------
    */

    if (
        $error_message === "" &&
        isset($_FILES["profile_photo"]) &&
        $_FILES["profile_photo"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES["profile_photo"]["error"] !== UPLOAD_ERR_OK) {

            $error_message = "There was a problem uploading the profile photo.";

        } else {

            $file = $_FILES["profile_photo"];

            /*
            | Maximum size = 2 MB
            */

            if ($file["size"] > 2 * 1024 * 1024) {

                $error_message = "Profile photo must be less than 2 MB.";

            } else {

                /*
                | Check real image
                */

                $image_info = @getimagesize($file["tmp_name"]);

                if ($image_info === false) {

                    $error_message = "The selected file is not a valid image.";

                } else {

                    $allowed_types = [
                        IMAGETYPE_JPEG => "jpg",
                        IMAGETYPE_PNG  => "png",
                        IMAGETYPE_WEBP => "webp"
                    ];

                    $image_type = $image_info[2];

                    if (!isset($allowed_types[$image_type])) {

                        $error_message = "Only JPG, PNG and WEBP images are allowed.";

                    } else {

                        /*
                        | Create upload folder if it doesn't exist
                        */

                        $upload_directory = __DIR__ . "/uploads/profile_photos/";

                        if (!is_dir($upload_directory)) {

                            if (!mkdir($upload_directory, 0755, true)) {
                                $error_message = "Could not create profile photo folder.";
                            }
                        }


                        /*
                        | Upload
                        */

                        if ($error_message === "") {

                            $extension = $allowed_types[$image_type];

                            $unique_name =
                                "user_" .
                                $user_id .
                                "_" .
                                time() .
                                "_" .
                                bin2hex(random_bytes(4)) .
                                "." .
                                $extension;

                            $new_photo_full_path =
                                $upload_directory . $unique_name;

                            $new_photo_path =
                                "uploads/profile_photos/" . $unique_name;


                            if (move_uploaded_file(
                                $file["tmp_name"],
                                $new_photo_full_path
                            )) {

                                $uploaded_new_photo = true;

                            } else {

                                $error_message = "Could not save the profile photo.";
                            }
                        }
                    }
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Database
    |--------------------------------------------------------------------------
    */

    if ($error_message === "") {

        $sql = "UPDATE users
                SET
                    name = ?,
                    username = ?,
                    phone = ?,
                    email = ?,
                    profile_photo = ?
                WHERE id = ?
                AND shop_id = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            $error_message = "Database update failed.";

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "sssssii",
                $new_name,
                $new_username,
                $new_phone,
                $new_email,
                $new_photo_path,
                $user_id,
                $_SESSION["shop_id"]
            );


            if (mysqli_stmt_execute($stmt)) {

                /*
                |--------------------------------------------------------------------------
                | Delete Old Photo
                |--------------------------------------------------------------------------
                */

                if ($uploaded_new_photo && !empty($profile_photo)) {

                    $old_photo_full_path = __DIR__ . "/" . ltrim(
                        $profile_photo,
                        "/"
                    );

                    if (
                        file_exists($old_photo_full_path) &&
                        strpos(
                            realpath($old_photo_full_path),
                            realpath(__DIR__ . "/uploads/profile_photos/")
                        ) === 0
                    ) {
                        @unlink($old_photo_full_path);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Update Session
                |--------------------------------------------------------------------------
                */

                $_SESSION["user_name"] = $new_name;
                $_SESSION["username"] = $new_username;


                /*
                |--------------------------------------------------------------------------
                | Reload User
                |--------------------------------------------------------------------------
                */

                $user = getCurrentUser($conn);

                $name = $user["name"];
                $username = $user["username"];
                $phone = $user["phone"];
                $email = $user["email"];
                $role = $user["role"];
                $status = $user["status"];
                $profile_photo = $user["profile_photo"];
                $created_at = $user["created_at"];

                $initials = getUserInitials($name);
                $photo_url = getProfilePhotoUrl($profile_photo);

                $success_message = "Profile updated successfully.";

            } else {

                /*
                | If database update failed, remove newly uploaded image
                */

                if (
                    $uploaded_new_photo &&
                    file_exists($new_photo_full_path)
                ) {
                    @unlink($new_photo_full_path);
                }

                $error_message = "Profile could not be updated.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Profile</title>

    <link
        rel="stylesheet"
        href="assets/css/profile.css"
    >

</head>

<body>

<div class="profile-page">

    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="page-header">

        <div>
            <p class="page-small-title">ACCOUNT</p>

            <h1>My Profile</h1>

            <p class="page-description">
                Manage your personal information and profile photo.
            </p>
        </div>

    </div>


    <!-- =====================================================
         MESSAGES
    ====================================================== -->

    <?php if ($success_message !== ""): ?>

        <div class="alert success-alert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>

    <?php endif; ?>


    <?php if ($error_message !== ""): ?>

        <div class="alert error-alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>

    <?php endif; ?>


    <!-- =====================================================
         PROFILE CARD
    ====================================================== -->

    <div class="profile-card">

        <div class="profile-cover"></div>

        <div class="profile-content">

            <div class="profile-photo-area">

                <?php if ($photo_url !== ""): ?>

                    <img
                        src="<?php echo htmlspecialchars($photo_url); ?>"
                        class="profile-photo"
                        id="mainProfilePhoto"
                        alt="Profile Photo"
                    >

                <?php else: ?>

                    <div
                        class="profile-initials"
                        id="mainProfileInitials"
                    >
                        <?php echo htmlspecialchars($initials); ?>
                    </div>

                <?php endif; ?>


                <button
                    type="button"
                    class="camera-button"
                    id="cameraButton"
                    title="Change Profile Photo"
                >
                    📷
                </button>

            </div>


            <div class="profile-main-info">

                <h2 id="displayName">
                    <?php echo htmlspecialchars($name); ?>
                </h2>

                <p>
                    @<?php echo htmlspecialchars($username); ?>
                </p>

                <span class="role-badge">
                    <?php echo htmlspecialchars(ucfirst($role)); ?>
                </span>

            </div>


            <div class="profile-actions">

                <button
                    type="button"
                    class="edit-button"
                    id="openModalButton"
                >
                    Edit Profile
                </button>

            </div>

        </div>

    </div>


    <!-- =====================================================
         INFORMATION CARDS
    ====================================================== -->

    <div class="information-grid">

        <div class="information-card">

            <div class="card-title">
                Personal Information
            </div>

            <div class="info-row">

                <span>Name</span>

                <strong>
                    <?php echo htmlspecialchars($name); ?>
                </strong>

            </div>

            <div class="info-row">

                <span>Username</span>

                <strong>
                    <?php echo htmlspecialchars($username); ?>
                </strong>

            </div>

            <div class="info-row">

                <span>Phone</span>

                <strong>
                    <?php
                    echo $phone !== ""
                        ? htmlspecialchars($phone)
                        : "Not added";
                    ?>
                </strong>

            </div>

            <div class="info-row">

                <span>Email</span>

                <strong>
                    <?php
                    echo $email !== ""
                        ? htmlspecialchars($email)
                        : "Not added";
                    ?>
                </strong>

            </div>

        </div>


        <div class="information-card">

            <div class="card-title">
                Account Information
            </div>

            <div class="info-row">

                <span>Role</span>

                <strong>
                    <?php echo htmlspecialchars(ucfirst($role)); ?>
                </strong>

            </div>

            <div class="info-row">

                <span>Status</span>

                <strong class="status-text">
                    <?php echo htmlspecialchars(ucfirst($status)); ?>
                </strong>

            </div>

            <div class="info-row">

                <span>Member Since</span>

                <strong>
                    <?php
                    echo !empty($created_at)
                        ? date("d M Y", strtotime($created_at))
                        : "-";
                    ?>
                </strong>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     EDIT PROFILE MODAL
========================================================== -->

<div
    class="modal-overlay"
    id="editModal"
>

    <div class="edit-modal">

        <div class="modal-header">

            <div>

                <h2>Edit Profile</h2>

                <p>
                    Update your profile information.
                </p>

            </div>

            <button
                type="button"
                class="close-button"
                id="closeModalButton"
            >
                &times;
            </button>

        </div>


        <!-- ONE AND ONLY PROFILE FORM -->

        <form
            method="POST"
            action=""
            enctype="multipart/form-data"
            id="profileForm"
        >

            <!-- =================================================
                 PHOTO
            ================================================== -->

            <div class="modal-photo-section">

                <div class="modal-photo-wrapper">

                    <?php if ($photo_url !== ""): ?>

                        <img
                            src="<?php echo htmlspecialchars($photo_url); ?>"
                            class="modal-photo"
                            id="modalProfilePhoto"
                            alt="Profile Photo"
                        >

                    <?php else: ?>

                        <div
                            class="modal-initials"
                            id="modalProfileInitials"
                        >
                            <?php echo htmlspecialchars($initials); ?>
                        </div>

                    <?php endif; ?>


                    <button
                        type="button"
                        class="modal-camera-button"
                        id="modalCameraButton"
                    >
                        📷
                    </button>

                </div>

                <p>Click camera to change photo</p>

            </div>


            <!-- Hidden File Input -->

            <input
                type="file"
                id="profilePhotoInput"
                name="profile_photo"
                accept="image/jpeg,image/png,image/webp"
                hidden
            >


            <!-- =================================================
                 FORM FIELDS
            ================================================== -->

            <div class="form-grid">

                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo htmlspecialchars($name); ?>"
                        required
                    >

                    <small class="field-error" id="nameError"></small>

                </div>


                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php echo htmlspecialchars($username); ?>"
                        required
                    >

                    <small
                        class="field-error"
                        id="usernameError"
                    ></small>

                </div>


                <div class="form-group">

                    <label for="phone">
                        Phone
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="<?php echo htmlspecialchars($phone); ?>"
                        placeholder="03XXXXXXXXX"
                    >

                </div>


                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="example@gmail.com"
                    >

                    <small
                        class="field-error"
                        id="emailError"
                    ></small>

                </div>

            </div>


            <!-- =================================================
                 BUTTONS
            ================================================== -->

            <div class="modal-footer">

                <button
                    type="button"
                    class="cancel-button"
                    id="cancelButton"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    name="update_profile"
                    class="save-button"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<script src="assets/js/profile.js"></script>

</body>

</html>