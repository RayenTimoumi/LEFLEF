<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'projet_integration';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all POST data
    $data = [
        'name' => $_POST['name'] ?? '',
        'cin' => $_POST['cin'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'societe_type' => $_POST['societe_type'] ?? 'prv',
        'institution_id' => $_POST['institution_id'] ?? 0,
        'service_id' => $_POST['service_id'] ?? 0,
        'reservation_date' => $_POST['reservation_date'] ?? '',
        'reservation_time' => $_POST['reservation_time'] ?? '',
        'notifications' => isset($_POST['notifications']) ? 1 : 0
    ];

    // Validate required fields
    $required = ['name', 'cin', 'phone', 'institution_id', 'service_id', 'reservation_date', 'reservation_time'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Le champ $field est requis");
        }
    }

    // Get institution and service names
    $stmt = $conn->prepare("SELECT name FROM institutions WHERE id = ?");
    $stmt->execute([$data['institution_id']]);
    $institution = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT name FROM services WHERE id = ?");
    $stmt->execute([$data['service_id']]);
    $service = $stmt->fetchColumn();

    // Generate ticket number
    $ticket_number = 'T-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Calculate queue position
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reservations 
                          WHERE institution_id = ? 
                          AND service_id = ?
                          AND reservation_date = ?
                          AND status = 'waiting'");
    $stmt->execute([$data['institution_id'], $data['service_id'], $data['reservation_date']]);
    $queue_position = $stmt->fetchColumn() + 1;

    // Insert reservation
    $stmt = $conn->prepare("INSERT INTO reservations 
                          (name, cin, phone, email, societe_type, institution_id, service_id,
                           institution, service, reservation_date, reservation_time,
                           ticket_number, queue_position, notifications, status)
                          VALUES 
                          (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'waiting')");

    $success = $stmt->execute([
        $data['name'],
        $data['cin'],
        $data['phone'],
        $data['email'],
        $data['societe_type'],
        $data['institution_id'],
        $data['service_id'],
        $institution,
        $service,
        $data['reservation_date'],
        $data['reservation_time'],
        $ticket_number,
        $queue_position,
        $data['notifications']
    ]);

    if ($success) {
        $reservation_id = $conn->lastInsertId();
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'reservation' => $reservation,
            'redirect_url' => 'queue.html?ticket=' . $reservation['ticket_number']
        ]);
    } else {
        throw new Exception("Échec de l'insertion dans la base de données");
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>