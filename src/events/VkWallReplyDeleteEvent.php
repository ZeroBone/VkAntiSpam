<?php

namespace VkAntiSpam\Event;

use PDO;
use VkAntiSpam\System\TextClassifier;
use VkAntiSpam\VkAntiSpam;

class VkWallReplyDeleteEvent extends VkEvent {

    public function __construct($type, $object) {

        parent::__construct($type, $object);

    }

    private function deleteMessageFromDb($messageId) {

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('DELETE FROM `messages` WHERE `id` = ?;');

        $query->execute([
            $messageId
        ]);

    }

    public function handle($vkGroup) {

        $commentId = (int)$this->object['id'];
        $postId = (int)$this->object['post_id'];
        $deleterId = (int)$this->object['deleter_id'];
        // $ownerId = (int)$this->object['owner_id'];

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('SELECT * FROM `messages` WHERE `type` = 1 AND `vkId` = ? AND `context` = ? LIMIT 1;');

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

        $classifier = new TextClassifier();

        $classifier->learn($message['message'], TextClassifier::CATEGORY_SPAM);

        $this->deleteMessageFromDb((int)$message['id']);

    }

}