<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Login Check
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'], $_SESSION['shop_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$shop_id = (int) $_SESSION['shop_id'];


/*
|--------------------------------------------------------------------------
| Only POST Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: new-sale.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$customer_id = !empty($_POST['customer_id'])
    ? (int) $_POST['customer_id']
    : null;

$discount = (float) ($_POST['discount'] ?? 0);

$paid_amount = (float) ($_POST['paid_amount'] ?? 0);

$payment_method = $_POST['payment_method'] ?? 'cash';

$notes = trim($_POST['notes'] ?? '');

$cart_json = $_POST['cart'] ?? '';


/*
|--------------------------------------------------------------------------
| Decode Cart
|--------------------------------------------------------------------------
*/

$cart = json_decode($cart_json, true);

if (!is_array($cart) || empty($cart)) {
    die("Sale error: Cart is empty.");
}


/*
|--------------------------------------------------------------------------
| Validate Discount & Paid Amount
|--------------------------------------------------------------------------
*/

if ($discount < 0) {
    $discount = 0;
}

if ($paid_amount < 0) {
    $paid_amount = 0;
}


/*
|--------------------------------------------------------------------------
| Allowed Payment Methods
|--------------------------------------------------------------------------
*/

$allowed_methods = [
    'cash',
    'bank',
    'jazzcash',
    'easypaisa',
    'other'
];

if (!in_array($payment_method, $allowed_methods, true)) {
    $payment_method = 'cash';
}


/*
|--------------------------------------------------------------------------
| Calculate Sale
|--------------------------------------------------------------------------
*/

$subtotal = 0;

$clean_cart = [];


/*
|--------------------------------------------------------------------------
| Get Products From Database
|--------------------------------------------------------------------------
*/

foreach ($cart as $item) {

    $product_id = (int) ($item['product_id'] ?? 0);

    $quantity = (float) ($item['quantity'] ?? 0);

    if ($product_id <= 0 || $quantity <= 0) {
        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Product
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            id,
            name,
            sale_price,
            stock_quantity,
            status
        FROM products
        WHERE id = ?
          AND shop_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Product query error: " . $conn->error);
    }

    $stmt->bind_param(
        "ii",
        $product_id,
        $shop_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $product = $result->fetch_assoc();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Product Not Found
    |--------------------------------------------------------------------------
    */

    if (!$product) {
        die("Sale error: Product not found.");
    }


    /*
    |--------------------------------------------------------------------------
    | Product Status
    |--------------------------------------------------------------------------
    */

    if ($product['status'] !== 'active') {
        die(
            "Sale error: Product \"" .
            htmlspecialchars($product['name']) .
            "\" is inactive."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Check Stock
    |--------------------------------------------------------------------------
    */

    $available_stock = (float) $product['stock_quantity'];

    if ($quantity > $available_stock) {

        die(
            "Sale error: Not enough stock for \"" .
            htmlspecialchars($product['name']) .
            "\". Available stock: " .
            $available_stock
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Use Database Price
    |--------------------------------------------------------------------------
    |
    | Price frontend se trust nahi karenge.
    | Database ka actual sale_price use hoga.
    |
    */

    $price = (float) $product['sale_price'];

    $item_total = $price * $quantity;

    $subtotal += $item_total;


    /*
    |--------------------------------------------------------------------------
    | Add Clean Item
    |--------------------------------------------------------------------------
    */

    $clean_cart[] = [
        'product_id' => $product_id,
        'quantity'   => $quantity,
        'price'      => $price,
        'total'      => $item_total
    ];
}


/*
|--------------------------------------------------------------------------
| Check Clean Cart
|--------------------------------------------------------------------------
*/

if (empty($clean_cart)) {
    die("Sale error: No valid products found.");
}


/*
|--------------------------------------------------------------------------
| Discount Cannot Exceed Subtotal
|--------------------------------------------------------------------------
*/

if ($discount > $subtotal) {
    $discount = $subtotal;
}


/*
|--------------------------------------------------------------------------
| Grand Total
|--------------------------------------------------------------------------
*/

$total_amount = $subtotal - $discount;


/*
|--------------------------------------------------------------------------
| Paid Cannot Exceed Total
|--------------------------------------------------------------------------
*/

if ($paid_amount > $total_amount) {
    $paid_amount = $total_amount;
}


/*
|--------------------------------------------------------------------------
| Due Amount
|--------------------------------------------------------------------------
*/

$due_amount = $total_amount - $paid_amount;


/*
|--------------------------------------------------------------------------
| Payment Status
|--------------------------------------------------------------------------
*/

if ($due_amount <= 0) {

    $due_amount = 0;

    $payment_status = 'paid';

} elseif ($paid_amount > 0) {

    $payment_status = 'partial';

} else {

    $payment_status = 'due';
}


/*
|--------------------------------------------------------------------------
| Generate Invoice Number
|--------------------------------------------------------------------------
*/

$invoice_no =
    'INV-' .
    date('YmdHis') .
    '-' .
    $shop_id .
    '-' .
    mt_rand(100, 999);


/*
|--------------------------------------------------------------------------
| Start Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();


try {

    /*
    |--------------------------------------------------------------------------
    | Insert Sale
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        INSERT INTO sales
        (
            shop_id,
            customer_id,
            user_id,
            invoice_no,
            sale_date,
            subtotal,
            discount,
            total_amount,
            paid_amount,
            due_amount,
            payment_status,
            notes
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            NOW(),
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    if (!$stmt) {
        throw new Exception(
            "Sale prepare error: " . $conn->error
        );
    }


    $stmt->bind_param(
        "iiisdddddds",
        $shop_id,
        $customer_id,
        $user_id,
        $invoice_no,
        $subtotal,
        $discount,
        $total_amount,
        $paid_amount,
        $due_amount,
        $payment_status,
        $notes
    );


    if (!$stmt->execute()) {
        throw new Exception(
            "Sale database error: " . $stmt->error
        );
    }


    $sale_id = $stmt->insert_id;

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Insert Sale Items
    |--------------------------------------------------------------------------
    */

    foreach ($clean_cart as $item) {

        $item_discount = 0;

        $item_total = $item['total'];


        $stmt = $conn->prepare("
            INSERT INTO sale_items
            (
                sale_id,
                product_id,
                quantity,
                price,
                discount,
                total
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        if (!$stmt) {
            throw new Exception(
                "Sale item prepare error: " .
                $conn->error
            );
        }


        $stmt->bind_param(
            "iidddd",
            $sale_id,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $item_discount,
            $item_total
        );


        if (!$stmt->execute()) {
            throw new Exception(
                "Sale item database error: " .
                $stmt->error
            );
        }


        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | Reduce Product Stock
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE products
            SET stock_quantity = stock_quantity - ?
            WHERE id = ?
              AND shop_id = ?
              AND stock_quantity >= ?
        ");

        if (!$stmt) {
            throw new Exception(
                "Stock update prepare error: " .
                $conn->error
            );
        }


        $quantity = $item['quantity'];

        $stmt->bind_param(
            "diid",
            $quantity,
            $item['product_id'],
            $shop_id,
            $quantity
        );


        if (!$stmt->execute()) {
            throw new Exception(
                "Stock update error: " .
                $stmt->error
            );
        }


        if ($stmt->affected_rows <= 0) {
            throw new Exception(
                "Stock update failed. Stock may have changed."
            );
        }


        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | If Customer + Paid Amount
    |--------------------------------------------------------------------------
    |
    | Customer ki payment ko payments table mein bhi save karenge.
    |
    */

    if ($customer_id !== null && $paid_amount > 0) {

        $reference_no = $invoice_no;

        $payment_notes =
            "Payment received against invoice " .
            $invoice_no;


        $stmt = $conn->prepare("
            INSERT INTO payments
            (
                shop_id,
                customer_id,
                sale_id,
                amount,
                payment_method,
                payment_date,
                reference_no,
                notes
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                NOW(),
                ?,
                ?
            )
        ");

        if (!$stmt) {
            throw new Exception(
                "Payment prepare error: " .
                $conn->error
            );
        }


        $stmt->bind_param(
            "iiidsss",
            $shop_id,
            $customer_id,
            $sale_id,
            $paid_amount,
            $payment_method,
            $reference_no,
            $payment_notes
        );


        if (!$stmt->execute()) {
            throw new Exception(
                "Payment database error: " .
                $stmt->error
            );
        }


        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    header(
        "Location: sale-success.php?id=" .
        $sale_id
    );

    exit;


} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | Rollback
    |--------------------------------------------------------------------------
    */

    $conn->rollback();


    /*
    |--------------------------------------------------------------------------
    | Show Error
    |--------------------------------------------------------------------------
    */

    die(
        "Sale failed: " .
        htmlspecialchars($e->getMessage())
    );
}