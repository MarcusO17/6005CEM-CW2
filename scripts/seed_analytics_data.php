<?php
require_once("../connection.php");
require_once("../modules/Logger.php");

class AnalyticsSeeder
{
  private $database;
  private $users = [];
  private $startDate;
  private $pages = [
    '/login.php' => 'Login Page',
    '/index.php' => 'Home Page',
    '/appointment.php' => 'Book Appointment',
    '/doctors.php' => 'Doctors List',
    '/schedule.php' => 'Doctor Schedule',
    '/patient/dashboard.php' => 'Patient Dashboard',
    '/doctor/dashboard.php' => 'Doctor Dashboard',
    '/admin/dashboard.php' => 'Admin Dashboard'
  ];

  public function __construct($database)
  {
    $this->database = $database;
    // Set a fixed start date for consistent data generation
    $this->startDate = new DateTime('30 days ago');
  }

  public function cleanup()
  {
    $tables = ['system_logs', 'page_views', 'user_events'];

    $this->database->begin_transaction();
    try {
      foreach ($tables as $table) {
        $this->database->query("TRUNCATE TABLE $table");
      }
      $this->database->commit();
      echo "Successfully cleaned up analytics tables.\n";
    } catch (Exception $e) {
      $this->database->rollback();
      throw new Exception("Failed to clean up tables: " . $e->getMessage());
    }
  }

  private function loadUsers()
  {
    $this->users = [];
    $result = $this->database->query("
            SELECT w.email, w.usertype, 
                   CASE 
                       WHEN w.usertype = 'p' THEN p.pname
                       WHEN w.usertype = 'd' THEN d.docname
                       ELSE 'Admin'
                   END as name
            FROM webuser w
            LEFT JOIN patient p ON w.email = p.pemail
            LEFT JOIN doctor d ON w.email = d.docemail
        ");

    while ($row = $result->fetch_assoc()) {
      $this->users[] = $row;
    }

    if (empty($this->users)) {
      throw new Exception("No users found in the database");
    }
  }

  private function generateTimestamp($dayOffset)
  {
    $date = clone $this->startDate;
    $date->modify("+$dayOffset days");
    return $date->format('Y-m-d H:i:s');
  }

  public function seedSystemLogs()
  {
    if (empty($this->users)) {
      $this->loadUsers();
    }

    $actions = [
      'AUTH' => [
        'LOGIN' => 40,      // 40% chance
        'LOGOUT' => 35,     // 35% chance
        'PASSWORD_CHANGE' => 15,
        'FAILED_LOGIN' => 10
      ],
      'APPOINTMENT' => [
        'BOOK' => 40,
        'CANCEL' => 20,
        'RESCHEDULE' => 20,
        'VIEW' => 20
      ],
      'PROFILE' => [
        'UPDATE' => 50,
        'VIEW' => 40,
        'DELETE' => 10
      ],
      'SYSTEM' => [
        'BACKUP' => 40,
        'MAINTENANCE' => 30,
        'CONFIG_CHANGE' => 30
      ]
    ];

    $levels = [
      Logger::LEVEL_INFO => 70,    // 70% chance
      Logger::LEVEL_WARNING => 20,  // 20% chance
      Logger::LEVEL_ERROR => 10     // 10% chance
    ];

    // Generate consistent number of logs per day
    for ($day = 0; $day < 30; $day++) {
      $logsPerDay = rand(10, 20); // Consistent range of logs per day

      for ($i = 0; $i < $logsPerDay; $i++) {
        $user = $this->users[array_rand($this->users)];
        $category = array_rand($actions);

        // Use weighted random selection for actions and levels
        $action = $this->weightedRandom($actions[$category]);
        $level = $this->weightedRandom($levels);

        $details = [
          'ip' => '192.168.' . rand(1, 255) . '.' . rand(1, 255),
          'browser' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
          'user_agent' => 'Chrome/91.0.4472.124'
        ];

        if ($category === 'AUTH' && $action === 'FAILED_LOGIN') {
          $details['attempt_count'] = rand(1, 3);
        }

        $timestamp = $this->generateTimestamp($day);

        $stmt = $this->database->prepare("
                    INSERT INTO system_logs 
                    (user_id, user_type, event_category, action, details, level, ip_address, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

        $detailsJson = json_encode($details);
        $stmt->bind_param(
          "sssssssss",
          $user['email'],
          $user['usertype'],
          $category,
          $action,
          $detailsJson,
          $level,
          $details['ip'],
          $details['browser'],
          $timestamp
        );

        $stmt->execute();
      }
    }
  }

  private function weightedRandom(array $weights)
  {
    $rand = rand(1, array_sum($weights));
    $current = 0;
    foreach ($weights as $key => $weight) {
      $current += $weight;
      if ($rand <= $current) {
        return $key;
      }
    }
    return array_key_first($weights); // Fallback
  }

  public function seedPageViews()
  {
    if (empty($this->users)) {
      $this->loadUsers();
    }

    // Generate consistent page views per day
    for ($day = 0; $day < 30; $day++) {
      $viewsPerDay = rand(20, 40); // Consistent range of views per day

      for ($i = 0; $i < $viewsPerDay; $i++) {
        $user = $this->users[array_rand($this->users)];
        $page = array_rand($this->pages);
        $pageTitle = $this->pages[$page];

        $timestamp = $this->generateTimestamp($day);

        $stmt = $this->database->prepare("
                    INSERT INTO page_views 
                    (page_url, page_title, user_id, user_type, session_id, ip_address, duration_seconds, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

        $sessionId = md5($user['email'] . $timestamp);
        $ip = '192.168.' . rand(1, 255) . '.' . rand(1, 255);
        $duration = rand(30, 300); // 30 seconds to 5 minutes

        $stmt->bind_param(
          "ssssssss",
          $page,
          $pageTitle,
          $user['email'],
          $user['usertype'],
          $sessionId,
          $ip,
          $duration,
          $timestamp
        );

        $stmt->execute();
      }
    }
  }

  public function seedUserEvents()
  {
    if (empty($this->users)) {
      $this->loadUsers();
    }

    $eventCategories = [
      'APPOINTMENT' => [
        'BOOK' => 40,
        'CANCEL' => 20,
        'RESCHEDULE' => 20,
        'VIEW' => 20
      ],
      'PROFILE' => [
        'UPDATE' => 60,
        'VIEW' => 40
      ],
      'SEARCH' => [
        'DOCTOR' => 40,
        'SPECIALTY' => 30,
        'SCHEDULE' => 30
      ],
      'INTERACTION' => [
        'CHAT' => 40,
        'REVIEW' => 30,
        'RATE' => 30
      ]
    ];

    // Generate consistent events per day
    for ($day = 0; $day < 30; $day++) {
      $eventsPerDay = rand(15, 30); // Consistent range of events per day

      for ($i = 0; $i < $eventsPerDay; $i++) {
        $user = $this->users[array_rand($this->users)];
        $category = array_rand($eventCategories);
        $action = $this->weightedRandom($eventCategories[$category]);

        $data = [
          'user_name' => $user['name'],
          'details' => $this->generateEventDetails($category, $action)
        ];

        $timestamp = $this->generateTimestamp($day);

        $stmt = $this->database->prepare("
                    INSERT INTO user_events 
                    (event_category, event_action, event_label, event_value, user_id, user_type, event_data, ip_address, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

        $label = strtoupper($category . '_' . $action);
        $value = rand(1, 100);
        $ip = '192.168.' . rand(1, 255) . '.' . rand(1, 255);
        $dataJson = json_encode($data);

        $stmt->bind_param(
          "sssisssss",
          $category,
          $action,
          $label,
          $value,
          $user['email'],
          $user['usertype'],
          $dataJson,
          $ip,
          $timestamp
        );

        $stmt->execute();
      }
    }
  }

  private function generateEventDetails($category, $action)
  {
    $details = [];
    switch ($category) {
      case 'APPOINTMENT':
        $details['doctor'] = 'Dr. ' . $this->users[array_rand($this->users)]['name'];
        $details['status'] = $action;
        $details['specialty'] = 'General Practice';
        break;
      case 'SEARCH':
        $details['query'] = $action === 'DOCTOR' ? 'cardiologist' : 'pediatrics';
        $details['results_count'] = rand(5, 20);
        break;
      case 'INTERACTION':
        $details['target_user'] = $this->users[array_rand($this->users)]['name'];
        if ($action === 'RATE') {
          $details['rating'] = rand(1, 5);
        }
        break;
    }
    return $details;
  }
}
