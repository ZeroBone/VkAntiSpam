CREATE TABLE `messages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` INT UNSIGNED NOT NULL, -- 1 - vk post comment
    `vkId` BIGINT NOT NULL,
    `author` BIGINT NOT NULL, -- author of the message
    `message` TEXT NOT NULL,
    `date` BIGINT UNSIGNED NOT NULL, -- message date
    `replyToUser` BIGINT NOT NULL, -- vk id of the user this message was replied to, 0 if not replied,
    `replyToMessage` BIGINT NOT NULL, -- vk id of the message, 0 if unknown
    `context` BIGINT NOT NULL, -- if type == 1, this is the vk post id
    PRIMARY KEY (`id`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;

CREATE TABLE `bans` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `message` BIGINT UNSIGNED NOT NULL, -- message the user was banned for
    `date` BIGINT UNSIGNED NOT NULL, -- ban date
    PRIMARY KEY (`id`),
    FOREIGN KEY (`message`) REFERENCES `messages`(`id`)
) ENGINE=MyISAM, charset=utf8, AUTO_INCREMENT=1;