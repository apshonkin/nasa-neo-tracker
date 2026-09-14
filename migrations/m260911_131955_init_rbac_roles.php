<?php

declare(strict_types=1);

use yii\db\Migration;

//добавляем через authManager, чтобы хоть чуть автоматизировать
class m260911_131955_init_rbac_roles extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;

        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Управление пользователями';
        $auth->add($manageUsers);

        $runSync = $auth->createPermission('runSync');
        $runSync->description = 'Запуск синхронизации с NASA';
        $auth->add($runSync);

        $user = $auth->createRole('user');
        $user->description = 'Зарегистрированный пользователь';
        $auth->add($user);

        $admin = $auth->createRole('admin');
        $admin->description = 'Администратор';
        $auth->add($admin);

        // админ - это обычный пользователь плюс свои права,
        // поэтому роль вкладывается в роль, а не дублируется правами
        $auth->addChild($admin, $user);
        $auth->addChild($admin, $manageUsers);
        $auth->addChild($admin, $runSync);
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;

        foreach (['admin', 'user'] as $name) {
            $role = $auth->getRole($name);
            if ($role !== null) {
                $auth->remove($role);
            }
        }

        foreach (['manageUsers', 'runSync'] as $name) {
            $permission = $auth->getPermission($name);
            if ($permission !== null) {
                $auth->remove($permission);
            }
        }
    }
}
