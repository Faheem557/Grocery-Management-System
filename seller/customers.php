<?php
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['shop_id'])) {
    header("Location: index.php");
    exit();
}

$shop_id = (int) $_SESSION['shop_id'];

$stmt = $conn->prepare("select id, name, phone, email, address, opening_balance, status, created_at from customers where shop_id = ? order by id asc");
if (!$stmt) {
    die("SQL Prepare Error: " . $conn->error);
}
$stmt->bind_param("i", $shop_id);
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_customers = count($customers);

$active_customers = 0;
$total_credit = 0;

foreach ($customers as $customer) {
    if ($customer['status'] === 'active') {
        $active_customers++;
    }
    $total_credit += (float)$customer['opening_balance'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customers | Grocery Management System</title>

    <link rel="stylesheet" href="../assets/css/customer.css">
</head>

<body>

    <div class="page-container">

        <!-- =========================
             PAGE HEADER
        ========================== -->
        <div class="page-header">

    <div>
        <h1>Customers</h1>
        <p>Manage your customers and their khata accounts.</p>
    </div>

    <div class="header-actions">

        <a href="dashboard.php" class="dashboard-btn">
            ← Back to Dashboard
        </a>

        <a href="add-customer.php" class="add-customer-btn">
            + Add Customer
        </a>

    </div>

    </div>


        <!-- =========================
             SUMMARY CARDS
        ========================== -->
        <div class="summary-grid">

            <div class="summary-card">
                <div class="summary-icon customers-icon">
                    👥
                </div>

                <div>
                    <span>Total Customers</span>
                    <h2><?php echo $total_customers; ?></h2>
                </div>
            </div>


            <div class="summary-card">
                <div class="summary-icon active-icon">
                    ✓
                </div>

                <div>
                    <span>Active Customers</span>
                    <h2><?php echo $active_customers; ?></h2>
                </div>
            </div>


            <div class="summary-card">
                <div class="summary-icon credit-icon">
                    ₨
                </div>

                <div>
                    <span>Total Credit</span>
                    <h2>₨ <?php echo number_format($total_credit, 2); ?></h2>
                </div>
            </div>


            <div class="summary-card">
                <div class="summary-icon paid-icon">
                    ✓
                </div>

                <div>
                    <span>Total Paid</span>
                    <h2>₨ 8,500</h2>
                </div>
            </div>

        </div>


        <!-- =========================
             CUSTOMER TABLE
        ========================== -->
        <div class="customer-card">

            <div class="customer-card-header">

                <div>
                    <h2>Customer List</h2>
                    <p>View and manage all your customers.</p>
                </div>

                <div class="search-box">
                    <input
                        type="text"
                        id="customerSearch"
                        placeholder="Search customer...">
                </div>

            </div>


            <div class="table-wrapper">

                <table class="customer-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Khata Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>


                    <tbody id="customerTableBody">


                        

<?php if (empty($customers)): ?>

    <tr>
        <td colspan="7" class="empty-row">
            No customers found.
        </td>
    </tr>

<?php else: ?>

    <?php foreach ($customers as $customer): ?>

        <?php

        $name = htmlspecialchars($customer['name']);

        $phone = htmlspecialchars(
            $customer['phone'] ?? ''
        );

        $address = htmlspecialchars(
            $customer['address'] ?? ''
        );

        $balance = (float) $customer['opening_balance'];

        $status = $customer['status'];

        $words = explode(' ', trim($customer['name']));

        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        ?>

        <tr>

            <!-- ID -->

            <td>
                <?php echo $customer['id']; ?>
            </td>


            <!-- CUSTOMER -->

            <td>

                <div class="customer-info">

                    <div class="customer-avatar">

                        <?php echo htmlspecialchars($initials); ?>

                    </div>

                    <div>

                        <strong>
                            <?php echo $name; ?>
                        </strong>

                        <span>
                            Customer #<?php echo $customer['id']; ?>
                        </span>

                    </div>

                </div>

            </td>


            <!-- PHONE -->

            <td>

                <?php echo $phone ?: '—'; ?>

            </td>


            <!-- ADDRESS -->

            <td>

                <?php echo $address ?: '—'; ?>

            </td>


            <!-- BALANCE -->

            <td>

                <?php if ($balance > 0): ?>

                    <span class="balance-danger">

                        ₨ <?php echo number_format($balance, 2); ?>

                    </span>

                <?php else: ?>

                    <span class="balance-success">

                        ₨ 0.00

                    </span>

                <?php endif; ?>

            </td>


            <!-- STATUS -->

            <td>

                <?php if ($status === 'active'): ?>

                    <span class="status active">
                        Active
                    </span>

                <?php else: ?>

                    <span class="status inactive">
                        Inactive
                    </span>

                <?php endif; ?>

            </td>


            <!-- ACTIONS -->

            <td>

                <div class="action-buttons">

                    <a
                         href="customer-khata.php?id=<?php echo $customer['id']; ?>"
                        class="action-button view">
                        View
                    </a>

                    <button
                        type="button"
                        class="action-button edit"
                        onclick="openEditModal(
                            '<?php echo htmlspecialchars($customer['name'], ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($customer['phone'] ?? '', ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($customer['address'] ?? '', ENT_QUOTES); ?>'
                        )">
                        Edit
                    </button>

                </div>

            </td>

        </tr>

    <?php endforeach; ?>

<?php endif; ?>

</tbody>
                
                </table>

            </div>

        </div>

    </div>



    <!-- =====================================================
         VIEW CUSTOMER MODAL
    ====================================================== -->

    <div class="modal-overlay" id="viewModal">

        <div class="customer-modal">

            <!-- HEADER -->
            <div class="modal-header">

                <div>
                    <h2>Customer Details</h2>
                    <p>View customer information and khata balance.</p>
                </div>

                <button
                    type="button"
                    class="modal-close"
                    onclick="closeViewModal()">
                    &times;
                </button>

            </div>


            <!-- CUSTOMER PROFILE -->
            <div class="customer-profile">

                <div class="customer-avatar" id="viewAvatar">
                    AK
                </div>

                <div>

                    <h3 id="viewName">
                        Ahmad Khan
                    </h3>

                    <p id="viewPhone">
                        0300-1234567
                    </p>

                </div>

            </div>


            <!-- DETAILS -->
            <div class="details-grid">

                <div class="detail-box">

                    <span>Phone Number</span>

                    <strong id="viewPhoneDetail">
                        0300-1234567
                    </strong>

                </div>


                <div class="detail-box">

                    <span>Customer Status</span>

                    <strong class="detail-active">
                        Active
                    </strong>

                </div>


                <div class="detail-box">

                    <span>Customer ID</span>

                    <strong>
                        #001
                    </strong>

                </div>


                <div class="detail-box">

                    <span>Member Since</span>

                    <strong>
                        01 September 2026
                    </strong>

                </div>

            </div>


            <!-- KHATA -->
            <div class="khata-box">

                <div>

                    <span>Current Khata Balance</span>

                    <h2>
                        ₨ <span id="viewBalance">4,500</span>
                    </h2>

                </div>

                <span class="khata-label">
                    Credit Due
                </span>

            </div>


            <!-- ADDRESS -->
            <div class="address-box">

                <span>Address</span>

                <p id="viewAddress">
                    Pabbi, Nowshera
                </p>

            </div>


            <!-- FOOTER -->
            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="closeViewModal()">
                    Close
                </button>

                <button
                    type="button"
                    class="btn-primary"
                    onclick="openEditModalFromView()">
                    Edit Customer
                </button>

            </div>

        </div>

    </div>



    <!-- =====================================================
         EDIT CUSTOMER MODAL
    ====================================================== -->

    <div class="modal-overlay" id="editModal">

        <div class="customer-modal">

            <!-- HEADER -->
            <div class="modal-header">

                <div>
                    <h2>Edit Customer</h2>
                    <p>Update customer information.</p>
                </div>

                <button
                    type="button"
                    class="modal-close"
                    onclick="closeEditModal()">
                    &times;
                </button>

            </div>


            <!-- FORM -->
            <form id="editCustomerForm">

                <div class="form-grid">

                    <!-- NAME -->
                    <div class="form-group">

                        <label for="editName">
                            Customer Name
                        </label>

                        <input
                            type="text"
                            id="editName"
                            name="name"
                            placeholder="Enter customer name"
                            required>

                    </div>


                    <!-- PHONE -->
                    <div class="form-group">

                        <label for="editPhone">
                            Phone Number
                        </label>

                        <input
                            type="text"
                            id="editPhone"
                            name="phone"
                            placeholder="03XX-XXXXXXX"
                            required>

                    </div>


                    <!-- ADDRESS -->
                    <div class="form-group full-width">

                        <label for="editAddress">
                            Address
                        </label>

                        <textarea
                            id="editAddress"
                            name="address"
                            rows="3"
                            placeholder="Enter customer address"></textarea>

                    </div>

                </div>


                <!-- FOOTER -->
                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn-secondary"
                        onclick="closeEditModal()">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn-primary">
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </div>



    <!-- =====================================================
         JAVASCRIPT
    ====================================================== -->

    <script>

        /* =========================
           VIEW MODAL
        ========================== */

        function openViewModal(name, phone, address, balance) {

            document.getElementById("viewName").textContent = name;

            document.getElementById("viewPhone").textContent = phone;

            document.getElementById("viewPhoneDetail").textContent = phone;

            document.getElementById("viewAddress").textContent = address;

            document.getElementById("viewBalance").textContent = balance;

            const words = name.trim().split(" ");

            let initials = "";

            if (words.length >= 2) {
                initials =
                    words[0].charAt(0) +
                    words[1].charAt(0);
            } else {
                initials = words[0].substring(0, 2);
            }

            document.getElementById("viewAvatar").textContent =
                initials.toUpperCase();

            document.getElementById("viewModal").style.display = "flex";
        }


        function closeViewModal() {

            document.getElementById("viewModal").style.display = "none";

        }


        /* =========================
           EDIT MODAL
        ========================== */

        function openEditModal(name, phone, address) {

            document.getElementById("editName").value = name;

            document.getElementById("editPhone").value = phone;

            document.getElementById("editAddress").value = address;

            document.getElementById("editModal").style.display = "flex";
        }


        function openEditModalFromView() {

            const name =
                document.getElementById("viewName").textContent;

            const phone =
                document.getElementById("viewPhoneDetail").textContent;

            const address =
                document.getElementById("viewAddress").textContent;

            closeViewModal();

            openEditModal(name, phone, address);
        }


        function closeEditModal() {

            document.getElementById("editModal").style.display = "none";

        }


        /* =========================
           CLOSE MODAL ON OUTSIDE CLICK
        ========================== */

        window.addEventListener("click", function(event) {

            const viewModal =
                document.getElementById("viewModal");

            const editModal =
                document.getElementById("editModal");


            if (event.target === viewModal) {
                closeViewModal();
            }


            if (event.target === editModal) {
                closeEditModal();
            }

        });


        /* =========================
           ESC KEY
        ========================== */

        document.addEventListener("keydown", function(event) {

            if (event.key === "Escape") {

                closeViewModal();

                closeEditModal();

            }

        });


        /* =========================
           SEARCH CUSTOMER
        ========================== */

        document
            .getElementById("customerSearch")
            .addEventListener("input", function() {

                const searchValue =
                    this.value.toLowerCase().trim();

                const rows =
                    document.querySelectorAll(
                        "#customerTableBody tr"
                    );


                rows.forEach(function(row) {

                    const rowText =
                        row.textContent.toLowerCase();

                    if (rowText.includes(searchValue)) {

                        row.style.display = "";

                    } else {

                        row.style.display = "none";

                    }

                });

            });


        /* =========================
           EDIT FORM
        ========================== */

        document
            .getElementById("editCustomerForm")
            .addEventListener("submit", function(event) {

                event.preventDefault();

                alert("Customer information updated successfully.");

                closeEditModal();

            });

    </script>

</body>

</html>