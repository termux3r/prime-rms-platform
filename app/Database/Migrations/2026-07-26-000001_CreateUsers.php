<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'username'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'    => ['type' => 'TIMESTAMPTZ', 'null' => true],
            'updated_at'    => ['type' => 'TIMESTAMPTZ', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('username');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');

        // Forge has no first-class CHECK constraint support -- add via raw SQL
        $this->db->query("ALTER TABLE users ADD CONSTRAINT chk_users_role CHECK (role IN ('admin','cashier'))");
        $this->db->query("ALTER TABLE users ADD CONSTRAINT chk_users_status CHECK (status IN ('active','inactive'))");
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
