<?php

namespace VkAntiSpam\Utils;


class VkAttachment {

    const PHOTO = 1;

    const VIDEO = 1 << 1;

    const AUDIO = 1 << 2;

    const DOC = 1 << 3;

    const GRAFFITY = 1 << 4;

    const LINK = 1 << 5;

    const NOTE = 1 << 6;

    const APP = 1 << 7;

    const POLL = 1 << 8;

    const PAGE = 1 << 9;

    const ALBUM = 1 << 10;

    const PHOTOS_LIST = 1 << 11;

    const MARKET = 1 << 12;

    const MARKET_ALBUM = 1 << 13;

    const STICKER = 1 << 14;

    const PRETTY_CARDS = 1 << 15;

    const EVENT = 1 << 16;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $vkId;

    /**
     * @var string
     */
    public $title;

    private function __construct($vkId, $title) {
        $this->vkId = $vkId;
        $this->title = $title;
    }

    public static function getAttachments() {

        return [
            static::PHOTO => new self('photo', 'Фотография'),
            static::VIDEO => new self('video', 'Видеозапись'),
            static::AUDIO => new self('audio', 'Аудиозапись'),
            static::DOC => new self('doc', 'Документ'),
            static::GRAFFITY => new self('graffity', 'Граффити'),
            static::LINK => new self('link', 'Ссылка'),
            static::NOTE => new self('note', 'Заметка'),
            static::APP => new self('app', 'Контент приложения'),
            static::POLL => new self('poll', 'Опрос'),
            static::PAGE => new self('page', 'Вики-страница'),
            static::ALBUM => new self('album', 'Альбом с фотографиями'),
            static::PHOTOS_LIST => new self('photos_list', 'Список фотографий'),
            static::MARKET => new self('market', 'Товар'),
            static::MARKET_ALBUM => new self('market_album', 'Подборка товаров'),
            static::STICKER => new self('sticker', 'Стикер'),
            static::PRETTY_CARDS => new self('pretty_cards', 'Карточки'),
            static::EVENT => new self('event', 'Встреча'),
        ];

    }

    public static function getCommentAttachments() {

        return [
            static::PHOTO => new self('photo', 'Фотография'),
            static::VIDEO => new self('video', 'Видеозапись'),
            static::AUDIO => new self('audio', 'Аудиозапись'),
            static::DOC => new self('doc', 'Документ'),
            static::GRAFFITY => new self('graffity', 'Граффити'),
            static::LINK => new self('link', 'Ссылка'),
            static::ALBUM => new self('album', 'Альбом с фотографиями'),
            static::PHOTOS_LIST => new self('photos_list', 'Список фотографий'),
            static::STICKER => new self('sticker', 'Стикер')
        ];

    }

    public static function getVkIdToBitmaskMapping() {

        return [
            'photo' => static::PHOTO,
            'video' => static::VIDEO,
            'audio' => static::AUDIO,
            'doc' => static::DOC,
            'graffity' => static::GRAFFITY,
            'link' => static::LINK,
            'note' => static::NOTE,
            'app' => static::APP,
            'poll' => static::POLL,
            'page' => static::PAGE,
            'album' => static::ALBUM,
            'photos_list' => static::PHOTOS_LIST,
            'market' => static::MARKET,
            'market_album' => static::MARKET_ALBUM,
            'sticker' => static::STICKER,
            'pretty_cards' => static::PRETTY_CARDS,
            'event' => static::EVENT
        ];

    }

    public static function getVkIdToBitmaskCommentMapping() {

        return [
            'photo' => static::PHOTO,
            'video' => static::VIDEO,
            'audio' => static::AUDIO,
            'doc' => static::DOC,
            'graffity' => static::GRAFFITY,
            'link' => static::LINK,
            'album' => static::ALBUM,
            'photos_list' => static::PHOTOS_LIST,
            'sticker' => static::STICKER
        ];

    }

}