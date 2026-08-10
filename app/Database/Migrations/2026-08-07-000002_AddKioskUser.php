<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Seeds a dedicated "kiosk" staff account used as the cashier for orders placed
 * through the public customer ordering screen (no logged-in staff member).
 */
class AddKioskUser extends Migration
{
    public function up()
    {
        $exists = $this->db->table('users')->where('username', 'kiosk')->countAllResults();

        if ($exists === 0) {
            $this->db->table('users')->insert([
                'name'          => 'Kiosk Ordering',
                'username'      => 'kiosk',
                'email'         => null,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'role'          => 'cashier',
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $row = $this->db->table('users')->select('id')->where('username', 'kiosk')->get()->getRow();

        if ($row) {
            $this->db->table('orders')->where('cashier_id', $row->id)->delete();
            $this->db->table('users')->where('username', 'kiosk')->delete();
        }
    }
}