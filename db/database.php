<?php
// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

//sDatabase configuration
$servername = 'localhost';  
$username = 'root';           
$password = '';              
$dbname = 'hospital_management'; 

// $servername = "localhost";
// $username = "papa.badu";
// $password = "password";
// $dbname = "webtech_fall2024_papa_badu";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_errno) {
    die("Connection failed: " . $conn->connect_error);
}

// Uncomment the next line to confirm connection
// echo "Connected successfully";
?>