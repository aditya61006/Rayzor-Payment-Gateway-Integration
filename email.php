<?php



function sendEmail($data){
$url = "https://hook.eu1.make.com/grn98eqacedwl8o33tcfopjtu2wxd204";

$ch = curl_init($url);
$emailData =  [
        "customer_name" => $data['customer_name'],
        "customer_email" => $data['customer_email'],
        "customer_phone" => $data['customer_phone'],
        "payment_type" => $data['payment_type'],
        "amount" => $data['amount'],
        "order_id" => $data['order_id'],
        "payment_id" => $data['payment_id'],
        "payment_status" => "success",
        "payment_date" => date('Y-m-d H:i:s')
];

curl_setopt_array($ch,[
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'content-type: Application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($emailData),
    CURLOPT_TIMEOUT =>30
])
;
$response = curl_exec($ch);





}