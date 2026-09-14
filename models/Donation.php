<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "donation".
 *
 * @property int $id
 * @property int $user_id Кто поддержал
 * @property float $amount Сумма, ₽
 * @property string|null $comment Комментарий
 * @property string $status Статус
 * @property string|null $payment_id ID платежа в шлюзе
 * @property string $created_at Создан
 * @property string|null $paid_at Оплачен
 *
 * @property User $user
 */
class Donation extends \yii\db\ActiveRecord
{
    // pending - платёж создан, но денег ещё нет; paid - шлюз подтвердил оплату
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'donation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['comment', 'payment_id', 'paid_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['user_id', 'amount'], 'required'],
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'paid_at'], 'safe'],
            [['comment'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 16],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_PAID]],
            [['amount'], 'number', 'min' => 1, 'max' => 1000000],
            [['payment_id'], 'string', 'max' => 64],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Кто поддержал',
            'amount' => 'Сумма, ₽',
            'comment' => 'Комментарий',
            'status' => 'Статус',
            'payment_id' => 'ID платежа в шлюзе',
            'created_at' => 'Создан',
            'paid_at' => 'Оплачен',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    // вызывается только после того, как оплату подтвердил сам шлюз
    public function markPaid(): bool
    {
        $this->status = self::STATUS_PAID;
        $this->paid_at = new \yii\db\Expression('now()');

        return $this->save(false);
    }
}
