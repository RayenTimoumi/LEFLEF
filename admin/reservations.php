<?php
require_once 'config.php';
checkAdminAuth();

// Handle status change
if (isset($_GET['change_status'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    
    $validStatuses = ['waiting', 'processing', 'completed'];
    if (in_array($status, $validStatuses)) {
        $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?")->execute([$status, $id]);
    }
    
    header('Location: reservations.php');
    exit();
}

// Build the base query
$query = "SELECT r.* FROM reservations r WHERE 1=1";
$params = [];

// Apply status filter if set
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $query .= " AND r.status = ?";
    $params[] = $_GET['status'];
}

// Apply date filter if set
if (isset($_GET['date']) && !empty($_GET['date'])) {
    $query .= " AND r.reservation_date = ?";
    $params[] = $_GET['date'];
}

// Complete the query with ordering
$query .= " ORDER BY r.reservation_date DESC, r.reservation_time DESC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reservations = $stmt->fetchAll();

// Get the current filter values for the form
$currentStatus = $_GET['status'] ?? '';
$currentDate = $_GET['date'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/stylesadmin.css">
    <link rel="stylesheet" href="css/res.css">
</head>
<body class="admin-dashboard">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Gestion des Réservations</h1>
            </div>
        </header>

        <section class="dashboard-overview">
            <div class="content-card">
                <div class="card-header">
                    <h3>Toutes les Réservations</h3>
                    <div class="card-actions">
                        <a href="reservations.php" class="btn btn-reset">Réinitialiser les filtres</a>
                    </div>
                </div>
                
                <form method="get" action="reservations.php" class="filter-bar">
                    <div class="filter-group">
                        <label for="status">Statut:</label>
                        <select id="status" name="status">
                            <option value="">Tous</option>
                            <option value="waiting" <?= $currentStatus === 'waiting' ? 'selected' : '' ?>>En attente</option>
                            <option value="processing" <?= $currentStatus === 'processing' ? 'selected' : '' ?>>En cours</option>
                            <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Terminé</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" value="<?= htmlspecialchars($currentDate) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-filter">Filtrer</button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Nom</th>
                                <th>CIN</th>
                                <th>Téléphone</th>
                                <th>Service</th>
                                <th>Date/Heure</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservations)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Aucune réservation trouvée</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td><?= htmlspecialchars($reservation['ticket_number']) ?></td>
                                    <td><?= htmlspecialchars($reservation['name']) ?></td>
                                    <td><?= htmlspecialchars($reservation['cin']) ?></td>
                                    <td><?= htmlspecialchars($reservation['phone']) ?></td>
                                    <td><?= htmlspecialchars($reservation['service']) ?></td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?>
                                        à <?= date('H:i', strtotime($reservation['reservation_time'])) ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $reservation['status'] ?>">
                                            <?= $reservation['status'] ?>
                                        </span>
                                    </td>
                                    <td class="status-actions">
                                        <a href="reservations.php?change_status=1&id=<?= $reservation['id'] ?>&status=waiting" class="status-btn btn-waiting" title="Marquer comme en attente">
                                            <i class="fas fa-clock"></i>
                                        </a>
                                        <a href="reservations.php?change_status=1&id=<?= $reservation['id'] ?>&status=processing" class="status-btn btn-processing" title="Marquer comme en cours">
                                            <i class="fas fa-spinner"></i>
                                        </a>
                                        <a href="reservations.php?change_status=1&id=<?= $reservation['id'] ?>&status=completed" class="status-btn btn-completed" title="Marquer comme terminé">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
    </script>
</body>
</html>