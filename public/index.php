<?php
// USSD endpoint for Africa's Talking with PostgreSQL session tracking
header('Content-Type: text/plain');

// Database connection using Render's DATABASE_URL
$dbUrl = getenv('DATABASE_URL');
$db = null;
if ($dbUrl) {
    $parts = parse_url($dbUrl);
    $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s", 
        $parts['host'], $parts['port'] ?? 5432, ltrim($parts['path'], '/'));
    try {
        $db = new PDO($dsn, $parts['user'], $parts['pass']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log("DB Connection failed: " . $e->getMessage());
    }
}

// Create sessions table if it doesn't exist
if ($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS ussd_sessions (
        id SERIAL PRIMARY KEY,
        session_id VARCHAR(255) NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        input_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

// Read the POST data
$sessionId   = $_POST['sessionId']   ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text        = $_POST['text']        ?? '';

// Save every request to the database
if ($db && $sessionId) {
    $stmt = $db->prepare("INSERT INTO ussd_sessions (session_id, phone_number, input_text) 
                          VALUES (?, ?, ?)");
    $stmt->execute([$sessionId, $phoneNumber, $text]);
}

// Split user input
$input = $text === '' ? [] : explode('*', $text);

// Menu logic (your working example)
if ($text === '') {
    $response  = "CON Welcome to my USSD app\n";
    $response .= "1. Say Hello\n";
    $response .= "2. About";
} elseif ($input[0] === '1') {
    $response = "END Hello $phoneNumber! 👋";
} elseif ($input[0] === '2') {
    $response = "END My USSD app v1.0 on Render";
} else {
    $response = "END Invalid option";
}

echo $response;
