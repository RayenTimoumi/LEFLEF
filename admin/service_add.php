<?php
require_once 'config.php';
checkAdminAuth();

// Get all institutions for the dropdown
$institutions = $pdo->query("SELECT id, name FROM institutions ORDER BY name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $institution_id = $_POST['institution_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    // Validate inputs
    $errors = [];
    if (empty($institution_id)) {
        $errors[] = "L'institution est requise";
    }
    if (empty($name)) {
        $errors[] = "Le nom du service est requis";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO services (institution_id, name, description) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$institution_id, $name, $description]);

        // Redirect to services list
        header('Location: services.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/stylesadmin.css">
    <link rel="stylesheet" href="css/ser.css">
</head>
<body class="admin-dashboard">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Ajouter un Service</h1>
            </div>
            <div class="header-right">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
            </div>
        </header>

        <section class="dashboard-overview">
            <div class="form-container">
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="service_add.php" method="post">
                    <div class="form-group">
                        <label for="institution_id">Institution *</label>
                        <select id="institution_id" name="institution_id" required>
                            <option value="">Sélectionnez une institution</option>
                            <?php foreach ($institutions as $institution): ?>
                                <option value="<?= $institution['id'] ?>" <?= isset($_POST['institution_id']) && $_POST['institution_id'] == $institution['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($institution['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Nom du service *</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-submit">Enregistrer</button>
                        <a href="services.php" class="btn-cancel">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script>
        // Sidebar toggle
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>