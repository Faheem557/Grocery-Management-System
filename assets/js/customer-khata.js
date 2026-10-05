/* =========================================================
   PAYMENT MODAL
========================================================= */

function openPaymentModal() {

    const modal =
        document.getElementById(
            "paymentModal"
        );

    if (modal) {

        modal.style.display =
            "flex";
    }
}


function closePaymentModal() {

    const modal =
        document.getElementById(
            "paymentModal"
        );

    if (modal) {

        modal.style.display =
            "none";
    }
}


/* =========================================================
   PRINT RECEIPT
========================================================= */

function printPaymentReceipt() {

    window.print();
}


/* =========================================================
   CLOSE MODAL OUTSIDE CLICK
========================================================= */

document
    .querySelectorAll(".modal-overlay")
    .forEach(function (modal) {

        modal.addEventListener(
            "click",
            function (event) {

                if (
                    event.target === modal
                ) {

                    modal.style.display =
                        "none";
                }

            }
        );

    });


/* =========================================================
   ESCAPE KEY
========================================================= */

document.addEventListener(
    "keydown",
    function (event) {

        if (
            event.key === "Escape"
        ) {

            closePaymentModal();
        }

    }
);


/* =========================================================
   PAYMENT AMOUNT PROTECTION
========================================================= */

const paymentAmount =
    document.getElementById(
        "paymentAmount"
    );

const paymentForm =
    document.getElementById(
        "paymentForm"
    );


if (
    paymentAmount
    &&
    paymentForm
) {


    paymentAmount.addEventListener(
        "input",
        function () {

            const max =
                parseFloat(
                    paymentAmount.max
                );


            const value =
                parseFloat(
                    paymentAmount.value
                );


            if (
                value > max
            ) {

                paymentAmount.value =
                    max;
            }

        }
    );


    paymentForm.addEventListener(
        "submit",
        function (event) {

            const max =
                parseFloat(
                    paymentAmount.max
                );


            const value =
                parseFloat(
                    paymentAmount.value
                );


            if (
                !value
                ||
                value <= 0
            ) {

                event.preventDefault();

                alert(
                    "Please enter a valid payment amount."
                );

                return;
            }


            if (
                value > max
            ) {

                event.preventDefault();

                alert(
                    "Payment cannot be greater than the remaining balance."
                );

                return;
            }

        }
    );

}
