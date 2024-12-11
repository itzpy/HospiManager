<?php
function logError($message, $level = 'ERROR') {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = '../logs/app_errors.log';
    
    $errorLog = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    file_put_contents($logFile, $errorLog, FILE_APPEND);
}

function displayError($message, $redirectUrl = null) {
    $_SESSION['error_message'] = $message;
    if ($redirectUrl) {
        header("Location: $redirectUrl");
    }
    exit();
}