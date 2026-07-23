<?php
require_once 'config.php';
checkAdminAuth();

// Get current settings from database
$settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $systemName = trim($_POST['system_name'] ?? '');
    $openTime = trim($_POST['open_time'] ?? '');
    $closeTime = trim($_POST['close_time'] ?? '');
    $slotDuration = intval($_POST['slot_duration'] ?? 15);
    
    // Validate time format
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $openTime) || 
        !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $closeTime)) {
        $_SESSION['error'] = "Format d'heure invalide (HH:MM requis)";
        header("Location: settings.php");
        exit();
    }
    
    // Validate slot duration
    if ($slotDuration < 5 || $slotDuration > 60) {
        $_SESSION['error'] = "Durée des créneaux doit être entre 5 et 60 minutes";
        header("Location: settings.php");
        exit();
    }
    
    // Update settings in transaction
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        $stmt->execute(['system_name', $systemName]);
        $stmt->execute(['open_time', $openTime]);
        $stmt->execute(['close_time', $closeTime]);
        $stmt->execute(['slot_duration', $slotDuration]);
        
        $pdo->commit();
        $_SESSION['success'] = "Paramètres mis à jour avec succès";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de la mise à jour: " . $e->getMessage();
    }
    
    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres du Système</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/stylesadmin.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body class="admin-dashboard">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Paramètres du Système</h1>
            </div>
        </header>

        <section class="dashboard-overview">
            <!-- Display success/error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']); ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="card-header">
                    <h3>Configuration</h3>
                </div>
                
                <div class="form-container">
                    <form method="POST">
                        <div class="form-group">
                            <label for="system_name">Nom du système</label>
                            <input type="text" id="system_name" name="system_name" 
                                   value="<?= htmlspecialchars($settings['system_name'] ?? 'Gestion des Files d\'Attente') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="open_time">Heure d'ouverture</label>
                            <input type="time" id="open_time" name="open_time" 
                                   value="<?= htmlspecialchars($settings['open_time'] ?? '08:00') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="close_time">Heure de fermeture</label>
                            <input type="time" id="close_time" name="close_time" 
                                   value="<?= htmlspecialchars($settings['close_time'] ?? '17:00') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="slot_duration">Durée des créneaux (minutes)</label>
                            <input type="number" id="slot_duration" name="slot_duration" 
                                   value="<?= htmlspecialchars($settings['slot_duration'] ?? 15) ?>" 
                                   min="5" max="60" required>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
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

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const openTime = document.getElementById('open_time').value;
            const closeTime = document.getElementById('close_time').value;
            
            if (openTime >= closeTime) {
                alert('L\'heure d\'ouverture doit être avant l\'heure de fermeture');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>