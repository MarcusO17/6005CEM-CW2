<?php
session_start();
include('../session_handler.php');
require_once("../connection.php");
require_once("../modules/Analytics.php");
require_once("../modules/Logger.php");

// Check authentication
if (!isset($_SESSION["user"]) || $_SESSION["usertype"] != 'a') {
  header("location: ../login.php");
  exit();
}

// Check if user is super admin
$admin_query = $database->prepare("SELECT isSuperAdmin FROM admin WHERE aemail=?");
$admin_query->bind_param("s", $_SESSION["user"]);
$admin_query->execute();
$admin_result = $admin_query->get_result();
$is_super_admin = $admin_result->fetch_assoc()['isSuperAdmin'] ?? false;
$admin_query->close();

if (!isset($is_super_admin) || !$is_super_admin) {
  header("location: index.php");
  exit();
}

// Initialize Analytics
$analytics = new Analytics($database, $_SESSION["user"], $_SESSION["usertype"]);

// Get time range from query params
$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : '7days';
$eventCategory = isset($_GET['category']) ? $_GET['category'] : null;
$logLevel = isset($_GET['level']) ? $_GET['level'] : null;

// Fetch analytics data
$pageViewStats = $analytics->getPageViewStats($timeframe);
$popularPages = $analytics->getPopularPages();
$userEvents = $analytics->getUserEventStats($eventCategory);
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$logsPerPage = 20;
$systemLogsData = $analytics->getSystemLogs(['level' => $logLevel], $currentPage, $logsPerPage);
$systemLogs = $systemLogsData['logs'];
$totalPages = $systemLogsData['pages'];

// Log this page view
$logger = Logger::getInstance($database);
$logger->setUser($_SESSION["user"], $_SESSION["usertype"])
  ->logPageView('/admin/analytics.php', 'Analytics Dashboard');

$page = 'analytics'; // Set current page for menu highlighting
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics Dashboard</title>
  <link rel="stylesheet" href="../css/animations.css">
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="stylesheet" href="../css/analytics.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <div class="container">
    <?php
    include("menu.php");
    ?>
    <div class="analytics-container">
      <div class="header-section">
        <div class="header-content">
          <h1 class="header-title">Analytics Dashboard</h1>
          <div class="header-filters">
            <select class="filter-select" onchange="updateTimeframe(this.value)">
              <option value="7days" <?php echo $timeframe === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
              <option value="30days" <?php echo $timeframe === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
            </select>
            <select class="filter-select" onchange="updateLogLevel(this.value)">
              <option value="">All Log Levels</option>
              <option value="INFO" <?php echo $logLevel === 'INFO' ? 'selected' : ''; ?>>Info</option>
              <option value="WARNING" <?php echo $logLevel === 'WARNING' ? 'selected' : ''; ?>>Warning</option>
              <option value="ERROR" <?php echo $logLevel === 'ERROR' ? 'selected' : ''; ?>>Error</option>
            </select>
          </div>
          <button class="export-button" onclick="exportAnalytics()">Export Data</button>
        </div>
      </div>

      <div class="analytics-content">
        <!-- Analytics Grid -->
        <div class="analytics-grid">
          <!-- Page Views Chart -->
          <div class="card">
            <h3>Page Views</h3>
            <?php if (empty($pageViewStats)): ?>
              <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <div class="empty-state-text">No page views data available</div>
                <div class="empty-state-subtext">Page views will appear here once tracked</div>
              </div>
            <?php else: ?>
              <div class="chart-container">
                <canvas id="pageViewsChart"></canvas>
              </div>
            <?php endif; ?>
          </div>

          <!-- Popular Pages -->
          <div class="card">
            <h3>Most Visited Pages</h3>
            <?php if (empty($popularPages)): ?>
              <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <div class="empty-state-text">No popular pages data</div>
                <div class="empty-state-subtext">Track more page visits to see trends</div>
              </div>
            <?php else: ?>
              <div class="chart-container">
                <canvas id="popularPagesChart"></canvas>
              </div>
            <?php endif; ?>
          </div>

          <!-- User Events -->
          <div class="card">
            <h3>User Events</h3>
            <?php if (empty($userEvents)): ?>
              <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <div class="empty-state-text">No user events recorded</div>
                <div class="empty-state-subtext">User interactions will show up here</div>
              </div>
            <?php else: ?>
              <div class="chart-container">
                <canvas id="userEventsChart"></canvas>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- System Logs -->
        <div class="card">
          <div class="logs-header">
            <h3>Recent System Logs</h3>
            <?php if ($totalPages > 1): ?>
              <div class="pagination">
                <?php if ($currentPage > 1): ?>
                  <a href="#" class="pagination-btn" onclick="changePage(<?php echo $currentPage - 1; ?>); return false;">Previous</a>
                <?php endif; ?>

                <div class="pagination-numbers">
                  <?php
                  $startPage = max(1, min($currentPage - 2, $totalPages - 4));
                  $endPage = min($totalPages, max(5, $currentPage + 2));

                  if ($startPage > 1): ?>
                    <a href="#" class="pagination-btn" onclick="changePage(1); return false;">1</a>
                    <?php if ($startPage > 2): ?>
                      <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="#"
                      class="pagination-btn <?php echo $i === $currentPage ? 'active' : ''; ?>"
                      onclick="changePage(<?php echo $i; ?>); return false;">
                      <?php echo $i; ?>
                    </a>
                  <?php endfor; ?>

                  <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                      <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                    <a href="#" class="pagination-btn" onclick="changePage(<?php echo $totalPages; ?>); return false;"><?php echo $totalPages; ?></a>
                  <?php endif; ?>
                </div>

                <?php if ($currentPage < $totalPages): ?>
                  <a href="#" class="pagination-btn" onclick="changePage(<?php echo $currentPage + 1; ?>); return false;">Next</a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

          <div id="logsContainer">
            <?php if (empty($systemLogs)): ?>
              <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <div class="empty-state-text">No system logs found</div>
                <div class="empty-state-subtext">System activities will be logged here</div>
              </div>
            <?php else: ?>
              <div class="table-container">
                <table class="logs-table">
                  <thead>
                    <tr>
                      <th>Timestamp</th>
                      <th>User</th>
                      <th>Category</th>
                      <th>Action</th>
                      <th>Level</th>
                    </tr>
                  </thead>
                  <tbody id="logsTableBody">
                    <?php foreach ($systemLogs as $log): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($log['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($log['event_category']); ?></td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td class="level-<?php echo strtolower($log['level']); ?>">
                          <?php echo htmlspecialchars($log['level']); ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>

            <div id="loadingState" class="loading-state" style="display: none;">
              <div class="loading-spinner"></div>
              <span>Loading logs...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Helper function to format dates
    function formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString();
    }

    // Update chart options for better responsiveness and appearance
    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 20,
            usePointStyle: true,
            font: {
              size: 12
            }
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          padding: 12,
          titleFont: {
            size: 14,
            weight: 'bold'
          },
          bodyFont: {
            size: 13
          },
          cornerRadius: 4
        }
      }
    };

    // Only initialize charts if data exists
    <?php if (!empty($pageViewStats)): ?>
      // Page Views Chart initialization
      new Chart(document.getElementById('pageViewsChart'), {
        type: 'line',
        data: {
          labels: <?php echo json_encode(array_column($pageViewStats, 'date')); ?>,
          datasets: [{
            label: 'Total Views',
            data: <?php echo json_encode(array_column($pageViewStats, 'total_views')); ?>,
            borderColor: '#4299E1',
            backgroundColor: 'rgba(66, 153, 225, 0.1)',
            tension: 0.4,
            fill: true
          }, {
            label: 'Unique Users',
            data: <?php echo json_encode(array_column($pageViewStats, 'unique_users')); ?>,
            borderColor: '#48BB78',
            backgroundColor: 'rgba(72, 187, 120, 0.1)',
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          ...chartOptions,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                display: true,
                color: 'rgba(0, 0, 0, 0.05)'
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          }
        }
      });
    <?php endif; ?>

    <?php if (!empty($popularPages)): ?>
      // Popular Pages Chart initialization
      new Chart(document.getElementById('popularPagesChart'), {
        type: 'bar',
        data: {
          labels: <?php echo json_encode(array_column($popularPages, 'page_url')); ?>,
          datasets: [{
            label: 'Page Views',
            data: <?php echo json_encode(array_column($popularPages, 'view_count')); ?>,
            backgroundColor: '#4299E1',
            borderRadius: 4
          }]
        },
        options: {
          ...chartOptions,
          indexAxis: 'y',
          scales: {
            x: {
              beginAtZero: true,
              grid: {
                display: false
              }
            },
            y: {
              grid: {
                display: false
              }
            }
          }
        }
      });
    <?php endif; ?>

    <?php if (!empty($userEvents)): ?>
      // Process data for the chart
      const eventData = <?php echo json_encode($userEvents); ?>;

      // Group and process the data
      const processedData = eventData.reduce((acc, event) => {
        const key = `${event.event_category}_${event.user_type}`;
        if (!acc[key]) {
          acc[key] = {
            category: event.event_category,
            userType: event.user_type,
            count: 0
          };
        }
        acc[key].count += parseInt(event.event_count);
        return acc;
      }, {});

      // Convert to arrays for Chart.js
      const chartData = Object.values(processedData);

      // Create datasets by user type
      const userTypeColors = {
        'p': '#4299E1', // Patient - Blue
        'd': '#48BB78', // Doctor - Green
        'a': '#ECC94B' // Admin - Yellow
      };

      new Chart(document.getElementById('userEventsChart'), {
        type: 'doughnut',
        data: {
          labels: chartData.map(d => {
            const userTypeLabel = d.userType === 'p' ? 'Patient' :
              (d.userType === 'd' ? 'Doctor' : 'Admin');
            return ` ${d.category} `;
          }),
          datasets: [{
            data: chartData.map(d => d.count),
            backgroundColor: chartData.map(d => userTypeColors[d.userType]),
            borderWidth: 2,
            borderColor: '#ffffff'
          }]
        },
        options: {
          ...chartOptions,
          cutout: '60%',
          plugins: {
            ...chartOptions.plugins,
            tooltip: {
              callbacks: {
                label: function(context) {
                  const data = context.raw;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((data * 100) / total).toFixed(1);
                  return `${context.label}: ${data} (${percentage}%)`;
                }
              }
            },
            legend: {
              position: 'bottom',
              labels: {
                generateLabels: function(chart) {
                  const data = chart.data;
                  if (data.labels.length && data.datasets.length) {
                    return data.labels.map((label, i) => {
                      const meta = chart.getDatasetMeta(0);
                      const style = meta.controller.getStyle(i);
                      const value = data.datasets[0].data[i];
                      const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                      const percentage = ((value * 100) / total).toFixed(1);

                      return {
                        text: `${label} - ${value} (${percentage}%)`,
                        fillStyle: style.backgroundColor,
                        strokeStyle: style.borderColor,
                        lineWidth: style.borderWidth,
                        hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                        index: i
                      };
                    });
                  }
                  return [];
                }
              }
            }
          }
        }
      });
    <?php endif; ?>

    // Filter functions
    function updateTimeframe(value) {
      const url = new URL(window.location.href);
      url.searchParams.set('timeframe', value);
      window.location.href = url.toString();
    }

    function updateLogLevel(value) {
      const url = new URL(window.location.href);
      if (value) {
        url.searchParams.set('level', value);
      } else {
        url.searchParams.delete('level');
      }
      window.location.href = url.toString();
    }

    function exportAnalytics() {
      // TODO: Implement export functionality
      alert('Export functionality coming soon!');
    }

    async function changePage(page) {
      // Show loading state
      const logsContainer = document.getElementById('logsContainer');
      const loadingState = document.getElementById('loadingState');
      const tableBody = document.getElementById('logsTableBody');

      tableBody.classList.add('table-fade');
      loadingState.style.display = 'flex';

      try {
        // Update URL without refreshing
        const url = new URL(window.location.href);
        url.searchParams.set('page', page);
        window.history.pushState({}, '', url.toString());

        // Fetch new data
        const response = await fetch(`get_logs.php?page=${page}&level=${url.searchParams.get('level') || ''}`);
        if (!response.ok) throw new Error('Failed to fetch logs');

        const data = await response.json();

        // Update table content
        tableBody.innerHTML = data.logs.map(log => `
          <tr>
            <td>${escapeHtml(log.created_at)}</td>
            <td>${escapeHtml(log.user_id)}</td>
            <td>${escapeHtml(log.event_category)}</td>
            <td>${escapeHtml(log.action)}</td>
            <td class="level-${log.level.toLowerCase()}">${escapeHtml(log.level)}</td>
          </tr>
        `).join('');

        // Update pagination UI
        const paginationContainer = document.querySelector('.pagination');
        if (paginationContainer) {
          let paginationHtml = '';

          // Previous button
          if (data.current_page > 1) {
            paginationHtml += `<a href="#" class="pagination-btn" onclick="changePage(${data.current_page - 1}); return false;">Previous</a>`;
          }

          paginationHtml += '<div class="pagination-numbers">';

          // Calculate page range
          const startPage = Math.max(1, Math.min(data.current_page - 2, data.pages - 4));
          const endPage = Math.min(data.pages, Math.max(5, data.current_page + 2));

          // First page and ellipsis
          if (startPage > 1) {
            paginationHtml += `<a href="#" class="pagination-btn" onclick="changePage(1); return false;">1</a>`;
            if (startPage > 2) {
              paginationHtml += '<span class="pagination-ellipsis">...</span>';
            }
          }

          // Page numbers
          for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `<a href="#" class="pagination-btn ${i === data.current_page ? 'active' : ''}" 
                                onclick="changePage(${i}); return false;">${i}</a>`;
          }

          // Last page and ellipsis
          if (endPage < data.pages) {
            if (endPage < data.pages - 1) {
              paginationHtml += '<span class="pagination-ellipsis">...</span>';
            }
            paginationHtml += `<a href="#" class="pagination-btn" onclick="changePage(${data.pages}); return false;">${data.pages}</a>`;
          }

          paginationHtml += '</div>';

          // Next button
          if (data.current_page < data.pages) {
            paginationHtml += `<a href="#" class="pagination-btn" onclick="changePage(${data.current_page + 1}); return false;">Next</a>`;
          }

          paginationContainer.innerHTML = paginationHtml;
        }

      } catch (error) {
        console.error('Error fetching logs:', error);
        // Show error message to user
        alert('Error loading logs. Please try again.');
      } finally {
        // Hide loading state
        tableBody.classList.remove('table-fade');
        loadingState.style.display = 'none';
      }
    }

    function escapeHtml(unsafe) {
      return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    // Add event listener for browser back/forward buttons
    window.addEventListener('popstate', async function(event) {
      const url = new URL(window.location.href);
      const page = parseInt(url.searchParams.get('page')) || 1;
      await changePage(page);
    });
  </script>

  <style>
    .table-container {
      position: relative;
      min-height: 400px;
    }

    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      margin-top: 20px;
      padding: 10px;
    }

    .pagination-numbers {
      display: flex;
      gap: 5px;
    }

    .pagination-btn {
      padding: 8px 12px;
      border: 1px solid #E2E8F0;
      border-radius: 6px;
      color: #4A5568;
      text-decoration: none;
      transition: all 0.2s;
    }

    .pagination-btn:hover {
      background: #EDF2F7;
    }

    .pagination-btn.active {
      background: #4299E1;
      color: white;
      border-color: #4299E1;
    }

    .loading-state {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.9);
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
    }

    .table-fade {
      opacity: 0.5;
      transition: opacity 0.3s;
    }
  </style>
</body>

</html>