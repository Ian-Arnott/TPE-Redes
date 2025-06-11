<?php

function generateLoginLog($email, $ip_address, $success = true) {
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SUCCESS' : 'FAILED';
    
    $logEntry = json_encode([
        '@timestamp' => date('c'),
        'email' => $email,
        'ip_address' => $ip_address,
        'login_status' => $status,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'session_id' => session_id() ?: uniqid(),
        'event_type' => 'user_login',
        'source' => 'web_application'
    ]);
    
    $logFile = '/var/log/apache2/login_events.log';
    file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    
    return $logEntry;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? 'test@example.com';
    $ip = $_POST['ip'] ?? $_SERVER['REMOTE_ADDR'];
    $success = $_POST['success'] ?? true;
    
    $result = generateLoginLog($email, $ip, $success);
    echo "<div class='result'>";
    echo "<strong>Log Generated:</strong><br>";
    echo "<pre>" . htmlspecialchars($result) . "</pre>";
    echo "</div>";
}


if (isset($_GET['auto'])) {
    $testUsers = [
        'alice@company.com',
        'bob@company.com', 
        'charlie@company.com'
    ];
    
    $testIPs = [
        '192.168.1.100',
        '10.0.0.50',
        '172.16.0.25',
        '203.0.113.15',
        '198.51.100.42'
    ];
    
    echo "<h3>Auto-generating test login logs...</h3>";
    
    for ($i = 0; $i < 10; $i++) {
        $email = $testUsers[array_rand($testUsers)];
        $ip = $testIPs[array_rand($testIPs)];
        
        $result = generateLoginLog($email, $ip);
        echo "<div class='log-entry'>";
        echo htmlspecialchars($result);
        echo "</div>";
        
        usleep(100000);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Log Generator</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; }
        button { padding: 10px 20px; margin: 10px; background: #007cba; color: white; border: none; border-radius: 5px; }
        .back-button { background: #6c757d; }
        .test-button { background: #28a745; }
        .form-group { margin: 10px 0; }
        label { display: inline-block; width: 120px; }
        input[type="text"], input[type="email"], select { width: 200px; padding: 5px; border: 1px solid #ddd; border-radius: 3px; }
        .section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .log-entry { background: #e8f5e8; padding: 5px; margin: 5px; border-radius: 3px; font-size: 12px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Login Log Simulator</h1>
    
    <div style="margin-bottom: 20px;">
        <a href="index.php">
            <button class="back-button">← Back to Main Menu</button>
        </a>
    </div>
    
    <div class="section">
        <h3>Quick Test Scenarios</h3>
        <p>Generate multiple random login logs automatically:</p>
        <a href="?auto=1"><button type="button" class="test-button">Generate 10 Random Login Logs</button></a>
    </div>
    
    <div class="section">
        <h3>Manual Log Generation</h3>
        <form method="POST">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="test@example.com" required>
            </div>
            <div class="form-group">
                <label>IP Address:</label>
                <input type="text" name="ip" value="192.168.1.100" required>
            </div>
            <div class="form-group">
                <label>Success:</label>
                <select name="success">
                    <option value="1">Success</option>
                    <option value="0">Failed</option>
                </select>
            </div>
            <button type="submit">Generate Login Log</button>
        </form>
    </div>
    
    <div class="section">
        <h3>Security Alert Test Scenarios</h3>
        <p>Click these buttons to generate specific test scenarios for unusual IP detection:</p>
        
        <form method="POST" style="display: inline;">
            <input type="hidden" name="email" value="alice@company.com">
            <input type="hidden" name="ip" value="192.168.1.100">
            <button type="submit">Alice from Office IP</button>
        </form>
        
        <form method="POST" style="display: inline;">
            <input type="hidden" name="email" value="alice@company.com">
            <input type="hidden" name="ip" value="203.0.113.15">
            <button type="submit" class="test-button">Alice from Suspicious IP</button>
        </form>
        
        <form method="POST" style="display: inline;">
            <input type="hidden" name="email" value="bob@company.com">
            <input type="hidden" name="ip" value="10.0.0.50">
            <button type="submit">Bob from Home IP</button>
        </form>
    </div>
</body>
</html>