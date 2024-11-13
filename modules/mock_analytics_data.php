<?php
class MockAnalyticsData {
    private static $logs = [
        ['timestamp' => '2024-03-07 14:30:25', 'user_id' => 'admin@edoc.com', 'action' => 'LOGIN', 'level' => 'INFO'],
        ['timestamp' => '2024-03-07 14:35:12', 'user_id' => 'doctor@edoc.com', 'action' => 'UPDATE_SCHEDULE', 'level' => 'INFO'],
        ['timestamp' => '2024-03-07 14:40:55', 'user_id' => 'patient@email.com', 'action' => 'BOOK_APPOINTMENT', 'level' => 'INFO'],
        ['timestamp' => '2024-03-07 15:10:30', 'user_id' => 'system', 'action' => 'BACKUP_FAILED', 'level' => 'ERROR'],
        ['timestamp' => '2024-03-07 15:15:45', 'user_id' => 'admin@edoc.com', 'action' => 'ADD_DOCTOR', 'level' => 'INFO'],
    ];

    // Mock data for page views over the last 7 days
    public static function getPageViews() {
        return [
            ['date' => '2024-03-01', 'total_views' => 150, 'unique_users' => 45],
            ['date' => '2024-03-02', 'total_views' => 165, 'unique_users' => 52],
            ['date' => '2024-03-03', 'total_views' => 142, 'unique_users' => 48],
            ['date' => '2024-03-04', 'total_views' => 178, 'unique_users' => 55],
            ['date' => '2024-03-05', 'total_views' => 190, 'unique_users' => 60],
            ['date' => '2024-03-06', 'total_views' => 168, 'unique_users' => 50],
            ['date' => '2024-03-07', 'total_views' => 182, 'unique_users' => 58],
        ];
    }

    // Mock data for user type distribution
    public static function getUserDistribution() {
        return [
            ['type' => 'Patients', 'count' => 250],
            ['type' => 'Doctors', 'count' => 45],
            ['type' => 'Admins', 'count' => 5],
        ];
    }

    // Mock data for most visited pages
    public static function getPopularPages() {
        return [
            ['page' => '/appointment.php', 'views' => 450, 'avg_time' => '3:45'],
            ['page' => '/doctors.php', 'views' => 380, 'avg_time' => '2:30'],
            ['page' => '/schedule.php', 'views' => 320, 'avg_time' => '4:15'],
            ['page' => '/patient.php', 'views' => 290, 'avg_time' => '2:50'],
            ['page' => '/index.php', 'views' => 250, 'avg_time' => '1:45'],
        ];
    }

    // Mock data for system logs
    public static function getSystemLogs() {
        return self::$logs;
    }

    public static function clearLogs() {
        self::$logs = [];
        return true;
    }
} 