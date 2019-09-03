CREATE TABLE `Tickets_Workspace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL UNIQUE,
  `requestPortal` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
);

CREATE TABLE Tickets_Attribute (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workspace` int(11) NOT NULL,
  `type` enum('type', 'status', 'category', 'severity') NOT NULL,
  `code` char(4) NOT NULL,
  `name` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`workspace`, `type`, `code`),
  FOREIGN KEY (`workspace`) REFERENCES `Tickets_Workspace`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Tickets_Team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
);

CREATE TABLE `Tickets_Team_User` (
  `team` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY (`team`, `user`),
  FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`team`) REFERENCES `Tickets_Team`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Tickets_Workspace_Team` (
  `team` int(11) NOT NULL,
  `workspace` int(11) NOT NULL,
  PRIMARY KEY (`team`, `workspace`),
  FOREIGN KEY (`team`) REFERENCES `Tickets_Team`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`workspace`) REFERENCES `Tickets_Workspace`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Tickets_Ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workspace` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `title` text NOT NULL,
  `contact` varchar(64) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `status` char(4) NOT NULL DEFAULT 'new',
  `closureCode` int(11) DEFAULT NULL,
  `severity` int(11) DEFAULT NULL,
  `desiredDate` date DEFAULT NULL,
  `scheduledDate` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`workspace`, `number`),
  FOREIGN KEY (`workspace`) REFERENCES `Tickets_Workspace`(`id`) ON UPDATE CASCADE,
  FOREIGN KEY (`contact`) REFERENCES `User`(`username`) ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY (`type`) REFERENCES `Tickets_Attribute`(`id`) ON UPDATE CASCADE,
  FOREIGN KEY (`category`) REFERENCES `Tickets_Attribute`(`id`) ON UPDATE CASCADE,
  FOREIGN KEY (`severity`) REFERENCES `Tickets_Attribute`(`id`) ON UPDATE CASCADE,
  FOREIGN KEY (`closureCode`) REFERENCES `Tickets_Attribute`(`id`) ON UPDATE CASCADE
);

CREATE TABLE `Tickets_Assignee` (
  `ticket` int(11) NOT NULL,
  `team` int(11) NOT NULL,
  `user` int(11),
  FOREIGN KEY (`ticket`) REFERENCES `Tickets_Ticket`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`user`) REFERENCES `Tickets_Team_User`(`user`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`team`) REFERENCES `Tickets_Team_User`(`team`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Tickets_Update` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `time` datetime NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`ticket`) REFERENCES `Tickets_Ticket`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Tickets_Search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workspace` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `encodedSearch` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`workspace`) REFERENCES `Tickets_Workspace`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`user`) REFERENCES User(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Tickets_Link` (
  `ticket1` int(11) NOT NULL,
  `ticket2` int(11) NOT NULL,
  PRIMARY KEY (`ticket1`, `ticket2`),
  FOREIGN KEY (`ticket1`) REFERENCES `Tickets_Ticket`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`ticket2`) REFERENCES `Tickets_Ticket`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);