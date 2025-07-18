<?php
require_once 'db.php';
require_once 'config.php';
require_once 'auth.php';

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT id, name, phone, email, message FROM contact WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $contact = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $contact]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contact not found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid contact ID.']);
}

$conn->close();
exit;
?>