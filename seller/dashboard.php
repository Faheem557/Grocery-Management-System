<?php
   session_start();
   
   $name = $_SESSION["user_name"];
    if(!$name){
        header("Location: ../login.php");
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

    <!-- Sidebar -->

    <aside class="sidebar">

        <div class="brand">

            <img src="../assets/IMAGES/mainLogo.png" alt="Grocery Management System">

            <div>
                <h2>Grocery Manager</h2>
                <span>Seller Panel</span>
            </div>

        </div>


        <nav class="sidebar-nav">

            <p class="nav-title">MAIN</p>

            <a href="#" class="nav-link active">
                <span class="nav-icon">⌂</span>
                Dashboard
            </a>

            <a href="products.php" class="nav-link">
                <span class="nav-icon">▣</span>
                Products
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">▤</span>
                Categories
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">▥</span>
                Stock
            </a>


            <p class="nav-title">SALES</p>

            <a href="#" class="nav-link">
                <span class="nav-icon">＋</span>
                New Sale
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">▤</span>
                Sales History
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">▧</span>
                Invoices
            </a>


            <p class="nav-title">CUSTOMERS</p>

            <a href="#" class="nav-link">
                <span class="nav-icon">♙</span>
                Customers
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">₨</span>
                Digital Khata
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">✓</span>
                Payments
            </a>


            <p class="nav-title">PURCHASE</p>

            <a href="#" class="nav-link">
                <span class="nav-icon">▣</span>
                Suppliers
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">↓</span>
                Purchases
            </a>


            <p class="nav-title">BUSINESS</p>

            <a href="#" class="nav-link">
                <span class="nav-icon">↗</span>
                Profit
            </a>

            <a href="#" class="nav-link">
                <span class="nav-icon">▥</span>
                Reports
            </a>

            <a href="#" class="nav-link">
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


    <!-- Main Area -->

    <main class="main-content">

        <!-- Top Bar -->

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
                <a href="../logout.php">
                    <div class="profile-avatar">
                        <?php
                            // $p_avatar = $name;
                            echo $name[0].$name[1];
                        ?>
                        
                    </div>
                    </a>

                    <div class="profile-info">
                        <strong><?php echo $name ?></strong>
                        <span><?php echo $_SESSION["role"] ?></span>
                    </div>

                </div>

            </div>

        </header>


        <!-- Dashboard Content -->

        <div class="dashboard-content">


            <!-- Date / Quick Action -->

            <div class="page-intro">

                <div>
                    <h2>Store Overview</h2>
                    <p>Here is what is happening in your grocery store today.</p>
                </div>

                <a href="#" class="primary-button">
                    + New Sale
                </a>

            </div>


            <!-- Statistics -->

            <section class="stats-grid">


                <div class="stat-card">

                    <div class="stat-top">
                        <span class="stat-title">Today's Sales</span>
                        <div class="stat-icon sales-icon">₨</div>
                    </div>

                    <h3>₨ 24,850</h3>

                    <p class="stat-positive">
                        ↑ 12.5% <span>from yesterday</span>
                    </p>

                </div>


                <div class="stat-card">

                    <div class="stat-top">
                        <span class="stat-title">Today's Profit</span>
                        <div class="stat-icon profit-icon">↗</div>
                    </div>

                    <h3>₨ 6,420</h3>

                    <p class="stat-positive">
                        ↑ 8.2% <span>from yesterday</span>
                    </p>

                </div>


                <div class="stat-card">

                    <div class="stat-top">
                        <span class="stat-title">Total Products</span>
                        <div class="stat-icon product-icon">▣</div>
                    </div>

                    <h3>486</h3>

                    <p class="stat-neutral">
                        18 categories
                    </p>

                </div>


                <div class="stat-card">

                    <div class="stat-top">
                        <span class="stat-title">Customers</span>
                        <div class="stat-icon customer-icon">♙</div>
                    </div>

                    <h3>328</h3>

                    <p class="stat-positive">
                        ↑ 6 new <span>this month</span>
                    </p>

                </div>


            </section>


            <!-- Main Dashboard Grid -->

            <section class="dashboard-grid">


                <!-- Recent Sales -->

                <div class="panel recent-sales">

                    <div class="panel-header">

                        <div>
                            <h3>Recent Sales</h3>
                            <p>Latest transactions from your store</p>
                        </div>

                        <a href="#" class="view-link">
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
                                    <td>#INV-1048</td>
                                    <td>Ahmed Khan</td>
                                    <td>Today, 02:15 PM</td>
                                    <td>₨ 4,250</td>
                                    <td>
                                        <span class="status paid">Paid</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>#INV-1047</td>
                                    <td>Usman Ali</td>
                                    <td>Today, 01:42 PM</td>
                                    <td>₨ 2,850</td>
                                    <td>
                                        <span class="status credit">Credit</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>#INV-1046</td>
                                    <td>Bilal Ahmad</td>
                                    <td>Today, 12:30 PM</td>
                                    <td>₨ 6,100</td>
                                    <td>
                                        <span class="status paid">Paid</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>#INV-1045</td>
                                    <td>Hamza Khan</td>
                                    <td>Today, 11:18 AM</td>
                                    <td>₨ 1,920</td>
                                    <td>
                                        <span class="status paid">Paid</span>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- Low Stock -->

                <div class="panel low-stock">

                    <div class="panel-header">

                        <div>
                            <h3>Low Stock</h3>
                            <p>Products that need attention</p>
                        </div>

                        <a href="#" class="view-link">
                            View Stock
                        </a>

                    </div>


                    <div class="stock-list">

                        <div class="stock-item">

                            <div class="product-info">
                                <strong>Cooking Oil 5L</strong>
                                <span>Grocery</span>
                            </div>

                            <div class="stock-number danger">
                                4 left
                            </div>

                        </div>


                        <div class="stock-item">

                            <div class="product-info">
                                <strong>Basmati Rice 5kg</strong>
                                <span>Rice & Grains</span>
                            </div>

                            <div class="stock-number warning">
                                8 left
                            </div>

                        </div>


                        <div class="stock-item">

                            <div class="product-info">
                                <strong>Surf Excel 1kg</strong>
                                <span>Cleaning</span>
                            </div>

                            <div class="stock-number warning">
                                11 left
                            </div>

                        </div>


                        <div class="stock-item">

                            <div class="product-info">
                                <strong>Milk Pack 1L</strong>
                                <span>Dairy</span>
                            </div>

                            <div class="stock-number danger">
                                3 left
                            </div>

                        </div>

                    </div>

                </div>


            </section>


            <!-- Bottom Grid -->

            <section class="bottom-grid">


                <!-- Quick Actions -->

                <div class="panel">

                    <div class="panel-header">

                        <div>
                            <h3>Quick Actions</h3>
                            <p>Common store operations</p>
                        </div>

                    </div>


                    <div class="quick-actions">

                        <a href="#" class="quick-action">
                            <span>＋</span>
                            <strong>New Sale</strong>
                            <small>Create a new invoice</small>
                        </a>

                        <a href="#" class="quick-action">
                            <span>▣</span>
                            <strong>Add Product</strong>
                            <small>Add item to inventory</small>
                        </a>

                        <a href="#" class="quick-action">
                            <span>♙</span>
                            <strong>Add Customer</strong>
                            <small>Register new customer</small>
                        </a>

                        <a href="#" class="quick-action">
                            <span>↓</span>
                            <strong>Add Purchase</strong>
                            <small>Record stock purchase</small>
                        </a>

                    </div>

                </div>


                <!-- Khata Summary -->

                <div class="panel">

                    <div class="panel-header">

                        <div>
                            <h3>Khata Summary</h3>
                            <p>Customer credit overview</p>
                        </div>

                        <a href="#" class="view-link">
                            Open Khata
                        </a>

                    </div>


                    <div class="khata-summary">

                        <div class="khata-row">

                            <span>Total Credit</span>

                            <strong>₨ 82,450</strong>

                        </div>

                        <div class="khata-row">

                            <span>Received Today</span>

                            <strong class="received">
                                ₨ 12,800
                            </strong>

                        </div>

                        <div class="khata-row">

                            <span>Remaining</span>

                            <strong class="remaining">
                                ₨ 69,650
                            </strong>

                        </div>

                    </div>

                </div>


            </section>


            <!-- Footer -->

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
