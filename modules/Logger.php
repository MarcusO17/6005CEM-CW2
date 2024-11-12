<?php
class Logger
{
  private $database;
  private $user_id;
  private $user_type;

  const LOG_LEVEL_INFO = 'INFO';
  const LOG_LEVEL_WARNING = 'WARNING';
  const LOG_LEVEL_ERROR = 'ERROR';

  public function __construct($database, $user_id = null, $user_type = null)
  {
    $this->database = $database;
    $this->user_id = $user_id;
    $this->user_type = $user_type;
  }

  public function log($action, $details, $level = self::LOG_LEVEL_INFO)
  {
    $stmt = $this->database->prepare("
            INSERT INTO system_logs 
            (user_id, user_type, action, details, level, ip_address, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param(
      "ssssss",
      $this->user_id,
      $this->user_type,
      $action,
      $details,
      $level,
      $ip
    );

    return $stmt->execute();
  }
}
