<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'projet_integration';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;projet_integration", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (isset($_GET['ticket'])) {
        $ticket = $_GET['ticket'];
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE ticket_number = :ticket");
        $stmt->bindParam(':ticket', $ticket);
    } elseif (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = :id");
        $stmt->bindParam(':id', $id);
    } else {
        throw new Exception("No identifier provided");
    }
    
    $stmt->execute();
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reservation) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations 
                              WHERE service = :service 
                              AND reservation_date = :reservation_date
                              AND status = 'waiting'
                              AND id <= :id");
        $stmt->bindParam(':service', $reservation['service']);
        $stmt->bindParam(':reservation_date', $reservation['reservation_date']);
        $stmt->bindParam(':id', $reservation['id']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] != $reservation['queue_position']) {
            $updateStmt = $conn->prepare("UPDATE reservations SET queue_position = :position WHERE id = :id");
            $updateStmt->bindParam(':position', $result['count']);
            $updateStmt->bindParam(':id', $reservation['id']);
            $updateStmt->execute();
            $reservation['queue_position'] = $result['count'];
        }
        
        echo json_encode([
            'success' => true,
            'reservation' => $reservation
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Reservation not found'
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>