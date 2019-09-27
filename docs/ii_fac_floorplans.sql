CREATE TABLE `FacilitiesFP_Floorplan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `building` INT(11) NOT NULL,
  `floor` CHAR(4) NOT NULL,
  `image` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY(`building`, `floor`),
  FOREIGN KEY (`building`) REFERENCES `FacilitiesCore_Building`(`id`) ON UPDATE CASCADE
);

CREATE TABLE `FacilitiesFP_Location` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `location` INT(11) NOT NULL,
  PRIMARY KEY(`id`),
  FOREIGN KEY (`location`) REFERENCES `FacilitiesCore_Location`(`id`) ON UPDATE CASCADE
);

CREATE TABLE `FacilitiesFP_Point` (
  `id` INT(11) NOT NULL,
  `location` INT(11) NOT NULL,
  `xPos` INT(11) NOT NULL,
  'yPos' INT(11) NOT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`location`) REFERENCES `FacilitiesFP_Location`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO `Permission` VALUES ('facilities_floorplans-r'), ('facilities_floorplans-w');