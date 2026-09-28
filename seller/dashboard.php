<?php

session_start();

require_once "../config/database.php";

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
    header("Location: ../index.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$shop_id = (int) $_SESSION["shop_id"];
/*
|--------------------------------------------------------------------------
| Get Latest User Data From Database
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, name, username, role, status, profile_photo FROM users WHERE id = ? AND shop_id = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed.");
}

mysqli_stmt_bind_param($stmt, "ii", $user_id, $shop_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);
/*
|--------------------------------------------------------------------------
| User Not Found
|--------------------------------------------------------------------------
*/
if (!$user) {

    session_destroy();
    header("Location: ../index.php");
    exit;
}
/*
|--------------------------------------------------------------------------
| Latest User Values
|--------------------------------------------------------------------------
*/
$name = $user["name"] ?? "User";

$role = $user["role"] ?? "seller";

$profile_photo = $user["profile_photo"] ?? "";
/*
|--------------------------------------------------------------------------
| Safety Check
|--------------------------------------------------------------------------
*/
if ($name === "") {
    header("Location: ../index.php", true, 303);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Seller Dashboard | Grocery Management System</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>

<!-- ================= SIDEBAR ================= -->

<aside class="sidebar">

    <div class="brand">

        <img src="../assets/IMAGES/mainLogo.png"
             alt="Grocery Management System">

        <div>
            <h2>Grocery Manager</h2>
            <span>Seller Panel</span>
        </div>

    </div>


    <nav class="sidebar-nav">

        <p class="nav-title">MAIN</p>

        <a href="dashboard.php" class="nav-link active">
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>

        <a href="products.php" class="nav-link">
            <span class="nav-icon">▣</span>
            Products
        </a>

        <a href="categories.php" class="nav-link">
            <span class="nav-icon">▤</span>
            Categories
        </a>

        <a href="stock.php" class="nav-link">
            <span class="nav-icon">▥</span>
            Stock
        </a>


        <p class="nav-title">SALES</p>

        <a href="new-sale.php" class="nav-link">
            <span class="nav-icon">＋</span>
            New Sale
        </a>

        <a href="sales-history.php" class="nav-link">
            <span class="nav-icon">▤</span>
            Sales History
        </a>

        <a href="invoices.php" class="nav-link">
            <span class="nav-icon">▧</span>
            Invoices
        </a>


        <p class="nav-title">CUSTOMERS</p>

        <a href="customers.php" class="nav-link">
            <span class="nav-icon">♙</span>
            Customers
        </a>

        <a href="khata.php" class="nav-link">
            <span class="nav-icon">₨</span>
            Digital Khata
        </a>

        <a href="payments.php" class="nav-link">
            <span class="nav-icon">✓</span>
            Payments
        </a>


        <p class="nav-title">PURCHASE</p>

        <a href="suppliers.php" class="nav-link">
            <span class="nav-icon">▣</span>
            Suppliers
        </a>

        <a href="purchases.php" class="nav-link">
            <span class="nav-icon">↓</span>
            Purchases
        </a>


        <p class="nav-title">BUSINESS</p>

        <a href="profit.php" class="nav-link">
            <span class="nav-icon">↗</span>
            Profit
        </a>

        <a href="reports.php" class="nav-link">
            <span class="nav-icon">▥</span>
            Reports
        </a>

        <a href="settings.php" class="nav-link">
            <span class="nav-icon">⚙</span>
            Settings
        </a>

    </nav>


    <div class="sidebar-bottom">

        <a href="#" class="nav-link">
            <span class="nav-icon">?</span>
            Help & Support
        </a>

        <a href="../logout.php" class="logout-link">
            <span class="nav-icon">↪</span>
            Logout
        </a>

    </div>

</aside>


<!-- ================= MAIN CONTENT ================= -->

<main class="main-content">

    <!-- TOPBAR -->

    <header class="topbar">

        <div class="topbar-left">

            <button class="menu-button">
                ☰
            </button>

            <div>
                <h1>Dashboard</h1>
                <p>Welcome back to your store</p>
            </div>

        </div>


        <div class="topbar-right">

            <button class="notification-button">
                🔔
                <span class="notification-dot"></span>
            </button>


<div class="profile">

    <a href="../profile.php">

        <div class="profile-avatar">

            <?php if (!empty($profile_photo)): ?>

                <img
                    src="../<?php echo htmlspecialchars($profile_photo); ?>"
                    alt="Profile Photo"
                    class="dashboard-profile-photo"
                >

            <?php else: ?>

                <?php
                echo strtoupper(substr($name, 0, 2));
                ?>

            <?php endif; ?>

        </div>

    </a>

</div>


                <div class="profile-info">

                    <strong>
                        <?php echo htmlspecialchars($name); ?>
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($role); ?>
                    </span>

                </div>

            </div>

        </div>

    </header>


    <!-- ================= DASHBOARD CONTENT ================= -->

    <div class="dashboard-content">


        <!-- PAGE INTRO -->

        <div class="page-intro">

            <div>

                <h2>Store Overview</h2>

                <p>
                    Here is what is happening in your grocery store today.
                </p>

            </div>

            <a href="new-sale.php" class="primary-button">
                + New Sale
            </a>

        </div>


        <!-- ================= STATISTICS ================= -->

        <section class="stats-grid">


            <!-- Today's Sales -->

            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-title">
                        Today's Sales
                    </span>

                    <div class="stat-icon sales-icon">
                        ₨
                    </div>

                </div>

                <h3>₨ 0</h3>

                <p class="stat-neutral">
                    Today's total sales
                </p>

            </div>


            <!-- Today's Profit -->

            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-title">
                        Today's Profit
                    </span>

                    <div class="stat-icon profit-icon">
                        ↗
                    </div>

                </div>

                <h3>₨ 0</h3>

                <p class="stat-neutral">
                    Today's estimated profit
                </p>

            </div>


            <!-- Products -->

            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-title">
                        Total Products
                    </span>

                    <div class="stat-icon product-icon">
                        ▣
                    </div>

                </div>

                <h3>0</h3>

                <p class="stat-neutral">
                    Active products
                </p>

            </div>


            <!-- Customers -->

            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-title">
                        Customers
                    </span>

                    <div class="stat-icon customer-icon">
                        ♙
                    </div>

                </div>

                <h3>0</h3>

                <p class="stat-neutral">
                    Active customers
                </p>

            </div>


        </section>


        <!-- ================= MAIN GRID ================= -->

        <section class="dashboard-grid">


            <!-- RECENT SALES -->

            <div class="panel recent-sales">

                <div class="panel-header">

                    <div>

                        <h3>Recent Sales</h3>

                        <p>
                            Latest transactions from your store
                        </p>

                    </div>

                    <a href="sales-history.php" class="view-link">
                        View All
                    </a>

                </div>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr>

                                <td>#INV-0001</td>

                                <td>Walk-in Customer</td>

                                <td>Today</td>

                                <td>₨ 0</td>

                                <td>
                                    <span class="status paid">
                                        Paid
                                    </span>
                                </td>

                            </tr>


                            <tr>

                                <td colspan="5" class="empty-row">
                                    No more sales available
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- LOW STOCK -->

            <div class="panel low-stock">

                <div class="panel-header">

                    <div>

                        <h3>Low Stock</h3>

                        <p>
                            Products that need attention
                        </p>

                    </div>

                    <a href="stock.php" class="view-link">
                        View Stock
                    </a>

                </div>


                <div class="stock-list">

                    <div class="empty-stock">

                        <span>✓</span>

                        <p>
                            No low stock products
                        </p>

                    </div>

                </div>

            </div>


        </section>


        <!-- ================= BOTTOM GRID ================= -->

        <section class="bottom-grid">


            <!-- QUICK ACTIONS -->

            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h3>Quick Actions</h3>

                        <p>
                            Common store operations
                        </p>

                    </div>

                </div>


                <div class="quick-actions">


                    <a href="new-sale.php" class="quick-action">

                        <span>＋</span>

                        <strong>New Sale</strong>

                        <small>
                            Create a new invoice
                        </small>

                    </a>


                    <a href="products.php" class="quick-action">

                        <span>▣</span>

                        <strong>Add Product</strong>

                        <small>
                            Add item to inventory
                        </small>

                    </a>


                    <a href="customers.php" class="quick-action">

                        <span>♙</span>

                        <strong>Add Customer</strong>

                        <small>
                            Register new customer
                        </small>

                    </a>


                    <a href="purchases.php" class="quick-action">

                        <span>↓</span>

                        <strong>Add Purchase</strong>

                        <small>
                            Record stock purchase
                        </small>

                    </a>


                </div>

            </div>


            <!-- KHATA SUMMARY -->

            <div class="panel">

                <div class="panel-header">

                    <div>

                        <h3>Khata Summary</h3>

                        <p>
                            Customer credit overview
                        </p>

                    </div>

                    <a href="khata.php" class="view-link">
                        Open Khata
                    </a>

                </div>


                <div class="khata-summary">


                    <div class="khata-row">

                        <span>Total Credit</span>

                        <strong>
                            ₨ 0
                        </strong>

                    </div>


                    <div class="khata-row">

                        <span>Received Today</span>

                        <strong class="received">
                            ₨ 0
                        </strong>

                    </div>


                    <div class="khata-row">

                        <span>Remaining</span>

                        <strong class="remaining">
                            ₨ 0
                        </strong>

                    </div>


                </div>

            </div>


        </section>


        <!-- FOOTER -->

        <footer class="dashboard-footer">

            <p>
                © 2026 Grocery Management System
            </p>

            <p>
                Seller Panel
            </p>

        </footer>


    </div>

</main>

</body>
</html>