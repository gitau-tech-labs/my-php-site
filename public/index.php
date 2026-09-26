<?php
// USSD endpoint for Africa's Talking
header('Content-Type: text/plain');

// Read the POST data sent by Africa's Talking
$sessionId   = $_POST['sessionId']   ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text        = $_POST['text']        ?? '';

// Split user input on '*'
$input = $text === '' ? [] : explode('*', $text);
$level = count($input);

// Menu logic
if ($text === '') {
    // First screen
    $response  = "CON Welcome to my USSD app\n";
    $response .= "1. Say Hello\n";
    $response .= "2. About";
} elseif ($input[0] === '1') {
    // User pressed 1
    $response = "END Hello $phoneNumber! 👋";
} elseif ($input[0] === '2') {
    // User pressed 2
    $response = "END My USSD app v1.0 on Render";
} else {
    $response = "END Invalid option";
}

echo $response;
