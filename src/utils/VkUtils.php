<?php

namespace VkAntiSpam\Utils;

use CURLFile;

class VkUtils {

    public static function callVkApi($access_token, $method, $params, $execute=false, $call_method='POST') {

        $url = 'https://api.vk.com/method/';

        if ($execute) {

            $url = $url . 'execute.';
        }

        $url = $url . $method;

        if (!isset($params['v'])) {

            $params['v'] = 5.69;
        }

        if (!isset($params['access_token']) and $access_token !== false) {

            $params['access_token'] = $access_token;
        }

        $result = file_get_contents($url, false, stream_context_create([
            'http' => [
                'method'  => $call_method,
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($params)
            ]
        ]));

        return json_decode($result, true);

    }

    public static function uploadImageToVkMessages($accessToken, $imagePath, $vkId) {

        $json = static::callVkApi($accessToken, 'photos.getMessagesUploadServer', [
            'peer_id' => $vkId
        ]);

        $ch = curl_init($json['response']['upload_url']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $file = new CurlFile($imagePath);

        curl_setopt($ch, CURLOPT_POSTFIELDS, ['photo' => $file]);

        $photoUploadResponse = curl_exec($ch);

        $json = json_decode($photoUploadResponse, true);

        return static::callVkApi($accessToken, 'photos.saveMessagesPhoto', [
            'photo' => $json['photo'],
            'server' => $json['server'],
            'hash' => $json['hash']
        ]);

        // attachment: 'photo'.$json['response'][0]['owner_id'].'_'.$json['response'][0]['id']


    }

    public static function uploadImageToVkWall($accessToken, $imagePath, $groupId) {

        $json = static::callVkApi($accessToken, 'photos.getWallUploadServer', [
            'group_id' => $groupId
        ]);

        $ch = curl_init($json['response']['upload_url']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $file = new CurlFile($imagePath);

        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'photo' => $file
        ]);

        $photoUploadResponse = curl_exec($ch);

        $json = json_decode($photoUploadResponse, true);

        return static::callVkApi($accessToken, 'photos.saveWallPhoto', [
            'group_id' => $groupId,
            'photo' => $json['photo'],
            'server' => $json['server'],
            'hash' => $json['hash']
        ]);

        // attachment: 'photo'.$json['response'][0]['owner_id'].'_'.$json['response'][0]['id']


    }

    public static function banGroupUser($token, $groupId, $userId, $time = 0, $reason = 0, $banComment = '', $banCommentVisible = 0) {

        $parameters = [
            'group_id' => $groupId,
            'user_id' => $userId,
            'reason' => $reason,
            'comment' => $banComment,
            'comment_visible' => $banCommentVisible
        ];

        if ($time !== 0) {

            $parameters['end_date'] = time() + $time;

        }

        return static::callVkApi($token, 'groups.banUser', $parameters);

    }

    public static function deleteGroupComment($token, $groupId, $commentId) {

        return static::callVkApi($token, 'wall.deleteComment', [
            'owner_id' => -$groupId,
            'comment_id' => $commentId
        ]);

        // return $res;

    }

}