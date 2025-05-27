<?php
// Log to file
$logFile = '/var/log/apache2/app.log';
$message = "Visited index.php on " . date('Y-m-d H:i:s') . "\n";

// Make sure the directory exists
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

file_put_contents($logFile, $message, FILE_APPEND);
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP App on Apache</title>
</head>
<body>
    <h1>Welcome to My PHP App</h1>
    <p>This is a simple test page served by Apache and PHP inside Docker.</p>
</body>
</html>
