-- System Logs Table with improved structure
CREATE TABLE
  IF NOT EXISTS system_logs (
    log_id BIGINT NOT NULL AUTO_INCREMENT,
    user_id VARCHAR(255),
    user_type CHAR(1), -- 'p' for patient, 'd' for doctor, 'a' for admin
    event_category VARCHAR(50) NOT NULL, -- e.g., 'AUTH', 'APPOINTMENT', 'SCHEDULE'
    action VARCHAR(255) NOT NULL, -- e.g., 'LOGIN', 'BOOK_APPOINTMENT'
    details JSON, -- Flexible JSON storage for event details
    level VARCHAR(20) NOT NULL, -- 'INFO', 'WARNING', 'ERROR'
    ip_address VARCHAR(45),
    user_agent TEXT, -- Browser/client information
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    INDEX idx_user (user_id, user_type),
    INDEX idx_category (event_category),
    INDEX idx_action (action),
    INDEX idx_level (level),
    INDEX idx_created_at (created_at)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Page Views Table with enhanced tracking
CREATE TABLE
  IF NOT EXISTS page_views (
    view_id BIGINT NOT NULL AUTO_INCREMENT,
    page_url VARCHAR(255) NOT NULL,
    page_title VARCHAR(255),
    user_id VARCHAR(255),
    user_type CHAR(1),
    session_id VARCHAR(255),
    referrer_url TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    duration_seconds INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (view_id),
    INDEX idx_page (page_url),
    INDEX idx_user (user_id, user_type),
    INDEX idx_session (session_id),
    INDEX idx_created_at (created_at)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- User Events Table for specific interaction tracking
CREATE TABLE
  IF NOT EXISTS user_events (
    event_id BIGINT NOT NULL AUTO_INCREMENT,
    event_category VARCHAR(50) NOT NULL, -- e.g., 'APPOINTMENT', 'PROFILE'
    event_action VARCHAR(255) NOT NULL, -- e.g., 'BOOK', 'UPDATE'
    event_label VARCHAR(255), -- Optional label for the event
    event_value INT, -- Optional numeric value
    user_id VARCHAR(255),
    user_type CHAR(1),
    event_data JSON, -- Additional event-specific data
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id),
    INDEX idx_category_action (event_category, event_action),
    INDEX idx_user (user_id, user_type),
    INDEX idx_created_at (created_at)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Analytics Access Control with more granular permissions
CREATE TABLE
  IF NOT EXISTS analytics_access (
    access_id INT NOT NULL AUTO_INCREMENT,
    user_type CHAR(1) NOT NULL, -- 'p', 'd', 'a'
    metric_category VARCHAR(50) NOT NULL, -- e.g., 'SYSTEM_LOGS', 'PAGE_VIEWS'
    metric_name VARCHAR(255) NOT NULL, -- Specific metric within category
    can_view BOOLEAN DEFAULT FALSE,
    view_level ENUM ('NONE', 'SUMMARY', 'DETAILED') DEFAULT 'NONE',
    PRIMARY KEY (access_id),
    UNIQUE KEY unique_access (user_type, metric_category, metric_name)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Insert default analytics access permissions
INSERT INTO
  analytics_access (
    user_type,
    metric_category,
    metric_name,
    can_view,
    view_level
  )
VALUES
  -- Super Admin access
  ('a', 'SYSTEM_LOGS', 'ALL_LOGS', TRUE, 'DETAILED'),
  ('a', 'PAGE_VIEWS', 'ALL_VIEWS', TRUE, 'DETAILED'),
  (
    'a',
    'USER_EVENTS',
    'ALL_EVENTS',
    TRUE,
    'DETAILED'
  ),
  -- Doctor access
  ('d', 'SYSTEM_LOGS', 'OWN_LOGS', TRUE, 'DETAILED'),
  (
    'd',
    'PAGE_VIEWS',
    'APPOINTMENT_VIEWS',
    TRUE,
    'SUMMARY'
  ),
  (
    'd',
    'USER_EVENTS',
    'PATIENT_INTERACTIONS',
    TRUE,
    'SUMMARY'
  ),
  -- Patient access
  ('p', 'SYSTEM_LOGS', 'OWN_LOGS', TRUE, 'SUMMARY'),
  (
    'p',
    'USER_EVENTS',
    'OWN_APPOINTMENTS',
    TRUE,
    'DETAILED'
  );