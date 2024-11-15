<?php

class Logger
{
  private static $instance = null;
  private $database;
  private $user_id;
  private $user_type;

  const LEVEL_INFO = 'INFO';
  const LEVEL_WARNING = 'WARNING';
  const LEVEL_ERROR = 'ERROR';

  const CATEGORY_AUTH = 'AUTH';
  const CATEGORY_APPOINTMENT = 'APPOINTMENT';
  const CATEGORY_PROFILE = 'PROFILE';
  const CATEGORY_SYSTEM = 'SYSTEM';

  private function __construct($database)
  {
    $this->database = $database;
  }

  public static function getInstance($database)
  {
    if (self::$instance === null) {
      self::$instance = new self($database);
    }
    return self::$instance;
  }

  public function setUser($user_id, $user_type)
  {
    $this->user_id = $user_id;
    $this->user_type = $user_type;
    return $this;
  }

  public function log($category, $action, $details = null, $level = self::LEVEL_INFO)
  {
    $stmt = $this->database->prepare("
            INSERT INTO system_logs 
            (user_id, user_type, event_category, action, details, level, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $detailsJson = $details ? json_encode($details) : null;

    $effectiveUserType = $this->user_type === 'unknown' ? 'u' : $this->user_type;

    $stmt->bind_param(
      "ssssssss",
      $this->user_id,
      $effectiveUserType,
      $category,
      $action,
      $detailsJson,
      $level,
      $ip,
      $userAgent
    );

    return $stmt->execute();
  }

  public function logPageView($pageUrl, $pageTitle = null)
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

    $stmt->bind_param(
      "ssssssss",
      $pageUrl,
      $pageTitle,
      $this->user_id,
      $this->user_type,
      $sessionId,
      $referrer,
      $ip,
      $userAgent
    );

    return $stmt->execute();
  }

  public function logUserEvent($category, $action, $label = null, $value = null, $data = null)
  {
    $stmt = $this->database->prepare("
            INSERT INTO user_events 
            (event_category, event_action, event_label, event_value, user_id, user_type, event_data, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

    $ip = $_SERVER['REMOTE_ADDR'];
    $dataJson = $data ? json_encode($data) : null;

    $stmt->bind_param(
      "sssissss",
      $category,
      $action,
      $label,
      $value,
      $this->user_id,
      $this->user_type,
      $dataJson,
      $ip
    );

    return $stmt->execute();
  }
}
