<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddForeignKeysToScheduleAiTables extends Migration
{
    public function up()
    {
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'google_accounts_user_id_fk');
        $this->forge->processIndexes('google_accounts');

        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'notification_settings_user_id_fk');
        $this->forge->processIndexes('notification_settings');
    }

    public function down()
    {
        $this->forge->dropForeignKey('google_accounts', 'google_accounts_user_id_fk');
        $this->forge->dropForeignKey('notification_settings', 'notification_settings_user_id_fk');
    }
}
