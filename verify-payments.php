<?php

header('Content-Type: Application/json');
require_once __DIR__ . '/config/razorpay.php';
require_once __DIR__ . '/append-sheets.php';
require_once __DIR__ . '/email.php';
require_once __DIR__ . '/createEmail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

//  after Payment verification appending transactions to google sheets

if ($_POST['payment_type'] === 'lumpsum') {
    
    $payment_id = $_POST["razorpay_payment_id"] ?? "";
    $order_id = $_POST["razorpay_order_id"] ?? "";
    $signature = $_POST["razorpay_signature"] ?? "";
    $amount = isset($_POST["amount"]) ? (int) $_POST["amount"] : 0;
    $totalAmount = (float) ($amount / 100);

    
    $payload = $order_id . '|' . $payment_id;

    $exp_sign = hash_hmac('sha256', $payload, RAZORPAY_KEY_SECRET);  // generating signature 

    if (hash_equals($exp_sign, $signature)) {
        echo json_encode([
            "success" => true,
            "message" => 'Payment signature verified successfully.',
            "payment_id" => $payment_id,
            "order_id" => $order_id
        ]);



        try {

            $name = $_POST["name"] ?? "";
            $email = $_POST["email"] ?? "";
            $phone = $_POST["phone"] ?? "";
            $payment_type = $_POST["payment_type"] ?? "";
            $currency = $_POST["currency"] ?? "";

            appendPaymentDetails([
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $phone,
                'payment_type' => $payment_type,
                'amount' => $totalAmount,
                'order_id' => $order_id,
                'payment_id' => $payment_id
            ]);




        } catch (Throwable $e) {
            error_log($e->getMessage());
            echo ' Connection Failed with google sheets : ' . $e->getMessage();
        }

        try {

            sendEmail([
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $phone,
                'payment_type' => $payment_type,
                'amount' => $totalAmount,
                'order_id' => $order_id,
                'payment_id' => $payment_id,
            ]);

            // sendEmailbyMailer([
            //     'customer_name' => $name,
            //     'customer_email' => $email,
            //     'customer_phone' => $phone,
            //     'payment_type' => $payment_type,
            //     'amount' => $totalAmount,
            //     'order_id' => $order_id,
            //     'payment_id' => $payment_id,
            // ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());
            echo ' Transaction Email send unsuccessful : ' . $e->getMessage();
        }

        exit;

    } 

echo json_encode([
    "success" => false,
    "message" => "'Payment signature verified failed."
]);

}


elseif($_POST['payment_type']==='installment'){

    $payment_id = $_POST['razorpay_payment_id'] ?? '';
    $subscription_id = $_POST['razorpay_subscription_id'] ?? '';
    $signature = $_POST['razorpay_signature'] ?? '';
    $amount = $_POST['razorpay_response']['plan']['item']['amount']?? 0;
    $totalAmount = (float) ($amount / 100);

        $payload = $payment_id . '|' . $subscription_id ;

        $exp_sign = hash_hmac('sha256', $payload, RAZORPAY_KEY_SECRET);  // generating signature 

        if (hash_equals($exp_sign, $signature)) {
            echo json_encode([
                "success" => true,
                "message" => 'Payment signature verified successfully.',
                "payment_id" => $payment_id,
                "subscription" => $subscription_id
            ]);


            try {


            $keyId = RAZORPAY_KEY_ID;
            $keySecret = RAZORPAY_KEY_SECRET;


            $url = RAZORPAY_API_URL . $subscription_id;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_USERPWD, $keyId . ":" . $keySecret);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'cURL Error: ' . curl_error($ch);
                exit;
            }
            $subscriptionData = json_decode($response, true);

            print_r($subscriptionData);
            




            appendPaymentDetails([
                'customer_name' => $subscriptionData['notes']['customer_name'],
                'customer_email' => $subscriptionData['notes']['customer_email'],
                'customer_phone' => $subscriptionData['notes']['customer_phone'],
                'payment_type' => $_POST['payment_type'],
                'amount' => $totalAmount,
                'order_id' => $subscriptionData['id'],
                'payment_id' => $payment_id
            ]);


            sendEmail([
                'customer_name' => $subscriptionData['notes']['customer_name'],
                'customer_email' => $subscriptionData['notes']['customer_email'],
                'customer_phone' => $subscriptionData['notes']['customer_phone'],
                'payment_type' => $_POST['payment_type'],
                'amount' => $totalAmount,
                'order_id' => $subscriptionData['id'],
                'payment_id' => $payment_id,
            ]);



        } catch (Throwable $e) {
            error_log($e->getMessage());
            echo ' Transaction Email send unsuccessful : ' . $e->getMessage();
        }

        echo json_encode([
            "success" => true,
            "message" => 'Payment signature verified successfully.',
            "payment_id" => $payment_id,
            "order_id" => $subscription_id
        ]);



    }
    exit;

}

