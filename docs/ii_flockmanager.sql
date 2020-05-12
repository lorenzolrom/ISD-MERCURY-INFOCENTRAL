CREATE TABLE `FLM_System` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `stamp` VARCHAR(32) NOT NULL UNIQUE,
  `keyway` TEXT NOT NULL DEFAULT '',
  `master` int(11) UNIQUE DEFAULT NULL,
  `control` int(11) UNIQUE DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `FLM_Key` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `system` int(11) UNSIGNED NOT NULL,
  `stamp` VARCHAR(32) NOT NULL,
  `bitting` TEXT NOT NULL DEFAULT '',
  CONSTRAINT `FLM_Key_ibfk_1` FOREIGN KEY (`system`) REFERENCES `FLM_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`system`, `stamp`)
);

CREATE TABLE `FLM_Core` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `system` int(11) UNSIGNED NOT NULL,
  `stamp` VARCHAR(32) NOT NULL,
  `sequence` TEXT NOT NULL DEFAULT '',
  CONSTRAINT `FLM_Core_ibfk_1` FOREIGN KEY (`system`) REFERENCES `FLM_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`system`, `stamp`)
);

ALTER TABLE `FLM_System` ADD FOREIGN KEY (`master`) REFERENCES `FLM_Key`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE `FLM_System` ADD FOREIGN KEY (`control`) REFERENCES `FLM_Key`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;

INSERT INTO `Permission` VALUES ('flm_systems-r'),
                                ('flm_systems-w'),
                                ('flm_keys-r'),
                                ('flm_keys-w'),
                                ('flm_cores-r'),
                                ('flm_cores-w');

-- TODO Doors, Key Issues