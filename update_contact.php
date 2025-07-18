<?php
require_once 'db.php';
require_once 'config.php';
require_once 'auth.php';

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    // Basic validation
    if (empty($id) || !is_numeric($id) || empty($name)) {
        $_SESSION['message'] = "Invalid data provided for update.";
        $_SESSION['message_type'] = "danger";
        header('Location: admin.php?page=contacts');
        exit;
    }

    $stmt = $conn->prepare("UPDATE contact SET name = ?, phone = ?, email = ?, message = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $phone, $email, $message, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Contact updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating contact: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }

    $stmt->close();
    header('Location: admin.php?page=contacts');
    exit;
} else {
    header('Location: admin.php?page=contacts');
    exit;
}
?>