
$(document).ready(function () {

    function updateInstallmentAmount() {
        var totalAmount = $("#total_amount").val()
        var installments = $("#installments").val()

        var amount = totalAmount / installments
        

        $("#installmentAmount").val("₹" + amount);
    }
  
    $("#payment_type").on("change", function () {
        if ($(this).val() === "installment") {
            $(".installment_section").slideDown();

        } else {
            $(".installment_section").slideUp();

        }

        updateInstallmentAmount();
    });

    $("#total_amount, #installments").on("input change", function () {
        updateInstallmentAmount();
    });

    $("#paymentForm").on("submit", function (event) {
        event.preventDefault();
        var button = $('#payNow')
        var message = $('#message')
        
        button.prop("disabled", true).text("Creating Order...");
        message.css("color", "#b91c1c").text("");
        let serializedData = $(this).serialize();
        let cleanData = decodeURIComponent(serializedData);
    
        $.ajax({
            url: "../create-order1.php",
            type: "POST",
            data: cleanData,
            dataType: "json",
            success: function (response) {
                console.log(response)
                if (response.success && response.order_id) {
                        console.log("inside ajax, backend sent success")
                    var options = {                // prepare for checkout.js of razor pay
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

                        handler: 
                             function(paymentResponse){
                                // console.log(paymentResponse)
                                 $("#message").css("color","#333").text("Verifying Payment Signature...")
                                //  console.log(paymentResponse.razorpay_payment_id)
                                //  console.log(paymentResponse.razorpay_order_id)
                                //  console.log(paymentResponse.razorpay_signature)


                               
                                //  signature verification call     
                                 $.ajax({
                                    type: "POST",
                                    url : "../verify-payments.php",
                                    dataType: "json",
                                    data: {
                                        key: response.key_id,
                                        amount: response.amount,
                                        amount_paid: parseFloat($("#installmentAmount").val()) ,
                                        currency: response.currency,
                                        name: response.notes.customer_name,
                                        email: response.notes.customer_email,
                                        phone: response.notes.customer_phone,
                                        payment_type: response.notes.payment_type,
                                        razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                        razorpay_order_id:  paymentResponse.razorpay_order_id,
                                        razorpay_signature: paymentResponse.razorpay_signature
                                    },
                                    success: function(response){
                                            if(response.success){
                                                $("#message").css("color","green").text(response.message +"Payment Id: "+ paymentResponse.razorpay_payment_id)
                                            }else{
                                                $("#message").css("color", "#b91c1c").text(response.message)

                                            }
                                    },
                                    error: function(xhr){
                                        var message = "Payment Verification Failed"
                                        if(xhr.responseJSON && xhr.responseJSON.message){
                                            message = xhr.responseJSON.message
                                        }
                                        $("#message").css("color","red").text(message)
                                    }
                                 })

                                //  console.log("inside handler")
                                //  console.log(paymentResponse)
                                 $("#message").css("color", "green").text("Chekout completed Payment id: " + paymentResponse.razorpay_payment_id)


                            }
                        ,  
                        modal: {
                            ondismiss: function(){
                                $("#message").css("color","red").text("Checkout Closed . No payment received.")
                            },
                        },
                        

                        theme: { color: "#2563eb"},
                        
                    

                    };



                    var checkout = new Razorpay(options);
                    checkout.on("payment.failed",function(response){
                        $("#message").css("color","red").text("Payment Failed"+ response.error.description)
                    })
                    checkout.open();


                }else{

                    $("#message")
                        .css("color", "#b91c1c")
                        .text("Unable to create   Razorpay order.");

                }},
                error: function(xhr) {
                    var msg = "order creation failed";
                    // console.log(msg);
                    console.log(xhr.status);
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                        console.log(msg);
                        console.log(error);

                    }
                    message.css("color", "#b91c1c").text(msg);
                },
                complete: function () {
                    button.prop("disabled", false).text("Pay Now");
                }

            })


    });

});
