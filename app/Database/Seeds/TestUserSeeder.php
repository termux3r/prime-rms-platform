<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('users')->insert([
            'name'          => 'Admin User',
            'username'      => 'admin',
            'email'         => 'admin@example.com',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'role'          => 'admin',
            'status'        => 'active',
        ]);
    }
}