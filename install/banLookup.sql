SELECT `bans`.`id` AS `banId`, `bans`.`date` AS `banDate`, `messages`.* FROM `bans`, `messages` WHERE `bans`.`message` = `messages`.`id`;