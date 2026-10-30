
<?php

header('contentType: Application/json');
require_once __DIR__.'/config/razorpay.php';
require_once __DIR__.'/append-sheets.php';

if($_SERVER['REQUEST_METHOD']!== 'POST'){
    http_response_code(405);
    exit;
}

// echo"hello ";

$payment_id = $_POST["razorpay_payment_id"] ?? "";
$order_id = $_POST["razorpay_order_id"]?? "";
$signature = $_POST["razorpay_signature"]?? "";
$name = $_POST["name"]?? "";
$email = $_POST["email"]?? "";
$phone = $_POST["phone"]?? "";
$payment_type = $_POST["payment_type"]?? "";
$currency = $_POST["currency"]?? "";
$key = $_POST["key"] ?? "";
$amount = $_POST["amount"] ?? "";
$amount_paid = isset($_POST['amount_paid']) ? (float) $_POST['amount_paid'] : '';

// $amount_due = $amount - $amount_paid ?? "";

$totalAmount = (float) ($amount / 100);


if($payment_id ===''|| $order_id === ""|| $signature === ""){
    http_response_code(400);
    echo json_encode([
        "success"=> false,
        "message"=> "Payment Verification Details are Empty or invalid "
    ]);
    exit;
}

$payload = $order_id.'|'. $payment_id;

$exp_sign = hash_hmac('sha256',$payload, RAZORPAY_KEY_SECRET);  // generating signature 

if(hash_equals($exp_sign,$signature)){
    echo json_encode([
        "success"=> true,
        "message" => 'Payment signature verified successfully.',
        "payment_id"=> $payment_id,
        "order_id"=> $order_id
    ]);

    //  after Payment verification appending transactions to google sheets

    try {

        appendPaymentDetails([
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'payment_type' => $payment_type,
            'amount' => $totalAmount,
            'amount_paid' => $amount_paid,
            'order_id' => $order_id,
            'payment_id' => $payment_id
        ]);


    } catch (Throwable $e) {
        error_log($e->getMessage());
        echo ' Connection Failed: ' . $e->getMessage();
    }


    exit;
}

echo json_encode([
    "success" => false,
    "message"=> "'Payment signature verified failed."
]);

