/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

let cart = [];


/*
|--------------------------------------------------------------------------
| Elements
|--------------------------------------------------------------------------
*/

const productSelect =
    document.getElementById("product_id");

const quantityInput =
    document.getElementById("quantity");

const addProductBtn =
    document.getElementById("addProductBtn");

const cartBody =
    document.getElementById("cartBody");

const cartInput =
    document.getElementById("cartInput");

const discountInput =
    document.getElementById("discount");

const paidInput =
    document.getElementById("paid_amount");

const subtotalElement =
    document.getElementById("subtotal");

const grandTotalElement =
    document.getElementById("grandTotal");

const dueAmountElement =
    document.getElementById("dueAmount");

const itemCountElement =
    document.getElementById("itemCount");

const infoProducts =
    document.getElementById("infoProducts");

const infoQuantity =
    document.getElementById("infoQuantity");

const paymentStatus =
    document.getElementById("paymentStatus");

const productInfo =
    document.getElementById("productInfo");

const selectedPrice =
    document.getElementById("selectedPrice");

const selectedStock =
    document.getElementById("selectedStock");


/*
|--------------------------------------------------------------------------
| Format Money
|--------------------------------------------------------------------------
*/

function money(amount) {

    return "Rs. " +
        Number(amount).toLocaleString(
            "en-PK",
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );
}


/*
|--------------------------------------------------------------------------
| Product Selection
|--------------------------------------------------------------------------
*/

productSelect.addEventListener(
    "change",
    function () {

        const option =
            productSelect.options[
            productSelect.selectedIndex
            ];

        if (!productSelect.value) {

            productInfo.style.display = "none";

            return;
        }

        const price =
            Number(option.dataset.price || 0);

        const stock =
            Number(option.dataset.stock || 0);

        selectedPrice.textContent =
            money(price);

        selectedStock.textContent =
            stock;

        productInfo.style.display = "flex";
    }
);


/*
|--------------------------------------------------------------------------
| Add Product
|--------------------------------------------------------------------------
*/

addProductBtn.addEventListener(
    "click",
    function () {

        const productId =
            Number(productSelect.value);

        const quantity =
            Number(quantityInput.value);

        if (!productId) {

            alert("Please select a product.");

            return;
        }

        if (quantity <= 0) {

            alert("Please enter a valid quantity.");

            return;
        }


        const option =
            productSelect.options[
            productSelect.selectedIndex
            ];

        const name =
            option.textContent
                .split("—")[0]
                .trim();

        const price =
            Number(option.dataset.price || 0);

        const stock =
            Number(option.dataset.stock || 0);

        const unit =
            option.dataset.unit || "piece";


        if (quantity > stock) {

            alert(
                "Not enough stock. Available stock: " +
                stock
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Existing Product
        |--------------------------------------------------------------------------
        */

        const existing =
            cart.find(
                item =>
                    Number(item.product_id) === productId
            );


        if (existing) {

            const newQuantity =
                Number(existing.quantity) + quantity;

            if (newQuantity > stock) {

                alert(
                    "You cannot add more than available stock."
                );

                return;
            }

            existing.quantity =
                newQuantity;

            existing.total =
                existing.quantity *
                existing.price;

        } else {

            cart.push({

                product_id: productId,

                name: name,

                quantity: quantity,

                price: price,

                unit: unit,

                stock: stock,

                total: quantity * price

            });

        }


        renderCart();


        /*
        |--------------------------------------------------------------------------
        | Reset Product
        |--------------------------------------------------------------------------
        */

        productSelect.value = "";

        quantityInput.value = 1;

        productInfo.style.display = "none";

    }
);


/*
|--------------------------------------------------------------------------
| Render Cart
|--------------------------------------------------------------------------
*/

function renderCart() {

    cartBody.innerHTML = "";


    if (cart.length === 0) {

        cartBody.innerHTML = `

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

        `;

    } else {

        cart.forEach(
            (item, index) => {

                const row =
                    document.createElement("tr");

                row.innerHTML = `

                    <td>

                        <div class="cart-product">

                            <strong>
                                ${escapeHtml(item.name)}
                            </strong>

                            <small>
                                ${escapeHtml(item.unit)}
                            </small>

                        </div>

                    </td>

                    <td>
                        ${money(item.price)}
                    </td>

                    <td>

                        <input
                            type="number"
                            class="cart-qty"
                            value="${item.quantity}"
                            min="0.01"
                            step="0.01"
                            data-index="${index}"
                        >

                    </td>

                    <td>

                        <strong>
                            ${money(item.total)}
                        </strong>

                    </td>

                    <td>

                        <button
                            type="button"
                            class="remove-btn"
                            data-index="${index}"
                        >
                            Remove
                        </button>

                    </td>

                `;

                cartBody.appendChild(row);
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Cart Quantity Changes
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(".cart-qty")
        .forEach(
            input => {

                input.addEventListener(
                    "change",
                    function () {

                        const index =
                            Number(
                                this.dataset.index
                            );

                        let quantity =
                            Number(this.value);


                        if (quantity <= 0) {

                            quantity = 1;

                            this.value = 1;
                        }


                        if (
                            quantity >
                            cart[index].stock
                        ) {

                            alert(
                                "Available stock: " +
                                cart[index].stock
                            );

                            quantity =
                                cart[index].stock;

                            this.value =
                                quantity;
                        }


                        cart[index].quantity =
                            quantity;

                        cart[index].total =
                            quantity *
                            cart[index].price;

                        renderCart();

                    }
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Remove Buttons
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(".remove-btn")
        .forEach(
            button => {

                button.addEventListener(
                    "click",
                    function () {

                        const index =
                            Number(
                                this.dataset.index
                            );

                        cart.splice(index, 1);

                        renderCart();

                    }
                );

            }
        );


    calculateTotals();
}


/*
|--------------------------------------------------------------------------
| Calculate Totals
|--------------------------------------------------------------------------
*/

function calculateTotals() {

    let subtotal = 0;

    let totalQuantity = 0;


    cart.forEach(
        item => {

            subtotal +=
                Number(item.total);

            totalQuantity +=
                Number(item.quantity);

        }
    );


    let discount =
        Number(discountInput.value) || 0;


    if (discount < 0) {

        discount = 0;

        discountInput.value = 0;
    }


    if (discount > subtotal) {

        discount = subtotal;

        discountInput.value =
            subtotal.toFixed(2);
    }


    const grandTotal =
        subtotal - discount;


    let paid =
        Number(paidInput.value) || 0;


    if (paid < 0) {

        paid = 0;

        paidInput.value = 0;
    }


    if (paid > grandTotal) {

        paid = grandTotal;

        paidInput.value =
            grandTotal.toFixed(2);
    }


    const due =
        grandTotal - paid;


    /*
    |--------------------------------------------------------------------------
    | Update UI
    |--------------------------------------------------------------------------
    */

    subtotalElement.textContent =
        money(subtotal);

    grandTotalElement.textContent =
        money(grandTotal);

    dueAmountElement.textContent =
        money(due);


    itemCountElement.textContent =
        cart.length +
        (
            cart.length === 1
                ? " Item"
                : " Items"
        );


    infoProducts.textContent =
        cart.length;

    infoQuantity.textContent =
        totalQuantity;


    if (grandTotal <= 0) {

        paymentStatus.textContent =
            "Paid";

    } else if (due <= 0) {

        paymentStatus.textContent =
            "Paid";

    } else if (paid > 0) {

        paymentStatus.textContent =
            "Partial";

    } else {

        paymentStatus.textContent =
            "Due";
    }


    /*
    |--------------------------------------------------------------------------
    | Update Cart Hidden Input
    |--------------------------------------------------------------------------
    |
    | save-sale.php کو صرف required fields بھیجیں گے۔
    |
    */

    const cleanCart =
        cart.map(
            item => ({

                product_id:
                    Number(item.product_id),

                quantity:
                    Number(item.quantity)

            })
        );


    cartInput.value =
        JSON.stringify(cleanCart);
}


/*
|--------------------------------------------------------------------------
| Discount Change
|--------------------------------------------------------------------------
*/

discountInput.addEventListener(
    "input",
    calculateTotals
);


/*
|--------------------------------------------------------------------------
| Paid Amount Change
|--------------------------------------------------------------------------
*/

paidInput.addEventListener(
    "input",
    calculateTotals
);


/*
|--------------------------------------------------------------------------
| Form Submit
|--------------------------------------------------------------------------
*/

document
    .getElementById("saleForm")
    .addEventListener(
        "submit",
        function (event) {

            if (cart.length === 0) {

                event.preventDefault();

                alert(
                    "Please add at least one product."
                );

                return;
            }


            calculateTotals();


            const total =
                Number(
                    grandTotalElement
                        .textContent
                        .replace("Rs.", "")
                        .replace(/,/g, "")
                );


            const paid =
                Number(paidInput.value) || 0;


            if (paid < 0) {

                event.preventDefault();

                alert(
                    "Paid amount cannot be negative."
                );

                return;
            }


            if (paid > total) {

                event.preventDefault();

                alert(
                    "Paid amount cannot be greater than total."
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Confirm Sale
            |--------------------------------------------------------------------------
            */

            const confirmed =
                confirm(
                    "Are you sure you want to save this sale?"
                );


            if (!confirmed) {

                event.preventDefault();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Disable Button
            |--------------------------------------------------------------------------
            */

            document
                .getElementById("saveSaleBtn")
                .disabled = true;

            document
                .getElementById("saveSaleBtn")
                .textContent =
                "Saving Sale...";

        }
    );


/*
|--------------------------------------------------------------------------
| HTML Escape
|--------------------------------------------------------------------------
*/

function escapeHtml(text) {

    const div =
        document.createElement("div");

    div.textContent = text;

    return div.innerHTML;
}


/*
|--------------------------------------------------------------------------
| Initial Calculation
|--------------------------------------------------------------------------
*/

calculateTotals();
