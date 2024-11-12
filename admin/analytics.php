<?php
require_once('../modules/Logger.php');
require_once('../modules/Analytics.php');
require_once('../modules/AnalyticsDashboard.php');

session_start();

// Initialize logger
$logger = new Logger($database, $_SESSION['user'], $_SESSION['usertype']);

// Initialize analytics
$analytics = new Analytics($database);

// Track page view
$analytics->trackPageView('admin/analytics', $_SESSION['user'], $_SESSION['usertype']);

// Initialize dashboard
$dashboard = new AnalyticsDashboard($database, $_SESSION['usertype']);

// Get accessible metrics
$accessible_metrics = $dashboard->getAccessibleMetrics();

// Log access
$logger->log(
  'VIEW_ANALYTICS',
  'User accessed analytics dashboard',
  Logger::LOG_LEVEL_INFO
);

// Get metrics if authorized
$user_metrics = $dashboard->getUserMetrics();
?>

<!-- Dashboard HTML -->
<div class="analytics-dashboard">
  <?php if ($user_metrics): ?>
    <div class="metric-card">
      <h3>User Activity (Last 30 Days)</h3>
      <!-- Add your visualization code here -->
    </div>
  <?php endif; ?>