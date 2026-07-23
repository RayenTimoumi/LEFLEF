<?php
require_once 'config.php';
checkAdminAuth();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM institutions WHERE id = ?")->execute([$id]);
    header('Location: institutions.php');
    exit();
}

// Get all institutions
$institutions = $pdo->query("
    SELECT i.*, 
           (SELECT COUNT(*) FROM services WHERE institution_id = i.id) as services_count,
           (SELECT COUNT(*) FROM reservations WHERE service IN (SELECT name FROM services WHERE institution_id = i.id)) as reservations_count
    FROM institutions i
    ORDER BY i.name
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Institutions</title>
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
                <h1>Gestion des Institutions</h1>
            </div>
          
        </header>

        <section class="dashboard-overview">
            <div class="content-card">
                <div class="card-header">
                    <h3>Toutes les Institutions</h3>
                    <a href="institution_add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Nom</th>
                                <th>Services</th>
                                <th>Réservations</th>
                                <th>Temps moyen</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($institutions as $institution): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($institution['logo']) && file_exists('uploads/institutions/' . $institution['logo'])): ?>
                                        <img src="uploads/institutions/<?php echo htmlspecialchars($institution['logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($institution['name']); ?>" 
                                             class="table-logo">
                                    <?php else: ?>
                                        <div class="default-logo">
                                            <?php echo strtoupper(substr($institution['name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($institution['name']); ?></td>
                                <td><?php echo $institution['services_count']; ?></td>
                                <td><?php echo $institution['reservations_count']; ?></td>
                                <td><?php echo $institution['average_time']; ?> min</td>
                                <td class="actions">
                                    <a href="institution_edit.php?id=<?php echo $institution['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="institution_services.php?id=<?php echo $institution['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-list"></i>
                                    </a>
                                    <a href="institutions.php?delete=<?php echo $institution['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette institution?');">
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sidebar toggle
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>