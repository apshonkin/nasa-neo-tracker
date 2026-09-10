<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "apod".
 *
 * @property int $id
 * @property string $date Дата публикации
 * @property string $title Название
 * @property string|null $explanation Описание снимка
 * @property string $media_type Тип медиа
 * @property string|null $url Ссылка на медиа
 * @property string|null $hdurl Ссылка в HD
 * @property string|null $thumbnail_url Превью для видео
 * @property string|null $copyright Автор снимка
 * @property string $created_at Загружено
 */
class Apod extends \yii\db\ActiveRecord
{
    public const MEDIA_IMAGE = 'image';
    public const MEDIA_VIDEO = 'video';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'apod';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['explanation', 'url', 'hdurl', 'thumbnail_url', 'copyright'], 'default', 'value' => null],
            [['media_type'], 'default', 'value' => self::MEDIA_IMAGE],
            [['date', 'title'], 'required'],
            [['date', 'created_at'], 'safe'],
            // Проверяем формат до правила unique: иначе unique пойдёт искать
            // мусор в базе и валидация упадёт ошибкой PostgreSQL.
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['explanation', 'url', 'hdurl', 'thumbnail_url', 'copyright'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['media_type'], 'string', 'max' => 32],
            [['date'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Дата публикации',
            'title' => 'Название',
            'explanation' => 'Описание снимка',
            'media_type' => 'Тип медиа',
            'url' => 'Ссылка на медиа',
            'hdurl' => 'Ссылка в HD',
            'thumbnail_url' => 'Превью для видео',
            'copyright' => 'Автор снимка',
            'created_at' => 'Загружено',
        ];
    }

    public function isImage(): bool
    {
        return $this->media_type === self::MEDIA_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->media_type === self::MEDIA_VIDEO;
    }

    // Что показывать в списке: у видео вместо картинки превью, если оно пришло.
    public function getPreviewUrl(): ?string
    {
        return $this->isVideo() ? $this->thumbnail_url : $this->url;
    }
}
