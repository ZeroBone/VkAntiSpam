<?php

namespace VkAntiSpam\System;

use PDO;
use VkAntiSpam\Utils\StringUtils;
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
            // assume it's ham

            if (StringUtils::getStringLength($commentText) > 250 || $commentText === '') {
                // empty comment
                return;
            }

            $antispam = new TextClassifier();

            $antispam->learn($commentText, TextClassifier::CATEGORY_HAM);

            return;

        }

        if (isset($this->object['attachments'])) {
            foreach ((array)$this->object['attachments'] as $commentAttachment) {

                switch ($commentAttachment['type']) {

                    case 'link':

                        VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

                        return;

                    case 'video':
                    case 'photo':
                    case 'posted_photo':
                    case 'photos_list':
                    case 'page':

                        // VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

                        return;

                    default:
                        break;


                }

            }
        }

        if (StringUtils::getStringLength($commentText) > 250) {

            VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

            return;

        }

        if ($commentAuthor < 0) {

            // message from group

            VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

            return;

        }

        if (StringUtils::getStringLength($commentText) === 0) {

            // there is no point in analyzing empty text
            // nothing to do

            return;

        }

        $antispam = new TextClassifier();

        $category = $antispam->classify($commentText);

        if ($category == TextClassifier::CATEGORY_INVALID) {

            VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

            return;

        }

        // ham or spam

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

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('SELECT `vkId` FROM `vkUsers` WHERE `vkId` = ? LIMIT 1;');
        $query->execute([
            $commentAuthor
        ]);

        if (isset($query->fetch(PDO::FETCH_ASSOC)['vkId'])) {

            // this user already exists

            // TODO: consider reputation change

        }
        else {

            $query = $db->prepare('INSERT INTO `vkUsers` (vkId, firstName, lastName, closedProfile, photo_50, photo_100, photo_200, photo_max) VALUES (?,?,?,?,?,?,?,?);');
            $query->execute([
                $commentAuthor,
                $vkResponse['first_name'],
                $vkResponse['last_name'],
                $vkResponse['is_closed'] ? 1 : 0,
                $vkResponse['photo_50'],
                $vkResponse['photo_100'],
                $vkResponse['photo_200'],
                $vkResponse['photo_max']
            ]);

        }

        $query = $db->prepare('INSERT INTO `messages` (`groupId`, `type`, `vkId`, `author`, `message`, `messageHash`, `date`, `replyToUser`, `replyToMessage`, `vkContext`, `category`) VALUES (?,?,?,?,?,?,?,?,?,?,?);');
        $query->execute([
            $vkGroup['vkId'], // groupId
            1, // type
            $commentId, // vkId
            $commentAuthor, // author
            $commentText, // message
            abs(crc32($commentText)), // message hash
            time(), // date
            isset($this->object['reply_to_user']) ? (int)$this->object['reply_to_user'] : 0,
            isset($this->object['reply_to_comment']) ? (int)$this->object['reply_to_comment'] : 0,
            (int)$this->object['post_id'], // context
            ($category === TextClassifier::CATEGORY_HAM) ? TextClassifier::CATEGORY_INVALID : TextClassifier::CATEGORY_SPAM // category
        ]);

        if ($category === TextClassifier::CATEGORY_SPAM) {

            VkUtils::deleteGroupComment($vkGroup['adminVkToken'], $vkGroup['vkId'], $commentId);

            $messageId = (int)$db->lastInsertId();

            $query = $db->prepare('INSERT INTO `bans` (`message`, `date`) VALUES (?,?);');
            $query->execute([
                $messageId,
                time()
            ]);

        }

    }

}