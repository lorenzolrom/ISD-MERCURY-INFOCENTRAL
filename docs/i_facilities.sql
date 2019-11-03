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
  `building` int(11) NOT NULL AUTO_INCREMENT,
  `floor` varchar(16) NOT NULL,
  PRIMARY KEY (`building`, `floor`)
);

-- Permissions
INSERT INTO `Permission` (`code`) VALUES
  ('facilities'),
  ('facilitiescore_facilities-r'),
  ('facilitiescore_facilities-w');