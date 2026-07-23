<?php
require_once 'config.php';
checkAdminAuth();

$institution = ['id' => 0, 'name' => '', 'logo' => '', 'description' => '', 'average_time' => 15];
$isEdit = false;

if (isset($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM institutions WHERE id = ?");
    $stmt->execute([$id]);
    $institution = $stmt->fetch();
    
    if (!$institution) {
        header('Location: institutions.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $average_time = (int)$_POST['average_time'] ?? 15;
    
    // Handle file upload
    $logo = $institution['logo'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/institutions/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
            $logo = $filename;
            
            // Delete old logo if it exists
            if ($isEdit && $institution['logo'] && file_exists($uploadDir . $institution['logo'])) {
                unlink($uploadDir . $institution['logo']);
            }
        }
    }
    
    if ($isEdit) {
        $stmt = $pdo->prepare("UPDATE institutions SET name = ?, logo = ?, description = ?, average_time = ? WHERE id = ?");
        $stmt->execute([$name, $logo, $description, $average_time, $institution['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO institutions (name, logo, description, average_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $logo, $description, $average_time]);
        $institution['id'] = $pdo->lastInsertId();
    }
    
    header('Location: institution_edit.php?id=' . $institution['id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Modifier' : 'Ajouter'; ?> Institution</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/stylesadmin.css">
    <link rel="stylesheet" href="css/ins.css">
    
</head>
<body class="admin-dashboard">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><?php echo $isEdit ? 'Modifier Institution' : 'Ajouter une Institution'; ?></h1>
            </div>
            
        </header>

        <section class="dashboard-overview">
            <div class="content-card">
                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="name">Nom de l'institution</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($institution['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Logo</label>
                            <?php if ($institution['logo']): ?>
                                <img src="uploads/institutions/<?php echo htmlspecialchars($institution['logo']); ?>" class="logo-preview" id="logoPreview">
                            <?php else: ?>
                                <img src="" class="logo-preview" id="logoPreview" style="display: none;">
                            <?php endif; ?>
                            
                            <input type="file" id="logo" name="logo" class="file-input" accept="image/*">
                            <label for="logo" class="file-label">
                                <i class="fas fa-upload"></i> Choisir un logo
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label for="average_time">Temps moyen d'attente (minutes)</label>
                            <input type="number" id="average_time" name="average_time" min="1" value="<?php echo $institution['average_time']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?php echo htmlspecialchars($institution['description']); ?></textarea>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                            <a href="institutions.php" class="btn btn-outline">
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
        
        // Logo preview
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('logoPreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>