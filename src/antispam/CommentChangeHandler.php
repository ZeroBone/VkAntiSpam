<?php

namespace VkAntiSpam\System;

use PDO;
use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\Utils\VkAttachment;
use VkAntiSpam\Utils\VkUtils;
use VkAntiSpam\VkAntiSpam;

class CommentChangeHandler {

    private $object;

    public function __construct($commentObject) {

        $this->object = $commentObject;

    }

    public function handle($vkGroup) {

        $commentId = (int)$this->object['id'];
        $commentText = stripslashes(trim((string)$this->object['text']));
        $commentAuthor = (int)$this->object['from_id'];

        // detect reply syntax
        $commentText = preg_replace('/\[(id)(\d+)\|([^\]]+)\]+/u', '$3', $commentText);

        if ($commentAuthor === -$vkGroup['vkId']) {
            // don't check messages from the group

            if (StringUtils::getStringLength($commentText) > 250 || $commentText === '') {
                // empty comment
                return;
            }

            if ((int)$vkGroup['learnFromOutcomingComments'] === 1) {
                // assume it's ham

                $antispam = new TextClassifier();

                $antispam->learn($commentText, TextClassifier::CATEGORY_HAM);

            }

            return;

        }

        if (isset($this->object['attachments'])) {

            $incomingAttachments = 0;

            $commentAttachmentsMap = VkAttachment::getVkIdToBitmaskCommentMapping();

            foreach ((array)$this->object['attachments'] as $commentAttachment) {

                $commentAttachmentType = (string)$commentAttachment['type'];

                if (!isset($commentAttachmentsMap[$commentAttachmentType])) {
                    continue;
                }

                $incomingAttachments |= $commentAttachmentsMap[$commentAttachmentType];

            }

            if ($incomingAttachments & (int)$vkGroup['restrictedAttachments'] !== 0) {
                // spam

                VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

                return;

            }

        }

        if (StringUtils::getStringLength($commentText) > (int)$vkGroup['maxMessageLength']) {

            VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

            return;

        }

        if (StringUtils::getStringLength($commentText) < (int)$vkGroup['minMessageLength']) {

            VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

            return;

        }

        if ((int)$vkGroup['deleteMessagesFromGroups'] === 1 && $commentAuthor < 0) {

            // message from group
            // TODO: search in whitelist

            VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

            return;

        }

        if (StringUtils::getStringLength($commentText) === 0) {

            // there is no point in analyzing empty text
            // nothing to do

            return;

        }

        $db = VkAntiSpam::get()->getDatabaseConnection();

        {

            // anti flood protection
            // it consists out of 2 checks

            // =======================
            // per-user flood check
            // =======================

            $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `messages` WHERE `type` = 1 AND `groupId` = ? AND `date` > ? AND `author` = ?');

            $query->execute([
                (int)$vkGroup['vkId'], // group id
                time() - 3600, // start date
                $commentAuthor // author
            ]);

            $lastHourMessagesCount = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

            if ($lastHourMessagesCount > 20) {

                // too many messages

                $query = $db->prepare('INSERT INTO `messages` (`groupId`, `type`, `vkId`, `author`, `message`, `messageHash`, `date`, `replyToUser`, `replyToMessage`, `vkContext`, `category`) VALUES (?,?,?,?,?,?,?,?,?,?,?);');

                $query->execute([
                    (int)$vkGroup['vkId'], // groupId
                    1, // type
                    $commentId, // vkId
                    $commentAuthor, // author
                    $commentText, // message
                    abs(crc32($commentText)), // message hash
                    time(), // date
                    isset($this->object['reply_to_user']) ? (int)$this->object['reply_to_user'] : 0,
                    isset($this->object['reply_to_comment']) ? (int)$this->object['reply_to_comment'] : 0,
                    (int)$this->object['post_id'], // vk context
                    TextClassifier::CATEGORY_DELETED
                ]);

                VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

                return;

            }

            // =======================
            // per-message flood check
            // =======================

            $commentTextHash = abs(crc32($commentText));

            // example
            // SELECT COUNT(*) AS `total`, COUNT(DISTINCT(`author`)) AS `fromUniqueAuthors` FROM `messages` WHERE `type` = 1 AND `groupId` = 85087785 AND `messageHash` = 1157730628
            // more advanced
            // SELECT COUNT(*) AS `total`, COUNT(DISTINCT(`author`)) AS `fromUniqueAuthors`, SUM(`author` = 516124954) AS `fromThisAuthor` FROM `messages` WHERE `type` = 1 AND `groupId` = 85087785 AND `messageHash` = 1157730628
            $query = $db->prepare('SELECT COUNT(*) AS `total`, COUNT(DISTINCT(`author`)) AS `fromUniqueAuthors`, SUM(`author` = ?) AS `fromThisAuthor` FROM `messages` WHERE `type` = 1 AND `groupId` = ? AND `messageHash` = ? AND `date` >= ? AND `message` = ?;');

            $query->execute([
                $commentAuthor, // author
                (int)$vkGroup['vkId'], // group id
                $commentTextHash, // message hash
                time() - 86400, // start date
                $commentText // message
            ]);

            $data = $query->fetch(PDO::FETCH_ASSOC);

            $totalMessageCount = (int)$data['total'];
            $uniqueAuthorMessageCount = (int)$data['fromUniqueAuthors'];
            $thisAuthorMessageCount = (int)$data['fromThisAuthor'];

            unset($data);

            if ($thisAuthorMessageCount >= 1) {

                // insert comment as deleted in database

                $query = $db->prepare('INSERT INTO `messages` (`groupId`, `type`, `vkId`, `author`, `message`, `messageHash`, `date`, `replyToUser`, `replyToMessage`, `vkContext`, `category`) VALUES (?,?,?,?,?,?,?,?,?,?,?);');

                $query->execute([
                    (int)$vkGroup['vkId'], // groupId
                    1, // type
                    $commentId, // vkId
                    $commentAuthor, // author
                    $commentText, // message
                    abs(crc32($commentText)), // message hash
                    time(), // date
                    isset($this->object['reply_to_user']) ? (int)$this->object['reply_to_user'] : 0,
                    isset($this->object['reply_to_comment']) ? (int)$this->object['reply_to_comment'] : 0,
                    (int)$this->object['post_id'], // vk context
                    TextClassifier::CATEGORY_DELETED
                ]);

                // the user has written a duplicating comment the second

                VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

                // reputation change

                $query = $db->prepare('UPDATE `vkUsers` SET `reputation` = `reputation` + ? WHERE `vkId` = ? LIMIT 1;');

                $query->execute([
                    Reputation::DUPLICATING_COMMENT,
                    $commentAuthor
                ]);

                return;

            }

            if ($uniqueAuthorMessageCount > 10 || $totalMessageCount > 15) {

                // insert comment as deleted in database

                $query = $db->prepare('INSERT INTO `messages` (`groupId`, `type`, `vkId`, `author`, `message`, `messageHash`, `date`, `replyToUser`, `replyToMessage`, `vkContext`, `category`) VALUES (?,?,?,?,?,?,?,?,?,?,?);');

                $query->execute([
                    (int)$vkGroup['vkId'], // groupId
                    1, // type
                    $commentId, // vkId
                    $commentAuthor, // author
                    $commentText, // message
                    abs(crc32($commentText)), // message hash
                    time(), // date
                    isset($this->object['reply_to_user']) ? (int)$this->object['reply_to_user'] : 0,
                    isset($this->object['reply_to_comment']) ? (int)$this->object['reply_to_comment'] : 0,
                    (int)$this->object['post_id'], // vk context
                    TextClassifier::CATEGORY_DELETED
                ]);

                VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

                return;

            }

        }

        $antispam = new TextClassifier();

        $category = $antispam->classify($commentText);

        if ($category == TextClassifier::CATEGORY_INVALID) {

            VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

            return;

        }

        // ham or spam

        if ($commentAuthor > 0) {

            $vkResponse = VkUtils::callVkApi($vkGroup['token'], 'users.get', [
                'user_ids' => $commentAuthor,
                'fields' => implode(',', [
                    'photo_50',
                    'photo_100',
                    'photo_200',
                    'photo_max'
                ])
            ]);

            // file_put_contents('test.json', json_encode($vkResponse));

            if (!isset($vkResponse['response'][0])) {
                return;
            }

            $vkResponse = $vkResponse['response'][0];

            $query = $db->prepare('SELECT `vkId` FROM `vkUsers` WHERE `vkId` = ? LIMIT 1;');
            $query->execute([
                $commentAuthor
            ]);

            if (isset($query->fetch(PDO::FETCH_ASSOC)['vkId'])) {

                // this user already exists

                // reputation change

                $query = $db->prepare('UPDATE `vkUsers` SET `reputation` = `reputation` + ? WHERE `vkId` = ? LIMIT 1;');

                $query->execute([
                    ($category === TextClassifier::CATEGORY_HAM) ? Reputation::CLASSIFIER_HAM : Reputation::CLASSIFIER_SPAM, // reputation change
                    $commentAuthor
                ]);

            }
            else {

                $query = $db->prepare('INSERT INTO `vkUsers` (vkId, `date`, firstName, lastName, closedProfile, photo_50, photo_100, photo_200, photo_max) VALUES (?,?,?,?,?,?,?,?,?);');
                $query->execute([
                    $commentAuthor,
                    time(),
                    $vkResponse['first_name'],
                    $vkResponse['last_name'],
                    $vkResponse['is_closed'] ? 1 : 0,
                    $vkResponse['photo_50'],
                    $vkResponse['photo_100'],
                    $vkResponse['photo_200'],
                    $vkResponse['photo_max']
                ]);

            }

        }

        $query = $db->prepare('INSERT INTO `messages` (`groupId`, `type`, `vkId`, `author`, `message`, `messageHash`, `date`, `replyToUser`, `replyToMessage`, `vkContext`, `category`) VALUES (?,?,?,?,?,?,?,?,?,?,?);');
        $query->execute([
            (int)$vkGroup['vkId'], // groupId
            1, // type
            $commentId, // vkId
            $commentAuthor, // author
            $commentText, // message
            abs(crc32($commentText)), // message hash
            time(), // date
            isset($this->object['reply_to_user']) ? (int)$this->object['reply_to_user'] : 0,
            isset($this->object['reply_to_comment']) ? (int)$this->object['reply_to_comment'] : 0,
            (int)$this->object['post_id'], // vk context
            ($category === TextClassifier::CATEGORY_HAM) ? TextClassifier::CATEGORY_INVALID : TextClassifier::CATEGORY_SPAM // category
        ]);

        $messageId = (int)$db->lastInsertId();

        if ($category === TextClassifier::CATEGORY_SPAM) {

            VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

            if ((int)$vkGroup['spamBanDuration'] !== 0) {

                $query = $db->prepare('INSERT INTO `bans` (`message`, `date`, `endDate`, `userId`) VALUES (?,?,?,?);');
                $query->execute([
                    $messageId,
                    time(),
                    time() + (int)$vkGroup['spamBanDuration'],
                    0
                ]);

                $banId = (int)$db->lastInsertId();

                VkUtils::banGroupUser(
                    $vkGroup['adminVkToken'],
                    (int)$vkGroup['vkId'],
                    $commentAuthor,
                    (int)$vkGroup['spamBanDuration'],
                    VkUtils::BAN_REASON_SPAM,
                    'Автоматический бан #' . $banId . '.',
                    1
                );

            }

        }

    }

}