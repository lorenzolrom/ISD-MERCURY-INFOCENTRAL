-- Vendor
CREATE TABLE `ITSM_Vendor`
(
  `id`            int(11) unsigned     NOT NULL AUTO_INCREMENT,
  `code`          varchar(32) NOT NULL,
  `name`          text        NOT NULL,
  `streetAddress` text        NOT NULL,
  `city`          text        NOT NULL,
  `state`         char(2)     NOT NULL,
  `zipCode`       char(5)     NOT NULL,
  `phone`         varchar(20) NOT NULL,
  `fax`           varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
);

-- Commodity
CREATE TABLE `ITSM_Commodity`
(
  `id`            int(11) unsigned      NOT NULL AUTO_INCREMENT,
  `code`          varchar(32)  NOT NULL,
  `name`          varchar(64)  NOT NULL,
  `commodityType` int(11) unsigned      NOT NULL,
  `assetType`     int(11) unsigned DEFAULT NULL,
  `manufacturer`  text         NOT NULL,
  `model`         text         NOT NULL,
  `unitCost`      float(11, 2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `commodityType` (`commodityType`),
  KEY `assetType` (`assetType`),
  CONSTRAINT `ITSM_Commodity_ibfk_1` FOREIGN KEY (`commodityType`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Commodity_ibfk_2` FOREIGN KEY (`assetType`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE
);

-- Warehouse

CREATE TABLE `ITSM_Warehouse` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `createDate` date DEFAULT NULL,
  `createUser` int(11) unsigned DEFAULT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `lastModifyDate` date DEFAULT NULL,
  `lastModifyUser` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `lastModifyUser` (`lastModifyUser`),
  KEY `createUser` (`createUser`),
  CONSTRAINT `ITSM_Warehouse_ibfk_1` FOREIGN KEY (`lastModifyUser`) REFERENCES `User` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Warehouse_ibfk_2` FOREIGN KEY (`createUser`) REFERENCES `User` (`id`) ON UPDATE CASCADE
);

-- Purchase Order

CREATE TABLE `ITSM_PurchaseOrder`
(
  `id`          int(11) unsigned    NOT NULL AUTO_INCREMENT,
  `number`      int(11) unsigned    NOT NULL,
  `orderDate`   date       NOT NULL,
  `warehouse`   int(11) unsigned    NOT NULL,
  `vendor`      int(11) unsigned    NOT NULL,
  `status`      int(11) unsigned    NOT NULL,
  `notes`       text,
  `sent`        tinyint(1) NOT NULL DEFAULT '0',
  `sendDate`    date                DEFAULT NULL,
  `received`    tinyint(1) NOT NULL DEFAULT '0',
  `receiveDate` date                DEFAULT NULL,
  `cancelDate`  date                DEFAULT NULL,
  `canceled`    tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `vendor` (`vendor`),
  KEY `warehouse` (`warehouse`),
  KEY `status` (`status`),
  CONSTRAINT `ITSM_PurchaseOrder_ibfk_1` FOREIGN KEY (`vendor`) REFERENCES `ITSM_Vendor` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_PurchaseOrder_ibfk_2` FOREIGN KEY (`warehouse`) REFERENCES `ITSM_Warehouse` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_PurchaseOrder_ibfk_3` FOREIGN KEY (`status`) REFERENCES `Attribute` (`id`) ON UPDATE CASCADE
);

CREATE TABLE `ITSM_PurchaseOrder_Commodity`
(
  `id`            int(11) unsigned      NOT NULL AUTO_INCREMENT,
  `purchaseOrder` int(11) unsigned      NOT NULL,
  `commodity`     int(11) unsigned      NOT NULL,
  `quantity`      int(11) unsigned      NOT NULL,
  `unitCost`      float(11, 2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `purchaseOrder` (`purchaseOrder`),
  KEY `commodity` (`commodity`),
  CONSTRAINT `ITSM_PurchaseOrder_Commodity_ibfk_1` FOREIGN KEY (`purchaseOrder`) REFERENCES `ITSM_PurchaseOrder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ITSM_PurchaseOrder_Commodity_ibfk_2` FOREIGN KEY (`commodity`) REFERENCES `ITSM_Commodity` (`id`) ON UPDATE CASCADE
);

CREATE TABLE `ITSM_PurchaseOrder_CostItem`
(
  `id`            int(11) unsigned      NOT NULL AUTO_INCREMENT,
  `purchaseOrder` int(11) unsigned      NOT NULL,
  `cost`          float(11, 2) NOT NULL,
  `notes`         text,
  PRIMARY KEY (`id`),
  KEY `purchaseOrder` (`purchaseOrder`),
  CONSTRAINT `ITSM_PurchaseOrder_CostItem_ibfk_1` FOREIGN KEY (`purchaseOrder`) REFERENCES `ITSM_PurchaseOrder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Discard Order
CREATE TABLE `ITSM_DiscardOrder`
(
  `id`          int(11) unsigned    NOT NULL AUTO_INCREMENT,
  `number`      int(11) unsigned    NOT NULL UNIQUE,
  `date`        date       NOT NULL,
  `notes`       text,
  `approved`    tinyint(1) NOT NULL DEFAULT '0',
  `approveDate` date                DEFAULT NULL,
  `fulfilled`   tinyint(1) NOT NULL DEFAULT '0',
  `fulfillDate` date                DEFAULT NULL,
  `canceled`    tinyint(1) NOT NULL DEFAULT '0',
  `cancelDate`  date                DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `ITSM_DiscardOrder_Asset`
(
  `order` int(11) unsigned NOT NULL,
  `asset` int(11) unsigned NOT NULL,
  PRIMARY KEY (`asset`),
  CONSTRAINT `ITSM_DiscardOrder_Asset_ibfk_1` FOREIGN KEY (`order`) REFERENCES `ITSM_DiscardOrder` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `ITSM_DiscardOrder_Asset_ibfk_2` FOREIGN KEY (`asset`) REFERENCES `ITSM_Asset` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Asset
CREATE TABLE `ITSM_Asset`
(
  `id`              int(11) unsigned    NOT NULL AUTO_INCREMENT,
  `commodity`       int(11) unsigned    NOT NULL,
  `warehouse`       int(11) unsigned             DEFAULT NULL,
  `assetTag`        int(11) unsigned    NOT NULL,
  `parent`          int(11) unsigned             DEFAULT NULL,
  `location`        int(11) unsigned             DEFAULT NULL,
  `serialNumber`    varchar(64)         DEFAULT NULL,
  `manufactureDate` varchar(64)         DEFAULT NULL,
  `purchaseOrder`   int(11) unsigned             DEFAULT NULL,
  `notes`           text,
  `discardOrder`    int(11) unsigned             DEFAULT NULL,
  `discarded`       tinyint(1) NOT NULL DEFAULT '0',
  `discardDate`     date                DEFAULT NULL,
  `verified`        tinyint(1) NOT NULL DEFAULT '0',
  `verifyDate`      date                DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assetTag` (`assetTag`),
  KEY `location` (`location`),
  KEY `commodity` (`commodity`),
  KEY `warehouse` (`warehouse`),
  KEY `purchaseOrder` (`purchaseOrder`),
  CONSTRAINT `ITSM_Asset_ibfk_1` FOREIGN KEY (`location`) REFERENCES `FacilitiesCore_Location` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_3` FOREIGN KEY (`commodity`) REFERENCES `ITSM_Commodity` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_4` FOREIGN KEY (`warehouse`) REFERENCES `ITSM_Warehouse` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_5` FOREIGN KEY (`purchaseOrder`) REFERENCES `ITSM_PurchaseOrder` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `ITSM_Asset_ibfk_6` FOREIGN KEY (`discardOrder`) REFERENCES `ITSM_DiscardOrder` (`id`) ON UPDATE CASCADE
);

ALTER TABLE `ITSM_Asset` ADD FOREIGN KEY (`parent`) REFERENCES `ITSM_Asset` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Asset Worksheet
CREATE TABLE `ITSM_Asset_Worksheet`
(
  `asset` int(11) unsigned NOT NULL,
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
('itsm', 'post', 'rcvf', 'Received'),
('itsm', 'roty', 'warr', 'Warranty'),
('itsm', 'roty', 'repa', 'Repair'),
('itsm', 'roty', 'trad', 'Trade-In'),
('itsm', 'rost', 'rdts', 'Ready To Send'),
('itsm', 'rost', 'sent', 'Sent'),
('itsm', 'rost', 'cncl', 'Canceled'),
('itsm', 'rost', 'rcvd', 'Received');

-- Permissions
INSERT INTO `Permission` (`code`) VALUES
  ('itsm'),
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
  ('itsm_inventory-warehouses-w');