CREATE TABLE `messages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `groupId` BIGINT NOT NULL,
    `type` INT UNSIGNED NOT NULL, -- 1 - vk post comment
    `vkId` BIGINT NOT NULL, -- if type == 1, it is the comment id
    `author` BIGINT NOT NULL, -- author of the message, vk id
    `message` TEXT NOT NULL,
    `messageHash` BIGINT UNSIGNED NOT NULL, -- crc32 of the message
    `date` BIGINT UNSIGNED NOT NULL, -- message date
    `replyToUser` BIGINT NOT NULL, -- vk id of the user this message was replied to, 0 if not replied,
    `replyToMessage` BIGINT NOT NULL, -- vk id of the message, 0 if unknown
    `vkContext` BIGINT NOT NULL, -- if type == 1, this is the vk post id
    `category` INT UNSIGNED NOT NULL, -- 0 if unknown (probably ham), 1 if we are 100% sure it is ham, 2 if this message was classified as spam
    PRIMARY KEY (`id`),
    KEY (`type`),
    KEY (`messageHash`),
    KEY (`date`),
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
    `text` TEXT NOT NULL,
    `category` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`category`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `words` (
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
    `salt` CHAR(64) NOT NULL,
    `csrfToken` CHAR(32) NOT NULL,
    `ip` VARCHAR(39) NOT NULL,
    `ipLastLogin` VARCHAR(39) NOT NULL,
    `dateRegister` BIGINT UNSIGNED NOT NULL,
    `dateLastLogin` BIGINT UNSIGNED NOT NULL,
    `role` INT UNSIGNED NOT NULL, -- overall platform role
    PRIMARY KEY (`id`),
    KEY (`name`),
    KEY (`ip`),
    KEY (`email`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `vkUsers` ( -- TODO
   `vkId` BIGINT NOT NULL,
   `firstName` VARCHAR(40) NOT NULL,
   `lastName` VARCHAR(40) NOT NULL,
   `closedProfile` TINYINT NOT NULL,
   `photo_50` VARCHAR(255) NOT NULL,
   `photo_100` VARCHAR(255) NOT NULL,
   `photo_200` VARCHAR(255) NOT NULL,
   `photo_max` VARCHAR(255) NOT NULL,
   PRIMARY KEY (`vkId`)
) ENGINE=MyISAM, charset=utf8;

CREATE TABLE `vkGroups` ( -- TODO
    `vkId` BIGINT NOT NULL,
    `name` VARCHAR(32) NOT NULL,
    `secret` VARCHAR(50) NOT NULL,
    `token` VARCHAR(85) NOT NULL,
    `adminVkId` BIGINT NOT NULL,
    `adminVkToken` BIGINT NOT NULL,
    `confirmationToken` VARCHAR(8) NOT NULL,
    PRIMARY KEY (`vkId`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `vkGroupManagers` ( -- TODO
    `vkGroupId` BIGINT NOT NULL,
    `userId` BIGINT UNSIGNED NOT NULL,
    `role` INT NOT NULL,
    FOREIGN KEY (`vkGroupId`) REFERENCES `vkGroups`(`vkId`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `vkMessageWhitelist` ( -- TODO
    `vkGroupId` BIGINT NOT NULL,
    `vkId` BIGINT NOT NULL, -- user or group id
    KEY (`vkGroupId`),
    KEY (`vkId`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `vkPostWhitelist` ( -- TODO
    `vkGroupId` BIGINT NOT NULL,
    `postVkId` BIGINT NOT NULL,
    KEY (`vkGroupId`),
    KEY (`postVkId`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `vkRestrictedAttachments` ( -- TODO
    `vkGroupId` BIGINT NOT NULL,
    `attachmentId` INT NOT NULL,
    KEY (`vkGroupId`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `vkGroupConfig` ( -- TODO
    `vkGroupId` BIGINT NOT NULL,
    `minMessageLength` INT UNSIGNED NOT NULL,
    `maxMessageLength` INT UNSIGNED NOT NULL,
    `spamBanDuration` INT UNSIGNED NOT NULL, -- 0 if bans are disabled
    PRIMARY KEY (`vkGroupId`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;