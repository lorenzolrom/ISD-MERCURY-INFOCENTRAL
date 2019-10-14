INSERT INTO `Permission` (`code`) VALUES ('mlchat'), ('mlchat-admin');

-- Chat room
CREATE TABLE MLChat_Room (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `public` tinyint(1) DEFAULT 0, -- Is this room publicly searchable
  `direct` tinyint(1) DEFAULT 0, -- Is this room a private message between two users
  PRIMARY KEY (`id`),
  CONSTRAINT `MLChat_Room_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `User`(`id`) ON UPDATE CASCADE
);

-- User membership to room
CREATE TABLE MLChat_Room_User (
  `room` int(11) UNSIGNED NOT NULL,
  `user` int(11) NOT NULL,
  CONSTRAINT `MLChat_Room_User_ibfk_1` FOREIGN KEY (`room`) REFERENCES `MLChat_Room`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `MLChat_Room_User_ibfk_2` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Message sent to room
CREATE TABLE `MLChat_Message` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room` int(11) UNSIGNED NOT NULL,
  `user` int(11) NOT NULL,
  `time` DATETIME NOT NULL,
  `message` TEXT NOT NULL,
  CONSTRAINT `MLChat_Message_ibfk_1` FOREIGN KEY (`room`) REFERENCES `MLChat_Room`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `MLChat_Message_ibfk_2` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);