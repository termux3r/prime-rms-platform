<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'null' => true],
            'action'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_id'   => ['type' => 'INT'],
            'meta'        => ['type' => 'JSONB', 'null' => true],
            'created_at'  => ['type' => 'TIMESTAMPTZ', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id');
        $this->forge->createTable('audit_logs');
        $this->db->query("CREATE INDEX idx_audit_logs_entity ON audit_logs(entity_type, entity_id)");
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs');
    }
}
