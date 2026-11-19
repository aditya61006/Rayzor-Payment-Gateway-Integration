
<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/razorpay.php';


if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Request method should be POST'
    ]);
    exit;
}


$keyId = RAZORPAY_KEY_ID;
$keySecret = RAZORPAY_KEY_SECRET;
$action = $_POST['action'] ?? '';
// $action = 'refund';
$subscription_id = $_POST['subscription_id']?? '';
// $subscription_id = 'sub_U14XlBC224TdZI';
$idempotency_key = $_POST['idempotency_key'] ??'';
// $idempotency_key ='550e8400-e29b-41d4-a716-446655440002';
$payment_id = $_POST['payment_id'] ?? '';
// $payment_id = 'pay_U0kbzEQEliQ5d9';

$url = "";
$data = [];


if ($action == "refund") {

    $url = "https://api.razorpay.com/v1/payments/". $payment_id . "/refund";

    $data = [
        "speed" => "optimum"
    ];

} elseif ($action == "pause") {



    $url = "https://api.razorpay.com/v1/subscriptions/"
        .$subscription_id."/pause";

    $data = [
        "pause_at" => "now"
    ];

} elseif ($action == "resume") {


    $url = "https://api.razorpay.com/v1/subscriptions/"
        . $subscription_id . "/resume";

    $data = [
        "resume_at" => "now"
    ];

} elseif ($action == "cancel") {

    $url = "https://api.razorpay.com/v1/subscriptions/"
        . $subscription_id . "/cancel";

    $data = [
        "cancel_at_cycle_end" => false
    ];

} else {

    echo json_encode([
        "success" => false,
        "message" => "Invalid action."
    ]);
    exit;

}

$headers = [
    "Content-Type: application/json"
];

if ($action == "refund") {
    $headers[] = "X-Refund-Idempotency: ". $idempotency_key;
}




$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => $keyId . ":" . $keySecret,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => $headers
]);

$response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {

    echo json_encode([
        "success" => false,
        "message" => curl_error($ch)
    ]);

    exit;
}

$result = json_decode($response, true);

// print_r($result);

if ($httpCode >= 200 && $httpCode < 300) {

    echo json_encode([
        "success" => true,
        "message" => $result['status'] . " successful.",
        "data" => $result
    ]);

} else {
    echo json_encode([
        "success" => false,
        "message" => $result['error']['description']
            ?? "Razorpay API request failed.",
        "data" => $result
    ]);
}
