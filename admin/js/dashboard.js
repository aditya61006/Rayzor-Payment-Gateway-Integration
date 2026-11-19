
$(document).ready(function () {

   

    let data

    $.ajax({
        url: 'fetch-payments.php',
        type: 'GET',
        dataType: 'json',
        success: function(response){
                if(response.success){
                    data = response.records
                    displayRecords(data)
                    // console.log(response.records)
                } else {

                    $("#paymentRecords").html(
                        '<p class="text-danger text-center">Unable to load payments.</p>'
                    );

                }
        },
        error: function(xhr){
            console.log(xhr.responseText)
        }
    })


    function displayRecords(records){
        let html = ""

        records.forEach(function (record, index) {

            let customerName = record[0] || "N/A";
            let email = record[1] || "N/A";
            let phone = record[2] || "N/A";
            let paymentDate = record[3] || "N/A";
            let paymentType = record[5] || "N/A";
            let amount = record[6] || "N/A";
            let installmentAmount = record[7] || "N/A";
            let totalInstallments = record[8] || "N/A";
            let paidInstallments = record[9] || "N/A";
            let remainingInstallments = record[10] || "N/A";
            let orderId = record[11] || "N/A";
            let paymentId = record[12] || "N/A";
            let subscriptionId = record[13] || "N/A";
            let paymentStatus = record[14] || "N/A";
            let subscriptionStatus = record[15] || "N/A";
            let duedate = record[4] || "N/A";


            let detailsHTML = "";

            if (paymentType === "lumpsum") {

                detailsHTML = `
            <hr>
            <h6>Lumpsum Payment Details</h6>

            <p><strong>Customer:</strong> ${customerName}</p>
            <p><strong>Email:</strong> ${email}</p>
            <p><strong>Phone:</strong> ${phone}</p>
            <p><strong>Amount:</strong> ₹${amount}</p>
            <p><strong>Payment Date:</strong> ${paymentDate}</p>
            <p><strong>Payment ID:</strong> ${paymentId}</p>   
            <p><strong>Payment Status:</strong> ${paymentStatus}</p>

            <button class="btn btn-sm btn-danger payment-action-btn" data-action="refund" data-payment-id="${paymentId}" ${paymentId === "N/A" ? "disabled" : ""}>
            Refund
            </button>`

            } else if (paymentType === "installment") {

                detailsHTML = `
            <hr>
            <h6>Subscription Details</h6>

            <p><strong>Customer:</strong> ${customerName}</p>
            <p><strong>Email:</strong> ${email}</p>
            <p><strong>Phone:</strong> ${phone}</p>
            <p><strong>Total Amount:</strong> ₹${amount}</p>
            <p><strong>Installment Amount:</strong> ₹${installmentAmount}</p>
            <p><strong>Total Installments:</strong> ${totalInstallments}</p>
            <p><strong>Installments Paid:</strong> ${paidInstallments}</p>
            <p><strong>Remaining Installments:</strong> ${remainingInstallments}</p>
            <p><strong>Last Payment Date:</strong> ${paymentDate}</p>
              <p><strong>Subscription ID:</strong> ${subscriptionId}</p>
            <p><strong>Subscription Status:</strong> ${subscriptionStatus}</p>

            <p><strong>Next Payment Date:</strong> ${duedate}</p>

            <button class="btn btn-sm btn-danger payment-action-btn" data-action="refund" data-payment-id="${paymentId}" ${paymentId === "N/A" ? "disabled" : ""}>
            Refund
            </button>

<button class="btn btn-sm btn-warning payment-action-btn"
        data-action="pause"
        data-subscription-id="${subscriptionId}">
    Pause
</button>

<button class="btn btn-sm btn-success payment-action-btn"
        data-action="resume"
        data-subscription-id="${subscriptionId}">
    Resume
</button>

<button class="btn btn-sm btn-outline-danger payment-action-btn"
        data-action="cancel"
        data-subscription-id="${subscriptionId}">
    Cancel Subscription </button>` ; 
            }

            html += `
        <div class="card mb-3 shadow-sm payment-record"
             data-payment-type="${paymentType}">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center"
                     data-bs-toggle="collapse"
                     data-bs-target="#customer${index}"
                     role="button">

                    <div>
                        <h5 class="mb-1">${customerName}</h5>
                        <small class="text-muted">${email}</small>
                    </div>

                    <div class="text-end">
                        <h5 class="mb-1">₹${amount}</h5>
                        <span class="badge bg-success">${paymentStatus}</span>
                    </div>

                </div>

                <div class="collapse mt-3" id="customer${index}">
                    ${detailsHTML}
                </div>

            </div>
        </div>
    `;

        });

        $('#paymentRecords').html(html);

    }





    $(".payment-filters button").click(function () {

        let filter = $(this).data("filter");

        $(".payment-filters button")
            .removeClass("btn-primary active")
            .addClass("btn-outline-primary");

        $(this)
            .removeClass("btn-outline-primary")
            .addClass("btn-primary active");

        if (filter === "all") {
            $(".payment-record").show();

        } else {
            $(".payment-record").hide();
            $('.payment-record[data-payment-type="' + filter + '"]').show();

        }

    });








    $(document).on("click", ".payment-action-btn", function () {

        let button = $(this);
        console.log(button)
        let action = button.data("action");
        let paymentId = button.data("payment-id");
        let subscriptionId = button.data("subscription-id");

        let data = {
            action: action
        };

        if (action === "refund") {
            data.payment_id = paymentId;
            data.idempotency_key = "refund_" + paymentId;
        } else {
            data.subscription_id = subscriptionId;
        }

        button.prop("disabled", true).text("Processing...");

    
       



        $.ajax({
            url: "payment-manage.php",
            type: "POST",
            data: data,
            dataType: "json",

            success: function (response) {

                if (response.success) {
                   
                    button.prop("disabled", false);
                    showToast(response.message, "success");

                } else {
                    showToast(response.message, "danger");
                    button.prop("disabled", false);
                }
            },

            error: function (xhr) {
                showToast(xhr.responseText, "danger");
                button.prop("disabled", false);
            },

            complete: function () {
                if (!button.prop("disabled")) {
                    button.text(action);
                }
            }
        });

    });


    function showToast(message, type = "success") {

        $("#toastMessage").text(message);

        var toast = $("#myToast");

        toast.removeClass("text-bg-success text-bg-danger text-bg-warning text-bg-primary");

        toast.addClass("text-bg-" + type);

        var toastInstance = bootstrap.Toast.getOrCreateInstance(
            document.getElementById("myToast"),
            { delay: 5000 }
        );

        toastInstance.show();
    }

});


