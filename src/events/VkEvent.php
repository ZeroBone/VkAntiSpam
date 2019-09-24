<?php

namespace VkAntiSpam\Event;

use VkAntiSpam\Config\VkAntiSpamGroupConfig;

abstract class VkEvent {

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $object;

    public function __construct($type, $object) {

        $this->type = $type;

        $this->object = $object;

    }

    /**
     * @param $vkGroup VkAntiSpamGroupConfig
     * @return mixed
     */
    public abstract function handle($vkGroup);

}