<?php

require_once __DIR__ . '/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('login.html', ['error' => 'invalid_method']);
    }

    $email = trim((string) ($_POST['email'] ?? ''));
    $mot_de_passe = (string) ($_POST['password'] ?? '');

    if ($email === '' || $mot_de_passe === '') {
        redirect('login.html', ['error' => 'missing_fields']);
    }

    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT id, email, nom, mot_de_passe FROM utilisateurs WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $utilisateur = $stmt->fetch();

    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        session_regenerate_id(true);
        $_SESSION['utilisateur_id'] = (int) $utilisateur['id'];
        $_SESSION['email'] = $utilisateur['email'];
        $_SESSION['nom'] = $utilisateur['nom'];

        redirect('reservation.html');
    }

    redirect('login.html', ['error' => 'invalid_credentials']);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    redirect('login.html', ['error' => 'database_error']);
}
