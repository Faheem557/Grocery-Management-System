<?php
session_start();
$name = $_SESSION["user_name"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Dashboard | Grocery Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .brand {
            height: 90px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 22px;
            border-bottom: 1px solid #f0f1f3;
        }

        .brand img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .brand h2 {
            font-size: 18px;
            color: #166534;
            margin-bottom: 4px;
        }

        .brand span {
            font-size: 12px;
            color: #6b7280;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 14px;
            overflow-y: auto;
        }

        .nav-title {
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            letter-spacing: 1px;
            padding: 12px 12px 7px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 13px;
            margin: 3px 0;
            border-radius: 8px;
            color: #4b5563;
            font-size: 14px;
            transition: 0.2s;
        }

        .nav-link:hover {
            background: #f0fdf4;
            color: #15803d;
        }

        .nav-link.active {
            background: #dcfce7;
            color: #15803d;
            font-weight: 600;
        }

        .nav-icon {
            width: 22px;
            text-align: center;
            font-size: 17px;
        }

        .sidebar-bottom {
            padding: 12px 14px 18px;
            border-top: 1px solid #f0f1f3;
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 13px;
            color: #dc2626;
            font-size: 14px;
            border-radius: 8px;
        }

        .logout-link:hover {
            background: #fef2f2;
        }

        /* =========================
           MAIN CONTENT
        ========================= */

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* =========================
           TOPBAR
        ========================= */

        .topbar {
            height: 90px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .menu-button {
            display: none;
            border: none;
            background: #f3f4f6;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
        }

        .topbar h1 {
            font-size: 24px;
            margin-bottom: 4px;
        }

        .topbar p {
            color: #6b7280;
            font-size: 13px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .notification-button {
            position: relative;
            width: 40px;
            height: 40px;
            border: none;
            background: #f9fafb;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
        }

        .notification-dot {
            position: absolute;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            top: 7px;
            right: 7px;
            border: 2px solid white;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-avatar {
            width: 42px;
            height: 42px;
            background: #16a34a;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
        }

        .profile-info strong {
            font-size: 14px;
        }

        .profile-info span {
            font-size: 12px;
            color: #6b7280;
            margin-top: 3px;
        }

        /* =========================
           CONTENT
        ========================= */

        .dashboard-content {
            padding: 30px;
        }

        .page-intro {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-intro h2 {
            font-size: 23px;
            margin-bottom: 6px;
        }

        .page-intro p {
            color: #6b7280;
            font-size: 14px;
        }

        .primary-button {
            background: #16a34a;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .primary-button:hover {
            background: #15803d;
        }

        /* =========================
           STATISTICS
        ========================= */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-title {
            color: #6b7280;
            font-size: 13px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            font-weight: bold;
        }

        .orders-icon {
            background: #dcfce7;
            color: #16a34a;
        }

        .pending-icon {
            background: #fef3c7;
            color: #d97706;
        }

        .credit-icon {
            background: #fee2e2;
            color: #dc2626;
        }

        .points-icon {
            background: #dbeafe;
            color: #2563eb;
        }

        .stat-card h3 {
            font-size: 25px;
            margin-top: 17px;
            margin-bottom: 8px;
        }

        .stat-positive {
            color: #16a34a;
            font-size: 12px;
        }

        .stat-warning {
            color: #d97706;
            font-size: 12px;
        }

        .stat-danger {
            color: #dc2626;
            font-size: 12px;
        }

        .stat-neutral {
            color: #6b7280;
            font-size: 12px;
        }

        /* =========================
           DASHBOARD GRID
        ========================= */

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .panel {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .panel-header h3 {
            font-size: 17px;
            margin-bottom: 5px;
        }

        .panel-header p {
            font-size: 12px;
            color: #6b7280;
        }

        .view-link {
            color: #16a34a;
            font-size: 13px;
            font-weight: 600;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        /* =========================
           ORDERS TABLE
        ========================= */

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 10px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
        }

        td {
            padding: 14px 10px;
            border-bottom: 1px solid #f0f1f3;
            font-size: 13px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status {
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .delivered {
            background: #dcfce7;
            color: #15803d;
        }

        .processing {
            background: #dbeafe;
            color: #2563eb;
        }

        .pending {
            background: #fef3c7;
            color: #b45309;
        }

        .cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

        /* =========================
           RECOMMENDED PRODUCTS
        ========================= */

        .product-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .product-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            border: 1px solid #f0f1f3;
            border-radius: 9px;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .product-image {
            width: 45px;
            height: 45px;
            background: #f0fdf4;
            color: #15803d;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
        }

        .product-details {
            display: flex;
            flex-direction: column;
        }

        .product-details strong {
            font-size: 13px;
        }

        .product-details span {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .product-price {
            color: #15803d;
            font-weight: 700;
            font-size: 13px;
        }

        /* =========================
           BOTTOM GRID
        ========================= */

        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* =========================
           QUICK ACTIONS
        ========================= */

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .quick-action {
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            transition: 0.2s;
        }

        .quick-action:hover {
            border-color: #86efac;
            background: #f0fdf4;
        }

        .quick-action > span {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            background: #dcfce7;
            color: #15803d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 17px;
        }

        .quick-action strong {
            font-size: 13px;
        }

        .quick-action small {
            color: #6b7280;
            font-size: 11px;
            margin-top: 4px;
        }

        /* =========================
           ACCOUNT SUMMARY
        ========================= */

        .account-summary {
            display: flex;
            flex-direction: column;
        }

        .account-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f1f3;
            font-size: 13px;
        }

        .account-row:last-child {
            border-bottom: none;
        }

        .account-row span {
            color: #6b7280;
        }

        .account-row strong {
            color: #1f2937;
        }

        .credit {
            color: #dc2626 !important;
        }

        .green {
            color: #16a34a !important;
        }

        /* =========================
           FOOTER
        ========================= */

        .dashboard-footer {
            margin-top: 30px;
            padding: 20px 0;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            color: #9ca3af;
            font-size: 12px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {

            .sidebar {
                width: 220px;
                transform: translateX(-100%);
                transition: 0.3s;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-button {
                display: block;
            }

            .topbar {
                padding: 0 18px;
            }

            .profile-info {
                display: none;
            }

            .dashboard-content {
                padding: 20px;
            }

            .page-intro {
                align-items: flex-start;
                gap: 15px;
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 500px) {

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .dashboard-footer {
                flex-direction: column;
                gap: 5px;
            }

            .topbar h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- =========================
         SIDEBAR
    ========================= -->

    <aside class="sidebar" id="sidebar">

        <div class="brand">

            <img
                src="../assets/IMAGES/mainLogo.png"
                alt="Grocery Management System"
            >

            <div>
                <h2>Grocery Manager</h2>
                <span>Customer Panel</span>
            </div>

        </div>

        <nav class="sidebar-nav">

            <p class="nav-title">MAIN</p>

            <a href="#" class="nav-link active">
                <span class="nav-icon">⌂</span>
                Dashboard
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">▣</span>
                Shop Products
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">▤</span>
                Categories
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">🛒</span>
                My Cart
            </a>

            <p class="nav-title">ORDERS</p>

            <a href="#" class="nav-link">
                <span class="nav-icon">＋</span>
                Place Order
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">▤</span>
                My Orders
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">▧</span>
                Invoices
            </a>

            <p class="nav-title">ACCOUNT</p>

            <a href="#" class="nav-link">
                <span class="nav-icon">♙</span>
                My Profile
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">₨</span>
                Payments
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">♥</span>
                Wishlist
            </a>

            <p class="nav-title">SUPPORT</p>

            <a href="#" class="nav-link">
                <span class="nav-icon">?</span>
                Help & Support
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">⚙</span>
                Settings
            </a>

        </nav>

        <div class="sidebar-bottom">

            <a href="#" class="nav-link">
                <span class="nav-icon">✉</span>
                Contact Us
            </a>

            <a href="../logout.php" class="logout-link">
                <span class="nav-icon">↪</span>
                Logout
            </a>

        </div>

    </aside>


    <!-- =========================
         MAIN CONTENT
    ========================= -->

    <main class="main-content">

        <!-- TOPBAR -->

        <header class="topbar">

            <div class="topbar-left">

                <button
                    class="menu-button"
                    id="menuButton"
                    type="button"
                >
                    ☰
                </button>

                <div>
                    <h1>Customer Dashboard</h1>
                    <p>Welcome back to your grocery store</p>
                </div>

            </div>

            <div class="topbar-right">

                <button
                    class="notification-button"
                    type="button"
                >
                    🔔
                    <span class="notification-dot"></span>
                </button>

                <div class="profile">

                    <div class="profile-avatar">
                        <?php
                        echo $name[0].$name[1];
                        ?>
                    </div>

                    <div class="profile-info">
                        <strong><?php echo $name ?></strong>
                        <span><?php echo $_SESSION["role"] ?></span>
                    </div>

                </div>

            </div>

        </header>


        <!-- =========================
             DASHBOARD CONTENT
        ========================= -->

        <div class="dashboard-content">

            <!-- INTRO -->

            <div class="page-intro">

                <div>

                    <h2>My Store Overview</h2>

                    <p>
                        View your orders, account activity and shopping information.
                    </p>

                </div>

                <a href="#" class="primary-button">
                    + Shop Now
                </a>

            </div>


            <!-- =========================
                 STATISTICS
            ========================= -->

            <section class="stats-grid">

                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Total Orders
                        </span>

                        <div class="stat-icon orders-icon">
                            🛒
                        </div>

                    </div>

                    <h3>24</h3>

                    <p class="stat-positive">
                        ↑ 4 <span>this month</span>
                    </p>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Pending Orders
                        </span>

                        <div class="stat-icon pending-icon">
                            ⏳
                        </div>

                    </div>

                    <h3>2</h3>

                    <p class="stat-warning">
                        Currently processing
                    </p>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Total Spent
                        </span>

                        <div class="stat-icon credit-icon">
                            ₨
                        </div>

                    </div>

                    <h3>₨ 48,650</h3>

                    <p class="stat-neutral">
                        All completed orders
                    </p>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <span class="stat-title">
                            Reward Points
                        </span>

                        <div class="stat-icon points-icon">
                            ★
                        </div>

                    </div>

                    <h3>1,280</h3>

                    <p class="stat-positive">
                        Available points
                    </p>

                </div>

            </section>


            <!-- =========================
                 MAIN GRID
            ========================= -->

            <section class="dashboard-grid">

                <!-- RECENT ORDERS -->

                <div class="panel">

                    <div class="panel-header">

                        <div>

                            <h3>Recent Orders</h3>

                            <p>
                                Your latest grocery purchases
                            </p>

                        </div>

                        <a href="#" class="view-link">
                            View All
                        </a>

                    </div>


                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>
                                    <th>Order</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>

                            </thead>

                            <tbody>

                                <tr>

                                    <td>
                                        #ORD-1024
                                    </td>

                                    <td>
                                        Today, 02:15 PM
                                    </td>

                                    <td>
                                        5 Items
                                    </td>

                                    <td>
                                        ₨ 4,250
                                    </td>

                                    <td>
                                        <span class="status delivered">
                                            Delivered
                                        </span>
                                    </td>

                                </tr>

                                <tr>

                                    <td>
                                        #ORD-1023
                                    </td>

                                    <td>
                                        Yesterday
                                    </td>

                                    <td>
                                        8 Items
                                    </td>

                                    <td>
                                        ₨ 2,850
                                    </td>

                                    <td>
                                        <span class="status processing">
                                            Processing
                                        </span>
                                    </td>

                                </tr>

                                <tr>

                                    <td>
                                        #ORD-1022
                                    </td>

                                    <td>
                                        18 Sep 2026
                                    </td>

                                    <td>
                                        4 Items
                                    </td>

                                    <td>
                                        ₨ 6,100
                                    </td>

                                    <td>
                                        <span class="status delivered">
                                            Delivered
                                        </span>
                                    </td>

                                </tr>

                                <tr>

                                    <td>
                                        #ORD-1021
                                    </td>

                                    <td>
                                        15 Sep 2026
                                    </td>

                                    <td>
                                        6 Items
                                    </td>

                                    <td>
                                        ₨ 1,920
                                    </td>

                                    <td>
                                        <span class="status pending">
                                            Pending
                                        </span>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- RECOMMENDED PRODUCTS -->

                <div class="panel">

                    <div class="panel-header">

                        <div>

                            <h3>Recommended</h3>

                            <p>
                                Products you may like
                            </p>

                        </div>

                        <a href="#" class="view-link">
                            Shop All
                        </a>

                    </div>


                    <div class="product-list">

                        <div class="product-item">

                            <div class="product-info">

                                <div class="product-image">
                                    CO
                                </div>

                                <div class="product-details">

                                    <strong>
                                        Cooking Oil 5L
                                    </strong>

                                    <span>
                                        Grocery
                                    </span>

                                </div>

                            </div>

                            <div class="product-price">
                                ₨ 2,850
                            </div>

                        </div>


                        <div class="product-item">

                            <div class="product-info">

                                <div class="product-image">
                                    RI
                                </div>

                                <div class="product-details">

                                    <strong>
                                        Basmati Rice 5kg
                                    </strong>

                                    <span>
                                        Rice & Grains
                                    </span>

                                </div>

                            </div>

                            <div class="product-price">
                                ₨ 1,250
                            </div>

                        </div>


                        <div class="product-item">

                            <div class="product-info">

                                <div class="product-image">
                                    MI
                                </div>

                                <div class="product-details">

                                    <strong>
                                        Milk Pack 1L
                                    </strong>

                                    <span>
                                        Dairy
                                    </span>

                                </div>

                            </div>

                            <div class="product-price">
                                ₨ 280
                            </div>

                        </div>


                        <div class="product-item">

                            <div class="product-info">

                                <div class="product-image">
                                    SA
                                </div>

                                <div class="product-details">

                                    <strong>
                                        National Salt 800g
                                    </strong>

                                    <span>
                                        Grocery
                                    </span>

                                </div>

                            </div>

                            <div class="product-price">
                                ₨ 95
                            </div>

                        </div>

                    </div>

                </div>

            </section>


            <!-- =========================
                 BOTTOM GRID
            ========================= -->

            <section class="bottom-grid">

                <!-- QUICK ACTIONS -->

                <div class="panel">

                    <div class="panel-header">

                        <div>

                            <h3>Quick Actions</h3>

                            <p>
                                Common customer operations
                            </p>

                        </div>

                    </div>


                    <div class="quick-actions">

                        <a href="#" class="quick-action">

                            <span>
                                🛒
                            </span>

                            <strong>
                                Shop Products
                            </strong>

                            <small>
                                Browse grocery items
                            </small>

                        </a>


                        <a href="#" class="quick-action">

                            <span>
                                ▤
                            </span>

                            <strong>
                                My Orders
                            </strong>

                            <small>
                                Track your orders
                            </small>

                        </a>


                        <a href="#" class="quick-action">

                            <span>
                                ♙
                            </span>

                            <strong>
                                My Profile
                            </strong>

                            <small>
                                Update account details
                            </small>

                        </a>


                        <a href="#" class="quick-action">

                            <span>
                                ♥
                            </span>

                            <strong>
                                Wishlist
                            </strong>

                            <small>
                                View saved products
                            </small>

                        </a>

                    </div>

                </div>


                <!-- ACCOUNT SUMMARY -->

                <div class="panel">

                    <div class="panel-header">

                        <div>

                            <h3>Account Summary</h3>

                            <p>
                                Your current account information
                            </p>

                        </div>

                        <a href="#" class="view-link">
                            My Account
                        </a>

                    </div>


                    <div class="account-summary">

                        <div class="account-row">

                            <span>
                                Account Status
                            </span>

                            <strong class="green">
                                Active
                            </strong>

                        </div>


                        <div class="account-row">

                            <span>
                                Total Orders
                            </span>

                            <strong>
                                24
                            </strong>

                        </div>


                        <div class="account-row">

                            <span>
                                Total Spent
                            </span>

                            <strong>
                                ₨ 48,650
                            </strong>

                        </div>


                        <div class="account-row">

                            <span>
                                Outstanding Credit
                            </span>

                            <strong class="credit">
                                ₨ 3,500
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
                    Customer Panel
                </p>

            </footer>

        </div>

    </main>


    <!-- =========================
         MOBILE MENU SCRIPT
    ========================= -->

    <script>

        const menuButton = document.getElementById("menuButton");
        const sidebar = document.getElementById("sidebar");

        menuButton.addEventListener("click", function () {

            sidebar.classList.toggle("show");

        });

    </script>

</body>
</html>
