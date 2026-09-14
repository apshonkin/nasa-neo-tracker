<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;

// Регистрация. Только для обычных юзеров, админа даем ручками.
class SignupForm extends Model
{
    public const ROLE = 'user';

    public string $username = '';
    public string $email = '';
    public string $password = '';

    public function rules(): array
    {
        return [
            [['username', 'email', 'password'], 'trim'],
            [['username', 'email', 'password'], 'required'],
            [['username'], 'string', 'min' => 3, 'max' => 64],
            [['username'], 'match', 'pattern' => '/^[\w.-]+$/', 'message' => 'Только буквы, цифры, точка, дефис и подчёркивание.'],
            [['username'], 'unique', 'targetClass' => User::class, 'message' => 'Такой логин уже занят.'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'unique', 'targetClass' => User::class, 'message' => 'На эту почту уже есть аккаунт.'],
            [['password'], 'string', 'min' => 6, 'max' => 72],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => 'Логин',
            'email' => 'E-mail',
            'password' => 'Пароль',
        ];
    }

    public function signup(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User(['username' => $this->username, 'email' => $this->email]);
        $user->setPassword($this->password);
        $user->generateAuthKey();

        $transaction = User::getDb()->beginTransaction();

        try {
            if (!$user->save()) {
                $transaction->rollBack();

                return null;
            }

            $auth = Yii::$app->authManager;
            $auth->assign($auth->getRole(self::ROLE), $user->id);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return $user;
    }
}
