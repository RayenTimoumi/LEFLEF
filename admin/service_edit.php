<?php
require_once 'config.php';
checkAdminAuth();

$service = ['id' => 0, 'institution_id' => '', 'name' => '', 'description' => ''];
$isEdit = false;

if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    
    if (!$service) {
        header('Location: services.php');
        exit();
    }
}

// Get institutions for dropdown
$institutions = $pdo->query("SELECT id, name FROM institutions ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $institution_id = (int)$_POST['institution_id'];
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if ($isEdit) {
        $stmt = $pdo->prepare("UPDATE services SET institution_id = ?, name = ?, description = ? WHERE id = ?");
        $stmt->execute([$institution_id, $name, $description, $service['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (institution_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$institution_id, $name, $description]);
        $service['id'] = $pdo->lastInsertId();
    }
    
    header('Location: service_edit.php?id=' . $service['id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Modifier' : 'Ajouter'; ?> Service</title>
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
                <h1><?php echo $isEdit ? 'Modifier Service' : 'Ajouter un Service'; ?></h1>
            </div>
          
        </header>

        <section class="dashboard-overview">
            <div class="content-card">
                <div class="form-container">
                    <form method="POST">
                        <div class="form-group">
                            <label for="institution_id">Institution</label>
                            <select id="institution_id" name="institution_id" required>
                                <option value="">Sélectionner une institution</option>
                                <?php foreach ($institutions as $institution): ?>
                                <option value="<?php echo $institution['id']; ?>" <?php echo $service['institution_id'] == $institution['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($institution['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Nom du service</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($service['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?php echo htmlspecialchars($service['description']); ?></textarea>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                            <a href="services.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
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