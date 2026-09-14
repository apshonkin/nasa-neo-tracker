<?php

declare(strict_types=1);

use yii\db\Migration;

class m260911_143517_add_donation_stats_permission extends Migration
{
    private const PERMISSION = 'viewDonationStats';

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;

        $permission = $auth->createPermission(self::PERMISSION);
        $permission->description = 'Просмотр статистики поддержки';
        $auth->add($permission);

        $auth->addChild($auth->getRole('admin'), $permission);
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(self::PERMISSION);

        if ($permission !== null) {
            $auth->remove($permission);
        }
    }
}
