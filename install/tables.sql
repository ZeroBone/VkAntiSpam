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
    KEY (`groupId`),
    KEY (`type`),
    KEY (`author`),
    KEY (`messageHash`),
    KEY (`date`),
    KEY (`category`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `bans` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `message` BIGINT UNSIGNED NOT NULL, -- message the user was banned for
    `date` BIGINT UNSIGNED NOT NULL, -- ban date
    `endDate` BIGINT UNSIGNED NOT NULL, -- 0 if ban is permanent
    `userId` BIGINT UNSIGNED NOT NULL, -- user that banned, 0 if the ban was automatic
    PRIMARY KEY (`id`),
    FOREIGN KEY (`message`) REFERENCES `messages`(`id`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`)
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

CREATE TABLE `vkUsers` (
   `vkId` BIGINT NOT NULL,
   `date` BIGINT UNSIGNED NOT NULL, -- insert date
   `firstName` VARCHAR(40) NOT NULL,
   `lastName` VARCHAR(40) NOT NULL,
   `closedProfile` TINYINT NOT NULL,
   `photo_50` VARCHAR(255) NOT NULL,
   `photo_100` VARCHAR(255) NOT NULL,
   `photo_200` VARCHAR(255) NOT NULL,
   `photo_max` VARCHAR(255) NOT NULL,
   `wasUnbanned` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
   `reputation` BIGINT NOT NULL DEFAULT 0,
   PRIMARY KEY (`vkId`),
   KEY (`reputation`)
) ENGINE=MyISAM, charset=utf8;

CREATE TABLE `vkGroups` (
    `vkId` BIGINT NOT NULL,
    `name` VARCHAR(32) NOT NULL,
    `secret` VARCHAR(50) NOT NULL,
    `token` VARCHAR(85) NOT NULL,
    `adminVkId` BIGINT NOT NULL,
    `adminVkToken` VARCHAR(85) NOT NULL,
    `confirmationToken` VARCHAR(8) NOT NULL,
    -- config
    `minMessageLength` INT UNSIGNED NOT NULL DEFAULT 0,
    `maxMessageLength` INT UNSIGNED NOT NULL DEFAULT 255,
    `restrictedAttachments` BIGINT UNSIGNED NOT NULL DEFAULT 0, -- bitmask of restricted attachments
    `spamBanDuration` INT UNSIGNED NOT NULL DEFAULT 0, -- 0 if bans are disabled
    `adminBanDuration` INT UNSIGNED NOT NULL DEFAULT 0, -- 0 if bans are disabled
    `learnFromOutcomingComments` TINYINT(1) NOT NULL DEFAULT 0,
    `learnFromDeletedComments` TINYINT(1) NOT NULL DEFAULT 0,
    `deleteMessagesFromGroups` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`vkId`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `vkGroupManagers` (
    `vkGroupId` BIGINT NOT NULL,
    `userId` BIGINT UNSIGNED NOT NULL,
    `role` INT NOT NULL,
    FOREIGN KEY (`vkGroupId`) REFERENCES `vkGroups`(`vkId`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`),
    KEY (`vkGroupId`),
    KEY (`userId`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `vkMessageWhitelist` (
    `vkGroupId` BIGINT NOT NULL,
    `vkId` BIGINT NOT NULL, -- user or group id
    `whitelister` BIGINT UNSIGNED NOT NULL, -- who added this entry to the whitelist
    KEY (`vkGroupId`),
    KEY (`vkId`),
    FOREIGN KEY (`whitelister`) REFERENCES `users`(`id`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `vkPostWhitelist` (
    `vkGroupId` BIGINT NOT NULL,
    `postVkId` BIGINT NOT NULL,
    `whitelister` BIGINT UNSIGNED NOT NULL, -- who added this entry to the whitelist
    KEY (`vkGroupId`),
    KEY (`postVkId`),
    FOREIGN KEY (`whitelister`) REFERENCES `users`(`id`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;