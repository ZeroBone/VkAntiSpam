<?php

namespace VkAntiSpam\Event;

use VkAntiSpam\System\CommentChangeHandler;
use VkAntiSpam\VkAntiSpam;

class VkWallReplyEditEvent extends VkEvent {

    public function __construct($type, $object) {

        parent::__construct($type, $object);

    }

    public function handle($vkGroup) {

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('DELETE FROM `messages` WHERE `type` = 1 AND `vkId` = ? AND `vkContext` = ? LIMIT 1;');

        $query->execute([
            (int)$this->object['id'],
            (int)$this->object['post_id']
        ]);

        $handler = new CommentChangeHandler($this->object);

        $handler->handle($vkGroup);

    }

}