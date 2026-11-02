<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Payment System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="payment-container">
        <h2>Payment form</h2>

        <form action="" id="paymentForm">
            <!-- customer name  -->
            <label for="customer_name">Customer Name</label>
            <input type="text" name="customer_name" id="customer_name" placeholder="Enter you Full Name" required>
                <!-- customer_email -->
            <label for="customer_email">Email Address</label>
            <input type="email" id="customer_email" name="customer_email" placeholder="Enter your email address"
                required>

            <!-- Customer Phone -->
            <label for="customer_phone">Phone Number</label>
            <input type="tel" id="customer_phone" name="customer_phone" placeholder="Enter your phone number" required>
            <!-- paymetn opt  -->
            <label for="payment_type">Payment Type</label>
            <select name="payment_type" id="payment_type" required>
                <option value="">Select Payment Type</option>
                <option value="lumpsum">Lumpsum Payment</option>
                <option value="installment">Installment Payment</option>
            </select>

            <!-- payment  -->
            <label for="total_amount">Total Payment Amount</label>
            <input type="number" name="total_amount" id="total_amount" min="1" step=".01" required>

            <!-- Installment Section -->
<div class="installment_section" style="display: none;">

    <!-- Billing Period -->
    <label for="period">Billing Period</label>
    <select name="period" id="period">
        <option value="weekly">Weekly</option>
        <option value="monthly" selected>Monthly</option>
        <option value="yearly">Yearly</option>
    </select>

    <!-- Billing Interval -->
    <label for="interval">Billing Interval</label>
    <select name="interval" id="interval">
        <option value="1">Every 1 period</option>
        <option value="2">Every 2 periods</option>
        <option value="3">Every 3 periods</option>
    </select>

    <!-- Number of Installments -->
    <label for="installments">Number of Installments</label>
    <select name="installments" id="installments">
        <option value="2">2 Installments</option>
        <option value="3">3 Installments</option>
        <option value="4">4 Installments</option>
        <option value="6" selected>6 Installments</option>
        <option value="12">12 Installments</option>
    </select>

    <!-- Payment Summary -->
    <div class="payment-summary">
        <h4>Payment Summary</h4>
        <p>
            Total Amount:
            <span id="totalAmount">₹0.00</span>
        </p>
        <p>
            Amount per installment:
            <input type="text" name="installmentAmount"
                id="installmentAmount" value="₹0.00" readonly>
        </p>
        <p>
            Billing Frequency:
            <span id="billingFrequency">Every 1 month</span>
        </p>

    </div>

</div>


            <button type="submit" id="payNow">Pay Now</button>

            <p id="message"></p>


        </form>

    </div>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../js/script.js"></script>

</body>

</html>