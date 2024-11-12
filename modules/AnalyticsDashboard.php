<?php
class AnalyticsDashboard
{
  private $database;
  private $user_type;

  public function __construct($database, $user_type)
  {
    $this->database = $database;
    $this->user_type = $user_type;
  }

  public function getAccessibleMetrics()
  {
    $stmt = $this->database->prepare("
            SELECT metric_name 
            FROM analytics_access 
            WHERE user_type = ? AND can_view = 1
        ");
    $stmt->bind_param("s", $this->user_type);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getUserMetrics()
  {
    if (!$this->canAccessMetric('user_metrics')) {
      return null;
    }

    // Example metric query
    return $this->database->query("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users
            FROM page_views
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ")->fetch_all(MYSQLI_ASSOC);
  }

  private function canAccessMetric($metric_name)
  {
    $stmt = $this->database->prepare("
            SELECT can_view 
            FROM analytics_access 
            WHERE user_type = ? AND metric_name = ?
        ");
    $stmt->bind_param("ss", $this->user_type, $metric_name);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result && $result['can_view'];
  }
}
