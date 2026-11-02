
$(document).ready(function () {

    function updateInstallmentOptions() {
        var totalAmount = parseFloat($("#total_amount").val()) || 0;
        var installmentOptions = $("#installments option");

        installmentOptions.prop("disabled", true);
        if (totalAmount >= 500 && totalAmount < 1000) {
            $("#installments option[value='2']")
                .prop("disabled", false);
        } else if (totalAmount >= 1000 && totalAmount < 2000) {
            $("#installments option[value='2'], #installments option[value='3']")
                .prop("disabled", false);
        } else if (totalAmount >= 2000 && totalAmount < 3000) {
            $("#installments option[value='2'], #installments option[value='3'], #installments option[value='4']")
                .prop("disabled", false);
        } else if (totalAmount >= 3000 && totalAmount < 6000) {
            $("#installments option[value='2'], #installments option[value='3'], #installments option[value='4'], #installments option[value='6']")
                .prop("disabled", false);
        } else if (totalAmount >= 6000) {
            installmentOptions.prop("disabled", false);
        }

        if ($("#installments option:selected").prop("disabled")) {
            var firstAvailable = $("#installments option:not(:disabled):first").val();
            $("#installments").val(firstAvailable);
        }
        updateInstallmentAmount();
    }


    function updateInstallmentAmount() {
        var totalAmount = parseFloat($("#total_amount").val()) || 0;
        var installments = parseInt($("#installments").val()) || 0;
        var period = $("#period").val() || "monthly";
        var installmentAmount = 0;
        if (totalAmount > 0 && installments > 0) {
            installmentAmount = totalAmount / installments;
        }
        $("#totalAmount").text(
            "₹" + totalAmount.toFixed(2)
        );
        $("#installmentAmount").text(
            "₹" + installmentAmount.toFixed(2)
        );
        $("#billingFrequency").text(
            "Every " + period
        );
    }

    $("#payment_type").on("change", function () {
        if ($(this).val() === "installment") {
            $(".installment_section").slideDown();
            $("#installments").prop("required", true);
            updateInstallmentOptions();
        } else {
            $(".installment_section").slideUp();
            $("#installments").prop("required", false);
        }

    });

    $("#total_amount").on("input change", function () {
        if ($("#payment_type").val() === "installment") {
            updateInstallmentOptions();
        }
    });

    $("#installments").on("change", function () {
        updateInstallmentAmount();
    });


    $("#paymentForm").on("submit", function (event) {
        event.preventDefault();
        var button = $("#payNow");
        var message = $("#message");
        var paymentType = $("#payment_type").val();
        var totalAmount = parseFloat($("#total_amount").val()) || 0;
        if (!paymentType) {
            message.css("color", "red")
                .text("Please select a payment type.");
            return;
        }
        if (paymentType === "installment") {
            if (totalAmount < 500) {
                message.css("color", "red")
                    .text("Minimum amount for installment payment is ₹500.");
                return;
            }
            if ($("#installments option:selected").prop("disabled")) {
                message.css("color", "red")
                    .text("Please select a valid installment option.");
                return;
            }
        }

        button.prop("disabled", true).text("Processing...");

        message.css("color", "#333").text("");

        var formData = $(this).serialize();


        if (paymentType === "lumpsum") {
            button.text("Creating Order...");
            $.ajax({
                url: "../create-order1.php",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    if (response.success && response.order_id) {
                        var options = {
                            key: response.key_id,
                            amount: response.amount,
                            currency: response.currency,
                            name: response.notes.customer_name,
                            order_id: response.order_id,
                            prefill: {
                                name: $("#customer_name").val(),
                                email: $("#customer_email").val(),
                                contact: $("#customer_phone").val()
                            },

                            handler: function (paymentResponse) {
                                message.css("color", "#333")
                                    .text("Verifying Payment Signature...");

                             
                                $.ajax({
                                    type: "POST",
                                    url: "../verify-payments.php",
                                    dataType: "json",
                                    data: {
                                        key: response.key_id,
                                        amount: response.amount,
                                        currency: response.currency,
                                        name: response.notes.customer_name,
                                        email: response.notes.customer_email,
                                        phone: response.notes.customer_phone,
                                        payment_type: response.notes.payment_type,
                                        razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                        razorpay_order_id: paymentResponse.razorpay_order_id,
                                        razorpay_signature: paymentResponse.razorpay_signature
                                    },

                                    success: function (verificationResponse) {
                                        if (verificationResponse.success) {
                                            message.css("color", "green")
                                                .text(
                                                    verificationResponse.message +
                                                    " Payment ID: " +
                                                    paymentResponse.razorpay_payment_id
                                                );
                                        } else {

                                            message.css("color", "#b91c1c")
                                                .text(verificationResponse.message);
                                        }
                                    },
                                    error: function (xhr) {
                                        var errorMessage = "Payment verification failed.";
                                        if (xhr.responseJSON && xhr.responseJSON.message) {
                                            errorMessage = xhr.responseJSON.message;
                                        }
                                        message.css("color", "red")
                                            .text(errorMessage);
                                    },
                                    complete: function () {
                                        button.prop("disabled", false)
                                            .text("Pay Now");
                                    }
                                });
                            },
                            modal: {
                                ondismiss: function () {
                                    message.css("color", "red")
                                        .text("Checkout closed. No payment received.");
                                    button.prop("disabled", false)
                                        .text("Pay Now");

                                }
                            },
                            theme: {
                                color: "#2563eb"
                            }
                        };

                        var checkout = new Razorpay(options);
                        checkout.on("payment.failed", function (response) {
                            message.css("color", "red")
                                .text("Payment Failed: " + response.error.description);
                            button.prop("disabled", false)
                                .text("Pay Now");
                        });
                        checkout.open();
                    } else {
                        message.css("color", "#b91c1c")
                            .text("Unable to create Razorpay order.");
                        button.prop("disabled", false)
                            .text("Pay Now");

                    }

                },

                error: function (xhr) {
                    var msg = "Order creation failed.";
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                    }
                    message.css("color", "#b91c1c").text(msg);
                    button.prop("disabled", false)
                        .text("Pay Now");
                }
            });
        }

        else if (paymentType === "installment") {
            button.text("Creating Subscription Plan...");
            $.ajax({
                url: "../create-plan.php",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    console.log("Plan response:", response);
                    if (response.success && response.plan_id) {
                        message.css("color", "green")
                            .text(
                                "Plan created successfully. Plan ID: " +
                                response.plan_id
                            );
                        // Next step:
                        // Send plan_id to create-subscription.php
                        // Open Razorpay Checkout using subscription_id.

                    } else {
                        message.css("color", "red")
                            .text(response.message || "Unable to create plan.");
                    }
                },

                error: function (xhr) {
                    var msg = "Subscription plan creation failed.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    message.css("color", "red").text(msg);
                },
                complete: function () {
                button.prop("disabled", false)
                        .text("Pay Now");
                }
            });
        }
    });
});
