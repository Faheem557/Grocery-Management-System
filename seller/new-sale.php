<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id'], $_SESSION['shop_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$shop_id = (int) $_SESSION['shop_id'];


/*
|--------------------------------------------------------------------------
| Get Customers
|--------------------------------------------------------------------------
*/

$customers = [];

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        phone
    FROM customers
    WHERE shop_id = ?
      AND status = 'active'
    ORDER BY name ASC
");

if ($stmt) {

    $stmt->bind_param("i", $shop_id);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| Get Products
|--------------------------------------------------------------------------
*/

$products = [];

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        sale_price,
        stock_quantity,
        unit
    FROM products
    WHERE shop_id = ?
      AND status = 'active'
    ORDER BY name ASC
");

if ($stmt) {

    $stmt->bind_param("i", $shop_id);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt->close();
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

    <title>New Sale</title>

    <link
        rel="stylesheet"
        href="../assets/css/new-sale.css"
    >

</head>

<body>

<div class="sale-page">

    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="sale-header">

        <div>

            <h1>New Sale</h1>

            <p>
                Create a new customer sale
            </p>

        </div>

        <a
            href="dashboard.php"
            class="back-btn"
        >
            ← Back
        </a>

    </div>


    <!-- =========================================================
         SALE FORM
    ========================================================== -->

    <form
        action="save-sale.php"
        method="POST"
        id="saleForm"
    >

        <!-- Hidden cart data -->
        <input
            type="hidden"
            name="cart"
            id="cartInput"
        >


        <div class="sale-layout">


            <!-- =================================================
                 LEFT SIDE
            ================================================== -->

            <div class="sale-left">


                <!-- CUSTOMER CARD -->

                <div class="sale-card">

                    <div class="card-title">

                        <div>

                            <h2>Customer</h2>

                            <p>
                                Select customer for this sale
                            </p>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="customer_id">
                            Customer
                        </label>

                        <select
                            name="customer_id"
                            id="customer_id"
                        >

                            <option value="">
                                Walk-in Customer
                            </option>

                            <?php foreach ($customers as $customer): ?>

                                <option
                                    value="<?= (int) $customer['id'] ?>"
                                >

                                    <?= htmlspecialchars($customer['name']) ?>

                                    <?php if (!empty($customer['phone'])): ?>

                                        -
                                        <?= htmlspecialchars($customer['phone']) ?>

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>


                <!-- =================================================
                     PRODUCT CARD
                ================================================== -->

                <div class="sale-card">

                    <div class="card-title">

                        <div>

                            <h2>Add Product</h2>

                            <p>
                                Select product and quantity
                            </p>

                        </div>

                    </div>


                    <div class="product-entry">


                        <div class="form-group product-select-group">

                            <label for="product_id">
                                Product
                            </label>

                            <select
                                id="product_id"
                            >

                                <option value="">
                                    Select Product
                                </option>

                                <?php foreach ($products as $product): ?>

                                    <option
                                        value="<?= (int) $product['id'] ?>"
                                        data-price="<?= htmlspecialchars($product['sale_price']) ?>"
                                        data-stock="<?= htmlspecialchars($product['stock_quantity']) ?>"
                                        data-unit="<?= htmlspecialchars($product['unit']) ?>"
                                    >

                                        <?= htmlspecialchars($product['name']) ?>

                                        —
                                        Rs.
                                        <?= number_format((float) $product['sale_price'], 2) ?>

                                        —
                                        Stock:
                                        <?= number_format((float) $product['stock_quantity'], 2) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="form-group quantity-group">

                            <label for="quantity">
                                Quantity
                            </label>

                            <input
                                type="number"
                                id="quantity"
                                min="0.01"
                                step="0.01"
                                value="1"
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                &nbsp;
                            </label>

                            <button
                                type="button"
                                class="add-product-btn"
                                id="addProductBtn"
                            >
                                + Add Product
                            </button>

                        </div>

                    </div>


                    <!-- PRODUCT INFO -->

                    <div
                        class="product-info"
                        id="productInfo"
                        style="display:none;"
                    >

                        <span>
                            Price:
                            <strong id="selectedPrice">
                                Rs. 0.00
                            </strong>
                        </span>

                        <span>
                            Available Stock:
                            <strong id="selectedStock">
                                0
                            </strong>
                        </span>

                    </div>

                </div>


                <!-- =================================================
                     CART CARD
                ================================================== -->

                <div class="sale-card">

                    <div class="card-title">

                        <div>

                            <h2>Sale Items</h2>

                            <p>
                                Products added to this sale
                            </p>

                        </div>

                        <span
                            class="item-count"
                            id="itemCount"
                        >
                            0 Items
                        </span>

                    </div>


                    <div class="table-wrapper">

                        <table class="sale-table">

                            <thead>

                                <tr>

                                    <th>
                                        Product
                                    </th>

                                    <th>
                                        Price
                                    </th>

                                    <th>
                                        Qty
                                    </th>

                                    <th>
                                        Total
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody id="cartBody">

                                <tr class="empty-row">

                                    <td colspan="5">

                                        <div class="empty-cart">

                                            <div class="empty-icon">
                                                🛒
                                            </div>

                                            <h3>
                                                No products added
                                            </h3>

                                            <p>
                                                Select a product above to start the sale.
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 RIGHT SIDE
            ================================================== -->

            <div class="sale-right">


                <!-- SUMMARY -->

                <div class="sale-card summary-card">

                    <div class="card-title">

                        <div>

                            <h2>Sale Summary</h2>

                            <p>
                                Payment details
                            </p>

                        </div>

                    </div>


                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <strong id="subtotal">
                            Rs. 0.00
                        </strong>

                    </div>


                    <div class="summary-row">

                        <label for="discount">
                            Discount
                        </label>

                        <input
                            type="number"
                            name="discount"
                            id="discount"
                            min="0"
                            step="0.01"
                            value="0"
                        >

                    </div>


                    <div class="summary-row total-row">

                        <span>
                            Grand Total
                        </span>

                        <strong id="grandTotal">
                            Rs. 0.00
                        </strong>

                    </div>


                    <div class="summary-divider"></div>


                    <div class="form-group">

                        <label for="paid_amount">
                            Paid Amount
                        </label>

                        <input
                            type="number"
                            name="paid_amount"
                            id="paid_amount"
                            min="0"
                            step="0.01"
                            value="0"
                        >

                    </div>


                    <div class="summary-row due-row">

                        <span>
                            Remaining Due
                        </span>

                        <strong id="dueAmount">
                            Rs. 0.00
                        </strong>

                    </div>


                    <!-- PAYMENT METHOD -->

                    <div class="form-group">

                        <label for="payment_method">
                            Payment Method
                        </label>

                        <select
                            name="payment_method"
                            id="payment_method"
                        >

                            <option value="cash">
                                Cash
                            </option>

                            <option value="bank">
                                Bank
                            </option>

                            <option value="jazzcash">
                                JazzCash
                            </option>

                            <option value="easypaisa">
                                EasyPaisa
                            </option>

                            <option value="other">
                                Other
                            </option>

                        </select>

                    </div>


                    <!-- NOTES -->

                    <div class="form-group">

                        <label for="notes">
                            Notes
                        </label>

                        <textarea
                            name="notes"
                            id="notes"
                            rows="3"
                            placeholder="Optional sale notes..."
                        ></textarea>

                    </div>


                    <!-- SAVE -->

                    <button
                        type="submit"
                        class="save-sale-btn"
                        id="saveSaleBtn"
                    >

                        Save Sale

                    </button>

                </div>


                <!-- QUICK INFO -->

                <div class="sale-card info-card">

                    <h3>
                        Sale Information
                    </h3>

                    <div class="info-item">

                        <span>
                            Products
                        </span>

                        <strong id="infoProducts">
                            0
                        </strong>

                    </div>

                    <div class="info-item">

                        <span>
                            Total Quantity
                        </span>

                        <strong id="infoQuantity">
                            0
                        </strong>

                    </div>

                    <div class="info-item">

                        <span>
                            Payment Status
                        </span>

                        <strong id="paymentStatus">
                            Due
                        </strong>

                    </div>

                </div>


            </div>

        </div>

    </form>

</div>


<script src="../assets/js/new-sale.js"></script>

</body>

</html>