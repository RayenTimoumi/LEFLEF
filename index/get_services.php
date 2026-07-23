<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'projet_integration';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $institution_id = $_GET['institution_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT id, name FROM services WHERE institution_id = :institution_id");
    $stmt->bindParam(':institution_id', $institution_id);
    $stmt->execute();
    
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($services);
    
} catch(PDOException $e) {
    echo json_encode([]);
}
?>