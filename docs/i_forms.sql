CREATE TABLE `Forms_Form` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` TEXT NOT NULL,
  `owner` int(11) NOT NULL,
  `active` TINYINT(1) UNSIGNED DEFAULT 1,
  `emailRequired` TINYINT(1) UNSIGNED DEFAULT 0, -- Should form require an e-mail address
  `sendConfirmationEmail` TINYINT(1) UNSIGNED DEFAULT 0, -- Should a confirmation e-mail be sent
  PRIMARY KEY (`id`),
  CONSTRAINT `Forms_Form_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `User`(`id`) ON UPDATE CASCADE
);

-- User-defined tags on form
CREATE TABLE `Forms_Tag` (
  `form` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `flag` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`form`, `flag`),
  CONSTRAINT `Forms_FormFlag_ibfk_1` FOREIGN KEY (`form`) REFERENCES `Forms_Form`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Forms_Validation` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(64) NOT NULL UNIQUE,
  `regex` TEXT NOT NULL,
  `errorMessage` TEXT NOT NULL, -- will be appended to field title to make human readable error
  PRIMARY KEY (`id`)
);

CREATE TABLE `Forms_Field` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `form` int(11) UNSIGNED NOT NULL,
  `sequence` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `private` tinyint(1) UNSIGNED NOT NULL DEFAULT 0, -- will this field only be available to back office user
  `type` enum('text', 'select', 'upload') NOT NULL DEFAULT 'text',
  `title` varchar(64) NOT NULL,
  `placeholder` TEXT DEFAULT NULL,
  `required` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `validation` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`form`, `title`),
  CONSTRAINT `Forms_Field_ibfk_1` FOREIGN KEY (`form`) REFERENCES `Forms_Form`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Forms_Field_ibfk_2` FOREIGN KEY (`validation`) REFERENCES `Forms_Validation`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Only used if Forms_Field type is 'select'
CREATE TABLE `Forms_FieldOption` (
  `field` int(11) UNSIGNED NOT NULL,
  `value` VARCHAR(64),
  `title` TEXT NOT NULL,
  `sequence` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`field`, `value`),
  CONSTRAINT `Forms_FieldOption_ibfk_1` FOREIGN KEY (`field`) REFERENCES `Forms_Field`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Instance of a submitted form
CREATE TABLE `Forms_Submission` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `form` int(11) UNSIGNED NOT NULL,
  `number` int(11) UNSIGNED NOT NULL,
  `email` TEXT DEFAULT NULL, -- required if form submitterEmailRequired is TRUE
  PRIMARY KEY (`id`),
  UNIQUE KEY (`form`, `number`),
  CONSTRAINT `Forms_Submission_ibfk_1` FOREIGN KEY (`form`) REFERENCES `Forms_Form`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `Forms_SubmissionField` (
  `submission` int(11) UNSIGNED NOT NULL,
  `field` int(11) UNSIGNED NOT NULL,
  `value` TEXT DEFAULT NULL,
  PRIMARY KEY (`submission`, `field`),
  CONSTRAINT `Forms_SubmissionField_ibfk_1` FOREIGN KEY (`submission`) REFERENCES `Forms_Submission`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Forms_SubmissionField_ibfk_2` FOREIGN KEY (`field`) REFERENCES `Forms_Field`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Specified roles who can manage the form, and who will receive notifications about submission
CREATE TABLE `Forms_Form_ManagerRole` (
  `form` int(11) UNSIGNED NOT NULL,
  `role` int(11) NOT NULL,
  PRIMARY KEY (`form`, `role`),
  CONSTRAINT `Forms_Form_ManagerRole_ibfk_1` FOREIGN KEY (`form`) REFERENCES `Forms_Form`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `Forms_Form_ManagerRole_ibfk_2` FOREIGN KEY (`role`) REFERENCES `Role`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);

-- If this is used, only users with specified roles can submit the form
CREATE TABLE `Forms_Form_AuthorizedSubmitter` (
  `form` int(11) UNSIGNED NOT NULL,
  `role` int(11) NOT NULL,
  PRIMARY KEY (`form`, `role`),
  CONSTRAINT `Forms_Form_AuthorizedSubmitter_ibfk_1` FOREIGN KEY (`form`) REFERENCES `Forms_Form`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Forms_Form_AuthorizedSubmitter_ibfk_2` FOREIGN KEY (`role`) REFERENCES `Role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Permissions
-- forms: use Form Center
-- forms-submit: submit a form allowed by filter or form setting
-- forms-manage: create, edit, and see results of forms assigned
-- forms-admin: perform all operations with all forms, regardless of owners, filters, and privacy settings
INSERT INTO `Permission` (`code`) VALUES ('forms'), ('forms-submit'), ('forms-manage') ,('forms-admin');