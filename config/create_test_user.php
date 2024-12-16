<?php
require_once 'database.php';

// Create a test staff user
$firstName = 'Test';
$lastName = 'Staff';
$email = 'test_staff@hospital.com';
$password = 'password123';
$role = 'staff';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare SQL to insert new user
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);

// Execute the statement
if ($stmt->execute()) {
    echo "Test staff user created successfully!";
} else {
    echo "Error creating test user: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
