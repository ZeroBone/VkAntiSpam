<?php

namespace VkAntiSpam;

use mysql_xdevapi\Exception;
use PDO;
use VkAntiSpam\Config\VkAntiSpamConfig;
use VkAntiSpam\Config\VkAntiSpamGroupConfig;
use VkAntiSpam\Event\VkEvent;
use VkAntiSpam\Event\VkWallReplyNewEvent;

class VkAntiSpam {

    /**
     * @var VkAntiSpam
     */
    private static $instance = null;

    /**
     * @var VkAntiSpamConfig
     */
    public $config;

    /**
     * @var \PDO
     */
    private $db = null;

    /**
     * VkAntiSpam constructor.
     * @param $config VkAntiSpamConfig
     */
    public function __construct($config) {

        if (VkAntiSpam::$instance !== null) {
            throw new Exception('Multiple instances violate singleton.');
        }

        VkAntiSpam::$instance = $this;

        $this->config = $config;

    }

    public function run() {

        ignore_user_abort(true);

        set_time_limit(80);

        $vkEvent = json_decode(file_get_contents('php://input'), true);

        if (!$vkEvent) {

            echo 'ok';

            exit(0);

        }

        $vkEvent['type'] = (string)$vkEvent['type'];
        $vkEvent['group_id'] = (int)$vkEvent['group_id'];

        $response = 'ok';

        /**
         * @var $event VkEvent
         */
        $event = null;

        /**
         * @var $groupConfig VkAntiSpamGroupConfig
         */
        foreach ($this->config->groups as $groupId => $groupConfig) {

            if ((int)$groupId === $vkEvent['group_id']) {

                $groupConfig->vkId = (int)$groupId;

                if ($vkEvent['type'] === 'confirmation') {

                    $response = $groupConfig->confirmationToken;

                }
                else {

                    $vkEvent['secret'] = (string)$vkEvent['secret'];
                    $vkEvent['object'] = (array)$vkEvent['object'];

                    if (!hash_equals($groupConfig->secret, $vkEvent['secret'])) {

                        throw new Exception('Invalid key');

                    }

                    switch ($vkEvent['type']) {

                        case 'wall_reply_new':

                            $event = new VkWallReplyNewEvent($vkEvent['type'], $vkEvent['object']);

                            break;

                        default:
                            break;

                    }

                }

                break;

            }

        }

        unset($groupId);

        echo $response;

        unset($response);
        unset($vkEvent);

        /*session_write_close();
        fastcgi_finish_request();*/ // TODO: uncomment

        ////////////////////////////////

        // ob_end_flush();
        // ob_flush();
        // ob_implicit_flush(true);
        // flush();

        if ($event !== null) {
            $event->handle($groupConfig);
        }

    }

    public function getDatabaseConnection() {

        if ($this->db === null) {

            $this->db = new PDO(
                'mysql:host=' . $this->config->dbHost . ';dbname=' . $this->config->dbName . ';charset=' . $this->config->dbCharset,
                $this->config->dbUser,
                $this->config->dbPassword
            );

        }

        return $this->db;

    }

    public static function get() {
        return VkAntiSpam::$instance;
    }

}