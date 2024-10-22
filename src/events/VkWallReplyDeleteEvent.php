<?php

namespace VkAntiSpam\Event;

use PDO;
use VkAntiSpam\System\Reputation;
use VkAntiSpam\System\TextClassifier;
use VkAntiSpam\VkAntiSpam;

class VkWallReplyDeleteEvent extends VkEvent {

    public function __construct($type, $object) {

        parent::__construct($type, $object);

    }

    private function deleteMessageFromDb($messageId) {

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('UPDATE `messages` SET `category` = ? WHERE `id` = ?;');

        $query->execute([
            TextClassifier::CATEGORY_DELETED,
            $messageId
        ]);

    }

    public function handle($vkGroup) {

        $commentId = (int)$this->object['id'];
        $postId = (int)$this->object['post_id'];
        $deleterId = (int)$this->object['deleter_id'];
        // $ownerId = (int)$this->object['owner_id'];

        if ($deleterId === (int)$vkGroup['adminVkId']) {
            // the response is issued by us
            // everything that had to be done has already been done
            return;
        }

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('SELECT `id`, `author`, `message` FROM `messages` WHERE `type` = 1 AND `vkId` = ? AND `vkContext` = ? LIMIT 1;');

        $query->execute([
            $commentId,
            $postId
        ]);

        $message = $query->fetch(PDO::FETCH_ASSOC);

        if (!isset($message['id'])) {
            return;
        }

        // check author, may be the user deleted the comment
        // we care only about deletions from admins

        if ((int)$message['author'] === $deleterId) {
            // the user deleted his own comment

            $this->deleteMessageFromDb((int)$message['id']);

            return;
        }

        if ((int)$vkGroup['learnFromDeletedComments'] === 1) {

            $classifier = new TextClassifier();

            $classifier->learn($message['message'], TextClassifier::CATEGORY_SPAM);

            // update reputation

            $query = $db->prepare('UPDATE `vkUsers` SET `reputation` = `reputation` + ? WHERE `vkId` = ? LIMIT 1;');

            $query->execute([
                Reputation::ADMIN_DELETES,
                (int)$message['author']
            ]);

        }

        $this->deleteMessageFromDb((int)$message['id']);

    }

}