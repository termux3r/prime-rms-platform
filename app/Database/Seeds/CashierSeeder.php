<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CashierSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('users')->insert([
            'name'          => 'Cashier User',
            'username'      => 'cashier',
            'email'         => null,
            'password_hash' => password_hash('register123', PASSWORD_BCRYPT),
            'role'          => 'cashier',
            'status'        => 'active',
        ]);
    }
}