<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBills extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'auto_increment' => true],
            'order_id'       => ['type' => 'INT'],
            'total_amount'   => ['type' => 'NUMERIC', 'constraint' => '10,2'],
            'payment_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'unpaid'],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'paid_at'        => ['type' => 'TIMESTAMPTZ', 'null' => true],
            'created_at'     => ['type' => 'TIMESTAMPTZ', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('order_id');
        $this->forge->addForeignKey('order_id', 'orders', 'id');
        $this->forge->createTable('bills');

        $this->db->query("ALTER TABLE bills ADD CONSTRAINT chk_bills_payment_status CHECK (payment_status IN ('unpaid','paid'))");
        $this->db->query("ALTER TABLE bills ADD CONSTRAINT chk_bills_payment_method CHECK (payment_method IN ('cash','card','mobile'))");
        $this->db->query("CREATE INDEX idx_bills_paid_at ON bills(paid_at)");
    }

    public function down()
    {
        $this->forge->dropTable('bills');
    }
}
