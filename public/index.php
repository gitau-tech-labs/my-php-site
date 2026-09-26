<?php
$time = date("Y-m-d H:i:s");
$phpVersion = phpversion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My PHP Site on Render</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: rgba(255,255,255,0.1);
            padding: 3rem;
            border-radius: 16px;
            backdrop-filter: blur(10px);
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        h1 { margin-top: 0; }
        code { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 Hello from PHP on Render!</h1>
        <p>Server time: <code><?= $time ?></code></p>
        <p>PHP version: <code><?= $phpVersion ?></code></p>
    </div>
</body>
</html>
