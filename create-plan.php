<?php

require_once __DIR__ . '/config/razorpay.php';

header('Content-Type: application/json');


$amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
$installments = isset($_POST['installments']) ? (int) $_POST['installments']: 0;


$totalAmount = (int) round($amount * 100);
$installmentAmount = (int) round($totalAmount / $installments);

if ($installmentAmount * $installments !== $totalAmount) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Amount must divide evenly into selected installments.'
    ]);

    exit;
}

$planData = [
    'period' => 'monthly',
    'interval' => 1,
    'item' => [
        'name' => 'Monthly Installment Plan',
        'description' => $installments . ' monthly installments',
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
print_r($result);


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
    'installment_amount' => $installmentAmount,
    'total_installments' => $installments
]);

