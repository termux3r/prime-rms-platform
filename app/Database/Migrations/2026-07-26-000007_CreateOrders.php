<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrders extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'auto_increment' => true],
            'table_id'     => ['type' => 'INT'],
            'cashier_id'   => ['type' => 'INT'],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'total_amount' => ['type' => 'NUMERIC', 'constraint' => '10,2', 'default' => 0],
            'created_at'   => ['type' => 'TIMESTAMPTZ', 'null' => true],
            'updated_at'   => ['type' => 'TIMESTAMPTZ', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('table_id', 'restaurant_tables', 'id');
        $this->forge->addForeignKey('cashier_id', 'users', 'id');
        $this->forge->createTable('orders');

        $this->db->query("ALTER TABLE orders ADD CONSTRAINT chk_orders_status CHECK (status IN ('pending','completed','paid','cancelled'))");
        // Business rule: one active order per table -- enforced at the DB level, not just app code
        $this->db->query("CREATE UNIQUE INDEX one_active_order_per_table ON orders (table_id) WHERE status IN ('pending','completed')");
        $this->db->query("CREATE INDEX idx_orders_table ON orders(table_id)");
        $this->db->query("CREATE INDEX idx_orders_cashier ON orders(cashier_id)");
        $this->db->query("CREATE INDEX idx_orders_created_at ON orders(created_at)");
    }

    public function down()
    {
        $this->forge->dropTable('orders');
    }
}
