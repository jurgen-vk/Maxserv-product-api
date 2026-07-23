CREATE TABLE IF NOT EXISTS imports (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  type         VARCHAR(50)  NOT NULL,
  status       VARCHAR(20)  NOT NULL DEFAULT 'pending',
  started_at   DATETIME     NOT NULL,
  completed_at DATETIME     NULL,
  count        INT          NOT NULL DEFAULT 0,
  INDEX idx_imports_status (status)
);
