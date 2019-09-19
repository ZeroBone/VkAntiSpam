<?php

namespace VkAntiSpam\Event;

use VkAntiSpam\System\CommentChangeHandler;

class VkWallReplyEditEvent extends VkEvent {

    public function __construct($type, $object) {

        parent::__construct($type, $object);

    }

    public function handle($vkGroup) {

        $handler = new CommentChangeHandler($this->object);

        $handler->handle($vkGroup);

    }

}