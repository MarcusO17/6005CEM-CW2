-- System Logs Table
CREATE TABLE
  IF NOT EXISTS system_logs (
    log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255),
    user_type CHAR(1),
    action VARCHAR(255) NOT NULL,
    details TEXT,
    level VARCHAR(20) NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_type),
    INDEX idx_action (action),
    INDEX idx_level (level),
    INDEX idx_created_at (created_at)
  );

-- Page Views Table
CREATE TABLE
  IF NOT EXISTS page_views (
    view_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(255) NOT NULL,
    user_id VARCHAR(255),
    user_type CHAR(1),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page (page),
    INDEX idx_user (user_id, user_type),
    INDEX idx_created_at (created_at)
  );

-- Events Table
CREATE TABLE
  IF NOT EXISTS events (
    event_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(255) NOT NULL,
    event_data JSON,
    user_id VARCHAR(255),
    user_type CHAR(1),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_user (user_id, user_type),
    INDEX idx_created_at (created_at)
  );

-- Analytics Dashboard Access Control
CREATE TABLE
  IF NOT EXISTS analytics_access (
    access_id INT AUTO_INCREMENT PRIMARY KEY,
    user_type CHAR(1) NOT NULL,
    metric_name VARCHAR(255) NOT NULL,
    can_view BOOLEAN DEFAULT FALSE,
    UNIQUE KEY unique_access (user_type, metric_name)
  );