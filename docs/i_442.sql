-- This database is for the temporary '442' extension, that is a two-player game engine with chat

-- This will be the match instance
CREATE TABLE `442_Room` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id),
  `user1` int(11) NOT NULL,
  `user2` int(11) NOT NULL,
  CONSTRAINT `442_Room_ibfk_1` FOREIGN KEY (`user1`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `442_Room_ibfk_2` FOREIGN KEY (`user2`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- This is a chat message
CREATE TABLE `442_Message` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `room` int(11) UNSIGNED DEFAULT NULL,
  `message` text NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT `442_Message_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `442_Message_ibfk_2` FOREIGN KEY (`room`) REFERENCES `442_Room`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- This is the user heartbeat
CREATE TABLE `442_Heartbeat` (
  `user` int(11) NOT NULL,
  `lastCheckIn` DATETIME NOT NULL,
  PRIMARY KEY (`user`),
  CONSTRAINT `442_Heartbeat_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- TODO match invitation

INSERT INTO `Permission` (`code`) VALUES ('442');