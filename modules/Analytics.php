<?php

class Analytics
{
  private $database;
  private $user_id;
  private $user_type;
  private $access_level;

  public function __construct($database, $user_id = null, $user_type = null)
  {
    $this->database = $database;
    $this->user_id = $user_id;
    $this->user_type = $user_type;
    $this->access_level = $this->determineAccessLevel();
  }

  private function determineAccessLevel()
  {
    if (!$this->user_type) return 'NONE';

    $stmt = $this->database->prepare("
            SELECT metric_category, metric_name, view_level 
            FROM analytics_access 
            WHERE user_type = ?
        ");

    $stmt->bind_param("s", $this->user_type);
    $stmt->execute();
    $result = $stmt->get_result();

    $access = [];
    while ($row = $result->fetch_assoc()) {
      $access[$row['metric_category']][$row['metric_name']] = $row['view_level'];
    }

    return $access;
  }

  public function getSystemLogs($filters = [], $page = 1, $perPage = 20)
  {
    if (
      !isset($this->access_level['SYSTEM_LOGS']) ||
      $this->access_level['SYSTEM_LOGS']['ALL_LOGS'] === 'NONE'
    ) {
      return ['logs' => [], 'total' => 0];
    }

    // First, get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM system_logs WHERE 1=1";
    $params = [];
    $types = "";

    // Apply filters to count query
    if ($this->access_level['SYSTEM_LOGS']['ALL_LOGS'] !== 'DETAILED') {
      $countQuery .= " AND user_id = ?";
      $params[] = $this->user_id;
      $types .= "s";
    }

    if (!empty($filters['level'])) {
      $countQuery .= " AND level = ?";
      $params[] = $filters['level'];
      $types .= "s";
    }

    if (!empty($filters['category'])) {
      $countQuery .= " AND event_category = ?";
      $params[] = $filters['category'];
      $types .= "s";
    }

    $stmt = $this->database->prepare($countQuery);
    if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $totalRows = $stmt->get_result()->fetch_assoc()['total'];

    // Now get the actual logs with pagination
    $query = "SELECT * FROM system_logs WHERE 1=1";

    // Apply same filters to main query
    if ($this->access_level['SYSTEM_LOGS']['ALL_LOGS'] !== 'DETAILED') {
      $query .= " AND user_id = ?";
    }

    if (!empty($filters['level'])) {
      $query .= " AND level = ?";
    }

    if (!empty($filters['category'])) {
      $query .= " AND event_category = ?";
    }

    $offset = ($page - 1) * $perPage;
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

    // Add pagination parameters
    $params[] = $perPage;
    $types .= "i";
    $params[] = $offset;
    $types .= "i";

    $stmt = $this->database->prepare($query);
    if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return [
      'logs' => $logs,
      'total' => $totalRows,
      'pages' => ceil($totalRows / $perPage),
      'current_page' => $page
    ];
  }

  public function getPageViewStats($timeframe = '7days')
  {
    if (
      !isset($this->access_level['PAGE_VIEWS']) ||
      $this->access_level['PAGE_VIEWS']['ALL_VIEWS'] === 'NONE'
    ) {
      return [];
    }

    $query = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_views,
                COUNT(DISTINCT user_id) as unique_users,
                AVG(duration_seconds) as avg_duration
            FROM page_views
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ";

    if ($this->access_level['PAGE_VIEWS']['ALL_VIEWS'] !== 'DETAILED') {
      $query .= " AND user_id = ?";
    }

    $query .= " GROUP BY DATE(created_at) ORDER BY date DESC";

    $stmt = $this->database->prepare($query);

    $days = ($timeframe === '30days') ? 30 : 7;
    if ($this->access_level['PAGE_VIEWS']['ALL_VIEWS'] !== 'DETAILED') {
      $stmt->bind_param("is", $days, $this->user_id);
    } else {
      $stmt->bind_param("i", $days);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getPopularPages()
  {
    if (
      !isset($this->access_level['PAGE_VIEWS']) ||
      $this->access_level['PAGE_VIEWS']['ALL_VIEWS'] === 'NONE'
    ) {
      return [];
    }

    $query = "
            SELECT 
                page_url,
                page_title,
                COUNT(*) as view_count,
                COUNT(DISTINCT user_id) as unique_visitors,
                AVG(duration_seconds) as avg_duration
            FROM page_views
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ";

    if ($this->access_level['PAGE_VIEWS']['ALL_VIEWS'] !== 'DETAILED') {
      $query .= " AND user_id = ?";
    }

    $query .= " GROUP BY page_url, page_title ORDER BY view_count DESC LIMIT 10";

    $stmt = $this->database->prepare($query);

    if ($this->access_level['PAGE_VIEWS']['ALL_VIEWS'] !== 'DETAILED') {
      $stmt->bind_param("s", $this->user_id);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getUserEventStats($category = null)
  {
    if (
      !isset($this->access_level['USER_EVENTS']) ||
      $this->access_level['USER_EVENTS']['ALL_EVENTS'] === 'NONE'
    ) {
      return [];
    }

    $query = "
        SELECT 
            CONCAT(event_category, ' (', 
                CASE user_type 
                    WHEN 'p' THEN 'Patient'
                    WHEN 'd' THEN 'Doctor'
                    WHEN 'a' THEN 'Admin'
                END, 
            ')') as event_category,
            event_action,
            user_type,
            COUNT(*) as event_count
        FROM user_events
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ";

    $params = [];
    $types = "";

    if ($category) {
      $query .= " AND event_category = ?";
      $params[] = $category;
      $types .= "s";
    }

    if ($this->access_level['USER_EVENTS']['ALL_EVENTS'] !== 'DETAILED') {
      $query .= " AND user_id = ?";
      $params[] = $this->user_id;
      $types .= "s";
    }

    $query .= " GROUP BY event_category, event_action, user_type 
                ORDER BY user_type, event_category, event_count DESC";

    $stmt = $this->database->prepare($query);
    if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function logUserEvent($category, $action, $label = null, $value = null, $data = null)
  {
    // Get the current user's type if not already set
    if (!$this->user_type && isset($_SESSION['usertype'])) {
      $this->user_type = $_SESSION['usertype'];
    }

    $stmt = $this->database->prepare("
        INSERT INTO user_events 
        (event_category, event_action, event_label, event_value, user_id, user_type, event_data, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $ip = $_SERVER['REMOTE_ADDR'];
    $dataJson = $data ? json_encode($data) : null;

    // Ensure we're using the correct user type from the session or passed data
    $effectiveUserType = $data['user_type'] ?? $this->user_type;

    $stmt->bind_param(
      "sssissss",
      $category,
      $action,
      $label,
      $value,
      $this->user_id,
      $effectiveUserType,
      $dataJson,
      $ip
    );

    return $stmt->execute();
  }

  public function trackPageView($pageUrl, $pageTitle = null, $userId = null, $userType = null)
  {
    $stmt = $this->database->prepare("
      INSERT INTO page_views 
      (page_url, page_title, user_id, user_type, session_id, referrer_url, ip_address, user_agent, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $sessionId = session_id();
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Use provided user info or fall back to class properties
    $effectiveUserId = $userId ?? $this->user_id;
    $effectiveUserType = $userType ?? $this->user_type;

    $stmt->bind_param(
      "ssssssss",
      $pageUrl,
      $pageTitle,
      $effectiveUserId,
      $effectiveUserType,
      $sessionId,
      $referrer,
      $ip,
      $userAgent
    );

    return $stmt->execute();
  }
}
