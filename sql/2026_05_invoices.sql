-- 2026_05_invoices.sql
-- Adds invoice-related columns to orders. Referenced by admin.php (case 'invoices'),
-- admin_invoice_edit.php (save/generate/send), and checkout/index.php (initial draft).
-- MySQL <8.0 has no IF NOT EXISTS on ADD COLUMN — re-run will error "Duplicate column"; safe to ignore.

ALTER TABLE orders ADD COLUMN invoice_status     ENUM('draft','sent','paid','void') NOT NULL DEFAULT 'draft' AFTER invoice_file;
ALTER TABLE orders ADD COLUMN invoice_amount     INT          NOT NULL DEFAULT 0      AFTER invoice_status;
ALTER TABLE orders ADD COLUMN invoice_currency   VARCHAR(8)   NOT NULL DEFAULT 'IDR'  AFTER invoice_amount;
ALTER TABLE orders ADD COLUMN invoice_due_date   DATE         NULL                    AFTER invoice_currency;
ALTER TABLE orders ADD COLUMN invoice_notes      TEXT         NULL                    AFTER invoice_due_date;
ALTER TABLE orders ADD COLUMN invoice_line_items JSON         NULL                    AFTER invoice_notes;
