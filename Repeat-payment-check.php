<?php

date_default_timezone_set('Asia/Kolkata');
header('Content-Type: application/json');
function printer($stmt)
{
    echo "<pre>";
    print_r($stmt);
    echo "</pre>";
}


function convertGoogleDate($value)
{
    if (preg_match('/Date\((\d+),(\d+),(\d+)(?:,(\d+),(\d+),(\d+))?\)/', $value, $matches)) {

        return new DateTimeImmutable(
            sprintf(
                '%04d-%02d-%02d %02d:%02d:%02d',
                $matches[1],
                $matches[2] + 1,
                $matches[3],
                $matches[4] ?? 0,
                $matches[5] ?? 0,
                $matches[6] ?? 0
            ),
            new DateTimeZone('Asia/Kolkata')
        );
    }

    return null;
}


$spreadsheetId = '1OTEZkxsFm_rdjLJZnMiUk53Ru_MZGOyLBl9Etwr4yjQ';
$sheetName = 'Sheet1';
$email = $_POST['email']??'';
// printer($email);
$query = urlencode("SELECT *
    WHERE B = '$email'
    ORDER BY D DESC
    LIMIT 1
"); 
$url = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/gviz/tq?tqx=out:json&sheet={$sheetName}&tq={$query}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL Error: '. curl_error($ch);
    exit;
}


$res = preg_match('/google\.visualization\.Query\.setResponse\((.*)\);/s', $response, $matches);


    $json = json_decode($matches[1], true);
$rows = $json['table']['rows'] ?? [];   
       $cells = $rows[0]['c'] ?? null;

if (empty($rows)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'New customer. Payment is allowed.'
    ]);

    exit;
}

$now = new DateTimeImmutable('now');
$requestedPaymentType = $_POST['payment_type'] ?? '';
// $requestedPaymentType ='installment';
// printer($requestedPaymentType);


$lastPaymentDate = $cells[3]['v'] ?? null;
$nextDueDate = $cells[4]['v'] ?? null;
$lastPayment = convertGoogleDate($lastPaymentDate);
$nextDue = convertGoogleDate($nextDueDate);

if ($requestedPaymentType === 'lumpsum') {

        $allowedPaymentTime = $lastPayment->modify('+24 hours');

        if ($now < $allowedPaymentTime) {

        echo json_encode([
            
            'status' => 'error',
            'message' =>  'You have already made a payment. You can make your next payment after ' . $allowedPaymentTime->format('d-m-Y h:i A')
        ]);
        exit;

        }
    }

elseif ($requestedPaymentType === 'installment') {

    // printer($nextDue);

    if ($now->format('Y-m-d') < $nextDue->format('Y-m-d')) {

        echo json_encode([
            'status' => 'error',
            'message' =>'Your next installment is not due yet. You can pay on ' .$nextDue->format('d-m-Y')
        ]);
        exit;
    }
}

else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid payment type.'
    ]);
    exit;

}


echo json_encode([
    'status' => 'success',
    'message' => 'You are eligible to make this payment.'
]);