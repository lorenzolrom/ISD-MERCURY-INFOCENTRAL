-- Host Category
CREATE TABLE `ITSM_HostCategory` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `displayed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE `ITSM_Host_HostCategory` (
  `host` int(11) unsigned NOT NULL,
  `category` int(11) unsigned NOT NULL,
  PRIMARY KEY (`host`,`category`),
  KEY `category` (`category`),
  CONSTRAINT `ITSM_Host_HostCategory_ibfk_1` FOREIGN KEY (`host`) REFERENCES `ITSM_Host` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Host_HostCategory_ibfk_2` FOREIGN KEY (`category`) REFERENCES `ITSM_HostCategory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Permissions
INSERT INTO `Permission` (`code`) VALUES
  ('itsmmonitor'),
  ('itsmmonitor-hosts-r'),
  ('itsmmonitor-hosts-w'),
  ('itsmmonitor-services-r'),
  ('itsmmonitor-services-w');