<?php

session_start();

require_once '../config/database.php';

/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'], $_SESSION['shop_id'])) {
    header("Location: index.php");
    exit();
}

$shop_id = (int) $_SESSION['shop_id'];


/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $opening_balance = (float) ($_POST['opening_balance'] ?? 0);
    $credit_limit = (float) ($_POST['credit_limit'] ?? 0);

    $status = $_POST['status'] ?? 'active';


    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if ($name === '') {
        $error = "Customer name is required.";
    } elseif ($opening_balance < 0) {
        $error = "Opening balance cannot be negative.";
    } elseif ($credit_limit < 0) {
        $error = "Credit limit cannot be negative.";
    } else {

        /*
        |--------------------------------------------------------------------------
        | Insert Customer
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            INSERT INTO customers
            (
                shop_id,
                user_id,
                name,
                phone,
                email,
                address,
                opening_balance,
                credit_limit,
                status
            )
            VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?)
        ");


        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "issssdds",
                $shop_id,
                $name,
                $phone,
                $email,
                $address,
                $opening_balance,
                $credit_limit,
                $status
            );


            if ($stmt->execute()) {

                $stmt->close();

                header("Location: customers.php");
                exit();

            } else {

                $error = "Unable to add customer: " . $stmt->error;

                $stmt->close();
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
        content="width=device-width, initial-scale=1.0">

    <title>Add Customer</title>

    <link
        rel="stylesheet"
        href="../assets/css/add-customer.css">

</head>


<body>

<div class="page">


    <!-- =========================================
         PAGE HEADER
    ========================================== -->

    <div class="page-header">

        <div class="header-left">

            <h1>Add Customer</h1>

            <p>
                Add a new customer to your grocery management system.
            </p>

        </div>


        <a
            href="customers.php"
            class="back-button">

            ← Back to Customers

        </a>

    </div>



    <!-- =========================================
         ERROR MESSAGE
    ========================================== -->

    <?php if (isset($error)): ?>

        <div class="error-message">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>



    <!-- =========================================
         CUSTOMER CARD
    ========================================== -->

    <div class="customer-card">


        <div class="card-header">

            <h2>
                Customer Information
            </h2>

            <p>
                Enter the customer's basic information below.
            </p>

        </div>



        <!-- =====================================
             FORM
        ====================================== -->

        <form
            class="customer-form"
            method="POST"
            action="">


            <div class="form-grid">


                <!-- =================================
                     BASIC INFORMATION
                ================================== -->

                <div class="section-title">

                    <h3>
                        Basic Information
                    </h3>

                    <p>
                        Customer personal information
                    </p>

                </div>



                <!-- NAME -->

                <div class="form-group">

                    <label for="customerName">

                        Customer Name

                        <span>*</span>

                    </label>

                    <input
                        type="text"
                        id="customerName"
                        name="name"
                        placeholder="Enter customer name"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        required>

                </div>



                <!-- PHONE -->

                <div class="form-group">

                    <label for="customerPhone">

                        Phone Number

                    </label>

                    <input
                        type="text"
                        id="customerPhone"
                        name="phone"
                        placeholder="03XXXXXXXXX"
                        value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label for="customerEmail">

                        Email

                    </label>

                    <input
                        type="email"
                        id="customerEmail"
                        name="email"
                        placeholder="example@gmail.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

                </div>



                <!-- STATUS -->

                <div class="form-group">

                    <label for="customerStatus">

                        Account Status

                    </label>

                    <select
                        id="customerStatus"
                        name="status">

                        <option
                            value="active"
                            <?php echo (($_POST['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>

                            Active

                        </option>

                        <option
                            value="inactive"
                            <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>

                            Inactive

                        </option>

                    </select>

                </div>



                <!-- ADDRESS -->

                <div class="form-group full-width">

                    <label for="customerAddress">

                        Address

                    </label>

                    <textarea
                        id="customerAddress"
                        name="address"
                        placeholder="Enter customer address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>

                </div>



                <!-- =================================
                     KHATA INFORMATION
                ================================== -->

                <div class="section-title">

                    <h3>
                        Khata Information
                    </h3>

                    <p>
                        Initial credit and account details
                    </p>

                </div>



                <!-- OPENING BALANCE -->

                <div class="form-group">

                    <label for="openingBalance">

                        Opening Balance

                    </label>

                    <input
                        type="number"
                        id="openingBalance"
                        name="opening_balance"
                        value="<?php echo htmlspecialchars($_POST['opening_balance'] ?? '0'); ?>"
                        min="0"
                        step="0.01"
                        placeholder="0.00">

                    <span class="form-help">

                        Enter the amount the customer already owes.

                    </span>

                </div>



                <!-- CREDIT LIMIT -->

                <div class="form-group">

                    <label for="creditLimit">

                        Credit Limit

                    </label>

                    <input
                        type="number"
                        id="creditLimit"
                        name="credit_limit"
                        value="<?php echo htmlspecialchars($_POST['credit_limit'] ?? '0'); ?>"
                        min="0"
                        step="0.01"
                        placeholder="0.00">

                    <span class="form-help">

                        Maximum credit allowed for this customer.

                    </span>

                </div>


            </div>



            <!-- =====================================
                 FORM FOOTER
            ====================================== -->

            <div class="form-footer">

                <a style="text-decoration: none;"
                    href="customers.php"
                    class="cancel-button">

                    Cancel

                </a>


                <button
                    type="submit"
                    class="save-button">

                    Add Customer

                </button>

            </div>


        </form>


    </div>

</div>

</body>

</html>