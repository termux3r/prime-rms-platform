<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKitchenStatuses extends Migration
{
    public function up()
    {
        // Drop the old constraint and add new one with kitchen statuses
        $this->db->query("ALTER TABLE orders DROP CONSTRAINT chk_orders_status");
        $this->db->query("ALTER TABLE orders ADD CONSTRAINT chk_orders_status CHECK (status IN ('pending','preparing','ready','served','completed','paid','cancelled'))");
        
        // Update the unique index so that only in-progress orders are unique per table.
        // Final/locked statuses (completed, paid, cancelled) must NOT block a fresh order.
        $this->db->query('DROP INDEX IF EXISTS one_active_order_per_table');
        $this->db->query("CREATE UNIQUE INDEX one_active_order_per_table ON orders (table_id) WHERE status IN ('pending','preparing','ready','served')");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE orders DROP CONSTRAINT chk_orders_status");
        $this->db->query("ALTER TABLE orders ADD CONSTRAINT chk_orders_status CHECK (status IN ('pending','completed','paid','cancelled'))");
        
        $this->db->query("DROP INDEX one_active_order_per_table");
        $this->db->query("CREATE UNIQUE INDEX one_active_order_per_table ON orders (table_id) WHERE status IN ('pending','completed')");
    }
}