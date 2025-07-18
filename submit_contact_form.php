<?php
// Ensure db.php is included for database connection
require_once 'db.php';

// Start a session if not already started (useful for messages)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set content type for JSON response
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get form data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Basic validation
    if (empty($name)) {
        $response['message'] = 'Name is required.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'A valid email address is required.';
    } elseif (empty($phone)) {
        $response['message'] = 'Phone number is required.';
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO contact (name, phone, email, message) VALUES (?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("ssss", $name, $phone, $email, $message);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Your message has been sent successfully!';
            } else {
                // Log the error for debugging, but don't expose sensitive info to users
                error_log("Database error in submit_contact.php: " . $stmt->error);
                $response['message'] = 'Failed to save contact. Please try again later.';
            }
            $stmt->close();
        } else {
            // Log the error
            error_log("Failed to prepare statement in submit_contact.php: " . $conn->error);
            $response['message'] = 'System error. Please try again later.';
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Close database connection
$conn->close();

echo json_encode($response);
exit;
?>