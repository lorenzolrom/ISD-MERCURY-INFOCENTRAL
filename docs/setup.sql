-- These tables are additions to existing FASTAPPS schema

CREATE TABLE Secret (
  secret CHAR(128) NOT NULL,
  name VARCHAR(64) NOT NULL UNIQUE,
  PRIMARY KEY (secret)
);

CREATE TABLE Secret_Permission (
  secret CHAR(128) NOT NULL,
  permission VARCHAR(30) NOT NULL,
  FOREIGN KEY (secret) REFERENCES Secret(secret) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (permission) REFERENCES Permission(code) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `History` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `action` ENUM('CREATE', 'MODIFY', 'DELETE') NOT NULL,
  `table` TEXT NOT NULL,
  `index` TEXT NOT NULL,
  `username` VARCHAR(64) NOT NULL,
  `time` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`username`) REFERENCES `User`(`username`) ON UPDATE CASCADE
);

CREATE TABLE `HistoryItem` (
  `history` INT(11) NOT NULL,
  `column` TEXT NOT NULL,
  `oldValue` TEXT,
  `newValue` TEXT,
  FOREIGN KEY (`history`) REFERENCES `History`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);