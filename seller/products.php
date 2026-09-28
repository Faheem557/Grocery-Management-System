<?php

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Products | Grocery Management System</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Products CSS -->
    <link rel="stylesheet" href="../assets/css/products.css">

</head>

<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <div class="brand">

        <img
            src="../assets/IMAGES/mainLogo.png"
            alt="Grocery Management System"
        >

        <div>
            <h2>Grocery Manager</h2>
            <span>Seller Panel</span>
        </div>

    </div>


    <nav class="sidebar-nav">

        <p class="nav-title">MAIN</p>

        <a href="dashboard.php" class="nav-link">
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>

        <a href="products.php" class="nav-link active">
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


<!-- =========================
     MAIN CONTENT
========================= -->

<main class="main-content">


    <!-- TOPBAR -->

    <header class="topbar">

        <div class="topbar-left">

            <button class="menu-button">
                ☰
            </button>

            <div>

                <h1>Products</h1>

                <p>
                    Manage your grocery products
                </p>

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

                        <?php
                        echo strtoupper(
                            substr($_SESSION["user_name"] ?? "U", 0, 2)
                        );
                        ?>

                    </div>

                </a>


                <div class="profile-info">

                    <strong>
                        <?php echo htmlspecialchars($_SESSION["user_name"] ?? "User"); ?>
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($_SESSION["role"] ?? "seller"); ?>
                    </span>

                </div>

            </div>

        </div>

    </header>


    <!-- PAGE CONTENT -->

    <div class="page-content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h2>Product Management</h2>

                <p>
                    Add, update and manage products in your store.
                </p>

            </div>


            <button
                type="button"
                class="btn add-product-btn"
                data-bs-toggle="modal"
                data-bs-target="#addProductModal"
            >
                + Add Product
            </button>

        </div>


        <!-- =========================
             SUMMARY CARDS
        ========================= -->

        <div class="summary-grid">


            <div class="summary-card">

                <div class="summary-icon total">
                    ▣
                </div>

                <div>

                    <span>Total Products</span>

                    <h3>0</h3>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon active">
                    ✓
                </div>

                <div>

                    <span>Active Products</span>

                    <h3>0</h3>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon low">
                    !
                </div>

                <div>

                    <span>Low Stock</span>

                    <h3>0</h3>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon out">
                    ×
                </div>

                <div>

                    <span>Out of Stock</span>

                    <h3>0</h3>

                </div>

            </div>


        </div>


        <!-- =========================
             PRODUCT TABLE
        ========================= -->

        <div class="product-panel">


            <!-- FILTER AREA -->

            <div class="filter-area">

                <div class="search-box">

                    <span>⌕</span>

                    <input
                        type="text"
                        placeholder="Search product..."
                    >

                </div>


                <select class="filter-select">

                    <option value="">
                        All Categories
                    </option>

                </select>


                <select class="filter-select">

                    <option value="">
                        All Stock
                    </option>

                    <option value="in-stock">
                        In Stock
                    </option>

                    <option value="low-stock">
                        Low Stock
                    </option>

                    <option value="out-stock">
                        Out of Stock
                    </option>

                </select>


                <button class="filter-btn">
                    Filter
                </button>

            </div>


            <!-- TABLE -->

            <div class="table-responsive">

                <table class="table product-table align-middle">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Product</th>

                            <th>Category</th>

                            <th>SKU</th>

                            <th>Barcode</th>

                            <th>Unit</th>

                            <th>Purchase Price</th>

                            <th>Sale Price</th>

                            <th>Stock</th>

                            <th>Status</th>

                            <th class="text-end">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr>

                            <td colspan="11" class="text-center">
                                No products added yet.
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- PAGINATION -->

            <div class="table-footer">

                <p>
                    Showing 0 products
                </p>


                <nav>

                    <ul class="pagination pagination-sm mb-0">

                        <li class="page-item disabled">

                            <a class="page-link" href="#">
                                Previous
                            </a>

                        </li>


                        <li class="page-item active">

                            <a class="page-link" href="#">
                                1
                            </a>

                        </li>


                        <li class="page-item disabled">

                            <a class="page-link" href="#">
                                Next
                            </a>

                        </li>

                    </ul>

                </nav>

            </div>


        </div>


    </div>

</main>



<!-- =========================
     ADD PRODUCT MODAL
========================= -->

<div
    class="modal fade"
    id="addProductModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">


            <div class="modal-header">

                <div>

                    <h5 class="modal-title">
                        Add New Product
                    </h5>

                    <small>
                        Enter product information below
                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <form>

                    <div class="row g-3">


                        <!-- PRODUCT NAME -->

                        <div class="col-md-8">

                            <label class="form-label">
                                Product Name
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Enter product name"
                            >

                        </div>


                        <!-- SKU -->

                        <div class="col-md-4">

                            <label class="form-label">
                                SKU
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                placeholder="e.g. PRD-001"
                            >

                        </div>


                        <!-- BARCODE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Barcode
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Enter barcode"
                            >

                        </div>


                        <!-- CATEGORY -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Category
                            </label>

                            <select class="form-select">

                                <option selected>
                                    Select category
                                </option>

                            </select>

                        </div>


                        <!-- UNIT -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Unit
                            </label>

                            <select class="form-select">

                                <option selected>
                                    Select unit
                                </option>

                                <option value="piece">
                                    Piece
                                </option>

                                <option value="kg">
                                    Kg
                                </option>

                                <option value="gram">
                                    Gram
                                </option>

                                <option value="liter">
                                    Liter
                                </option>

                                <option value="pack">
                                    Pack
                                </option>

                                <option value="dozen">
                                    Dozen
                                </option>

                            </select>

                        </div>


                        <!-- PURCHASE PRICE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Purchase Price
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                            >

                        </div>


                        <!-- SALE PRICE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Sale Price
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                            >

                        </div>


                        <!-- STOCK -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Stock Quantity
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="0"
                            >

                        </div>


                        <!-- LOW STOCK LIMIT -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Low Stock Limit
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                step="0.01"
                                min="0"
                                value="5"
                                placeholder="5"
                            >

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Status
                            </label>

                            <select class="form-select">

                                <option value="active" selected>
                                    Active
                                </option>

                                <option value="inactive">
                                    Inactive
                                </option>

                            </select>

                        </div>


                    </div>

                </form>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    class="btn add-product-btn"
                >
                    Save Product
                </button>

            </div>


        </div>

    </div>

</div>



<!-- =========================
     EDIT PRODUCT MODAL
========================= -->

<div
    class="modal fade"
    id="editProductModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">


            <div class="modal-header">

                <div>

                    <h5 class="modal-title">
                        Edit Product
                    </h5>

                    <small>
                        Update product information
                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <form>

                    <div class="row g-3">


                        <div class="col-md-8">

                            <label class="form-label">
                                Product Name
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Enter product name"
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                SKU
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Enter SKU"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Barcode
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Enter barcode"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Category
                            </label>

                            <select class="form-select">

                                <option selected>
                                    Select category
                                </option>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Unit
                            </label>

                            <select class="form-select">

                                <option value="piece">
                                    Piece
                                </option>

                                <option value="kg">
                                    Kg
                                </option>

                                <option value="gram">
                                    Gram
                                </option>

                                <option value="liter">
                                    Liter
                                </option>

                                <option value="pack">
                                    Pack
                                </option>

                                <option value="dozen">
                                    Dozen
                                </option>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Purchase Price
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Sale Price
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Stock Quantity
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="0"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Low Stock Limit
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="5"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Status
                            </label>

                            <select class="form-select">

                                <option value="active">
                                    Active
                                </option>

                                <option value="inactive">
                                    Inactive
                                </option>

                            </select>

                        </div>


                    </div>

                </form>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>


                <button
                    type="button"
                    class="btn add-product-btn"
                >
                    Update Product
                </button>

            </div>


        </div>

    </div>

</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>