-- Vendor
CREATE TABLE `ITSM_Vendor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `name` text NOT NULL,
  `streetAddress` text NOT NULL,
  `city` text NOT NULL,
  `state` char(2) NOT NULL,
  `zipCode` char(5) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `fax` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
);

-- Commodity
CREATE TABLE `ITSM_Commodity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `commodityType` int(11) NOT NULL,
  `assetType` int(11) DEFAULT NULL,
  `manufacturer` text NOT NULL,
  `model` text NOT NULL,
  `unitCost` float(11,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `commodityType` (`commodityType`),
  KEY `assetType` (`assetType`),
  CONSTRAINT `ITSM_Commodity_ibfk_1` FOREIGN KEY (`commodityType`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Commodity_ibfk_2` FOREIGN KEY (`assetType`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE
);

-- Purchase Order TODO

-- Return Order TODO

-- Discard Order TODO

-- Asset
CREATE TABLE `ITSM_Asset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commodity` int(11) NOT NULL,
  `warehouse` int(11) DEFAULT NULL,
  `assetTag` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `location` int(11) DEFAULT NULL,
  `serialNumber` varchar(64) DEFAULT NULL,
  `manufactureDate` varchar(64) DEFAULT NULL,
  `purchaseOrder` int(11) DEFAULT NULL,
  `notes` text,
  `createDate` date DEFAULT NULL,
  `discarded` tinyint(1) NOT NULL DEFAULT '0',
  `discardDate` date DEFAULT NULL,
  `lastModifyDate` date DEFAULT NULL,
  `lastModifyUser` int(11) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `verifyDate` date DEFAULT NULL,
  `verifyUser` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assetTag` (`assetTag`),
  KEY `location` (`location`),
  KEY `lastModifyUser` (`lastModifyUser`),
  KEY `commodity` (`commodity`),
  KEY `warehouse` (`warehouse`),
  KEY `purchaseOrder` (`purchaseOrder`),
  KEY `verifyUser` (`verifyUser`),
  KEY `parent` (`parent`),
  CONSTRAINT `ITSM_Asset_ibfk_1` FOREIGN KEY (`location`) REFERENCES `FacilitiesCore_Location` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_2` FOREIGN KEY (`lastModifyUser`) REFERENCES `User` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_3` FOREIGN KEY (`commodity`) REFERENCES `ITSM_Commodity` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_4` FOREIGN KEY (`warehouse`) REFERENCES `ITSM_Warehouse` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_5` FOREIGN KEY (`purchaseOrder`) REFERENCES `ITSM_PurchaseOrder` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_6` FOREIGN KEY (`verifyUser`) REFERENCES `User` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_7` FOREIGN KEY (`parent`) REFERENCES `ITSM_Asset` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Asset Worksheet
CREATE TABLE `ITSM_Asset_Worksheet` (
  `asset` int(11) NOT NULL,
  PRIMARY KEY (`asset`),
  CONSTRAINT `ITSM_Asset_Worksheet_ibfk_1` FOREIGN KEY (`asset`) REFERENCES `ITSM_Asset` (`id`) ON UPDATE CASCADE
);

--
-- DEFAULT DATA
--

-- Attribute (coty = Commodity Type, post = Purchase Order Status, roty = Return Order Type, rost = Return Order Status)
INSERT INTO `Attribute` (`extension`, `type`, `code`, `name`) VALUES
('itsm', 'coty', 'mate', 'Materials'),
('itsm', 'coty', 'equi', 'Equipment'),
('itsm', 'coty', 'asse', 'Asset'),
('itsm', 'post', 'rdts', 'Ready To Send'),
('itsm', 'post', 'sent', 'Sent'),
('itsm', 'post', 'cncl', 'Canceled'),
('itsm', 'post', 'rcvp', 'Received In Part'),
('itsm', 'post', 'rcvf', 'Received In Full'),
('itsm', 'roty', 'warr', 'Warranty'),
('itsm', 'roty', 'repa', 'Repair'),
('itsm', 'roty', 'trad', 'Trade-In'),
('itsm', 'rost', 'rdts', 'Ready To Send'),
('itsm', 'rost', 'sent', 'Sent'),
('itsm', 'rost', 'cncl', 'Canceled'),
('itsm', 'rost', 'rcvd', 'Received');