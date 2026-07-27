CREATE TABLE IF NOT EXISTS media (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(50)      NOT NULL,
  entity_id   INT              NOT NULL,
  url         VARCHAR(500)     NOT NULL,
  media_type  VARCHAR(50)      NOT NULL DEFAULT 'image',
  sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  INDEX idx_media_entity (entity_type, entity_id)
);