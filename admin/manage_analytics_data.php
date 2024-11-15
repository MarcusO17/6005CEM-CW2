<?php
session_start();
include('../session_handler.php');
require_once("../connection.php");
require_once("../modules/Logger.php");
require_once("../scripts/seed_analytics_data.php");

// Check authentication and super admin status
if (!isset($_SESSION["user"]) || $_SESSION["usertype"] != 'a') {
  header("location: ../login.php");
  exit();
}

$admin_query = $database->prepare("SELECT isSuperAdmin FROM admin WHERE aemail=?");
$admin_query->bind_param("s", $_SESSION["user"]);
$admin_query->execute();
$admin_result = $admin_query->get_result();
$is_super_admin = $admin_result->fetch_assoc()['isSuperAdmin'] ?? false;
$admin_query->close();

if (!$is_super_admin) {
  header("location: index.php");
  exit();
}

$message = '';
$messageType = '';

// Only process actions when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $seeder = new AnalyticsSeeder($database);

    if (isset($_POST['action'])) {
      switch ($_POST['action']) {
        case 'seed':
          $seeder->cleanup(); // Clean first
          $seeder->seedSystemLogs();
          $seeder->seedPageViews();
          $seeder->seedUserEvents();
          $message = "Analytics data seeded successfully!";
          $messageType = 'success';
          break;

        case 'cleanup':
          $seeder->cleanup();
          $message = "Analytics data cleaned up successfully!";
          $messageType = 'success';
          break;
      }
    }
  } catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
    $messageType = 'error';
  }
}

// Get current data counts
$counts = [
  'system_logs' => $database->query("SELECT COUNT(*) as count FROM system_logs")->fetch_assoc()['count'],
  'page_views' => $database->query("SELECT COUNT(*) as count FROM page_views")->fetch_assoc()['count'],
  'user_events' => $database->query("SELECT COUNT(*) as count FROM user_events")->fetch_assoc()['count']
];

// Set the current page for menu highlighting
$page = 'manage_analytics_data';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Analytics Data</title>
  <link rel="stylesheet" href="../css/animations.css">
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/analytics.css">
  <style>
    .data-management {
      max-width: 800px;
      margin: 0 auto;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      text-align: center;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 600;
      color: #2D3748;
      margin: 0.5rem 0;
    }

    .stat-label {
      color: #718096;
      font-size: 0.9rem;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
    }

    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
      flex: 1;
    }

    .btn-seed {
      background: #48BB78;
      color: white;
    }

    .btn-seed:hover {
      background: #38A169;
    }

    .btn-cleanup {
      background: #F56565;
      color: white;
    }

    .btn-cleanup:hover {
      background: #E53E3E;
    }

    .message {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
    }

    .message-success {
      background: #C6F6D5;
      color: #2F855A;
    }

    .message-error {
      background: #FED7D7;
      color: #C53030;
    }

    .warning-text {
      color: #718096;
      font-size: 0.9rem;
      margin-top: 1rem;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="container">
    <?php
    include("menu.php");
    ?>
    <div class="analytics-container">
      <div class="header-section">
        <h1 class="header-title">Manage Analytics Data</h1>
      </div>

      <div class="data-management">
        <?php if ($message): ?>
          <div class="message message-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>

        <div class="card">
          <h3>Current Data Statistics</h3>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-value"><?php echo number_format($counts['system_logs']); ?></div>
              <div class="stat-label">System Logs</div>
            </div>
            <div class="stat-card">
              <div class="stat-value"><?php echo number_format($counts['page_views']); ?></div>
              <div class="stat-label">Page Views</div>
            </div>
            <div class="stat-card">
              <div class="stat-value"><?php echo number_format($counts['user_events']); ?></div>
              <div class="stat-label">User Events</div>
            </div>
          </div>

          <div class="action-buttons">
            <form method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to seed the database with sample data? This will first clean up existing data.');">
              <input type="hidden" name="action" value="seed">
              <button type="submit" class="btn btn-seed">Seed Sample Data</button>
            </form>

            <form method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to clean up all analytics data? This cannot be undone.');">
              <input type="hidden" name="action" value="cleanup">
              <button type="submit" class="btn btn-cleanup">Clean Up Data</button>
            </form>
          </div>

          <p class="warning-text">
            Note: Seeding will first clean up existing data before generating new sample data.
            Clean Up will remove all analytics data from the system.
          </p>
        </div>
      </div>
    </div>
  </div>
</body>

</html>