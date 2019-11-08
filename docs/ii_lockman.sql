-- System of keys and locks
CREATE TABLE `Lockman_System` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL UNIQUE,
  `master` int(11) UNSIGNED DEFAULT NULL,
  `control` int(11) UNSIGNED DEFAULT NULL,
  `parent` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `Lockman_System_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `Lockman_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Key
CREATE TABLE `Lockman_Key` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `system` int(11) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL,
  `bitting` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`system`, `code`),
  CONSTRAINT `Lockman_Key_ibfk_1` FOREIGN KEY (`system`) REFERENCES `Lockman_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE `Lockman_System` ADD CONSTRAINT `Lockman_System_ibfk_2` FOREIGN KEY (`master`) REFERENCES `Lockman_Key`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;

-- Lock
CREATE TABLE `Lockman_Lock` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `system` int(11) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`system`, `code`),
  CONSTRAINT `Lockman_Lock_ibfk_1` FOREIGN KEY (`system`) REFERENCES `Lockman_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Key opens lock
CREATE TABLE `Lockman_Key_Lock` (
  `lock` int(11) UNSIGNED NOT NULL,
  `key` int(11) UNSIGNED NOT NULL,
  CONSTRAINT `Lockman_Key_Lock_ibfk_1` FOREIGN KEY (`lock`) REFERENCES `Lockman_Lock`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Lockman_Key_Lock_ibfk_2` FOREIGN KEY (`key`) REFERENCES `Lockman_Key`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Door in location
CREATE TABLE `Lockman_Door` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `location` int(11) NOT NULL,
  `description` TEXT,
  CONSTRAINT `Lockman_Door_ibfk_1` FOREIGN KEY (`location`) REFERENCES `FacilitiesCore_Location`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Lock installed in door
CREATE TABLE `Lockman_Lock_Door` (
  `lock` int(11) UNSIGNED NOT NULL,
  `door` int(11) UNSIGNED NOT NULL,
  CONSTRAINT `Lockman_Lock_Door_ibfk_1` FOREIGN KEY (`lock`) REFERENCES `Lockman_Lock`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Lockman_Lock_Door_ibfk_2` FOREIGN KEY (`door`) REFERENCES `Lockman_Door`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Key given to user
CREATE TABLE `Lockman_KeyIssue` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` int(11) UNSIGNED NOT NULL,
  `issue` int(11) UNSIGNED NOT NULL,
  `user` int(11) NOT NULL,
  `notes` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`key`, `issue`),
  CONSTRAINT `Lockman_KeyIssue_ibfk_1` FOREIGN KEY (`key`) REFERENCES `Lockman_Key`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Lockman_KeyIssue_ibfk_2` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);