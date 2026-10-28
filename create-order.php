<?php
header('Content-Type: application/json');

require_once __DIR__ .'/config/razorpay.php';


if( $_SERVER['REQUEST_METHOD'] !=='POST'){
  
    exit;
}
// echo "'crossed barrier\n";

$customerName = $_POST['customer_name']??'';
$paymentType  = $_POST['payment_type'] ?? '';
$totalAmount = $_POST['total_amount'] ?? '';




$totalPaise = round((float) $totalAmount * 100);  // converting rupees to paise


$orderData = [
    'receipt' => 'rcpt_'. rand(1000, 9999),
    'amount' => (int)$totalPaise,
    'currency'=> 'INR',
    'notes' => [
        'customer_name' => $customerName,
        'payment_type' => $paymentType,
    ]
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' =>
            "Content-Type: application/json\r\n" . 
            "Authorization: Basic " . base64_encode(
                RAZORPAY_KEY_ID.':'.RAZORPAY_KEY_SECRET
            ) . "\r\n",
        'content' => json_encode($orderData),
        'ignore_errors' => true, //To capture response body even on 4xx/5xx HTTP codes
        'timeout' => 30
    ]
];
   

$context = stream_context_create($options); // creates context like set of config options 
$response = file_get_contents( RAZORPAY_API_URL.'/orders', false, $context); // Executes the request using that context created by stream_context_create and get data.

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Could not connect to the payment service.']);
    exit;
}

$result = json_decode($response, true);

$statusLine = $http_response_header[0] ?? ''; //API  response ka HTTP Status Code
preg_match('/\s(\d{3})\s/', $statusLine, $matches);  // finding 3 digit no.  status code 
$statusCode = (int) ($matches[1] ?? 0);


if ($statusCode < 200 ||$statusCode >= 300 ||empty($result['id']) ) {
    http_response_code(502);
    echo json_encode([
        'error' => 'Razorpay order creation failed. Check your test credentials and API response.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'order_id' => $result['id'],
    'amount' => $result['amount'],
    'currency' => $result['currency'],
    'key_id' => RAZORPAY_KEY_ID
]);


