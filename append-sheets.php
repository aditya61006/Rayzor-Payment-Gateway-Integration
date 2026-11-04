<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/google-sheets.php';
// array $data
function appendPaymentDetails($data)
{
    $client = new Google_Client();
    $client->setApplicationName('Google Sheet Integraton');
    $client->setScopes([GOOGLE_SHEETS_SCOPE]);
    $client->setAuthConfig(GOOGLE_SERVICE_ACCOUNT_FILE);

    $obj = new Google_Service_Sheets($client);
    $spreadsheetId = GOOGLE_SHEETS_ID;
    $range = GOOGLE_SHEETS_RANGE;

    
    $values = [
        
            [
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_phone'],
            $data['start_date'],
            $data['due_date'],
            $data['payment_type'],
            $data['total_amount'],
            $data['emi_amount'],
            $data['total_installments'],
            $data['paid_installments'],
            $data['remaining_installments'],
            $data['order_id'],
            $data['payment_id'],
            $data['subscription_id'],
            $data['payment_status'], // dekh liyo 
            $data['subscription_status'] // dekh liy
            ]
        
    ];


    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);

    $obj->spreadsheets_values->append(
        $spreadsheetId,
        $range,
        $body,
        [
            'valueInputOption' => 'USER_ENTERED',

        ]
    );
}

