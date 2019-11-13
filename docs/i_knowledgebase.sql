CREATE TABLE `KB_Collection` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `KB_Article`(
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `collection` int(11) unsigned NOT NULL,
  `author` varchar(64) NOT NULL,
  `status` ENUM('Draft', 'Review', 'Published', 'Discarded') NOT NULL DEFAULT 'Draft',
  `title` text NOT NULL,
  `summary` text NOT NULL,
  `errorMessage` text NOT NULL,
  `cause` text NOT NULL,
  `solution` text NOT NULL,
  `details` text NOT NULL,
  `symptoms` text NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `KB_Article_ibfk_1` FOREIGN KEY (`collection`) REFERENCES `KB_Collection`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `KB_Article_ibfk_2` FOREIGN KEY (`author`) REFERENCES `User`(`username`) ON UPDATE CASCADE
);

-- Add permissions
INSERT INTO `Permission` (`code`) VALUES ('kb_articles-r'), ('kb_articles-w'), ('kb_articles-admin');