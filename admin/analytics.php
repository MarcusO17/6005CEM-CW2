<?php
session_start();

if (isset($_SESSION["user"])) {
  if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
  }
} else {
  header("location: ../login.php");
}

include("../connection.php");
require_once("../modules/mock_analytics_data.php");

// Check if user is super admin
$admin_query = $database->prepare("SELECT isSuperAdmin FROM admin WHERE aemail=?");
$admin_query->bind_param("s", $_SESSION["user"]);
$admin_query->execute();
$admin_result = $admin_query->get_result();
$is_super_admin = $admin_result->fetch_assoc()['isSuperAdmin'] ?? false;

if (!$is_super_admin) {
  header("location: index.php");
  exit();
}

// Handle clear logs action
if (isset($_POST['clear_logs'])) {
    MockAnalyticsData::clearLogs();
    // Redirect to prevent form resubmission
    header("Location: analytics.php?cleared=1");
    exit();
}

// Get mock data
$pageViews = MockAnalyticsData::getPageViews();
$userTypes = MockAnalyticsData::getUserDistribution();
$popularPages = MockAnalyticsData::getPopularPages();
$systemLogs = MockAnalyticsData::getSystemLogs();

// Success message
$showClearedMessage = isset($_GET['cleared']) && $_GET['cleared'] == 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/animations.css">
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Analytics Dashboard</title>
  <style>
    .container {
      width: 100%;
      height: 100vh;
      display: flex;
    }

    .menu {
      width: 250px;
      flex-shrink: 0;
    }

    .analytics-container {
      flex-grow: 1;
      padding: 20px 30px;
      background: #f3f6ff;
      min-height: 100vh;
      overflow-y: auto;
    }

    .analytics-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 25px;
      margin-top: 20px;
    }

    .metric-card {
      background: white;
      border-radius: 8px;
      padding: 25px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .chart-container {
      position: relative;
      height: 300px;
      width: 100%;
      margin-top: 15px;
    }

    .logs-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    .logs-table th,
    .logs-table td {
      padding: 12px 20px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    .logs-table th {
      background: #f8f9fa;
      font-weight: 600;
      color: #444;
    }

    .logs-table tr:hover {
      background-color: #f5f5f5;
    }

    .level-info {
      color: #2196F3;
      font-weight: 500;
    }

    .level-error {
      color: #f44336;
      font-weight: 500;
    }

    h1 {
      color: #2196F3;
      margin-bottom: 30px;
      font-size: 28px;
    }

    h3 {
      color: #444;
      margin: 0;
      font-size: 18px;
    }

    @media (max-width: 1200px) {
      .analytics-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .analytics-grid {
        grid-template-columns: 1fr;
      }

      .menu {
        width: 200px;
      }
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.2s;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .alert {
        padding: 12px 20px;
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-dismiss {
        color: #155724;
        text-decoration: none;
        font-weight: bold;
        margin-left: 10px;
    }

    .logs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
  </style>
</head>

<body>
  <div class="container">
    <?php include("menu.php"); ?>
    <div class="analytics-container">
      <h1>Analytics Dashboard</h1>

      <?php if ($showClearedMessage): ?>
      <div class="alert alert-success">
          System logs have been cleared successfully
          <a href="analytics.php" class="alert-dismiss">×</a>
      </div>
      <?php endif; ?>

      <div class="analytics-grid">
        <!-- Page Views Chart -->
        <div class="metric-card">
          <h3>Page Views (Last 7 Days)</h3>
          <div class="chart-container">
            <canvas id="pageViewsChart"></canvas>
          </div>
        </div>

        <!-- User Distribution -->
        <div class="metric-card">
          <h3>User Distribution</h3>
          <div class="chart-container">
            <canvas id="userDistributionChart"></canvas>
          </div>
        </div>

        <!-- Popular Pages -->
        <div class="metric-card">
          <h3>Most Visited Pages</h3>
          <div class="chart-container">
            <canvas id="popularPagesChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Updated System Logs Table -->
      <div class="metric-card" style="margin-top: 25px;">
        <div class="logs-header">
          <h3>Recent System Logs</h3>
          <form method="POST" onsubmit="return confirm('Are you sure you want to clear all logs?');">
            <button type="submit" name="clear_logs" class="btn-danger">
              Clear Logs
            </button>
          </form>
        </div>
        <table class="logs-table">
          <thead>
            <tr>
              <th>Timestamp</th>
              <th>User</th>
              <th>Action</th>
              <th>Level</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($systemLogs)): ?>
              <tr>
                <td colspan="4" style="text-align: center; padding: 20px;">
                  No logs available
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($systemLogs as $log): ?>
                <tr>
                  <td><?php echo $log['timestamp']; ?></td>
                  <td><?php echo $log['user_id']; ?></td>
                  <td><?php echo $log['action']; ?></td>
                  <td class="level-<?php echo strtolower($log['level']); ?>">
                    <?php echo $log['level']; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    // Page Views Chart
    new Chart(document.getElementById('pageViewsChart'), {
      type: 'line',
      data: {
        labels: <?php echo json_encode(array_column($pageViews, 'date')); ?>,
        datasets: [{
          label: 'Total Views',
          data: <?php echo json_encode(array_column($pageViews, 'total_views')); ?>,
          borderColor: 'rgb(75, 192, 192)',
          tension: 0.1
        }, {
          label: 'Unique Users',
          data: <?php echo json_encode(array_column($pageViews, 'unique_users')); ?>,
          borderColor: 'rgb(255, 99, 132)',
          tension: 0.1
        }]
      }
    });

    // User Distribution Chart
    new Chart(document.getElementById('userDistributionChart'), {
      type: 'pie',
      data: {
        labels: <?php echo json_encode(array_column($userTypes, 'type')); ?>,
        datasets: [{
          data: <?php echo json_encode(array_column($userTypes, 'count')); ?>,
          backgroundColor: [
            'rgb(255, 99, 132)',
            'rgb(54, 162, 235)',
            'rgb(255, 205, 86)'
          ]
        }]
      }
    });

    // Popular Pages Chart
    new Chart(document.getElementById('popularPagesChart'), {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_column($popularPages, 'page')); ?>,
        datasets: [{
          label: 'Page Views',
          data: <?php echo json_encode(array_column($popularPages, 'views')); ?>,
          backgroundColor: 'rgb(54, 162, 235)'
        }]
      },
      options: {
        indexAxis: 'y'
      }
    });
  </script>
</body>

</html>