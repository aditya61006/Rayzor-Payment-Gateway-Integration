<?php

header('Content-Type: application/json');

require_once __DIR__ . '/config/razorpay.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Request method should be post '
    ]);
    exit;
}


$planId = $_POST['plan_id'];


$totalCount = $_POST['total_installments'];
$customerName = trim($_POST['customer_name'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');



$subscriptionData = [
    'plan_id' => $planId,
    'total_count' => $totalCount,
    'quantity' => 1,
    'customer_notify' => 1,
    'notes' => [
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'customer_phone' => $customerPhone
    ]
];



$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.razorpay.com/v1/subscriptions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($subscriptionData),
    CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_TIMEOUT => 30
]);



$response = curl_exec($curl);

$curlError = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);



if ($response === false) {
    error_log('Subscription curl error: ' . $curlError);

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to connect with r/azorpay'
    ]);
    exit;
}

$result = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300) {

    echo json_encode([
        'status' => 'success',
        'razorpay_id' => RAZORPAY_KEY_ID,
        'message' => 'Subscription created successfully',
        'subscription_id' => $result['id'] ?? null,
        'short_url' => $result['short_url'] ?? null,
        'plan_id' => $result['plan_id'] ?? null,
        'razorpay_response' => $result
    ]);

} else {
    echo json_encode([
        'status' => 'error',
        'message' => $result['error']['description']  ?? 'Subscription creation failed'
    ]);
}

?>