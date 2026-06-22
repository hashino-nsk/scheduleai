<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGoogleAccountsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'google_email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'access_token' => ['type' => 'TEXT', 'null' => true],
            'refresh_token' => ['type' => 'TEXT', 'null' => true],
            'token_expires_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('google_accounts');
    }

    public function down()
    {
        $this->forge->dropTable('google_accounts');
    }
}
