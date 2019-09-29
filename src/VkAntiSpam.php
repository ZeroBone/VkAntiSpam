<?php

namespace VkAntiSpam;

use \Exception;
use PDO;
use VkAntiSpam\Account\Account;
use VkAntiSpam\Config\VkAntiSpamConfig;
use VkAntiSpam\Config\VkAntiSpamGroupConfig;
use VkAntiSpam\Event\VkEvent;
use VkAntiSpam\Event\VkWallReplyDeleteEvent;
use VkAntiSpam\Event\VkWallReplyEditEvent;
use VkAntiSpam\Event\VkWallReplyNewEvent;
use VkAntiSpam\Event\VkWallReplyRestoreEvent;

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
     * @var Account
     */
    public $account = null;

    /**
     * VkAntiSpam constructor.
     * @param $config VkAntiSpamConfig
     * @throws Exception
     */
    private function __construct($config) {

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

        $eventType = (string)$vkEvent['type'];
        $groupId = (int)$vkEvent['group_id'];

        $db = static::getDatabaseConnection();

        $query = $db->prepare('SELECT * FROM `vkGroups` WHERE `vkId` = ? LIMIT 1;');
        $query->execute([
            $groupId
        ]);

        $vkGroup = $query->fetch(PDO::FETCH_ASSOC);

        if (!isset($vkGroup['vkId'])) {
            echo 'ok';
            exit(0);
        }

        if ($eventType === 'confirmation') {
            echo $vkGroup['confirmationToken'];
            exit(0);
        }

        $submSecret = (string)$vkEvent['secret'];
        $submObject = (array)$vkEvent['object'];

        unset($vkEvent);

        if (!hash_equals($vkGroup['secret'], $submSecret)) {
            echo 'ok';
            exit(0);
        }

        /**
         * @var $event VkEvent
         */
        $event = null;

        switch ($eventType) {

            case 'wall_reply_new':

                $event = new VkWallReplyNewEvent($eventType, $submObject);

                break;

            case 'wall_reply_edit':

                $event = new VkWallReplyEditEvent($eventType, $submObject);

                break;

            case 'wall_reply_restore':

                $event = new VkWallReplyRestoreEvent($eventType, $submObject);

                break;

            case 'wall_reply_delete':

                $event = new VkWallReplyDeleteEvent($eventType, $submObject);

                break;

            default:
                break;

        }

        echo 'ok';

        /*session_write_close();
        fastcgi_finish_request();*/ // TODO: uncomment

        ////////////////////////////////

        // ob_end_flush();
        // ob_flush();
        // ob_implicit_flush(true);
        // flush();

        if ($event !== null) {
            $event->handle($vkGroup);
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

    public static function vkCallback() {

        return new self(require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php');

    }

    public static function web() {

        $instance = new self(require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php');

        $instance->account = new Account();

        return $instance;

    }

}