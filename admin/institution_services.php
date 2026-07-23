<?php
require_once 'config.php';
checkAdminAuth();

// Get institution ID from URL
$institution_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get institution details
$stmt = $pdo->prepare("
    SELECT 
        id, 
        name, 
        description, 
        logo, 
        COALESCE(average_time, 0) as average_time 
    FROM institutions 
    WHERE id = ?
");
$stmt->execute([$institution_id]);
$institution = $stmt->fetch();

if (!$institution) {
    header('Location: institutions.php');
    exit();
}

// Handle service deletion
if (isset($_GET['delete_service'])) {
    $service_id = (int)$_GET['delete_service'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND institution_id = ?");
    $stmt->execute([$service_id, $institution_id]);
    header("Location: institution_services.php?id=$institution_id");
    exit();
}

// Get all services for this institution
$stmt = $pdo->prepare("
    SELECT s.*, 
           (SELECT COUNT(*) FROM reservations WHERE service = s.name) as reservations_count
    FROM services s
    WHERE s.institution_id = ?
    ORDER BY s.name
");
$stmt->execute([$institution_id]);
$services = $stmt->fetchAll();

// Handle form submission for adding new service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $average_time = (int)$_POST['average_time'];
    
    if (!empty($name)) {
        $stmt = $pdo->prepare("
            INSERT INTO services (institution_id, name, description, average_time)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$institution_id, $name, $description, $average_time]);
        
        header("Location: institution_services.php?id=$institution_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Services - <?php echo htmlspecialchars($institution['name']); ?></title>
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
                <h1>Services de <?php echo htmlspecialchars($institution['name']); ?></h1>
            </div>
            
        </header>

        <section class="dashboard-overview">
            <!-- Institution Info Card -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Informations sur l'institution</h3>
                    <a href="institution_edit.php?id=<?php echo $institution_id; ?>" class="btn btn-outline">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                </div>
                <div class="institution-info">
                    <div class="institution-logo">
                        <?php if (!empty($institution['logo']) && file_exists('uploads/institutions/' . $institution['logo'])): ?>
                            <img src="uploads/institutions/<?php echo htmlspecialchars($institution['logo']); ?>" 
                                 alt="<?php echo htmlspecialchars($institution['name']); ?>">
                        <?php else: ?>
                            <div class="default-logo">
                                <?php echo strtoupper(substr($institution['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="institution-details">
                        <h4><?php echo htmlspecialchars($institution['name']); ?></h4>
                        <p><?php echo htmlspecialchars($institution['description']); ?></p>
                        <div class="institution-stats">
                        <div>
                            <span class="stat-number"><?php echo count($services); ?></span>
                            <span class="stat-label">services</span>
                        </div>
                        <div>
                            <span class="stat-number"><?php echo $institution['average_time'] ?? 0; ?> min</span>
                            <span class="stat-label">temps moyen</span>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Services List -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Services disponibles</h3>
                    <button class="btn btn-primary" id="addServiceBtn">
                        <i class="fas fa-plus"></i> Ajouter un service
                    </button>
                </div>
                
                <!-- Add Service Form (initially hidden) -->
                <div class="add-service-form" id="addServiceForm" style="display: none;">
                    <form method="POST">
                        <div class="form-group">
                            <label for="name">Nom du service</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="average_time">Temps moyen (minutes)</label>
                            <input type="number" id="average_time" name="average_time" value="15" min="1" required>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="cancelAddService">Annuler</button>
                            <button type="submit" name="add_service" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Temps moyen</th>
                                <th>Réservations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                <td><?php echo htmlspecialchars($service['description']); ?></td>
                                <td><?php echo $service['average_time']; ?> min</td>
                                <td><?php echo $service['reservations_count']; ?></td>
                                <td class="actions">
                                    <a href="service_edit.php?id=<?php echo $service['id']; ?>&institution_id=<?php echo $institution_id; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="institution_services.php?id=<?php echo $institution_id; ?>&delete_service=<?php echo $service['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce service?');">
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

        // Show/hide add service form
        document.getElementById('addServiceBtn').addEventListener('click', function() {
            document.getElementById('addServiceForm').style.display = 'block';
            this.style.display = 'none';
        });

        document.getElementById('cancelAddService').addEventListener('click', function() {
            document.getElementById('addServiceForm').style.display = 'none';
            document.getElementById('addServiceBtn').style.display = 'block';
        });
    </script>
</body>
</html>