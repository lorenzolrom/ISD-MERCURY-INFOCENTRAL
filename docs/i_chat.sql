CREATE TABLE `Chat_Room` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(64) DEFAULT NULL,
  `private` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `archived` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
);

CREATE TABLE `Chat_Message` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `room` int(11) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT `Chat_Message_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Chat_Message_ibfk_2` FOREIGN KEY (`room`) REFERENCES `Chat_Room`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Chat_Room_User` (
  `room` int(11) UNSIGNED NOT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY (`room`, `user`),
  CONSTRAINT `Chat_Room_User_ibfk_1` FOREIGN KEY (`room`) REFERENCES `Chat_Room`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Chat_Room_User_ibfk_2` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Chat_RoomLastChecked` (
  `user` int(11) NOT NULL,
  `room` int(11) UNSIGNED NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`user`, `room`),
  CONSTRAINT `Chat_RoomLastChecked_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Chat_RoomLastChecked_ibfk_2` FOREIGN KEY (`room`) REFERENCES `Chat_Room`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Chat_Heartbeat` (
  `user` int(11) NOT NULL,
  `lastCheckIn` DATETIME NOT NULL,
  PRIMARY KEY (`user`),
  CONSTRAINT `Chat_Hearbeat_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO `Permission` (`code`) VALUES ('chat');