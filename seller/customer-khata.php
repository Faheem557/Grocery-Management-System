<?php

session_start();

require_once "../config/database.php";


/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION['user_id'], $_SESSION['shop_id'])) {
    header("Location: ../index.php");
    exit;
}


$shop_id = (int) $_SESSION['shop_id'];
$user_id = (int) $_SESSION['user_id'];

$customer_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$print_payment_id = isset($_GET['print_payment']) ? (int) $_GET['print_payment'] : 0;


if ($customer_id <= 0) {
    header("Location: customers.php");
    exit;
}


/* =========================================================
   GET CUSTOMER
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        phone,
        email,
        address,
        opening_balance,
        credit_limit,
        status
    FROM customers
    WHERE id = ?
      AND shop_id = ?
    LIMIT 1
");


if (!$stmt) {
    die("Customer query error: " . $conn->error);
}


$stmt->bind_param(
    "ii",
    $customer_id,
    $shop_id
);

$stmt->execute();

$result = $stmt->get_result();

$customer = $result->fetch_assoc();

$stmt->close();


if (!$customer) {
    header("Location: customers.php");
    exit;
}


/* =========================================================
   GET SALES
========================================================= */

$sales = [];


$stmt = $conn->prepare("
    SELECT
        id,
        total_amount,
        paid_amount,
        due_amount,
        payment_status,
        notes,
        created_at
    FROM sales
    WHERE customer_id = ?
      AND shop_id = ?
    ORDER BY created_at ASC, id ASC
");


if (!$stmt) {
    die("Sales query error: " . $conn->error);
}


$stmt->bind_param(
    "ii",
    $customer_id,
    $shop_id
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {
    $sales[] = $row;
}


$stmt->close();


/* =========================================================
   GET PAYMENTS
========================================================= */

$payments = [];


$stmt = $conn->prepare("
    SELECT
        id,
        amount,
        payment_method,
        payment_date,
        reference_no,
        notes
    FROM payments
    WHERE customer_id = ?
      AND shop_id = ?
    ORDER BY payment_date ASC, id ASC
");


if (!$stmt) {
    die("Payments query error: " . $conn->error);
}


$stmt->bind_param(
    "ii",
    $customer_id,
    $shop_id
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}


$stmt->close();


/* =========================================================
   CALCULATE CURRENT BALANCE
========================================================= */

$opening_balance = (float) ($customer['opening_balance'] ?? 0);

$total_given = 0;

$total_paid = 0;


foreach ($sales as $sale) {

    $total_given += (float) $sale['due_amount'];
}


foreach ($payments as $payment) {

    $total_paid += (float) $payment['amount'];
}


$current_balance =
    $opening_balance
    + $total_given
    - $total_paid;


/*
   Safety:
   Balance کو negative نہیں دکھانا۔
*/

if ($current_balance < 0) {
    $current_balance = 0;
}


/* =========================================================
   ADD PAYMENT
   Server-side protection
========================================================= */

$payment_error = '';

$payment_success = false;


if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'add_payment'
) {


    /*
       دوبارہ balance calculate کریں
       تاکہ user HTML/JS bypass نہ کر سکے۔
    */

    $server_opening_balance =
        (float) ($customer['opening_balance'] ?? 0);

    $server_total_given = 0;

    $server_total_paid = 0;


    foreach ($sales as $sale) {

        $server_total_given +=
            (float) $sale['due_amount'];
    }


    foreach ($payments as $payment) {

        $server_total_paid +=
            (float) $payment['amount'];
    }


    $server_current_balance =
        $server_opening_balance
        + $server_total_given
        - $server_total_paid;


    if ($server_current_balance < 0) {
        $server_current_balance = 0;
    }


    $amount =
        (float) ($_POST['payment_amount'] ?? 0);


    $payment_method =
        $_POST['payment_method'] ?? 'cash';


    $payment_date =
        $_POST['payment_date']
        ?? date('Y-m-d H:i');


    $payment_date =
        str_replace(
            'T',
            ' ',
            $payment_date
        );


    $reference_no =
        trim(
            $_POST['reference_no'] ?? ''
        );


    $notes =
        trim(
            $_POST['payment_notes'] ?? ''
        );


    /*
       Allowed payment methods
    */

    $allowed_methods = [
        'cash',
        'bank',
        'jazzcash',
        'easypaisa',
        'other'
    ];


    if (
        !in_array(
            $payment_method,
            $allowed_methods,
            true
        )
    ) {

        $payment_method = 'cash';
    }


    /*
       No balance
    */

    if ($server_current_balance <= 0) {

        $payment_error =
            "This customer has no outstanding balance. Payment cannot be added.";
    }


    /*
       Invalid amount
    */

    elseif ($amount <= 0) {

        $payment_error =
            "Please enter a valid payment amount.";
    }


    /*
       Payment greater than balance
    */

    elseif ($amount > $server_current_balance) {

        $payment_error =
            "Payment cannot be greater than the remaining balance of Rs. "
            . number_format(
                $server_current_balance,
                2
            )
            . ".";
    }


    /*
       Save payment
    */

    else {

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
                NULL,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");


        if (!$stmt) {

            die(
                "Payment prepare error: "
                . $conn->error
            );
        }


        $stmt->bind_param(
            "iidssss",
            $shop_id,
            $customer_id,
            $amount,
            $payment_method,
            $payment_date,
            $reference_no,
            $notes
        );


        if (!$stmt->execute()) {

            die(
                "Payment database error: "
                . $stmt->error
            );
        }


        $new_payment_id =
            $stmt->insert_id;


        $stmt->close();


        /*
           Redirect after successful payment.
           Print card will appear separately.
        */

        header(
            "Location: customer-khata.php?id="
            . $customer_id
            . "&success=payment"
            . "&print_payment="
            . $new_payment_id
        );

        exit;
    }
}


/* =========================================================
   CREATE KHATA HISTORY
========================================================= */

$history = [];


/*
   Opening Balance
*/

if ($opening_balance > 0) {

    $history[] = [

        'type' => 'opening',

        'payment_id' => 0,

        'date' => null,

        'description' => 'Opening Balance',

        'given' => $opening_balance,

        'paid' => 0,

        'sort_time' =>
            '0000-00-00 00:00:00',

        'id' => 0
    ];
}


/*
   Sales
*/

foreach ($sales as $sale) {

    $history[] = [

        'type' => 'sale',

        'payment_id' => 0,

        'date' => $sale['created_at'],

        'description' =>
            $sale['notes'] ?: 'Sale',

        'given' =>
            (float) $sale['due_amount'],

        'paid' => 0,

        'sort_time' =>
            $sale['created_at'],

        'id' =>
            (int) $sale['id']
    ];
}


/*
   Payments
*/

foreach ($payments as $payment) {

    $payment_description =
        'Payment';


    $method =
        $payment['payment_method'];


    if ($method === 'jazzcash') {

        $method_name = 'JazzCash';

    } elseif ($method === 'easypaisa') {

        $method_name = 'Easypaisa';

    } else {

        $method_name =
            ucfirst($method);
    }


    $payment_description .=
        ' - '
        . $method_name;


    if (!empty($payment['reference_no'])) {

        $payment_description .=
            ' ('
            . $payment['reference_no']
            . ')';
    }


    $history[] = [

        'type' => 'payment',

        'payment_id' =>
            (int) $payment['id'],

        'date' =>
            $payment['payment_date'],

        'description' =>
            $payment_description,

        'given' => 0,

        'paid' =>
            (float) $payment['amount'],

        'sort_time' =>
            $payment['payment_date'],

        'id' =>
            (int) $payment['id']
    ];
}


/* =========================================================
   SORT HISTORY
========================================================= */

usort(
    $history,
    function ($a, $b) {

        $compare =
            strcmp(
                $a['sort_time'],
                $b['sort_time']
            );


        if ($compare === 0) {

            return
                $a['id']
                <=>
                $b['id'];
        }


        return $compare;
    }
);


/* =========================================================
   RUNNING BALANCE
========================================================= */

$running_balance = 0;


foreach ($history as &$item) {

    $running_balance +=
        $item['given'];


    $running_balance -=
        $item['paid'];


    /*
       Never show negative balance.
    */

    if ($running_balance < 0) {

        $running_balance = 0;
    }


    $item['balance'] =
        $running_balance;
}


unset($item);


/* =========================================================
   PAYMENT RECEIPT DATA
========================================================= */

$print_payment = null;

$print_previous_balance = 0;

$print_remaining_balance = 0;


if ($print_payment_id > 0) {


    foreach ($history as $item) {


        if (
            $item['type'] === 'payment'
            &&
            $item['payment_id']
            === $print_payment_id
        ) {


            foreach ($payments as $payment) {


                if (
                    (int) $payment['id']
                    === $print_payment_id
                ) {

                    $print_payment =
                        $payment;

                    break;
                }
            }


            if ($print_payment) {

                $print_remaining_balance =
                    (float)
                    $item['balance'];


                $print_previous_balance =
                    $print_remaining_balance
                    +
                    (float)
                    $item['paid'];
            }


            break;
        }
    }
}


/* =========================================================
   PAYMENT METHOD LABEL
========================================================= */

$payment_method_label = '';


if ($print_payment) {

    switch (
        $print_payment['payment_method']
    ) {

        case 'cash':

            $payment_method_label =
                'Cash';

            break;


        case 'bank':

            $payment_method_label =
                'Bank';

            break;


        case 'jazzcash':

            $payment_method_label =
                'JazzCash';

            break;


        case 'easypaisa':

            $payment_method_label =
                'Easypaisa';

            break;


        default:

            $payment_method_label =
                'Other';

            break;
    }
}


/* =========================================================
   CUSTOMER HAS BALANCE?
========================================================= */

$can_add_payment =
    $current_balance > 0;

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php
        echo htmlspecialchars(
            $customer['name']
        );
        ?>
        - Khata
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/customer-khata.css"
    >

</head>


<body>


<div class="page-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="page-header">

        <div>

            <a
                href="customers.php"
                class="back-link"
            >
                ← Back to Customers
            </a>


            <h1>
                <?php
                echo htmlspecialchars(
                    $customer['name']
                );
                ?>
                's Khata
            </h1>


            <?php if (!empty($customer['phone'])): ?>

                <p>
                    <?php
                    echo htmlspecialchars(
                        $customer['phone']
                    );
                    ?>
                </p>

            <?php endif; ?>

        </div>


        <?php if ($can_add_payment): ?>

            <button
                type="button"
                class="payment-btn"
                onclick="openPaymentModal()"
            >
                + Add Payment
            </button>

        <?php else: ?>

            <button
                type="button"
                class="payment-btn disabled"
                disabled
            >
                ✓ Balance Cleared
            </button>

        <?php endif; ?>

    </div>



    <!-- =====================================================
         SUCCESS MESSAGE
         صرف success message
    ====================================================== -->

    <?php if (
        isset($_GET['success'])
        &&
        $_GET['success'] === 'payment'
    ): ?>

        <div class="form-success">

            ✓ Payment successfully added.

        </div>

    <?php endif; ?>



    <!-- =====================================================
         ERROR MESSAGE
    ====================================================== -->

    <?php if (!empty($payment_error)): ?>

        <div class="form-error">

            <?php
            echo htmlspecialchars(
                $payment_error
            );
            ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         CUSTOMER PROFILE
    ====================================================== -->

    <div class="customer-profile-card">

        <div class="customer-avatar">

            <?php

            echo htmlspecialchars(
                strtoupper(
                    substr(
                        $customer['name'],
                        0,
                        1
                    )
                )
            );

            ?>

        </div>


        <div class="customer-info">

            <h2>
                <?php
                echo htmlspecialchars(
                    $customer['name']
                );
                ?>
            </h2>


            <?php if (!empty($customer['phone'])): ?>

                <p>
                    📞
                    <?php
                    echo htmlspecialchars(
                        $customer['phone']
                    );
                    ?>
                </p>

            <?php endif; ?>


            <?php if (!empty($customer['address'])): ?>

                <p>
                    📍
                    <?php
                    echo htmlspecialchars(
                        $customer['address']
                    );
                    ?>
                </p>

            <?php endif; ?>

        </div>

    </div>



    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="summary-grid">


        <div class="summary-card">

            <span>
                Total Given
            </span>

            <strong>
                Rs.
                <?php
                echo number_format(
                    $total_given,
                    2
                );
                ?>
            </strong>

        </div>



        <div class="summary-card paid">

            <span>
                Total Paid
            </span>

            <strong>
                Rs.
                <?php
                echo number_format(
                    $total_paid,
                    2
                );
                ?>
            </strong>

        </div>



        <div class="summary-card balance">

            <span>
                Remaining Balance
            </span>

            <strong>
                Rs.
                <?php
                echo number_format(
                    $current_balance,
                    2
                );
                ?>
            </strong>

        </div>



        <div class="summary-card">

            <span>
                Credit Limit
            </span>

            <strong>
                Rs.
                <?php
                echo number_format(
                    (float)
                    $customer['credit_limit'],
                    2
                );
                ?>
            </strong>

        </div>

    </div>



    <!-- =====================================================
         LATEST PAYMENT RECEIPT CARD
         Success message سے الگ
    ====================================================== -->

    <?php if ($print_payment): ?>

        <div class="latest-payment-card">

            <div class="latest-payment-info">

                <div class="latest-payment-icon">
                    ✓
                </div>


                <div>

                    <span class="latest-label">
                        Latest Payment
                    </span>

                    <h3>
                        Rs.
                        <?php
                        echo number_format(
                            (float)
                            $print_payment['amount'],
                            2
                        );
                        ?>
                    </h3>

                    <p>

                        <?php
                        echo htmlspecialchars(
                            $payment_method_label
                        );
                        ?>

                        •

                        <?php

                        echo date(
                            'd M Y, h:i A',
                            strtotime(
                                $print_payment[
                                    'payment_date'
                                ]
                            )
                        );

                        ?>

                    </p>

                </div>

            </div>


            <button
                type="button"
                class="receipt-btn"
                onclick="printPaymentReceipt()"
            >
                🖨 Print Receipt
            </button>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         BALANCE CLEARED MESSAGE
    ====================================================== -->

    <?php if (
        $current_balance <= 0
    ): ?>

        <div class="balance-cleared-card">

            <div class="cleared-icon">
                ✓
            </div>


            <div>

                <h3>
                    Balance Cleared
                </h3>

                <p>
                    This customer's outstanding
                    balance has been fully paid.
                    No further payment is required.
                </p>

            </div>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         KHATA HISTORY
    ====================================================== -->

    <div class="khata-card">


        <div class="card-header">

            <div>

                <h2>
                    Khata History
                </h2>

                <p>
                    Customer transaction history
                </p>

            </div>

        </div>


        <div class="table-wrapper">

            <table class="khata-table">


                <thead>

                    <tr>

                        <th>
                            Date
                        </th>

                        <th>
                            Description
                        </th>

                        <th>
                            Given
                        </th>

                        <th>
                            Paid
                        </th>

                        <th>
                            Balance
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php if (!empty($history)): ?>


                        <?php foreach ($history as $item): ?>


                            <tr>


                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $item['date']
                                        )
                                    ) {

                                        echo date(
                                            'd M Y, h:i A',
                                            strtotime(
                                                $item['date']
                                            )
                                        );

                                    } else {

                                        echo '-';
                                    }

                                    ?>

                                </td>


                                <td>


                                    <?php if (
                                        $item['type']
                                        ===
                                        'sale'
                                    ): ?>

                                        <span
                                            class="sale-badge"
                                        >
                                            Sale
                                        </span>


                                    <?php elseif (
                                        $item['type']
                                        ===
                                        'payment'
                                    ): ?>

                                        <span
                                            class="payment-badge"
                                        >
                                            Payment
                                        </span>


                                    <?php else: ?>

                                        <span
                                            class="transaction-badge"
                                        >
                                            Opening
                                        </span>

                                    <?php endif; ?>


                                    <span
                                        class="transaction-description"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $item[
                                                'description'
                                            ]
                                        );

                                        ?>

                                    </span>

                                </td>


                                <td
                                    class="given-amount"
                                >

                                    <?php if (
                                        $item['given']
                                        > 0
                                    ): ?>

                                        Rs.
                                        <?php

                                        echo number_format(
                                            $item[
                                                'given'
                                            ],
                                            2
                                        );

                                        ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>


                                <td
                                    class="paid-amount"
                                >

                                    <?php if (
                                        $item['paid']
                                        > 0
                                    ): ?>

                                        Rs.
                                        <?php

                                        echo number_format(
                                            $item[
                                                'paid'
                                            ],
                                            2
                                        );

                                        ?>

                                    <?php else: ?>

                                        -

                                    <?php endif; ?>

                                </td>


                                <td
                                    class="balance-amount"
                                >

                                    Rs.
                                    <?php

                                    echo number_format(
                                        $item[
                                            'balance'
                                        ],
                                        2
                                    );

                                    ?>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="5"
                                class="empty-history"
                            >

                                No transaction
                                history found.

                            </td>

                        </tr>


                    <?php endif; ?>


                </tbody>

            </table>

        </div>

    </div>

</div>



<!-- =========================================================
     PAYMENT MODAL
========================================================= -->

<?php if ($can_add_payment): ?>


<div
    id="paymentModal"
    class="modal-overlay"
>


    <div class="payment-modal">


        <div class="modal-header">

            <div>

                <h2>
                    Add Payment
                </h2>

                <p>
                    Record customer payment
                </p>

            </div>


            <button
                type="button"
                class="modal-close"
                onclick="closePaymentModal()"
            >
                ×
            </button>

        </div>



        <!-- OUTSTANDING BALANCE -->

        <div class="payment-balance-info">

            <span>
                Outstanding Balance
            </span>

            <strong>
                Rs.
                <?php
                echo number_format(
                    $current_balance,
                    2
                );
                ?>
            </strong>

        </div>



        <form
            method="POST"
            action=""
            id="paymentForm"
        >


            <input
                type="hidden"
                name="action"
                value="add_payment"
            >


            <div class="form-group">

                <label>
                    Payment Amount
                </label>

                <input
                    type="number"
                    name="payment_amount"
                    id="paymentAmount"
                    step="0.01"
                    min="0.01"
                    max="<?php echo $current_balance; ?>"
                    required
                    placeholder="Enter payment amount"
                >

                <small id="amountHelp">
                    Maximum payment:
                    Rs.
                    <?php
                    echo number_format(
                        $current_balance,
                        2
                    );
                    ?>
                </small>

            </div>



            <div class="form-group">

                <label>
                    Payment Date & Time
                </label>

                <input
                    type="datetime-local"
                    name="payment_date"
                    value="<?php
                    echo date(
                        'Y-m-d\TH:i'
                    );
                    ?>"
                    required
                >

            </div>



            <div class="form-group">

                <label>
                    Payment Method
                </label>

                <select
                    name="payment_method"
                    required
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
                        Easypaisa
                    </option>

                    <option value="other">
                        Other
                    </option>

                </select>

            </div>



            <div class="form-group">

                <label>
                    Reference Number
                </label>

                <input
                    type="text"
                    name="reference_no"
                    placeholder="Transaction / reference number"
                >

            </div>



            <div class="form-group">

                <label>
                    Notes
                </label>

                <textarea
                    name="payment_notes"
                    rows="3"
                    placeholder="Payment notes"
                ></textarea>

            </div>



            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="closePaymentModal()"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="btn-primary"
                >
                    Save Payment
                </button>

            </div>


        </form>

    </div>

</div>


<?php endif; ?>



<!-- =========================================================
     PRINT RECEIPT
========================================================= -->

<?php if ($print_payment): ?>


<div
    id="printReceipt"
    class="print-receipt"
>


    <div class="receipt-header">

        <h1>
            Payment Receipt
        </h1>

        <p>
            Grocery Management
        </p>

    </div>


    <div class="receipt-number">

        Receipt No:
        <strong>
            PAY-<?php
            echo (int)
                $print_payment['id'];
            ?>
        </strong>

    </div>


    <div class="receipt-row">

        <span>
            Customer
        </span>

        <strong>
            <?php
            echo htmlspecialchars(
                $customer['name']
            );
            ?>
        </strong>

    </div>


    <div class="receipt-row">

        <span>
            Phone
        </span>

        <strong>

            <?php

            echo !empty(
                $customer['phone']
            )
                ? htmlspecialchars(
                    $customer['phone']
                )
                : '—';

            ?>

        </strong>

    </div>


    <div class="receipt-row">

        <span>
            Date
        </span>

        <strong>

            <?php

            echo date(
                'd M Y, h:i A',
                strtotime(
                    $print_payment[
                        'payment_date'
                    ]
                )
            );

            ?>

        </strong>

    </div>


    <div class="receipt-row">

        <span>
            Payment Method
        </span>

        <strong>

            <?php
            echo htmlspecialchars(
                $payment_method_label
            );
            ?>

        </strong>

    </div>


    <div class="receipt-row">

        <span>
            Reference
        </span>

        <strong>

            <?php

            echo !empty(
                $print_payment[
                    'reference_no'
                ]
            )
                ? htmlspecialchars(
                    $print_payment[
                        'reference_no'
                    ]
                )
                : '—';

            ?>

        </strong>

    </div>


    <div class="receipt-row">

        <span>
            Notes
        </span>

        <strong>

            <?php

            echo !empty(
                $print_payment['notes']
            )
                ? htmlspecialchars(
                    $print_payment['notes']
                )
                : '—';

            ?>

        </strong>

    </div>


    <div class="receipt-total">

        <span>
            Paid Amount
        </span>

        <strong>

            Rs.
            <?php

            echo number_format(
                (float)
                $print_payment[
                    'amount'
                ],
                2
            );

            ?>

        </strong>

    </div>


    <div class="receipt-balance">


        <div class="receipt-row">

            <span>
                Previous Balance
            </span>

            <strong>

                Rs.
                <?php

                echo number_format(
                    $print_previous_balance,
                    2
                );

                ?>

            </strong>

        </div>


        <div class="receipt-row">

            <span>
                Remaining Balance
            </span>

            <strong>

                Rs.
                <?php

                echo number_format(
                    $print_remaining_balance,
                    2
                );

                ?>

            </strong>

        </div>


    </div>


    <div class="receipt-footer">

        Thank you for your payment.

        <br><br>

        This is a computer generated receipt.

    </div>


</div>


<?php endif; ?>



<script src="../assets/js/customer-khata.js"></script>


</body>

</html>