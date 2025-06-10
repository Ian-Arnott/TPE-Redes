<?php
// Simple PHP app to generate logs for ELK testing

session_start();

?>
<!DOCTYPE html>
<html>
<head>
    <title>ELK Test App</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; }
        button { padding: 10px 20px; margin: 10px; background: #007cba; color: white; border: none; border-radius: 5px; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>ELK Stack Test Application</h1>
    
    <h2>Generate Test Logs</h2>
    <form method="POST">
        <button name="action" value="normal">Normal Request</button>
        <button name="action" value="error">Trigger PHP Error (500)</button>
        <button name="action" value="http500">Return HTTP 500</button>
        <button name="action" value="http503">Return HTTP 503</button>
        <button name="action" value="bulk">Generate Bulk Logs</button>
    </form>

    <?php
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch($action) {
            case 'normal':
                echo "<div class='result'>✅ Normal request processed - Check access logs!</div>";
                break;

            case 'error':
                http_response_code(500);
                error_log("🚨 Triggered PHP Error 500 for ELK testing");
                trigger_error("Manual PHP ERROR triggered", E_USER_ERROR);
                echo "<div class='result'>❌ Error 500 simulated</div>";
                exit;

            case 'http500':
                http_response_code(500);
                error_log("🚨 Returned HTTP 500 response for ELK test");
                echo "<div class='result'>❌ Forced HTTP 500 error</div>";
                exit;

            case 'http503':
                http_response_code(503);
                error_log("🚨 Returned HTTP 503 response for ELK test");
                echo "<div class='result'>❌ Forced HTTP 503 error</div>";
                exit;

            case 'bulk':
                for($i = 1; $i <= 10; $i++) {
                    error_log("📄 Bulk log entry #$i - User simulation");
                }
                echo "<div class='result'>✅ Generated 10 bulk log entries</div>";
                break;
        }
    }
    ?>

    <h2>Current Info</h2>
    <div class='result'>
        <strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
        <strong>IP:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?><br>
        <strong>User Agent:</strong> <?php echo substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 100); ?>
    </div>

    <h2>Test Links</h2>
    <p>Click these to generate different log entries:</p>
    <a href="?page=about">About Page (404)</a> |
    <a href="?search=test">Search</a> |
    <a href="?user=<?php echo rand(1,100); ?>">User Profile</a>
</body>
</html>
