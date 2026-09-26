<?php
// Read the variables sent via POST from Africa's Talking
$sessionId   = $_POST["sessionId"]   ?? "";
$serviceCode = $_POST["serviceCode"] ?? "";
$phoneNumber = $_POST["phoneNumber"] ?? "";
$text        = $_POST["text"]        ?? "";

$hop = "menu"; // default fallback

if ($text == "") {
    // First request — main menu
    $response  = "CON What would you want to check \n";
    $response .= "1. My Account \n";
    $response .= "2. My phone number";
    $hop = "menu";

} else if ($text == "1") {
    // First-level: account menu
    $response  = "CON Choose account information you want to view \n";
    $response .= "1. Account number";
    $hop = "view";

} else if ($text == "2") {
    // Terminal: phone number
    $response = "END Your phone number is " . $phoneNumber;
    $hop = "phoneNumberEnd";

} else if ($text == "1*1") {
    // Second-level: account number
    $accountNumber = "ACC1001";
    $response = "END Your account number is " . $accountNumber;
    $hop = "acNumberEnd";

} else {
    // Any other input
    $response = "END Invalid option. Please try again.";
    $hop = "invalid";
}

// Send response back to Africa's Talking
header('Content-type: text/plain');
header('at-ussd-hop-metadata: ' . $hop);
echo $response;<?php
// Read the variables sent via POST from our API
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"];

if ($text == "") {
    // This is the first request. Note how we start the response with CON
    $response  = "CON What would you want to check \n";
    $response .= "1. My Account \n";
    $response .= "2. My phone number";
    $hop = "menu"

} else if ($text == "1") {
    // Business logic for first level response
    $response = "CON Choose account information you want to view \n";
    $response .= "1. Account number \n";
    $hop = "view"

} else if ($text == "2") {
    // Business logic for first level response
    // This is a terminal request. Note how we start the response with END
    $response = "END Your phone number is ".$phoneNumber;
    $hop = "phoneNumberEnd"

} else if($text == "1*1") { 
    // This is a second level response where the user selected 1 in the first instance
    $accountNumber  = "ACC1001";

    // This is a terminal request. Note how we start the response with END
    $response = "END Your account number is ".$accountNumber;
    $hop = "acNumberEnd"

}

// Echo the response back to the API
header('Content-type: text/plain');
header('at-ussd-hop-metadata: '.$hop);
echo $response;
