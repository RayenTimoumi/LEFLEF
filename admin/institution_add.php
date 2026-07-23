<?php
require_once 'config.php';
checkAdminAuth();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $average_time = $_POST['average_time'] ?? 15;

    // Handle file upload
    $logo = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'images/';
        $uploadFile = $uploadDir . basename($_FILES['logo']['name']);
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['logo']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadFile)) {
                $logo = $uploadFile;
            }
        }
    }

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO institutions (name, logo, description, average_time) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$name, $logo, $description, $average_time]);

    // Redirect to institutions list
    header('Location: institutions.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Institution</title>
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
                <h1>Ajouter une Institution</h1>
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
                <form action="institution_add.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nom de l'institution *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="logo">Logo</label>
                        <div class="file-upload">
                            <input type="file" id="logo" name="logo" accept="image/*">
                            <img id="logo-preview" class="file-upload-preview" src="#" alt="Preview">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="average_time">Temps moyen d'attente (minutes) *</label>
                        <input type="number" id="average_time" name="average_time" value="15" min="1" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-submit">Enregistrer</button>
                        <a href="institutions.php" class="btn-cancel">Annuler</a>
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

        // Logo preview
        document.getElementById('logo').addEventListener('change', function(e) {
            const preview = document.getElementById('logo-preview');
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>