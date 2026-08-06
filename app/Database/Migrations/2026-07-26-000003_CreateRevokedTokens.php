<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRevokedTokens extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'jti'        => ['type' => 'VARCHAR', 'constraint' => 36],
            'expires_at' => ['type' => 'TIMESTAMPTZ'],
            'revoked_at' => ['type' => 'TIMESTAMPTZ', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('jti');
        $this->forge->createTable('revoked_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('revoked_tokens');
    }
}
