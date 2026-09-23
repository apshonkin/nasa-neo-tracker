<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username Логин
 * @property string $auth_key Ключ автологина
 * @property string $password_hash Хеш пароля
 * @property string|null $password_reset_token Токен сброса пароля
 * @property string $email E-mail
 * @property int $status Статус
 * @property string|null $verification_token Токен подтверждения почты
 * @property int $created_at Зарегистрирован
 * @property int $updated_at Изменён
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const STATUS_DELETED = 0;
    public const STATUS_INACTIVE = 9;
    public const STATUS_ACTIVE = 10;

    // created_at и updated_at заполняются сами, числом
    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password_reset_token', 'verification_token'], 'default', 'value' => null],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['username', 'auth_key', 'password_hash', 'email'], 'required'],
            // created_at и updated_at не перечисляем: их заполняет TimestampBehavior
            // уже после валидации, и с required новая запись не прошла бы проверку
            [['status'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            [['email'], 'email'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'verification_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
            [['username'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'auth_key' => 'Ключ автологина',
            'password_hash' => 'Хеш пароля',
            'password_reset_token' => 'Токен сброса пароля',
            'email' => 'E-mail',
            'status' => 'Статус',
            'verification_token' => 'Токен подтверждения почты',
            'created_at' => 'Зарегистрирован',
            'updated_at' => 'Изменён',
        ];
    }

    // --- то, чего требует IdentityInterface ---

    public static function findIdentity($id): self|null
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null): static|null
    {
        // вход по токену в проекте не используется
        throw new \yii\base\NotSupportedException('Аутентификация по access token не поддерживается.');
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getAuthKey(): string|null
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    // --- вход и пароль ---

    public static function findByUsername(string $username): self|null
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}
