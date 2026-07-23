<?php
session_start();

// Database connection using PDO (consistent with your reservation system)
$host = 'localhost';
$dbname = 'projet_integration';
$username = 'root';
$password = '';

try {
    // Check if we have a ticket number (from GET or SESSION)
    $ticket_number = $_GET['ticket'] ?? $_SESSION['ticket_number'] ?? null;
    
    if (!$ticket_number) {
        header("Location: index.html?annule=0&reason=no_ticket");
        exit();
    }

    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Option 1: Soft delete (recommended)
    $stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE ticket_number = :ticket_number");
    $stmt->bindParam(':ticket_number', $ticket_number);
    $stmt->execute();
    
    // Option 2: Hard delete (if you prefer)
    // $stmt = $conn->prepare("DELETE FROM reservations WHERE ticket_number = :ticket_number");
    // $stmt->bindParam(':ticket_number', $ticket_number);
    // $stmt->execute();

    // Clear session data
    unset($_SESSION['reservation_id']);
    unset($_SESSION['ticket_number']);

    // Redirect with success
    header("Location: index.html?annule=1&ticket=" . urlencode($ticket_number));
    exit();

} catch(PDOException $e) {
    // Log error and redirect
    error_log("Cancellation error: " . $e->getMessage());
    header("Location: index.html?annule=0&reason=error");
    exit();
}
?>