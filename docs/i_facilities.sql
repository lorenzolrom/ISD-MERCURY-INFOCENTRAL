-- Building
CREATE TABLE `FacilitiesCore_Building` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `streetAddress` text NOT NULL,
  `city` text NOT NULL,
  `state` char(2) NOT NULL,
  `zipCode` char(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
);

-- Location
CREATE TABLE `FacilitiesCore_Location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `building` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `building` (`building`,`code`),
  CONSTRAINT `FacilitiesCore_Location_ibfk_1` FOREIGN KEY (`building`) REFERENCES `FacilitiesCore_Building` (`id`) ON UPDATE CASCADE
);

-- Floorplan
CREATE TABLE `Facilities_Floorplan` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `building` int(11) NOT NULL,
  `floor` varchar(16) NOT NULL,
  `imageType` TEXT NOT NULL,
  `imageName` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`building`, `floor`),
  CONSTRAINT `Facilities_Floorplan_ibfk_1` FOREIGN KEY (`building`) REFERENCES `FacilitiesCore_Building`(`id`) ON UPDATE CASCADE
);

CREATE TABLE `Facilities_Space` (
  `location` int(11) NOT NULL,
  `floor` int(11) UNSIGNED NOT NULL,
  `hexColor` char(6) NOT NULL DEFAULT 'FFFFFF',
  `area` decimal(7,1) NOT NULL DEFAULT 0.0,
  `unit` varchar(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`location`),
  CONSTRAINT `Facilities_Space_ibfk_1` FOREIGN KEY (`location`) REFERENCES `FacilitiesCore_Location`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Facilities_Space_ibfk_2` FOREIGN KEY (`floor`) REFERENCES `Facilities_Floorplan`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Facilities_SpacePoint` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `space` int(11) NOT NULL,
  `pD` decimal(7,4) UNSIGNED NOT NULL, -- percent down
  `pR` decimal(7,4) UNSIGNED NOT NULL, -- percent right
  PRIMARY KEY (`id`),
  CONSTRAINT `Facilities_SpacePoint_ibfk_1` FOREIGN KEY (`space`) REFERENCES `Facilities_Space`(`location`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Permissions
INSERT INTO `Permission` (`code`) VALUES
  ('facilities'),
  ('facilitiescore_facilities-r'),
  ('facilitiescore_facilities-w');

INSERT INTO `Permission` (`code`) VALUES
  ('facilitiescore_floorplans-r'),
  ('facilitiescore_floorplans-w');