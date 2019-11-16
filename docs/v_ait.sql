-- Application
CREATE TABLE `ITSM_Application` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(11) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `owner` int(11) unsigned NOT NULL,
  `type` int(11) unsigned NOT NULL,
  `status` int(11) unsigned NOT NULL,
  `publicFacing` tinyint(1) NOT NULL DEFAULT '0',
  `lifeExpectancy` int(11) unsigned NOT NULL,
  `dataVolume` int(11) unsigned NOT NULL,
  `authType` int(11) unsigned NOT NULL,
  `port` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `owner` (`owner`),
  KEY `lifeExpectancy` (`lifeExpectancy`),
  KEY `dataVolume` (`dataVolume`),
  KEY `authType` (`authType`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  CONSTRAINT `ITSM_Application_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `User` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Application_ibfk_2` FOREIGN KEY (`lifeExpectancy`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Application_ibfk_3` FOREIGN KEY (`dataVolume`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Application_ibfk_4` FOREIGN KEY (`authType`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Application_ibfk_5` FOREIGN KEY (`type`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Application_ibfk_6` FOREIGN KEY (`status`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE
);

-- App - VHOST
CREATE TABLE `ITSM_Application_VHost` (
  `application` int(11) unsigned NOT NULL,
  `vhost` int(11) unsigned NOT NULL,
  PRIMARY KEY (`application`,`vhost`),
  KEY `vhost` (`vhost`),
  CONSTRAINT `ITSM_Application_VHost_ibfk_1` FOREIGN KEY (`application`) REFERENCES `ITSM_Application` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Application_VHost_ibfk_2` FOREIGN KEY (`vhost`) REFERENCES `ITSM_VHost` (`id`) ON UPDATE CASCADE
);

-- App - Host
CREATE TABLE `ITSM_Application_Host` (
  `application` int(11) unsigned NOT NULL,
  `host` int(11) unsigned NOT NULL,
  `relationship` char(4) NOT NULL,
  PRIMARY KEY (`application`,`host`,`relationship`),
  KEY `host` (`host`),
  CONSTRAINT `ITSM_Application_Host_ibfk_1` FOREIGN KEY (`application`) REFERENCES `ITSM_Application` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Application_Host_ibfk_2` FOREIGN KEY (`host`) REFERENCES `ITSM_Host` (`id`) ON UPDATE CASCADE
);

--
-- DEFAULT DATA
--

-- Attributes (aitt = App Type, aitd = App Data Capacity, aitl = App Lifespan, aits = App Status, aita = App Auth Type)
INSERT INTO `Attribute` (`extension`, `type`, `code`, `name`) VALUES
('itsm', 'aitt', 'infr', 'Infrastructure Service'),
('itsm', 'aitt', 'weba', 'Web Application'),
('itsm', 'aitt', 'osap', 'O.S. Application'),
('itsm', 'aitd', 'lt1g', '<1 GB'),
('itsm', 'aitd', '110g', '1-10 GB'),
('itsm', 'aitd', '1150', '11-50 GB'),
('itsm', 'aitd', '5110', '51-100 GB'),
('itsm', 'aitd', '1015', '101-500 GB'),
('itsm', 'aitd', '501t', '501 GB - 1 TB'),
('itsm', 'aitd', 'gt1t', '>1 TB'),
('itsm', 'aitl', '1t5y', '1-5 Years'),
('itsm', 'aitl', '5t10', '5-10 Years'),
('itsm', 'aitl', 'gt10', '>10 Years'),
('itsm', 'aita', 'loca', 'Local D.B.'),
('itsm', 'aita', 'extn', 'External D.B.'),
('itsm', 'aita', 'ldap', 'LDAP'),
('itsm', 'aita', 'sson', 'Single Sign-On'),
('itsm', 'aita', 'none', 'None'),
('itsm', 'aits', 'addd', 'Added'),
('itsm', 'aits', 'inde', 'In Development'),
('itsm', 'aits', 'pror', 'Project Rejected'),
('itsm', 'aits', 'inpr', 'In Production'),
('itsm', 'aits', 'prjr', 'Projected Retire'),
('itsm', 'aits', 'read', 'Retain App & Data'),
('itsm', 'aits', 'rdao', 'Retain Data Only'),
('itsm', 'aits', 'reti', 'Retired'),
('itsm', 'aits', 'deco', 'Decomissioned');

-- Permissions
INSERT INTO `Permission` (`code`) VALUES
  ('itsm_ait'),
  ('itsm_ait-apps-r'),
  ('itsm_ait-apps-w');