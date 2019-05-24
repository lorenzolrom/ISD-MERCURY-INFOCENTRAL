-- Master Key System
CREATE TABLE `LockShop_System` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) DEFAULT NULL,
  `name` text NOT NULL,
  `code` varchar(32) NOT NULL,
  `master` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`code`),
  FOREIGN KEY (`parent`) REFERENCES `LockShop_System`(`id`) ON UPDATE CASCADE ON DELETE SET NULL
);

-- Lock Specification
CREATE TABLE `LockShop_Core` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`system`, `code`),
  FOREIGN KEY (`system`) REFERENCES `LockShop_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Key
CREATE TABLE `LockShop_Key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system` int(11) NOT NULL,
  `bitting` varchar(32) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`system`) REFERENCES `LockShop_System`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Assign key to lock
CREATE TABLE `LockShop_Key_Core` (
  `core` int(11) NOT NULL,
  `key` int(11) NOT NULL,
  FOREIGN KEY (`core`) REFERENCES `LockShop_Core`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`key`) REFERENCES `LockShop_Key`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Instance of lock
CREATE TABLE `LockShop_Core_Location` (
  `core` int(11) NOT NULL,
  `location` int(11) DEFAULT NULL,
  FOREIGN KEY (`core`) REFERENCES `LockShop_Core`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`location`) REFERENCES `FacilitiesCore_Location`(`id`) ON UPDATE CASCADE ON DELETE SET NULL
);

-- Instance of key
CREATE TABLE `LockShop_Key_User` (
  `key` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `serial` varchar(32) NOT NULL,
  UNIQUE KEY (`key`, `serial`),
  FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`key`) REFERENCES `LockShop_Key`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE `LockShop_System` ADD FOREIGN KEY (`master`) REFERENCES `LockShop_Key`(`id`) ON UPDATE CASCADE ON DELETE SET NULL;