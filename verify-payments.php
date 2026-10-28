
<?php

header('contentType: Application/json');
require_once __DIR__.'/config/razorpay.php';

if($_SERVER['REQUEST_METHOD']!== 'POST'){
    http_response_code(405);
    exit;
}

// echo"hello ";

$payment_id = $_POST["razorpay_payment_id"] ?? "";
$order_id = $_POST["razorpay_order_id"]?? "";
$signature = $_POST["razorpay_signature"]?? "";


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
    exit;
}

echo json_encode([
    "success" => false,
    "message"=> "'Payment signature verified failed."
]);

