CREATE TABLE IF NOT EXISTS imports (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  type             VARCHAR(50)  NOT NULL,
  status           VARCHAR(20)  NOT NULL DEFAULT 'pending',
  started_at       DATETIME     NOT NULL,
  ended_at         DATETIME     NULL,
  duration_seconds INT GENERATED ALWAYS AS (TIMESTAMPDIFF(SECOND, started_at, ended_at)) STORED,
  processed        INT          NOT NULL DEFAULT 0,
  total            INT          NOT NULL DEFAULT 0,
  INDEX idx_imports_status (status),
  INDEX idx_imports_type (type),
  INDEX idx_imports_started_at (started_at),
  INDEX idx_imports_ended_at (ended_at),
  INDEX idx_imports_duration_seconds (duration_seconds)
);
