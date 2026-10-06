<?php
session_start();

<<<<<<< HEAD
require_once "../config/database.php";

/* Prevent old cached page */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
=======
/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/
>>>>>>> Zuhran

/* Check login */
if (!isset($_SESSION["user_id"], $_SESSION["shop_id"])) {
    header("Location: ../index.php");
    exit;
}

<<<<<<< HEAD
$user_id = (int) $_SESSION["user_id"];
$shop_id = (int) $_SESSION["shop_id"];

/* Get latest user data from database */
$sql = "SELECT id, name, username, role, status, profile_photo
        FROM users
        WHERE id = ?
        AND shop_id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed.");
}

mysqli_stmt_bind_param($stmt, "ii", $user_id, $shop_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$name = $user["name"] ?? "User";
$role = $user["role"] ?? "seller";
$profile_photo = $user["profile_photo"] ?? "";

if ($name === "") {
    header("Location: ../index.php", true, 303);
    exit;
}
?>
=======

/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once "../config/database.php";


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


function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/*
|--------------------------------------------------------------------------
| SHOP ID
|--------------------------------------------------------------------------
|
| Your products table requires shop_id.
| This file checks several common session names so it can work with
| your existing login/session system.
|
*/

$shopId = (int) (
    $_SESSION["shop_id"]
    ?? $_SESSION["user_shop_id"]
    ?? $_SESSION["shop"]
    ?? 0
);


/*
|--------------------------------------------------------------------------
| If shop_id is not directly stored in session, try to get it
| from the users table using the logged-in user_id.
|--------------------------------------------------------------------------
*/

if ($shopId <= 0) {

    $userId = (int) ($_SESSION["user_id"] ?? 0);

    if ($userId > 0) {

        $userStmt = mysqli_prepare(
            $conn,
            "SELECT shop_id
             FROM users
             WHERE id = ?
             LIMIT 1"
        );

        if ($userStmt) {

            mysqli_stmt_bind_param(
                $userStmt,
                "i",
                $userId
            );

            mysqli_stmt_execute($userStmt);

            $userResult = mysqli_stmt_get_result(
                $userStmt
            );

            if ($userResult) {

                $userRow = mysqli_fetch_assoc(
                    $userResult
                );

                if ($userRow) {
                    $shopId = (int) (
                        $userRow["shop_id"] ?? 0
                    );
                }
            }

            mysqli_stmt_close($userStmt);
        }
    }
}


/*
|--------------------------------------------------------------------------
| SAFETY CHECK
|--------------------------------------------------------------------------
*/

if ($shopId <= 0) {

    die(
        "Unable to determine your shop. Please make sure shop_id is stored in your login session or users table."
    );
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

    $name = trim(
        $_POST["name"] ?? ""
    );

    $sku = trim(
        $_POST["sku"] ?? ""
    );

    $barcode = trim(
        $_POST["barcode"] ?? ""
    );

    $category_id = (int) (
        $_POST["category_id"] ?? 0
    );

    $unit = trim(
        $_POST["unit"] ?? ""
    );

    $purchase_price =
        $_POST["purchase_price"] ?? "";

    $sale_price =
        $_POST["sale_price"] ?? "";

    $stock_quantity =
        $_POST["stock_quantity"] ?? "";

    $low_stock_limit =
        $_POST["low_stock_limit"] ?? 5;

    $status =
        $_POST["status"] ?? "active";


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $name === "" ||
        $sku === "" ||
        $category_id <= 0 ||
        $unit === "" ||
        $purchase_price === "" ||
        $sale_price === "" ||
        $stock_quantity === ""
    ) {

        redirectMessage(
            "Please fill all required fields.",
            "danger"
        );
    }


    if (
        !is_numeric($purchase_price) ||
        !is_numeric($sale_price) ||
        !is_numeric($stock_quantity) ||
        !is_numeric($low_stock_limit)
    ) {

        redirectMessage(
            "Please enter valid price and stock values.",
            "danger"
        );
    }


    $purchase_price =
        (float) $purchase_price;

    $sale_price =
        (float) $sale_price;

    $stock_quantity =
        (float) $stock_quantity;

    $low_stock_limit =
        (float) $low_stock_limit;


    if (
        $purchase_price < 0 ||
        $sale_price < 0 ||
        $stock_quantity < 0 ||
        $low_stock_limit < 0
    ) {

        redirectMessage(
            "Price and stock values cannot be negative.",
            "danger"
        );
    }


    if (
        $status !== "active" &&
        $status !== "inactive"
    ) {

        $status = "active";
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SKU
    |--------------------------------------------------------------------------
    */

    $checkSku = mysqli_prepare(
        $conn,
        "SELECT id
         FROM products
         WHERE shop_id = ?
         AND sku = ?
         LIMIT 1"
    );


    if (!$checkSku) {

        redirectMessage(
            "Database error: " . mysqli_error($conn),
            "danger"
        );
    }


    mysqli_stmt_bind_param(
        $checkSku,
        "is",
        $shopId,
        $sku
    );


    mysqli_stmt_execute(
        $checkSku
    );

    mysqli_stmt_store_result(
        $checkSku
    );


    if (
        mysqli_stmt_num_rows(
            $checkSku
        ) > 0
    ) {

        mysqli_stmt_close(
            $checkSku
        );

        redirectMessage(
            "SKU already exists. Please use a different SKU.",
            "danger"
        );
    }


    mysqli_stmt_close(
        $checkSku
    );


    /*
    |--------------------------------------------------------------------------
    | INSERT PRODUCT
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO products
        (
            shop_id,
            category_id,
            name,
            sku,
            barcode,
            unit,
            purchase_price,
            sale_price,
            stock_quantity,
            low_stock_limit,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );


    if (!$stmt) {

        redirectMessage(
            "Database error: " . mysqli_error($conn),
            "danger"
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "iissssdddss",
        $shopId,
        $category_id,
        $name,
        $sku,
        $barcode,
        $unit,
        $purchase_price,
        $sale_price,
        $stock_quantity,
        $low_stock_limit,
        $status
    );


    if (
        mysqli_stmt_execute($stmt)
    ) {

        mysqli_stmt_close($stmt);

        redirectMessage(
            "Product added successfully.",
            "success"
        );

    } else {

        $error =
            mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        redirectMessage(
            "Error adding product: " . $error,
            "danger"
        );
    }
}


/*
|--------------------------------------------------------------------------
| UPDATE PRODUCT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_product"])
) {

    $id = (int) (
        $_POST["id"] ?? 0
    );

    $name = trim(
        $_POST["name"] ?? ""
    );

    $sku = trim(
        $_POST["sku"] ?? ""
    );

    $barcode = trim(
        $_POST["barcode"] ?? ""
    );

    $category_id = (int) (
        $_POST["category_id"] ?? 0
    );

    $unit = trim(
        $_POST["unit"] ?? ""
    );

    $purchase_price =
        $_POST["purchase_price"] ?? "";

    $sale_price =
        $_POST["sale_price"] ?? "";

    $stock_quantity =
        $_POST["stock_quantity"] ?? "";

    $low_stock_limit =
        $_POST["low_stock_limit"] ?? 5;

    $status =
        $_POST["status"] ?? "active";


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($id <= 0) {

        redirectMessage(
            "Invalid product.",
            "danger"
        );
    }


    if (
        $name === "" ||
        $sku === "" ||
        $category_id <= 0 ||
        $unit === "" ||
        $purchase_price === "" ||
        $sale_price === "" ||
        $stock_quantity === ""
    ) {

        redirectMessage(
            "Please fill all required fields.",
            "danger"
        );
    }


    if (
        !is_numeric($purchase_price) ||
        !is_numeric($sale_price) ||
        !is_numeric($stock_quantity) ||
        !is_numeric($low_stock_limit)
    ) {

        redirectMessage(
            "Please enter valid price and stock values.",
            "danger"
        );
    }


    $purchase_price =
        (float) $purchase_price;

    $sale_price =
        (float) $sale_price;

    $stock_quantity =
        (float) $stock_quantity;

    $low_stock_limit =
        (float) $low_stock_limit;


    if (
        $purchase_price < 0 ||
        $sale_price < 0 ||
        $stock_quantity < 0 ||
        $low_stock_limit < 0
    ) {

        redirectMessage(
            "Price and stock values cannot be negative.",
            "danger"
        );
    }


    if (
        $status !== "active" &&
        $status !== "inactive"
    ) {

        $status = "active";
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SKU
    |--------------------------------------------------------------------------
    */

    $checkSku = mysqli_prepare(
        $conn,
        "SELECT id
         FROM products
         WHERE shop_id = ?
         AND sku = ?
         AND id != ?
         LIMIT 1"
    );


    if (!$checkSku) {

        redirectMessage(
            "Database error: " . mysqli_error($conn),
            "danger"
        );
    }


    mysqli_stmt_bind_param(
        $checkSku,
        "isi",
        $shopId,
        $sku,
        $id
    );


    mysqli_stmt_execute(
        $checkSku
    );

    mysqli_stmt_store_result(
        $checkSku
    );


    if (
        mysqli_stmt_num_rows(
            $checkSku
        ) > 0
    ) {

        mysqli_stmt_close(
            $checkSku
        );

        redirectMessage(
            "SKU already belongs to another product.",
            "danger"
        );
    }


    mysqli_stmt_close(
        $checkSku
    );


    /*
    |--------------------------------------------------------------------------
    | UPDATE PRODUCT
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE products
         SET
            category_id = ?,
            name = ?,
            sku = ?,
            barcode = ?,
            unit = ?,
            purchase_price = ?,
            sale_price = ?,
            stock_quantity = ?,
            low_stock_limit = ?,
            status = ?
         WHERE id = ?
         AND shop_id = ?"
    );


    if (!$stmt) {

        redirectMessage(
            "Database error: " . mysqli_error($conn),
            "danger"
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "issssddddsii",
        $category_id,
        $name,
        $sku,
        $barcode,
        $unit,
        $purchase_price,
        $sale_price,
        $stock_quantity,
        $low_stock_limit,
        $status,
        $id,
        $shopId
    );


    if (
        mysqli_stmt_execute($stmt)
    ) {

        mysqli_stmt_close($stmt);

        redirectMessage(
            "Product updated successfully.",
            "success"
        );

    } else {

        $error =
            mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        redirectMessage(
            "Error updating product: " . $error,
            "danger"
        );
    }
}


/*
|--------------------------------------------------------------------------
| DELETE PRODUCT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_product"])
) {

    $id = (int) (
        $_POST["id"] ?? 0
    );


    if ($id <= 0) {

        redirectMessage(
            "Invalid product.",
            "danger"
        );
    }


    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM products
         WHERE id = ?
         AND shop_id = ?"
    );


    if (!$stmt) {

        redirectMessage(
            "Database error: " . mysqli_error($conn),
            "danger"
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $id,
        $shopId
    );


    if (
        mysqli_stmt_execute($stmt)
    ) {

        mysqli_stmt_close($stmt);

        redirectMessage(
            "Product deleted successfully.",
            "success"
        );

    } else {

        $error =
            mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        redirectMessage(
            "Error deleting product: " . $error,
            "danger"
        );
    }
}


/*
|--------------------------------------------------------------------------
| MESSAGE
|--------------------------------------------------------------------------
*/

$message =
    $_GET["message"] ?? "";

$messageType =
    $_GET["type"] ?? "success";


/*
|--------------------------------------------------------------------------
| GET CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = [];


$categoryQuery = mysqli_prepare(
    $conn,
    "SELECT id, name
     FROM categories
     WHERE shop_id = ?
     ORDER BY name ASC"
);


if ($categoryQuery) {

    mysqli_stmt_bind_param(
        $categoryQuery,
        "i",
        $shopId
    );

    mysqli_stmt_execute(
        $categoryQuery
    );

    $categoryResult =
        mysqli_stmt_get_result(
            $categoryQuery
        );

    if ($categoryResult) {

        while (
            $row =
                mysqli_fetch_assoc(
                    $categoryResult
                )
        ) {

            $categories[] = $row;
        }
    }

    mysqli_stmt_close(
        $categoryQuery
    );
}


/*
|--------------------------------------------------------------------------
| SEARCH / FILTER
|--------------------------------------------------------------------------
*/

$search =
    trim($_GET["search"] ?? "");

$categoryFilter =
    trim($_GET["category"] ?? "");

$stockFilter =
    trim($_GET["stock"] ?? "");


/*
|--------------------------------------------------------------------------
| PRODUCT QUERY
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT
        p.id,
        p.shop_id,
        p.category_id,
        p.name,
        p.sku,
        p.barcode,
        p.unit,
        p.purchase_price,
        p.sale_price,
        p.stock_quantity,
        p.low_stock_limit,
        p.status,
        p.created_at,

        c.name AS category_name

    FROM products p

    LEFT JOIN categories c
        ON c.id = p.category_id
        AND c.shop_id = p.shop_id

    WHERE p.shop_id = ?

";


$types = "i";

$params = [
    $shopId
];


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql .= "

        AND (
            p.name LIKE ?
            OR p.sku LIKE ?
            OR p.barcode LIKE ?
        )

    ";


    $searchValue =
        "%" . $search . "%";


    $types .= "sss";

    $params[] =
        $searchValue;

    $params[] =
        $searchValue;

    $params[] =
        $searchValue;
}


/*
|--------------------------------------------------------------------------
| CATEGORY FILTER
|--------------------------------------------------------------------------
*/

if ($categoryFilter !== "") {

    $categoryId =
        (int) $categoryFilter;


    if ($categoryId > 0) {

        $sql .= "
            AND p.category_id = ?
        ";

        $types .= "i";

        $params[] =
            $categoryId;
    }
}


/*
|--------------------------------------------------------------------------
| STOCK FILTER
|--------------------------------------------------------------------------
*/

if (
    $stockFilter === "in_stock"
) {

    $sql .= "
        AND p.stock_quantity > p.low_stock_limit
    ";

} elseif (
    $stockFilter === "low_stock"
) {

    $sql .= "
        AND p.stock_quantity > 0
        AND p.stock_quantity <= p.low_stock_limit
    ";

} elseif (
    $stockFilter === "out_of_stock"
) {

    $sql .= "
        AND p.stock_quantity <= 0
    ";
}


$sql .= "

    ORDER BY p.id DESC

";


/*
|--------------------------------------------------------------------------
| PREPARE PRODUCT QUERY
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Product query error: " .
        mysqli_error($conn)
    );
}


/*
|--------------------------------------------------------------------------
| BIND PARAMETERS
|--------------------------------------------------------------------------
*/

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);


/*
|--------------------------------------------------------------------------
| EXECUTE
|--------------------------------------------------------------------------
*/

mysqli_stmt_execute(
    $stmt
);


$result =
    mysqli_stmt_get_result(
        $stmt
    );


$products = [];


if ($result) {

    while (
        $row =
            mysqli_fetch_assoc($result)
    ) {

        $products[] =
            $row;
    }
}


mysqli_stmt_close(
    $stmt
);


/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

$totalProducts =
    0;

$activeProducts =
    0;

$lowStock =
    0;

$outOfStock =
    0;


/*
|--------------------------------------------------------------------------
| TOTAL PRODUCTS
|--------------------------------------------------------------------------
*/

$result = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE shop_id = ?"
);


if ($result) {

    mysqli_stmt_bind_param(
        $result,
        "i",
        $shopId
    );

    mysqli_stmt_execute(
        $result
    );

    $summaryResult =
        mysqli_stmt_get_result(
            $result
        );

    if ($summaryResult) {

        $row =
            mysqli_fetch_assoc(
                $summaryResult
            );

        $totalProducts =
            (int) $row["total"];
    }

    mysqli_stmt_close(
        $result
    );
}


/*
|--------------------------------------------------------------------------
| ACTIVE PRODUCTS
|--------------------------------------------------------------------------
*/

$result = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE shop_id = ?
     AND status = 'active'"
);


if ($result) {

    mysqli_stmt_bind_param(
        $result,
        "i",
        $shopId
    );

    mysqli_stmt_execute(
        $result
    );

    $summaryResult =
        mysqli_stmt_get_result(
            $result
        );

    if ($summaryResult) {

        $row =
            mysqli_fetch_assoc(
                $summaryResult
            );

        $activeProducts =
            (int) $row["total"];
    }

    mysqli_stmt_close(
        $result
    );
}


/*
|--------------------------------------------------------------------------
| LOW STOCK
|--------------------------------------------------------------------------
*/

$result = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE shop_id = ?
     AND stock_quantity > 0
     AND stock_quantity <= low_stock_limit"
);


if ($result) {

    mysqli_stmt_bind_param(
        $result,
        "i",
        $shopId
    );

    mysqli_stmt_execute(
        $result
    );

    $summaryResult =
        mysqli_stmt_get_result(
            $result
        );

    if ($summaryResult) {

        $row =
            mysqli_fetch_assoc(
                $summaryResult
            );

        $lowStock =
            (int) $row["total"];
    }

    mysqli_stmt_close(
        $result
    );
}


/*
|--------------------------------------------------------------------------
| OUT OF STOCK
|--------------------------------------------------------------------------
*/

$result = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE shop_id = ?
     AND stock_quantity <= 0"
);


if ($result) {

    mysqli_stmt_bind_param(
        $result,
        "i",
        $shopId
    );

    mysqli_stmt_execute(
        $result
    );

    $summaryResult =
        mysqli_stmt_get_result(
            $result
        );

    if ($summaryResult) {

        $row =
            mysqli_fetch_assoc(
                $summaryResult
            );

        $outOfStock =
            (int) $row["total"];
    }

    mysqli_stmt_close(
        $result
    );
}

?>


>>>>>>> Zuhran
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Products | Grocery Management System
    </title>


<<<<<<< HEAD
    <!-- Bootstrap CSS -->
=======
>>>>>>> Zuhran
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

<<<<<<< HEAD
    <!-- Your Products CSS -->
    <link rel="stylesheet" href="../assets/css/products.css">
=======

    <link
        rel="stylesheet"
        href="../assets/css/products.css"
    >
>>>>>>> Zuhran

    <style>
        /* PAGE LOADER */
        #page-loader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 14px;
        }

        #page-loader .loader-spinner {
            width: 45px;
            height: 45px;
            border: 4px solid #e5e7eb;
            border-top-color: #198754;
            border-radius: 50%;
            animation: loaderSpin 0.8s linear infinite;
        }

        #page-loader p {
            margin: 0;
            font-size: 14px;
            color: #555;
        }

        @keyframes loaderSpin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Profile photo */
        .profile-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dashboard-profile-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
</head>


<body>

<!-- PAGE LOADER -->
<div id="page-loader">
    <div class="loader-spinner"></div>
    <p>Loading Products...</p>
</div>

<<<<<<< HEAD
<!-- SIDEBAR -->
=======
<!-- =====================================================
     SIDEBAR
===================================================== -->

>>>>>>> Zuhran
<aside class="sidebar">


    <div class="brand">
<<<<<<< HEAD
=======


>>>>>>> Zuhran
        <img
            src="../assets/IMAGES/mainLogo.png"
            alt="Grocery Management System"
        >


        <div>

            <h2>
                Grocery Manager
            </h2>


            <span>
                Seller Panel
            </span>

        </div>
<<<<<<< HEAD
=======


>>>>>>> Zuhran
    </div>

    <nav class="sidebar-nav">


        <p class="nav-title">
            MAIN
        </p>


        <a
            href="dashboard.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ⌂
            </span>

            Dashboard

        </a>


        <a
            href="products.php"
            class="nav-link active"
        >

            <span class="nav-icon">
                ▣
            </span>

            Products

        </a>


        <a
            href="categories.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ▤
            </span>

            Categories

        </a>


        <a
            href="stock.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ▥
            </span>

            Stock

        </a>

<<<<<<< HEAD
        <p class="nav-title">SALES</p>
=======

        <p class="nav-title">
            SALES
        </p>


        <a
            href="new-sale.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ＋
            </span>
>>>>>>> Zuhran

            New Sale

        </a>


        <a
            href="sales-history.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ▤
            </span>

            Sales History

        </a>


        <a
            href="invoices.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ▧
            </span>

            Invoices

        </a>

<<<<<<< HEAD
        <p class="nav-title">CUSTOMERS</p>
=======

        <p class="nav-title">
            CUSTOMERS
        </p>


        <a
            href="customers.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ♙
            </span>
>>>>>>> Zuhran

            Customers

        </a>


        <a
            href="khata.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ₨
            </span>

            Digital Khata

        </a>


        <a
            href="payments.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ✓
            </span>

            Payments

        </a>

<<<<<<< HEAD
        <p class="nav-title">PURCHASE</p>
=======

        <p class="nav-title">
            PURCHASE
        </p>


        <a
            href="suppliers.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ▣
            </span>
>>>>>>> Zuhran

            Suppliers

        </a>


        <a
            href="purchases.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ↓
            </span>

            Purchases

        </a>

<<<<<<< HEAD
        <p class="nav-title">BUSINESS</p>
=======

        <p class="nav-title">
            BUSINESS
        </p>


        <a
            href="profit.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ↗
            </span>
>>>>>>> Zuhran

            Profit

        </a>


        <a
            href="reports.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ▥
            </span>

            Reports

        </a>


        <a
            href="settings.php"
            class="nav-link"
        >

            <span class="nav-icon">
                ⚙
            </span>

            Settings

        </a>


    </nav>

    <div class="sidebar-bottom">


        <a
            href="#"
            class="nav-link"
        >

            <span class="nav-icon">
                ?
            </span>

            Help & Support

        </a>


        <a
            href="../logout.php"
            class="logout-link"
        >

            <span class="nav-icon">
                ↪
            </span>

            Logout

        </a>


    </div>


</aside>

<<<<<<< HEAD
<!-- MAIN CONTENT -->
=======

<!-- =====================================================
     MAIN CONTENT
===================================================== -->

>>>>>>> Zuhran
<main class="main-content">

    <!-- TOPBAR -->
    <header class="topbar">


        <div class="topbar-left">

<<<<<<< HEAD
            <button class="menu-button" type="button">
=======

            <button
                class="menu-button"
                type="button"
            >

>>>>>>> Zuhran
                ☰

            </button>


            <div>
<<<<<<< HEAD
                <h1>Products</h1>
                <p>Manage your grocery products</p>
=======

                <h1>
                    Products
                </h1>


                <p>
                    Manage your grocery products
                </p>

>>>>>>> Zuhran
            </div>


        </div>

        <div class="topbar-right">

<<<<<<< HEAD
            <button class="notification-button" type="button">
=======

            <button
                class="notification-button"
                type="button"
            >

>>>>>>> Zuhran
                🔔

                <span
                    class="notification-dot"
                ></span>

            </button>

            <div class="profile">


                <a href="../profile.php">

                    <div class="profile-avatar">

<<<<<<< HEAD
                        <?php if (!empty($profile_photo)): ?>

                            <img
                                src="../<?php echo htmlspecialchars($profile_photo); ?>"
                                alt="Profile Photo"
                                class="dashboard-profile-photo"
                            >

                        <?php else: ?>

                            <?php echo strtoupper(substr($name, 0, 2)); ?>

                        <?php endif; ?>
=======
                        <?= e(
                            strtoupper(
                                substr(
                                    $_SESSION["user_name"] ?? "U",
                                    0,
                                    2
                                )
                            )
                        ) ?>
>>>>>>> Zuhran

                    </div>

                </a>

                <div class="profile-info">

                    <strong>
<<<<<<< HEAD
                        <?php echo htmlspecialchars($name); ?>
=======

                        <?= e(
                            $_SESSION["user_name"] ?? "User"
                        ) ?>

>>>>>>> Zuhran
                    </strong>


                    <span>
<<<<<<< HEAD
                        <?php echo htmlspecialchars($role); ?>
=======

                        <?= e(
                            $_SESSION["role"] ?? "seller"
                        ) ?>

>>>>>>> Zuhran
                    </span>

                </div>


            </div>


        </div>


    </header>

<<<<<<< HEAD
    <!-- PAGE CONTENT -->
    <div class="page-content">

=======

    <!-- =================================================
         PAGE CONTENT
    ================================================= -->

    <div class="page-content">


        <!-- MESSAGE -->

        <?php if ($message !== ""): ?>

            <div
                class="alert alert-<?= e($messageType) ?> alert-dismissible fade show"
                role="alert"
            >

                <?= e($message) ?>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <!-- PAGE HEADER -->

>>>>>>> Zuhran
        <div class="page-header">


            <div>
<<<<<<< HEAD
                <h2>Product Management</h2>
                <p>Add, update and manage products in your store.</p>
=======

                <h2>
                    Product Management
                </h2>


                <p>
                    Add, update and manage products in your store.
                </p>

>>>>>>> Zuhran
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

<<<<<<< HEAD
        <!-- SUMMARY CARDS -->
=======

        <!-- =================================================
             SUMMARY CARDS
        ================================================= -->

>>>>>>> Zuhran
        <div class="summary-grid">

            <div class="summary-card">
<<<<<<< HEAD
                <div class="summary-icon total">▣</div>
                <div>
                    <span>Total Products</span>
                    <h3>0</h3>
=======

                <div class="summary-icon total">
                    ▣
                </div>


                <div>

                    <span>
                        Total Products
                    </span>


                    <h3>
                        <?= number_format(
                            $totalProducts
                        ) ?>
                    </h3>

>>>>>>> Zuhran
                </div>
            </div>

            <div class="summary-card">
<<<<<<< HEAD
                <div class="summary-icon active">✓</div>
                <div>
                    <span>Active Products</span>
                    <h3>0</h3>
=======

                <div class="summary-icon active">
                    ✓
                </div>


                <div>

                    <span>
                        Active Products
                    </span>


                    <h3>
                        <?= number_format(
                            $activeProducts
                        ) ?>
                    </h3>

>>>>>>> Zuhran
                </div>
            </div>

            <div class="summary-card">
<<<<<<< HEAD
                <div class="summary-icon low">!</div>
                <div>
                    <span>Low Stock</span>
                    <h3>0</h3>
=======

                <div class="summary-icon low">
                    !
                </div>


                <div>

                    <span>
                        Low Stock
                    </span>


                    <h3>
                        <?= number_format(
                            $lowStock
                        ) ?>
                    </h3>

>>>>>>> Zuhran
                </div>
            </div>

            <div class="summary-card">
<<<<<<< HEAD
                <div class="summary-icon out">×</div>
                <div>
                    <span>Out of Stock</span>
                    <h3>0</h3>
=======

                <div class="summary-icon out">
                    ×
                </div>


                <div>

                    <span>
                        Out of Stock
                    </span>


                    <h3>
                        <?= number_format(
                            $outOfStock
                        ) ?>
                    </h3>

>>>>>>> Zuhran
                </div>
            </div>

        </div>

<<<<<<< HEAD
        <!-- PRODUCT PANEL -->
        <div class="product-panel">

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
                </select>

                <select class="filter-select">
                    <option value="">All Stock</option>
                    <option value="in-stock">In Stock</option>
                    <option value="low-stock">Low Stock</option>
                    <option value="out-stock">Out of Stock</option>
                </select>

                <button class="filter-btn" type="button">
                    Filter
                </button>

            </div>
=======

        <!-- =================================================
             PRODUCT PANEL
        ================================================= -->

        <div class="product-panel">


            <!-- FILTER -->

            <form
                method="GET"
                action="products.php"
            >

                <div class="filter-area">


                    <div class="search-box">

                        <span>
                            ⌕
                        </span>


                        <input
                            type="text"
                            name="search"
                            placeholder="Search product..."
                            value="<?= e($search) ?>"
                        >

                    </div>


                    <select
                        name="category"
                        class="filter-select"
                    >

                        <option value="">
                            All Categories
                        </option>


                        <?php foreach (
                            $categories as $category
                        ): ?>

                            <option
                                value="<?= (int) $category["id"] ?>"
                                <?= (string) $categoryFilter ===
                                    (string) $category["id"]
                                    ? "selected"
                                    : "" ?>
                            >

                                <?= e(
                                    $category["name"]
                                ) ?>

                            </option>

                        <?php endforeach; ?>


                    </select>


                    <select
                        name="stock"
                        class="filter-select"
                    >

                        <option value="">
                            All Stock
                        </option>


                        <option
                            value="in_stock"
                            <?= $stockFilter === "in_stock"
                                ? "selected"
                                : "" ?>
                        >

                            In Stock

                        </option>


                        <option
                            value="low_stock"
                            <?= $stockFilter === "low_stock"
                                ? "selected"
                                : "" ?>
                        >

                            Low Stock

                        </option>


                        <option
                            value="out_of_stock"
                            <?= $stockFilter === "out_of_stock"
                                ? "selected"
                                : "" ?>
                        >

                            Out of Stock

                        </option>


                    </select>


                    <button
                        type="submit"
                        class="filter-btn"
                    >

                        Filter

                    </button>


                    <?php if (
                        $search !== "" ||
                        $categoryFilter !== "" ||
                        $stockFilter !== ""
                    ): ?>

                        <a
                            href="products.php"
                            class="btn btn-light"
                        >

                            Clear

                        </a>

                    <?php endif; ?>


                </div>

            </form>


            <!-- =================================================
                 TABLE
            ================================================= -->
>>>>>>> Zuhran

            <div class="table-responsive">


                <table
                    class="table product-table align-middle"
                >


                    <thead>
                        <tr>
<<<<<<< HEAD
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
                            <th class="text-end">Action</th>
=======

                            <th>
                                #
                            </th>

                            <th>
                                Product
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                SKU
                            </th>

                            <th>
                                Barcode
                            </th>

                            <th>
                                Unit
                            </th>

                            <th>
                                Purchase Price
                            </th>

                            <th>
                                Sale Price
                            </th>

                            <th>
                                Stock
                            </th>

                            <th>
                                Status
                            </th>

                            <th class="text-end">
                                Action
                            </th>

>>>>>>> Zuhran
                        </tr>
                    </thead>

                    <tbody>


                    <?php if (
                        empty($products)
                    ): ?>

                        <tr>
<<<<<<< HEAD
                            <td colspan="11" class="text-center">
                                No products added yet.
=======

                            <td
                                colspan="11"
                                class="text-center py-5"
                            >

                                <h5>
                                    No products found
                                </h5>


                                <p class="text-muted mb-0">

                                    Add a product to get started.

                                </p>

>>>>>>> Zuhran
                            </td>
                        </tr>


                    <?php else: ?>


                        <?php foreach (
                            $products as $index => $product
                        ): ?>


                            <?php

                            $stock =
                                (float) $product[
                                    "stock_quantity"
                                ];

                            $lowLimit =
                                (float) $product[
                                    "low_stock_limit"
                                ];


                            if ($stock <= 0) {

                                $stockStatus =
                                    "Out of Stock";

                                $statusClass =
                                    "out-stock";

                            } elseif (
                                $stock <= $lowLimit
                            ) {

                                $stockStatus =
                                    "Low Stock";

                                $statusClass =
                                    "low-stock";

                            } else {

                                $stockStatus =
                                    "In Stock";

                                $statusClass =
                                    "in-stock";
                            }


                            $initials =
                                strtoupper(
                                    substr(
                                        $product["name"],
                                        0,
                                        2
                                    )
                                );

                            ?>


                            <tr>


                                <td>

                                    <?= str_pad(
                                        $index + 1,
                                        2,
                                        "0",
                                        STR_PAD_LEFT
                                    ) ?>

                                </td>


                                <td>

                                    <div class="product-name">


                                        <div class="product-image">

                                            <?= e(
                                                $initials
                                            ) ?>

                                        </div>


                                        <div>

                                            <strong>

                                                <?= e(
                                                    $product["name"]
                                                ) ?>

                                            </strong>

                                        </div>


                                    </div>

                                </td>


                                <td>

                                    <?= e(
                                        $product["category_name"]
                                        ?? "Uncategorized"
                                    ) ?>

                                </td>


                                <td>

                                    <?= e(
                                        $product["sku"]
                                    ) ?>

                                </td>


                                <td>

                                    <?= e(
                                        $product["barcode"]
                                    ) ?>

                                </td>


                                <td>

                                    <?= e(
                                        $product["unit"]
                                    ) ?>

                                </td>


                                <td>

                                    ₨
                                    <?= number_format(
                                        (float)
                                        $product[
                                            "purchase_price"
                                        ],
                                        2
                                    ) ?>

                                </td>


                                <td>

                                    ₨
                                    <?= number_format(
                                        (float)
                                        $product[
                                            "sale_price"
                                        ],
                                        2
                                    ) ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= rtrim(
                                            rtrim(
                                                number_format(
                                                    $stock,
                                                    2,
                                                    ".",
                                                    ""
                                                ),
                                                "0"
                                            ),
                                            "."
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <span
                                        class="stock-status <?= e(
                                            $statusClass
                                        ) ?>"
                                    >

                                        <?= e(
                                            $stockStatus
                                        ) ?>

                                    </span>

                                </td>


                                <td class="text-end">


                                    <button
                                        type="button"
                                        class="action-btn edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editProductModal<?= (int) $product["id"] ?>"
                                    >

                                        Edit

                                    </button>


                                    <form
                                        method="POST"
                                        action="products.php"
                                        style="display:inline;"
                                    >


                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $product["id"] ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="delete_product"
                                            class="action-btn delete"
                                            onclick="return confirm('Are you sure you want to delete this product?')"
                                        >

                                            Delete

                                        </button>


                                    </form>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>

<<<<<<< HEAD
            <div class="table-footer">

                <p>Showing 0 products</p>

                <nav>

                    <ul class="pagination pagination-sm mb-0">

                        <li class="page-item disabled">
                            <a class="page-link" href="#">Previous</a>
                        </li>

                        <li class="page-item active">
                            <a class="page-link" href="#">1</a>
                        </li>

                        <li class="page-item disabled">
                            <a class="page-link" href="#">Next</a>
                        </li>

                    </ul>

                </nav>
=======

            <!-- TABLE FOOTER -->

            <div class="table-footer">

                <p>

                    Showing
                    <?= count($products) ?>
                    product(s)

                </p>
>>>>>>> Zuhran

            </div>

        </div>

    </div>


</main>

<<<<<<< HEAD
<!-- ADD PRODUCT MODAL -->
=======

<!-- =====================================================
     ADD PRODUCT MODAL
===================================================== -->

>>>>>>> Zuhran
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
<<<<<<< HEAD
                    <h5 class="modal-title">Add New Product</h5>
                    <small>Enter product information below</small>
=======

                    <h5 class="modal-title">
                        Add New Product
                    </h5>


                    <small>
                        Enter product information below
                    </small>

>>>>>>> Zuhran
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>


            </div>

<<<<<<< HEAD
            <!-- IMPORTANT: ONLY ONE FORM -->
            <form action="products.php" method="POST">

=======

            <form
                method="POST"
                action="products.php"
            >


>>>>>>> Zuhran
                <div class="modal-body">

                    <div class="row g-3">

                    <div class="row g-3">


                        <!-- PRODUCT NAME -->

                        <div class="col-md-8">
<<<<<<< HEAD
                            <label class="form-label">Product Name</label>
=======

                            <label class="form-label">
                                Product Name
                            </label>


                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                placeholder="Enter product name"
                                maxlength="150"
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
                                maxlength="50"
                                required
                            >

                        </div>


                        <!-- BARCODE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Barcode
                            </label>
>>>>>>> Zuhran


                            <input
                                type="text"
<<<<<<< HEAD
                                name="product_name"
                                class="form-control"
                                placeholder="Enter product name"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">SKU</label>

                            <input
                                type="text"
                                name="sku"
                                class="form-control"
                                placeholder="e.g. PRD-001"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Barcode</label>

                            <input
                                type="text"
=======
>>>>>>> Zuhran
                                name="barcode"
                                class="form-control"
                                placeholder="Enter barcode"
                                maxlength="100"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category</label>

<<<<<<< HEAD
                            <select
                                name="category"
                                class="form-select"
                                required
                            >
                                <option value="">Select category</option>
                                <option>Grocery</option>
                                <option>Rice & Grains</option>
                                <option>Dairy</option>
                                <option>Beverages</option>
                                <option>Cleaning</option>
=======
                        <!-- CATEGORY -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Category
                            </label>


                            <select
                                name="category_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select category
                                </option>


                                <?php foreach (
                                    $categories as $category
                                ): ?>

                                    <option
                                        value="<?= (int) $category["id"] ?>"
                                    >

                                        <?= e(
                                            $category["name"]
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>


>>>>>>> Zuhran
                            </select>
                        </div>

<<<<<<< HEAD
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>

                            <select
                                name="unit"
                                class="form-select"
                                required
                            >
                                <option value="">Select unit</option>
                                <option>Piece</option>
                                <option>Kg</option>
                                <option>Gram</option>
                                <option>Liter</option>
                                <option>Pack</option>
                                <option>Dozen</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Purchase Price</label>
=======
                        </div>


                        <!-- UNIT -->

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


                        <!-- PURCHASE PRICE -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Purchase Price
                            </label>

>>>>>>> Zuhran

                            <input
                                type="number"
                                name="purchase_price"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
<<<<<<< HEAD
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Sale Price</label>
=======
                                required
                            >

                        </div>


                        <!-- SALE PRICE -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Sale Price
                            </label>

>>>>>>> Zuhran

                            <input
                                type="number"
                                name="sale_price"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
<<<<<<< HEAD
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Opening Stock</label>
=======
                                required
                            >

                        </div>


                        <!-- STOCK -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Opening Stock
                            </label>
>>>>>>> Zuhran


                            <input
                                type="number"
<<<<<<< HEAD
                                name="opening_stock"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="0"
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="3"
                                placeholder="Optional product description"
                            ></textarea>
                        </div>
=======
                                name="stock_quantity"
                                class="form-control"
                                step="0.01"
                                min="0"
                                value="0"
                                required
                            >

                        </div>


                        <!-- LOW STOCK LIMIT -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Low Stock Limit
                            </label>


                            <input
                                type="number"
                                name="low_stock_limit"
                                class="form-control"
                                step="0.01"
                                min="0"
                                value="5"
                                required
                            >

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Status
                            </label>


                            <select
                                name="status"
                                class="form-select"
                            >

                                <option
                                    value="active"
                                    selected
                                >
                                    Active
                                </option>


                                <option value="inactive">
                                    Inactive
                                </option>

                            </select>

                        </div>

>>>>>>> Zuhran

                    </div>

                </div>
<<<<<<< HEAD

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>
=======
>>>>>>> Zuhran

                    <button
                        type="submit"
                        name="add_product"
                        class="btn add-product-btn"
                    >
                        Save Product
                    </button>

<<<<<<< HEAD
                </div>
=======
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
>>>>>>> Zuhran

            </form>

        </div>

    </div>

</div>

<<<<<<< HEAD
<!-- EDIT PRODUCT MODAL -->
=======

<!-- =====================================================
     EDIT PRODUCT MODALS
===================================================== -->

<?php foreach (
    $products as $product
): ?>


>>>>>>> Zuhran
<div
    class="modal fade"
    id="editProductModal<?= (int) $product["id"] ?>"
    tabindex="-1"
    aria-hidden="true"
>


    <div class="modal-dialog modal-dialog-centered modal-lg">


        <div class="modal-content">

            <div class="modal-header">


                <div>
<<<<<<< HEAD
                    <h5 class="modal-title">Edit Product</h5>
                    <small>Update product information</small>
=======

                    <h5 class="modal-title">
                        Edit Product
                    </h5>


                    <small>
                        Update product information
                    </small>

>>>>>>> Zuhran
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>


            </div>

<<<<<<< HEAD
            <div class="modal-body">
=======

            <form
                method="POST"
                action="products.php"
            >


                <div class="modal-body">


                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $product["id"] ?>"
                    >
>>>>>>> Zuhran


                    <div class="row g-3">

<<<<<<< HEAD
=======

                        <!-- PRODUCT NAME -->

>>>>>>> Zuhran
                        <div class="col-md-8">
                            <label class="form-label">Product Name</label>


                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?= e(
                                    $product["name"]
                                ) ?>"
                                maxlength="150"
                                required
                            >
                        </div>

<<<<<<< HEAD
=======

                        <!-- SKU -->

>>>>>>> Zuhran
                        <div class="col-md-4">
                            <label class="form-label">SKU</label>


                            <input
                                type="text"
                                name="sku"
                                class="form-control"
                                value="<?= e(
                                    $product["sku"]
                                ) ?>"
                                maxlength="50"
                                required
                            >
                        </div>

<<<<<<< HEAD
=======

                        <!-- BARCODE -->

>>>>>>> Zuhran
                        <div class="col-md-6">
                            <label class="form-label">Barcode</label>


                            <input
                                type="text"
                                name="barcode"
                                class="form-control"
                                value="<?= e(
                                    $product["barcode"]
                                ) ?>"
                                maxlength="100"
                            >
                        </div>

<<<<<<< HEAD
=======

                        <!-- CATEGORY -->

>>>>>>> Zuhran
                        <div class="col-md-6">
                            <label class="form-label">Category</label>

<<<<<<< HEAD
                            <select class="form-select">
                                <option selected>Select category</option>
=======

                            <select
                                name="category_id"
                                class="form-select"
                                required
                            >


                                <option value="">
                                    Select category
                                </option>


                                <?php foreach (
                                    $categories as $category
                                ): ?>

                                    <option
                                        value="<?= (int) $category["id"] ?>"
                                        <?= (int) $product["category_id"] ===
                                            (int) $category["id"]
                                            ? "selected"
                                            : "" ?>
                                    >

                                        <?= e(
                                            $category["name"]
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>


>>>>>>> Zuhran
                            </select>
                        </div>

<<<<<<< HEAD
=======

                        <!-- UNIT -->

>>>>>>> Zuhran
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>

<<<<<<< HEAD
                            <select class="form-select">
                                <option>Piece</option>
                                <option>Kg</option>
                                <option>Gram</option>
                                <option>Liter</option>
                                <option>Pack</option>
                                <option>Dozen</option>
=======

                            <select
                                name="unit"
                                class="form-select"
                                required
                            >

                                <?php

                                $units = [
                                    "Piece",
                                    "Kg",
                                    "Gram",
                                    "Liter",
                                    "Pack",
                                    "Dozen"
                                ];

                                ?>


                                <?php foreach (
                                    $units as $unit
                                ): ?>

                                    <option
                                        value="<?= e($unit) ?>"
                                        <?= $product["unit"] === $unit
                                            ? "selected"
                                            : "" ?>
                                    >

                                        <?= e($unit) ?>

                                    </option>

                                <?php endforeach; ?>


>>>>>>> Zuhran
                            </select>
                        </div>

<<<<<<< HEAD
                        <div class="col-md-6">
                            <label class="form-label">Purchase Price</label>
=======

                        <!-- PURCHASE PRICE -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Purchase Price
                            </label>
>>>>>>> Zuhran


                            <input
                                type="number"
                                name="purchase_price"
                                class="form-control"
                                value="<?= e(
                                    $product["purchase_price"]
                                ) ?>"
                                step="0.01"
                                min="0"
                                required
                            >
                        </div>

<<<<<<< HEAD
                        <div class="col-md-6">
                            <label class="form-label">Sale Price</label>
=======

                        <!-- SALE PRICE -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Sale Price
                            </label>
>>>>>>> Zuhran


                            <input
                                type="number"
                                name="sale_price"
                                class="form-control"
                                value="<?= e(
                                    $product["sale_price"]
                                ) ?>"
                                step="0.01"
                                min="0"
                                required
                            >
                        </div>

<<<<<<< HEAD
                        <div class="col-md-6">
                            <label class="form-label">Stock Quantity</label>
=======

                        <!-- STOCK -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Stock Quantity
                            </label>
>>>>>>> Zuhran


                            <input
                                type="number"
                                name="stock_quantity"
                                class="form-control"
                                value="<?= e(
                                    $product["stock_quantity"]
                                ) ?>"
                                step="0.01"
                                min="0"
                                required
                            >
                        </div>

<<<<<<< HEAD
                        <div class="col-md-6">
                            <label class="form-label">Low Stock Limit</label>
=======

                        <!-- LOW STOCK LIMIT -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Low Stock Limit
                            </label>
>>>>>>> Zuhran


                            <input
                                type="number"
                                name="low_stock_limit"
                                class="form-control"
                                value="<?= e(
                                    $product["low_stock_limit"]
                                ) ?>"
                                step="0.01"
                                min="0"
                                required
                            >
                        </div>

<<<<<<< HEAD
=======

                        <!-- STATUS -->

>>>>>>> Zuhran
                        <div class="col-md-6">
                            <label class="form-label">Status</label>

<<<<<<< HEAD
                            <select class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
=======

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option
                                    value="active"
                                    <?= $product["status"] === "active"
                                        ? "selected"
                                        : "" ?>
                                >

                                    Active

                                </option>


                                <option
                                    value="inactive"
                                    <?= $product["status"] === "inactive"
                                        ? "selected"
                                        : "" ?>
                                >

                                    Inactive

                                </option>

>>>>>>> Zuhran
                            </select>
                        </div>

                    </div>

                </div>

<<<<<<< HEAD
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
=======

                <div class="modal-footer">


                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
>>>>>>> Zuhran

                        Cancel

                    </button>


                    <button
                        type="submit"
                        name="update_product"
                        class="btn add-product-btn"
                    >

                        Update Product

                    </button>


                </div>


            </form>

        </div>

    </div>

</div>

<<<<<<< HEAD
<!-- Bootstrap JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<!-- Loader control -->
<script>
    window.addEventListener("load", function () {
        const loader = document.getElementById("page-loader");

        if (loader) {
            loader.style.opacity = "0";
            loader.style.transition = "opacity 0.25s ease";

            setTimeout(function () {
                loader.style.display = "none";
            }, 250);
        }
    });
</script>
=======

<?php endforeach; ?>


<!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>
>>>>>>> Zuhran

</body>
<<<<<<< HEAD
=======

>>>>>>> Zuhran
</html>
