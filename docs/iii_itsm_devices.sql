-- HOST
CREATE TABLE `ITSM_Host` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset` int(11) unsigned NOT NULL,
  `ipAddress` varchar(39) NOT NULL,
  `macAddress` char(17) NOT NULL,
  `notes` text,
  `systemName` varchar(64) NOT NULL,
  `systemCPU` varchar(64) DEFAULT NULL,
  `systemRAM` varchar(64) DEFAULT NULL,
  `systemOS` varchar(64) DEFAULT NULL,
  `systemDomain` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ipAddress` (`ipAddress`),
  UNIQUE KEY `macAddress` (`macAddress`),
  KEY `asset` (`asset`),
  CONSTRAINT `ITSM_Host_ibfk_1` FOREIGN KEY (`asset`) REFERENCES `ITSM_Asset` (`id`) ON UPDATE CASCADE
);

-- Permissions
INSERT INTO `Permission` (`code`) VALUES
  ('itsm_devices'),
  ('itsm_devices-hosts-r'),
  ('itsm_devices-hosts-w'),
  ('itsm_dhcplogs-r');