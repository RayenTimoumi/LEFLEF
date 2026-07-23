<?php
require_once 'config.php';
checkAdminAuth();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
    header('Location: services.php');
    exit();
}

// Get all services with institution names
$services = $pdo->query("
    SELECT s.*, i.name as institution_name 
    FROM services s
    JOIN institutions i ON s.institution_id = i.id
    ORDER BY i.name, s.name
")->fetchAll();

// Get institutions for filter
$institutions = $pdo->query("SELECT id, name FROM institutions ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Services</title>
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
                <h1>Gestion des Services</h1>
            </div>
        </header>

        <section class="dashboard-overview">
            <div class="content-card">
                <div class="card-header">
                    <h3>Tous les Services</h3>
                    <a href="service_add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter
                    </a>
                </div>
                
                <div class="filter-bar">
                    <div class="filter-group">
                        <label for="institution">Institution:</label>
                        <select id="institution">
                            <option value="">Toutes</option>
                            <?php foreach ($institutions as $institution): ?>
                            <option value="<?php echo $institution['id']; ?>"><?php echo htmlspecialchars($institution['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Institution</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                <td><?php echo htmlspecialchars($service['institution_name']); ?></td>
                                <td><?php echo htmlspecialchars($service['description']); ?></td>
                                <td class="actions">
                                    <a href="service_edit.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="services.php?delete=<?php echo $service['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce service?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Sidebar toggle
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Filter by institution
        document.getElementById('institution').addEventListener('change', function() {
            const institutionId = this.value;
            if (institutionId) {
                window.location.href = `institution_services.php?id=${institutionId}`;
            }
        });
    </script>
</body>
</html>