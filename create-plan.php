<?php

require_once __DIR__ . '/config/razorpay.php';

header('Content-Type: application/json');

// print_r($_POST);


$customerName = $_POST['customer_name'] ?? '';
$customerEmail = $_POST['customer_email'] ?? '';
$customerPhone = $_POST['customer_phone'] ?? '';
$amount = isset($_POST['total_amount']) ? (float) $_POST['total_amount'] : 0;
$installments = isset($_POST['installments']) ? (int) $_POST['installments'] : 0;
$period = isset($_POST['period']) ?  $_POST['period'] : 0;
$interval = isset($_POST['interval']) ? (int) $_POST['interval'] : 0;




$totalAmount = (int) round($amount * 100);
$installmentAmount = (int) round($totalAmount / $installments);


$planData = [
    'period' => $period,
    'interval' => $interval,
    'item' => [
        'name' => $period.' Installment Plan',
        'description' => $installments .'--'.$period .' installments',
        'amount' => $installmentAmount,
        'currency' => 'INR'
    ]
];

$ch = curl_init(RAZORPAY_API_URL.'/plans');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => RAZORPAY_KEY_ID.':'.RAZORPAY_KEY_SECRET,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($planData),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);


if ($response === false || $curlError) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Razorpay connection failed.'
    ]);

    exit;
}
$result = json_decode($response, true);
// print_r($result);


if ($httpCode !== 200 && $httpCode !== 201) {
    http_response_code($httpCode);

    echo json_encode([
        'success' => false,
        'message' => $result['error']['description']
            ?? 'Unable to create plan.',
        'razorpay_error' => $result['error'] ?? $result,
        'http_code' => $httpCode
    ]);

    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Subscription plan created successfully.',
    'plan_id' => $result['id'],
    'customer_name' => $customerName,
    'customer_email' => $customerEmail,
    'customer_phone' => $customerPhone,
    'interval'=> $result['interval'],
    'period' => $result['period'],
    'installment_amount' => $installmentAmount,
    'total_installments' => $installments,
    'created_at' => $result['item']['created_at'],
    'updated_at' => $result['item']['updated_at']
]);

