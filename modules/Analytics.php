<?php
class Analytics
{
  private $database;

  public function __construct($database)
  {
    $this->database = $database;
  }

  public function trackPageView($page, $user_id = null, $user_type = null)
  {
    $stmt = $this->database->prepare("
            INSERT INTO page_views 
            (page, user_id, user_type, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");

    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("ssss", $page, $user_id, $user_type, $ip);
    return $stmt->execute();
  }

  public function trackEvent($event_type, $event_data, $user_id = null, $user_type = null)
  {
    $stmt = $this->database->prepare("
            INSERT INTO events 
            (event_type, event_data, user_id, user_type, ip_address, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

    $ip = $_SERVER['REMOTE_ADDR'];
    $event_data_json = json_encode($event_data);
    $stmt->bind_param(
      "sssss",
      $event_type,
      $event_data_json,
      $user_id,
      $user_type,
      $ip
    );
    return $stmt->execute();
  }
}
