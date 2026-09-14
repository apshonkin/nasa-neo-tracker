<?php

declare(strict_types=1);

namespace app\models\forms;

use yii\base\Model;

// Форма поддержки: только то, что вводит человек.
class DonationForm extends Model
{
    public string|float|null $amount = 500;
    public ?string $comment = null;

    public function rules(): array
    {
        return [
            [['comment'], 'trim'],
            [['amount'], 'required'],
            [['amount'], 'number', 'min' => 1, 'max' => 1000000],
            [['comment'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'amount' => 'Сумма, ₽',
            'comment' => 'Комментарий',
        ];
    }
}
