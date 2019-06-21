-- USER
CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `firstName` varchar(30) NOT NULL,
  `lastName` varchar(30) NOT NULL,
  `email` text NOT NULL,
  `password` char(96) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `authType` enum('loca','ldap') NOT NULL DEFAULT 'loca',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);

-- AUTH
CREATE TABLE `Token` (
  `token` char(128) NOT NULL,
  `user` int(11) NOT NULL,
  `issueTime` datetime NOT NULL,
  `expireTime` datetime NOT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  `ipAddress` varchar(39) NOT NULL,
  PRIMARY KEY (`token`),
  KEY `user` (`user`),
  CONSTRAINT `Token_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `Secret` (
  `id` int(11) NOT NULL auto_increment,
  `secret` char(128) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `secret` (`secret`),
  UNIQUE KEY `name` (`name`)
);

-- ROLE
CREATE TABLE `Role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE `User_Role` (
  `user` int(11) NOT NULL,
  `role` int(11) NOT NULL,
  PRIMARY KEY (`user`,`role`),
  KEY `role` (`role`),
  CONSTRAINT `User_Role_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `User_Role_ibfk_2` FOREIGN KEY (`role`) REFERENCES `Role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- PERMISSION
CREATE TABLE `Permission` (
  `code` varchar(64) NOT NULL,
  PRIMARY KEY (`code`),
  UNIQUE KEY `code` (`code`)
);

CREATE TABLE `Role_Permission` (
  `role` int(11) NOT NULL,
  `permission` varchar(64) NOT NULL,
  PRIMARY KEY (`role`,`permission`),
  KEY `permission` (`permission`),
  CONSTRAINT `Role_Permission_ibfk_1` FOREIGN KEY (`role`) REFERENCES `Role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Role_Permission_ibfk_2` FOREIGN KEY (`permission`) REFERENCES `Permission` (`code`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `Secret_Permission` (
  `secret` char(128) NOT NULL,
  `permission` varchar(64) NOT NULL,
  PRIMARY KEY (`secret`,`permission`),
  KEY `code` (`permission`),
  CONSTRAINT `Secret_Permission_ibfk_1` FOREIGN KEY (`secret`) REFERENCES `Secret` (`secret`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Secret_Permission_ibfk_2` FOREIGN KEY (`permission`) REFERENCES `Permission` (`code`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- NOTIFICATION
CREATE TABLE `Notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `title` text NOT NULL,
  `data` text NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `important` tinyint(1) NOT NULL DEFAULT '0',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  CONSTRAINT `Notification_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- BULLETIN
CREATE TABLE `Bulletin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `title` text NOT NULL,
  `message` text NOT NULL,
  `inactive` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('i','a') NOT NULL DEFAULT 'i',
  PRIMARY KEY (`id`)
);

CREATE TABLE `Role_Bulletin` (
  `role` int(11) NOT NULL,
  `bulletin` int(11) NOT NULL,
  PRIMARY KEY (`role`,`bulletin`),
  KEY `bulletin` (`bulletin`),
  CONSTRAINT `Role_Bulletin_ibfk_1` FOREIGN KEY (`role`) REFERENCES `Role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Role_Bulletin_ibfk_2` FOREIGN KEY (`bulletin`) REFERENCES `Bulletin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Attribute
CREATE TABLE `Attribute` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `extension` char(4) NOT NULL DEFAULT 'core',
  `type` char(4) NOT NULL,
  `code` char(4) NOT NULL,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `extension` (`extension`,`type`,`code`)
);

-- History
CREATE TABLE `History` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` enum('CREATE','MODIFY','DELETE') NOT NULL,
  `table` text NOT NULL,
  `index` text NOT NULL,
  `username` varchar(64) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  CONSTRAINT `History_ibfk_1` FOREIGN KEY (`username`) REFERENCES `User` (`username`) ON UPDATE CASCADE
);

CREATE TABLE `HistoryItem` (
  `history` int(11) NOT NULL,
  `column` text NOT NULL,
  `oldValue` text,
  `newValue` text,
  KEY `history` (`history`),
  CONSTRAINT `HistoryItem_ibfk_1` FOREIGN KEY (`history`) REFERENCES `History` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

--
-- DEFAULT DATA
--

-- Permissions
INSERT INTO `Permission` (`code`) VALUES
  ('facilitiescore_facilities-r'),
  ('facilitiescore_facilities-w'),
  ('itsm'),
  ('itsmmonitor'),
  ('itsmmonitor-hosts-r'),
  ('itsmmonitor-hosts-w'),
  ('itsmmonitor-services-r'),
  ('itsmmonitor-services-w'),
  ('itsm_ait'),
  ('itsm_ait-apps-r'),
  ('itsm_ait-apps-w'),
  ('itsm_devices'),
  ('itsm_devices-hosts-r'),
  ('itsm_devices-hosts-w'),
  ('itsm_inventory'),
  ('itsm_inventory-assets-r'),
  ('itsm_inventory-assets-w'),
  ('itsm_inventory-commodities-r'),
  ('itsm_inventory-commodities-w'),
  ('itsm_inventory-discards-r'),
  ('itsm_inventory-discards-w'),
  ('itsm_inventory-purchaseorders-r'),
  ('itsm_inventory-purchaseorders-w'),
  ('itsm_inventory-settings-rw'),
  ('itsm_inventory-vendors-r'),
  ('itsm_inventory-vendors-w'),
  ('itsm_inventory-warehouses-r'),
  ('itsm_inventory-warehouses-w'),
  ('itsm_web'),
  ('itsm_web-aliases-rw'),
  ('itsm_web-registrars-r'),
  ('itsm_web-registrars-w'),
  ('itsm_web-vhosts-r'),
  ('itsm_web-vhosts-w'),
  ('itsm_weblogs'),
  ('servicenter'),
  ('servicenter_admin'),
  ('servicenter_desk-r'),
  ('servicenter_desk-w'),
  ('servicenter_requests'),
  ('settings'),
  ('api-settings'),
  ('tickets'),
  ('tickets-customer'),
  ('tickets-agent'),
  ('tickets-admin'),
  ('lockshop'),
  ('lockshop-r'),
  ('lockshop-w');