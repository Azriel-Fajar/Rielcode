-- Pay subdomain migration: order_payments + invoice_counter + orders status enum extension
-- Run against the Rielcode DB (shared with main site).

CREATE TABLE IF NOT EXISTS order_payments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_id INT NOT NULL,
  stage ENUM('deposit','final') NOT NULL,
  invoice_number VARCHAR(30) NOT NULL UNIQUE,
  amount DECIMAL(15,2) NOT NULL,
  currency ENUM('IDR','USD') NOT NULL DEFAULT 'IDR',
  status ENUM('draft','sent','paid','overdue') NOT NULL DEFAULT 'draft',
  due_date DATE NULL,
  notes TEXT NULL,
  sent_at TIMESTAMP NULL,
  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_order_stage (order_id, stage)
);

CREATE TABLE IF NOT EXISTS invoice_counter (
  year YEAR PRIMARY KEY,
  last_number INT NOT NULL DEFAULT 0
);

ALTER TABLE orders
  MODIFY status ENUM('Pending','On Progress','Staging Ready','Completed') NOT NULL DEFAULT 'Pending';
