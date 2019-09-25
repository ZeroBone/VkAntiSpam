CREATE TABLE `messages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `groupId` BIGINT NOT NULL,
    `type` INT UNSIGNED NOT NULL, -- 1 - vk post comment
    `vkId` BIGINT NOT NULL, -- if type == 1, it is the comment id
    `author` BIGINT NOT NULL, -- author of the message, vk id
    `message` TEXT NOT NULL,
    `date` BIGINT UNSIGNED NOT NULL, -- message date
    `replyToUser` BIGINT NOT NULL, -- vk id of the user this message was replied to, 0 if not replied,
    `replyToMessage` BIGINT NOT NULL, -- vk id of the message, 0 if unknown
    `context` BIGINT NOT NULL, -- if type == 1, this is the vk post id
    `category` INT UNSIGNED NOT NULL, -- 0 if unknown (probably ham), 1 if we are 100% sure it is ham, 2 if this message was classified as spam
    PRIMARY KEY (`id`),
    KEY (`type`),
    KEY (`category`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `bans` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `message` BIGINT UNSIGNED NOT NULL, -- message the user was banned for
    `date` BIGINT UNSIGNED NOT NULL, -- ban date
    PRIMARY KEY (`id`),
    FOREIGN KEY (`message`) REFERENCES `messages`(`id`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `trainingSet` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `document` TEXT NOT NULL,
    `category` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`category`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `wordFrequency` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `word` VARCHAR(255),
    `count` INT UNSIGNED NOT NULL,
    `category` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`word`),
    KEY (`category`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(16) NOT NULL,
    `email` VARCHAR(40) NOT NULL,
    `password` CHAR(128) NOT NULL,
    `salt` CHAR(64),
    `ip` VARCHAR(39) NOT NULL,
    `ipLastLogin` VARCHAR(39) NOT NULL,
    `dateRegister` BIGINT UNSIGNED NOT NULL,
    `dateLastLogin` BIGINT UNSIGNED NOT NULL,
    `role` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`name`),
    KEY (`ip`),
    KEY (`email`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `settings` (
    `name` VARCHAR(16) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`name`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;