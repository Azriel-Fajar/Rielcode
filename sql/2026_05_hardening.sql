-- 2026_05_hardening.sql
-- Schema additions for rate limiting, audit logging, and chat_logs enrichment.
-- Idempotent where possible. Run against the rielcode database.

CREATE TABLE IF NOT EXISTS rate_limits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  bucket ENUM('hour','day','tokens_day') NOT NULL,
  window_start DATETIME NOT NULL,
  counter INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_ip_bucket_window (ip_address, bucket, window_start),
  KEY idx_ip (ip_address),
  KEY idx_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_code VARCHAR(32) NOT NULL,
  severity ENUM('info','warn','error') NOT NULL DEFAULT 'info',
  actor VARCHAR(120) NULL,
  ip_address VARCHAR(45) NULL,
  ref_table VARCHAR(64) NULL,
  ref_id INT UNSIGNED NULL,
  message VARCHAR(255) NULL,
  meta JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_event (event_code),
  KEY idx_created (created_at),
  KEY idx_actor (actor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- chat_logs enrichment. MySQL doesn't support IF NOT EXISTS on ADD COLUMN before 8.0.
-- These will error harmlessly on re-run; safe to ignore "Duplicate column" / "Duplicate key".
ALTER TABLE chat_logs ADD COLUMN ip_address    VARCHAR(45)  NULL AFTER bot_reply;
ALTER TABLE chat_logs ADD COLUMN input_tokens  INT UNSIGNED NULL;
ALTER TABLE chat_logs ADD COLUMN output_tokens INT UNSIGNED NULL;
ALTER TABLE chat_logs ADD KEY idx_chat_ip (ip_address);
