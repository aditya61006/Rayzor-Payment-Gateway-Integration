<?php
function printer($stmt)
{
    echo "<pre>";
    print_r($stmt);
    echo "</pre>";
}

// echo"hello";
header('Content-Type: application/json');

$spreadsheetId = '1OTEZkxsFm_rdjLJZnMiUk53Ru_MZGOyLBl9Etwr4yjQ';
$sheetName = 'Sheet1';

$query = urlencode("SELECT * ORDER BY D DESC");
$url = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/gviz/tq?tqx=out:json&sheet={$sheetName}&tq={$query}";

$ch = curl_init($url);

 curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch ,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_TIMEOUT,10);

$res = curl_exec($ch);
$response = preg_match('/google\.visualization\.Query\.setResponse\((.*)\);/s', $res, $matches);
$data = json_decode($matches[1],true);


if(!$data || !isset($data['table']['rows'])){
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message"=> "unable to read sheet data !"
    ]);

}

$records  = [];

    // printer($data['table']['rows']);
    foreach( $data['table']['rows'] as $row){
        $cells = [];
        foreach( $row['c'] as $cell){
            $cells[] = $cell['f']?? $cell['v'] ?? null; 
        }
        $records[] = $cells;
    }



echo json_encode([
    "success" => true, 
    'message'=> 'all data fetched successfully',
    'records'=> $records
]);