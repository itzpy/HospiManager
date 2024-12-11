<?php
// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$servername = 'localhost';  
$username = 'root';           
$password = '';              
$dbname = 'hospital_management'; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

// Uncomment the next line to confirm connection
// echo "Connected successfully";
?>