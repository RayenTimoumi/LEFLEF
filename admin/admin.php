<?php
require_once 'config.php';
checkAdminAuth();

// Get stats data
$stats = [
    'institutions' => $pdo->query("SELECT COUNT(*) as count FROM institutions")->fetch()['count'],
    'reservations' => $pdo->query("SELECT COUNT(*) as count FROM reservations")->fetch()['count'],
    'avg_time' => $pdo->query("SELECT COALESCE(AVG(average_time), 0) as avg FROM institutions")->fetch()['avg']
];

// Get recent reservations (simplified without user join)
$recentReservations = $pdo->query("
    SELECT *, CONCAT('Client ', id) as client_name 
    FROM reservations 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Get institutions data
$institutions = $pdo->query("
    SELECT i.*, 
           COALESCE(i.average_time, 0) as average_time,
           (SELECT COUNT(*) FROM reservations WHERE service IN (SELECT name FROM services WHERE institution_id = i.id)) as reservations_count
    FROM institutions i
    LIMIT 4
")->fetchAll();

// Weekly data for chart
$weeklyData = $pdo->query("
    SELECT 
        DAYNAME(created_at) as day,
        COUNT(*) as reservations,
        AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_time
    FROM reservations
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DAYNAME(created_at)
    ORDER BY created_at
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Gestion des Files d'Attente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/stylesadmin.css">
</head>
<body class="admin-dashboard">
   <!-- Sidebar Navigation -->
   <aside class="sidebar">
        <div class="sidebar-header">
            <img src="images/logo.png" alt="Logo" class="admin-logo">
            <h2>Admin Dashboard</h2>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active">
                    <a href="admin.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="institutions.php">
                        <i class="fas fa-building"></i>
                        <span>Institutions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="services.php">
                        <i class="fas fa-list"></i>
                        <span>Services</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="reservations.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Réservations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <div class="admin-profile">
                <img src="images/admin.jpeg" alt="Admin">
                <div>
                    <h4><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h4>
                    <span><?php echo $_SESSION['admin_role'] === 'super_admin' ? 'Super Administrateur' : 'Administrateur'; ?></span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Déconnexion
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Tableau de bord</h1>
            </div>
            
        </header>
    
    <!-- Dashboard Overview -->
    <section class="dashboard-overview">
        <div class="stats-grid">
            <!-- Stat Card 1 -->
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-info">
                    <h3>Institutions</h3>
                    <span class="stat-number"><?php echo $stats['institutions']; ?></span>
                    <span class="stat-change positive">+2 cette semaine</span>
                </div>
            </div>
            
            
            <!-- Stat Card 3 -->
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Temps moyen</h3>
                    <span class="stat-number"><?php echo round($stats['avg_time']); ?> min</span>
                    <span class="stat-change negative">-3 min cette semaine</span>
                </div>
            </div>
            
            <!-- Stat Card 4 -->
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Réservations</h3>
                    <span class="stat-number"><?php echo $stats['reservations']; ?></span>
                    <span class="stat-change positive">+12% cette semaine</span>
                </div>
            </div>
        </div>

   <!-- Recent Activity and Performance Charts -->
   <div class="content-grid">
                <!-- Recent Activity -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Activité récente</h3>
                        <a href="reservations.php" class="view-all">Voir tout</a>
                    </div>
                    <div class="activity-list">
                        <?php foreach ($recentReservations as $reservation): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="activity-details">
                                <p><strong><?php echo htmlspecialchars($reservation['name']); ?></strong> a réservé pour <?php echo htmlspecialchars($reservation['service']); ?></p>
                                <span class="activity-time"><?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Performance Chart -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Performance du système</h3>
                        <div class="time-filter">
                            <button class="active">7j</button>
                            <button>30j</button>
                            <button>90j</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
            
           <!-- Institutions Overview -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Institutions</h3>
                    <a href="institutions.php" class="view-all">Gérer les institutions</a>
                </div>
                <div class="institutions-grid">
                    <?php foreach ($institutions as $institution): ?>
                    <div class="institution-card">
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
                        <h4><?php echo htmlspecialchars($institution['name']); ?></h4>
                        <div class="institution-stats">
                            <div>
                                <span class="stat-number"><?php echo $institution['reservations_count']; ?></span>
                                <span class="stat-label">réservations</span>
                            </div>
                            <div>
                                <span class="stat-number"><?php echo isset($institution['average_time']) ? $institution['average_time'] : 0; ?> min</span>
                                <span class="stat-label">temps moyen</span>
                            </div>
                        </div>
                        <a href="institution_edit.php?id=<?php echo $institution['id']; ?>" class="btn btn-outline">Gérer</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar on mobile
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
            
            // Initialize performance chart
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const performanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [
                        {
                            label: 'Réservations',
                            data: [120, 190, 170, 210, 230, 180, 90],
                            borderColor: '#27ae60',
                            backgroundColor: 'rgba(39, 174, 96, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Temps moyen (min)',
                            data: [15, 14, 16, 13, 12, 14, 16],
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
            
            // Time filter buttons
            const timeFilterButtons = document.querySelectorAll('.time-filter button');
            timeFilterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    timeFilterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Here you would update the chart data based on the selected time filter
                    // For demo purposes, we'll just log the selected filter
                    console.log('Time filter changed to:', this.textContent);
                });
            });
            
            // Simulate loading data
            setTimeout(() => {
                document.querySelectorAll('.stat-number').forEach(el => {
                    el.style.opacity = 1;
                });
            }, 300);
        });
    </script>
</body>
</html>