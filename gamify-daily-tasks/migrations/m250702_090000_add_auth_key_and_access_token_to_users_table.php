<?php

use yii\db\Migration;

/**
 * Handles adding columns `auth_key` and `access_token` to table `{{%users}}`.
 */
class m250702_090000_add_auth_key_and_access_token_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%users}}', 'auth_key', $this->string(32)->null()->after('email'));
        $this->addColumn('{{%users}}', 'access_token', $this->string(255)->unique()->null()->after('auth_key'));
        // It might be good to also add password_reset_token for completeness, but sticking to plan for now.
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'access_token');
        $this->dropColumn('{{%users}}', 'auth_key');
    }
}
