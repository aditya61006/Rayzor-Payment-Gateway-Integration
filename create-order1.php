<?php
require_once __DIR__ . "/config/razorpay.php";


header('Content-Type: application/json');


if( $_SERVER['REQUEST_METHOD'] !== "POST"){
    http_response_code(405);
    echo json_encode([
        'success'=> false,
        'message'=> 'Request method should be POST'
    ]);
    exit;
}
$url = RAZORPAY_API_URL . '/orders';
$ch = curl_init($url);

// echo ' hello ';

$customerName = $_POST['customer_name'] ?? '';
$paymentType = $_POST['payment_type'] ?? '';
$totalAmount = $_POST['total_amount'] ?? '';
$customerEmail =$_POST['customer_email'] ?? '';
$customerPhone = $_POST['customer_phone'] ?? '';
if($paymentType === 'installment'){
    $totalAmount = $_POST['installmentAmount'];

    echo $totalAmount;


}


$totalPaise = round((float) $totalAmount * 100);  // converting rupees to paise

$orderData = [
    'receipt' => 'rcpt_'. rand(1000, 9999),
    'amount' => (int)$totalPaise,
    'currency'=> 'INR',
    'notes' => [
        'customer_name' => $customerName,
        'payment_type' => $paymentType,
        'customer_email' => $customerEmail,
        'customer_phone' => $customerPhone
    ]
];

curl_setopt_array($ch,[
CURLOPT_POST => true,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_USERPWD => RAZORPAY_KEY_ID.':'. RAZORPAY_KEY_SECRET,
CURLOPT_HTTPAUTH  => CURLAUTH_BASIC,
CURLOPT_HTTPHEADER=> [
    'content-Type:application/json',
    'Accept: application/json'
],
CURLOPT_POSTFIELDS => json_encode($orderData),
CURLOPT_TIMECONDITION => 10,
CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$statusCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);


if($response === false){
    
    error_log('razorpay curl err',$error);
    http_response_code(502);
    echo json_encode([
        'success'=> false,
        'message'=> "can't connect to payment service !"
    ]);
    exit;
}

// echo 'created order'. $response;
$result = json_decode($response, true);


if ($statusCode < 200 || $statusCode >= 300 || empty($result['id'])) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => $result['error']['description']
            ?? 'Could not create the payment order.']);
    exit;
}


echo json_encode([
    
    'success' => true,
    'order_id' => $result['id'],
    'amount' => $result['amount'],
    'currency' => $result['currency'],
    'key_id' => RAZORPAY_KEY_ID,
    'notes' =>$result['notes']

]);








?>