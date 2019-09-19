<?php

namespace VkAntiSpam\Event;

use VkAntiSpam\System\CommentChangeHandler;

class VkWallReplyDeleteEvent extends VkEvent {

    public function __construct($type, $object) {

        parent::__construct($type, $object);

    }

    public function handle($vkGroup) {



    }

}