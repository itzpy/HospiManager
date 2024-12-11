<?php
function getUserById($user_id) {
    global $conn; // Assuming you have a database connection variable
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserAnalytics($user_Id) {
    global $conn; // Assuming you have a database connection variable
    $query = "SELECT COUNT(*) as total_recipes FROM recipes WHERE recipe_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}


function getAllUsers() {
    global $conn; // Use the global connection variable
    
    // Prepare the SQL query to fetch all users
    $query = "SELECT user_id, fname, lname, email, role, created_at FROM users";
    
    // Execute the query
    $result = mysqli_query($conn, $query);
    
    // Check for errors in the query
    if (!$result) {
        die("Database query failed: " . mysqli_error($conn));
    }

    // Initialize an array to hold user data
    $users = [];

    // Fetch each row as an associative array and add it to the users array
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = [
            'user_id' => $row['user_id'],
            'full_name' => $row['fname'] . ' ' . $row['lname'], // Combine first and last name
            'email' => $row['email'],
            'role' => $row['role'],
            'created_at' => $row['created_at']
        ];
    }

    // Return the array of users
    return $users;
}

function getPendingUserApprovals() {
    global $conn;
    // Implement the logic to fetch pending user approvals
    $query = "SELECT COUNT(*) as pending_count FROM users WHERE approval_status = 'pending'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['pending_count'];
}

// function getUserList($page = 1) {
//     global $conn;
//     // Fetch user list data here
// }



function getRecipeList($userId) {
    global $conn;
    // Fetch recipe list data here
}