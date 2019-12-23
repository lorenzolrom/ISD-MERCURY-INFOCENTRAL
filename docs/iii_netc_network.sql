-- Subnet
CREATE TABLE `NETC_Subnet` (
  `ip` varchar(39) NOT NULL,
  `location` TEXT NOT NULL,
  `netmask` varchar(39) NOT NULL,
  PRIMARY KEY (`ip`)
);

-- Computer object
CREATE TABLE `NETC_Computer`(
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL UNIQUE,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `user` varchar(64) DEFAULT NULL, -- Primary user of device
  `tags` TEXT NOT NULL,
  `operatingSystems` TEXT NOT NULL,
  `notes` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `NETC_Computer_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User`(`username`) ON UPDATE CASCADE ON DELETE SET NULL
);

-- A network interface on a computer
CREATE TABLE `NETC_Interface`(
  `macAddress` char(17) NOT NULL,
  `computer` int(11) unsigned NOT NULL,
  `label` varchar(64) NOT NULL,
  `type` ENUM('IP', 'Subnet', 'Roamer'),
  `typeData` varchar(39) DEFAULT NULL, -- This is only set if type is IP (IP computer is assigned) or Subnet (IP of a subnet)
  PRIMARY KEY (`macAddress`),
  UNIQUE KEY `computer_label` (`computer`, `label`),
  CONSTRAINT `NETC_Interface_ibfk_1` FOREIGN KEY (`computer`) REFERENCES `NETC_Computer`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- DNS Entry (tied to a Computer's Interface)
CREATE TABLE `NETC_DNSRecord` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `interface` char(17) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `type` varchar(16) NOT NULL,
  `ttl` int(11) NOT NULL,
  `data` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ITSM_DNSRecord_ibfk_1` FOREIGN KEY (`interface`) REFERENCES `NETC_Interface`(`macAddress`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Permissions
INSERT INTO `Permission` (`code`) VALUES
  ('netc_subnets-r'), -- Gives permission to read subnets
  ('netc_subnets-w'), -- Gives permission to C/U/D subnets
  ('netc_computers-r'), -- Gives permission to read computers
  ('netc_computers-w'); -- Gives permission to C/U/D computers, and create DNS records associated with those computers