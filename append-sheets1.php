<?php

function printer($stmt)
{
    echo"<pre>";
    print_r($stmt);
    echo"</pre>";
}

$path =  __DIR__.'\config\google-service-account.json';


$credentials = json_decode(file_get_contents('config/google-service-account.json'),true);
// printer($credentials);
$clientEmail = $credentials['client_email'] ;
$privateKeyId = $credentials['private_key_id'];
$privateKey = $credentials['private_key'] ;
$tokenUri = $credentials['token_uri']; 

// jwt header 
$header = [
    'alg'=> 'RS256', //algorithm
    'typ' => 'JWT',   // token type 
    "kid"=> $privateKeyId  // key id 
];


$payload = [
    'iss' => $clientEmail,  // your service account email id 
    'scope' => 'https://www.googleapis.com/auth/spreadsheets',  // data access scope 
    'aud' => $tokenUri,
    'iat' => time(),   //Token create hone ka time.
    'exp' => time() + 3600  //Token 1 hour baad expire hoga.
];


$headerJson = json_encode($header);  // convert in json
$payloadJson = json_encode($payload);
function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'),'=');
}

$encodedHeader = base64UrlEncode($headerJson);
$encodedPayload = base64UrlEncode($payloadJson);


$jwtData = $encodedHeader .'.'. $encodedPayload;


// JWT ko OpenSSL se digitally sign 
$signature = '';
$success = openssl_sign(
    $jwtData,
    $signature,
    $privateKey,
    OPENSSL_ALGO_SHA256
);


if (!$success) {
    die("JWT Signature Generation Failed!");
}
// printer($signature);

$encodedSignature = base64UrlEncode($signature);
$jwt = $jwtData . '.' . $encodedSignature;


//   now  using jwt token to get google access token using curl



$postData = [
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 
    'assertion' => $jwt                      
];

$ch = curl_init($tokenUri);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded'
    ]
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("cURL Error: " . curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


$tokenData = json_decode($response, true);

if ($httpCode !== 200 ) {
    echo "Access Token Generation Failed!";
   printer($tokenData);
    exit;
}

$accessToken = $tokenData['access_token'];

// now we have accesstoken to make http request to append data 

$spreadsheetId = '1OTEZkxsFm_rdjLJZnMiUk53Ru_MZGOyLBl9Etwr4yjQ';
$range = "Sheet1!A:P";
$url = 'https://sheets.googleapis.com/v4/spreadsheets/'.$spreadsheetId.'/values/'.$range.':append?valueInputOption=USER_ENTERED';
$vals = json_encode(['values' =>  [
        
            [
            "testing_005    ",
            'testing_001@gmail.com',
            '9882324234',
             date('Y-m-d'),
             '2028-4-23',
            'installment',
            '5000',
            '1000',
            '5',
            '1',
            '4',
            'N/A',
            'pay_j32j4klr34',
            'sub_aslkjr3k4j334r',
            'successfull', //
            'active' // 
            ]
        
    ]
    ]);

$ch = curl_init($url);

// printer($ch);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $vals,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]
]);

$res = curl_exec($ch);
    
if(curl_errno($ch)){
    echo'curl Error'.curl_error($ch);
}
printer(json_decode($res));


        
