<?php
require_once 'db.php';
require_once 'config.php';
require_once 'auth.php';

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM contact WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Contact deleted successfully!";
        $_SESSION['message_type'] = "success";
        echo json_encode(['success' => true]);
    } else {
        $_SESSION['message'] = "Error deleting contact: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    $_SESSION['message'] = "Invalid request or contact ID.";
    $_SESSION['message_type'] = "danger";
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
exit;
?>