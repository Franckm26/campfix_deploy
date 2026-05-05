<?php
$token = '2467|t4pTYscBcgZJE6b9z47MkU2jbgsxkr9apNWK8PCKf879be4e';

$data = json_encode([
    'recipient' => '+639695118287',
    'sender_id' => 'PhilSMS',
    'type' => 'plain',
    'message' => 'Your CampFix verification code is: 123456. This code expires in 5 minutes.'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://app.philsms.com/api/v3/sms/send');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

file_put_contents('C:/xampp/htdocs/Campfix/sms_test_result.txt', 
    'HTTP Code: ' . $httpCode . PHP_EOL . 'Response: ' . $result . PHP_EOL
);
