<?php

require_once __DIR__ . '/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('signup.html', ['error' => 'invalid_method']);
    }

    $nom = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $date_naissance = trim((string) ($_POST['birthdate'] ?? ''));
    $adresse = trim((string) ($_POST['address'] ?? ''));
    $ville = trim((string) ($_POST['city'] ?? ''));
    $code_postal = trim((string) ($_POST['postal-code'] ?? ''));
    $mot_de_passe = (string) ($_POST['passwd'] ?? '');
    $confirmation = (string) ($_POST['confirm-password'] ?? '');

    if ($nom === '' || $email === '' || $date_naissance === '' || $adresse === '' || $ville === '' || $code_postal === '' || $mot_de_passe === '') {
        redirect('signup.html', ['error' => 'missing_fields']);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect('signup.html', ['error' => 'invalid_email']);
    }

    if ($mot_de_passe !== $confirmation) {
        redirect('signup.html', ['error' => 'password_mismatch']);
    }

    if (strlen($mot_de_passe) < 8 || !preg_match('/[A-Za-z]/', $mot_de_passe) || !preg_match('/\d/', $mot_de_passe)) {
        redirect('signup.html', ['error' => 'weak_password']);
    }

    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        redirect('signup.html', ['error' => 'email_exists']);
    }

    $passwordHash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO utilisateurs (email, nom, date_naissance, ville, code_postal, mot_de_passe, adresse) VALUES (:email, :nom, :date_naissance, :ville, :code_postal, :mot_de_passe, :adresse)');
    $stmt->execute([
        ':email' => $email,
        ':nom' => $nom,
        ':date_naissance' => $date_naissance,
        ':ville' => $ville,
        ':code_postal' => $code_postal,
        ':mot_de_passe' => $passwordHash,
        ':adresse' => $adresse,
    ]);

    redirect('login.html', ['success' => 'account_created']);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    redirect('signup.html', ['error' => 'database_error']);
}
