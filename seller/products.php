<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}
/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once "../config/database.php";


/*


/*
|--------------------------------------------------------------------------
| HELPER FUNCTIONS
|--------------------------------------------------------------------------
*/

function redirectMessage($message, $type = "success")
{
    header(
        "Location: products.php?" .
        http_build_query([
            "message" => $message,
            "type" => $type
        ])
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| ADD PRODUCT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["add_product"])
) {

    $product_name = trim($_POST["product_name"] ?? "");
    $sku = trim($_POST["sku"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $unit = trim($_POST["unit"] ?? "");

    $purchase_price = $_POST["purchase_price"] ?? "";
    $sale_price = $_POST["sale_price"] ?? "";
    $stock = $_POST["stock"] ?? "";

    $description = trim($_POST["description"] ?? "");


     /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $product_name === "" ||
        $sku === "" ||
        $category === "" ||
        $unit === "" ||
        $purchase_price === "" ||
        $sale_price === "" ||
        $stock === ""
    ) {
        redirectMessage(
            "Please fill all required fields.",
            "danger"
        );
    }


    if (
        !is_numeric($purchase_price) ||
        !is_numeric($sale_price) ||
        !is_numeric($stock)
    ) {
        redirectMessage(
            "Please enter valid price and stock values.",
            "danger"
        );
    }


    $purchase_price = (float) $purchase_price;
    $sale_price = (float) $sale_price;
    $stock = (int) $stock;


    if (
        $purchase_price < 0 ||
        $sale_price < 0 ||
        $stock < 0
    ) {
        redirectMessage(
            "Price and stock cannot be negative.",
            "danger"
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SKU
    |--------------------------------------------------------------------------
    */

    $checkSku = mysqli_prepare(
        $conn,
        "SELECT id FROM products WHERE sku = ? LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $checkSku,
        "s",
        $sku
    );

    mysqli_stmt_execute($checkSku);

    mysqli_stmt_store_result($checkSku);

    if (mysqli_stmt_num_rows($checkSku) > 0) {

        mysqli_stmt_close($checkSku);

        redirectMessage(
            "SKU already exists. Please use a different SKU.",
            "danger"
        );
    }

    mysqli_stmt_close($checkSku);


     /*
    |--------------------------------------------------------------------------
    | INSERT PRODUCT
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO products
        (
            product_name,
            sku,
            category,
            unit,
            purchase_price,
            sale_price,
            stock,
            description,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "ssssddis",
        $product_name,
        $sku,
        $category,
        $unit,
        $purchase_price,
        $sale_price,
        $stock,
        $description
    );


    if (mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        redirectMessage(
            "Product added successfully.",
            "success"
        );

    } else {

        $error = mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        redirectMessage(
            "Error adding product: " . $error,
            "danger"
        );
    }
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
                <p>Manage your grocery products</p>
            </div>

        </div>


        <div class="topbar-right">

            <button class="notification-button">
                🔔
                <span class="notification-dot"></span>
            </button>


            <div class="profile">

                <div class="profile-avatar">
                    T
                </div>

                <div class="profile-info">

                    <strong>
                        <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($_SESSION["role"]); ?>
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

                    <h3>486</h3>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon active">
                    ✓
                </div>

                <div>

                    <span>Active Products</span>

                    <h3>462</h3>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon low">
                    !
                </div>

                <div>

                    <span>Low Stock</span>

                    <h3>18</h3>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon out">
                    ×
                </div>

                <div>

                    <span>Out of Stock</span>

                    <h3>6</h3>

                </div>

            </div>


        </div>



        <!-- =========================
             PRODUCT TABLE PANEL
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

                    <option value="">All Categories</option>
                    <option>Grocery</option>
                    <option>Rice & Grains</option>
                    <option>Dairy</option>
                    <option>Beverages</option>
                    <option>Cleaning</option>

                </select>


                <select class="filter-select">

                    <option value="">All Stock</option>
                    <option>In Stock</option>
                    <option>Low Stock</option>
                    <option>Out of Stock</option>

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

                            <th>Purchase Price</th>

                            <th>Sale Price</th>

                            <th>Stock</th>

                            <th>Status</th>

                            <th class="text-end">Action</th>

                        </tr>

                    </thead>


                    <tbody>


                        <tr>

                            <td>01</td>

                            <td>

                                <div class="product-name">

                                    <div class="product-image">
                                        CO
                                    </div>

                                    <div>

                                        <strong>Cooking Oil 5L</strong>

                                        <small>
                                            SKU: OIL-001
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>
                                Grocery
                            </td>


                            <td>
                                ₨ 2,650
                            </td>


                            <td>
                                ₨ 2,850
                            </td>


                            <td>
                                <strong>32</strong>
                            </td>


                            <td>

                                <span class="stock-status in-stock">
                                    In Stock
                                </span>

                            </td>


                            <td class="text-end">

                                <button
                                    class="action-btn edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editProductModal"
                                >
                                    Edit
                                </button>

                                <button
                                    class="action-btn delete"
                                >
                                    Delete
                                </button>

                            </td>

                        </tr>



                        <tr>

                            <td>02</td>

                            <td>

                                <div class="product-name">

                                    <div class="product-image">
                                        RI
                                    </div>

                                    <div>

                                        <strong>Basmati Rice 5kg</strong>

                                        <small>
                                            SKU: RIC-002
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>
                                Rice & Grains
                            </td>


                            <td>
                                ₨ 1,050
                            </td>


                            <td>
                                ₨ 1,250
                            </td>


                            <td>
                                <strong>8</strong>
                            </td>


                            <td>

                                <span class="stock-status low-stock">
                                    Low Stock
                                </span>

                            </td>


                            <td class="text-end">

                                <button
                                    class="action-btn edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editProductModal"
                                >
                                    Edit
                                </button>

                                <button class="action-btn delete">
                                    Delete
                                </button>

                            </td>

                        </tr>



                        <tr>

                            <td>03</td>

                            <td>

                                <div class="product-name">

                                    <div class="product-image">
                                        MI
                                    </div>

                                    <div>

                                        <strong>Milk Pack 1L</strong>

                                        <small>
                                            SKU: MLK-003
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>
                                Dairy
                            </td>


                            <td>
                                ₨ 240
                            </td>


                            <td>
                                ₨ 280
                            </td>


                            <td>
                                <strong>3</strong>
                            </td>


                            <td>

                                <span class="stock-status out-stock">
                                    Out of Stock
                                </span>

                            </td>


                            <td class="text-end">

                                <button
                                    class="action-btn edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editProductModal"
                                >
                                    Edit
                                </button>

                                <button class="action-btn delete">
                                    Delete
                                </button>

                            </td>

                        </tr>



                        <tr>

                            <td>04</td>

                            <td>

                                <div class="product-name">

                                    <div class="product-image">
                                        SU
                                    </div>

                                    <div>

                                        <strong>Surf Excel 1kg</strong>

                                        <small>
                                            SKU: SUR-004
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>
                                Cleaning
                            </td>


                            <td>
                                ₨ 520
                            </td>


                            <td>
                                ₨ 590
                            </td>


                            <td>
                                <strong>11</strong>
                            </td>


                            <td>

                                <span class="stock-status low-stock">
                                    Low Stock
                                </span>

                            </td>


                            <td class="text-end">

                                <button
                                    class="action-btn edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editProductModal"
                                >
                                    Edit
                                </button>

                                <button class="action-btn delete">
                                    Delete
                                </button>

                            </td>

                        </tr>



                        <tr>

                            <td>05</td>

                            <td>

                                <div class="product-name">

                                    <div class="product-image">
                                        SA
                                    </div>

                                    <div>

                                        <strong>National Salt 800g</strong>

                                        <small>
                                            SKU: SAL-005
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>
                                Grocery
                            </td>


                            <td>
                                ₨ 75
                            </td>


                            <td>
                                ₨ 95
                            </td>


                            <td>
                                <strong>74</strong>
                            </td>


                            <td>

                                <span class="stock-status in-stock">
                                    In Stock
                                </span>

                            </td>


                            <td class="text-end">

                                <button
                                    class="action-btn edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editProductModal"
                                >
                                    Edit
                                </button>

                                <button class="action-btn delete">
                                    Delete
                                </button>

                            </td>

                        </tr>


                    </tbody>

                </table>

            </div>



            <!-- PAGINATION -->

            <div class="table-footer">

                <p>
                    Showing 1 to 5 of 486 products
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

                        <li class="page-item">
                            <a class="page-link" href="#">
                                2
                            </a>
                        </li>

                        <li class="page-item">
                            <a class="page-link" href="#">
                                3
                            </a>
                        </li>

                        <li class="page-item">
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

        <form action="products.php" method="POST">

    <div class="modal-body">

        <div class="row g-3">

            <!-- Product Name -->
            <div class="col-md-8">

                <label class="form-label">
                    Product Name
                </label>

                <input
                    type="text"
                    name="product_name"
                    class="form-control"
                    placeholder="Enter product name"
                    required
                >

            </div>


            <!-- SKU -->
            <div class="col-md-4">

                <label class="form-label">
                    SKU
                </label>

                <input
                    type="text"
                    name="sku"
                    class="form-control"
                    placeholder="e.g. PRD-001"
                    required
                >

            </div>


            <!-- Category -->
            <div class="col-md-6">

                <label class="form-label">
                    Category
                </label>

                <select
                    name="category"
                    class="form-select"
                    required
                >

                    <option value="">
                        Select category
                    </option>

                    <option value="Grocery">
                        Grocery
                    </option>

                    <option value="Rice & Grains">
                        Rice & Grains
                    </option>

                    <option value="Dairy">
                        Dairy
                    </option>

                    <option value="Beverages">
                        Beverages
                    </option>

                    <option value="Cleaning">
                        Cleaning
                    </option>

                </select>

            </div>


            <!-- Unit -->
            <div class="col-md-6">

                <label class="form-label">
                    Unit
                </label>

                <select
                    name="unit"
                    class="form-select"
                    required
                >

                    <option value="">
                        Select unit
                    </option>

                    <option value="Piece">
                        Piece
                    </option>

                    <option value="Kg">
                        Kg
                    </option>

                    <option value="Gram">
                        Gram
                    </option>

                    <option value="Liter">
                        Liter
                    </option>

                    <option value="Pack">
                        Pack
                    </option>

                    <option value="Dozen">
                        Dozen
                    </option>

                </select>

            </div>


            <!-- Purchase Price -->
            <div class="col-md-4">

                <label class="form-label">
                    Purchase Price
                </label>

                <input
                    type="number"
                    name="purchase_price"
                    class="form-control"
                    placeholder="0"
                    min="0"
                    step="0.01"
                    required
                >

            </div>


            <!-- Sale Price -->
            <div class="col-md-4">

                <label class="form-label">
                    Sale Price
                </label>

                <input
                    type="number"
                    name="sale_price"
                    class="form-control"
                    placeholder="0"
                    min="0"
                    step="0.01"
                    required
                >

            </div>


            <!-- Opening Stock -->
            <div class="col-md-4">

                <label class="form-label">
                    Opening Stock
                </label>

                <input
                    type="number"
                    name="stock"
                    class="form-control"
                    placeholder="0"
                    min="0"
                    required
                >

            </div>


            <!-- Description -->
            <div class="col-12">

                <label class="form-label">
                    Description
                </label>

                <textarea
                    name="description"
                    class="form-control"
                    rows="3"
                    placeholder="Optional product description"
                ></textarea>

            </div>

        </div>

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
            type="submit"
            name="add_product"
            class="btn add-product-btn"
        >
            Save Product
        </button>

    </div>

</form>


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
                                value="Cooking Oil 5L"
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                SKU
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="OIL-001"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Category
                            </label>

                            <select class="form-select">

                                <option selected>
                                    Grocery
                                </option>

                                <option>Rice & Grains</option>
                                <option>Dairy</option>
                                <option>Beverages</option>
                                <option>Cleaning</option>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Unit
                            </label>

                            <select class="form-select">

                                <option selected>
                                    Liter
                                </option>

                                <option>Piece</option>
                                <option>Kg</option>
                                <option>Gram</option>
                                <option>Pack</option>
                                <option>Dozen</option>

                            </select>

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Purchase Price
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                value="2650"
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Sale Price
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                value="2850"
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Current Stock
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                value="32"
                            >

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
