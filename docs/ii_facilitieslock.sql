-- System of keys and locks
CREATE TABLE `FacilitiesLock_System` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL UNIQUE,
  `master` int(11) UNSIGNED DEFAULT NULL,
  `control` int(11) UNSIGNED DEFAULT NULL,
  `parent` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FacilitiesLock_System_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `FacilitiesLock_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Key
CREATE TABLE `FacilitiesLock_Key` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `system` int(11) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL,
  `bitting` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`system`, `code`),
  CONSTRAINT `FacilitiesLock_Key_ibfk_1` FOREIGN KEY (`system`) REFERENCES `FacilitiesLock_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE `FacilitiesLock_System` ADD CONSTRAINT `FacilitiesLock_System_ibfk_2` FOREIGN KEY (`master`) REFERENCES `FacilitiesLock_Key`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE `FacilitiesLock_System` ADD CONSTRAINT `FacilitiesLock_System_ibfk_3` FOREIGN KEY (`control`) REFERENCES `FacilitiesLock_Key`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;

-- Lock
CREATE TABLE `FacilitiesLock_Lock` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `system` int(11) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`system`, `code`),
  CONSTRAINT `FacilitiesLock_Lock_ibfk_1` FOREIGN KEY (`system`) REFERENCES `FacilitiesLock_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Key opens lock
CREATE TABLE `FacilitiesLock_Key_Lock` (
  `lock` int(11) UNSIGNED NOT NULL,
  `key` int(11) UNSIGNED NOT NULL,
  CONSTRAINT `FacilitiesLock_Key_Lock_ibfk_1` FOREIGN KEY (`lock`) REFERENCES `FacilitiesLock_Lock`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FacilitiesLock_Key_Lock_ibfk_2` FOREIGN KEY (`key`) REFERENCES `FacilitiesLock_Key`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Door in location
CREATE TABLE `FacilitiesLock_Door` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `location` int(11) NOT NULL,
  `description` TEXT,
  PRIMARY KEY (`id`),
  CONSTRAINT `FacilitiesLock_Door_ibfk_1` FOREIGN KEY (`location`) REFERENCES `FacilitiesCore_Location`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Lock installed in door
CREATE TABLE `FacilitiesLock_Lock_Door` (
  `lock` int(11) UNSIGNED NOT NULL,
  `door` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`lock`, `door`),
  CONSTRAINT `FacilitiesLock_Lock_Door_ibfk_1` FOREIGN KEY (`lock`) REFERENCES `FacilitiesLock_Lock`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FacilitiesLock_Lock_Door_ibfk_2` FOREIGN KEY (`door`) REFERENCES `FacilitiesLock_Door`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Key given to user
CREATE TABLE `FacilitiesLock_KeyIssue` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` int(11) UNSIGNED NOT NULL,
  `issue` int(11) UNSIGNED NOT NULL,
  `user` int(11) NOT NULL,
  `notes` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`key`, `issue`),
  CONSTRAINT `FacilitiesLock_KeyIssue_ibfk_1` FOREIGN KEY (`key`) REFERENCES `FacilitiesLock_Key`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FacilitiesLock_KeyIssue_ibfk_2` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO `Permission` (`code`) VALUES
('facilities_locks-r'),
('facilities_locks-w');