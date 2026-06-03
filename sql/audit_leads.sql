-- Free Website Audit Tool — lead capture + score (Phase 1-2)
CREATE TABLE IF NOT EXISTS audit_leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  audit_id VARCHAR(36) NOT NULL UNIQUE,
  url VARCHAR(500) NOT NULL,
  email VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','complete') DEFAULT 'pending',
  score TINYINT NULL,
  checks_json TEXT NULL,
  load_time_ms INT NULL,
  INDEX idx_audit_id (audit_id),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
