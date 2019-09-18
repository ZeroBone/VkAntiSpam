<?php

namespace VkAntiSpam\Event;


use VkAntiSpam\System\AntiSpamSystem;
use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\Utils\VkUtils;
use VkAntiSpam\VkAntiSpam;

class VkWallReplyNewEvent extends VkEvent {

    public function __construct($type, $object) {

        parent::__construct($type, $object);

    }

    private function banUserForSpam($group) {}

    public function handle($vkGroup) {

        if ($this->object['from_id'] === -$vkGroup->vkId) {
            // don't check messages from the group
            return;
        }

        $commentId = (int)$this->object['id'];
        $commentText = htmlentities(stripslashes(trim((string)$this->object['text'])), ENT_QUOTES, 'utf-8');
        $commentAuthor = (int)$this->object['from_id'];

        foreach ((array)$this->object['attachments'] as $commentAttachment) {

            switch ($commentAttachment['type']) {

                case 'video':
                case 'link':
                case 'photo':

                    VkUtils::deleteGroupComment($vkGroup->token, $vkGroup->vkId, $commentId);

                    return;

                default:
                    break;


            }

        }

        if (StringUtils::getStringLength($commentText) > 250) {

            VkUtils::deleteGroupComment($vkGroup->token, $vkGroup->vkId, $commentId);

            return;

        }

        if ($commentAuthor < 0) {

            // message from group

            VkUtils::deleteGroupComment($vkGroup->token, $vkGroup->vkId, $commentId);

            return;

        }

        if (AntiSpamSystem::textInvalid($commentText)) {

            VkUtils::deleteGroupComment($vkGroup->token, $vkGroup->vkId, $commentId);

            return;

        }

        $db = VkAntiSpam::get()->getDatabaseConnection();
        $query = $db->prepare('INSERT INTO `messages` (`type`, `vkId`, `author`, `message`, `date`, `replyToUser`, `replyToMessage`, `context`) VALUES (?,?,?,?,?,?,?,?);');
        $query->execute([
            1, // type
            $commentId, // vkId
            $commentAuthor, // author
            $commentText, // message
            time(), // date
            isset($this->object['reply_to_user']) ? (int)$this->object['reply_to_user'] : 0,
            isset($this->object['reply_to_comment']) ? (int)$this->object['reply_to_comment'] : 0,
            $this->object['post_id'] // context
        ]);

        $messageId = (int)$db->lastInsertId();

        $antispam = new AntiSpamSystem();

        $spammieness = $antispam->classify($commentText);

        if ($spammieness >= 0.75) {

            VkUtils::deleteGroupComment($vkGroup->token, $vkGroup->vkId, $commentId);

            $query = $db->prepare('INSERT INTO `bans` (`message`, `date`) VALUES (?,?);');
            $query->execute([
                $messageId,
                time()
            ]);

            /*VkUtils::banGroupUser(
                $vkGroup->token,
                $vkGroup->vkId,
                $commentAuthor,
                3600 * 24 * 7,
                1,
                'Бан',
                1
            );*/

            return;

        }

    }

    /*public static function textClean($text) {

        $text = trim(mb_strtolower($text, 'UTF-8'));

        // $text = preg_replace('/[^a-zа-я0-9]/', '', $text);
        // $text = preg_replace('/[^a-zA-ZА-Яа-я0-9\s]/','', $text);
        $text = preg_replace('/[^a-zа-яё\d]+/iu','', $text);

        return $text;

    }*/

}