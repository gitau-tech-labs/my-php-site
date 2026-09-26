<?php
$sessionId   = $_POST["sessionId"]   ?? "";
$serviceCode = $_POST["serviceCode"] ?? "";
$phoneNumber = $_POST["phoneNumber"] ?? "";
$text        = $_POST["text"]        ?? "";

$hop = "menu";

if ($text == "") {
    $response  = "CON What would you want to check \n";
    $response .= "1. My Account \n";
    $response .= "2. My phone number";
    $hop = "menu";

} else if ($text == "1") {
    $response  = "CON Choose account information you want to view \n";
    $response .= "1. Account number";
    $hop = "view";

} else if ($text == "2") {
    $response = "END Your phone number is " . $phoneNumber;
    $hop = "phoneNumberEnd";

} else if ($text == "1*1") {
    $accountNumber = "ACC1001";
    $response = "END Your account number is " . $accountNumber;
    $hop = "acNumberEnd";

} else {
    $response = "END Invalid option. Please try again.";
    $hop = "invalid";
}

header('Content-type: text/plain');
header('at-ussd-hop-metadata: ' . $hop);
echo $response;
