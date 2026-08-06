<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Bootstraps the very first admin account. There is no signup endpoint by design --
 * this seeder is how the first admin gets in, then the Users module (Module 2) is how
 * that admin onboards everyone else.
 *
 * Run: php spark db:seed FirstAdminSeeder
 * Then log in and change the password immediately via POST /users/{id}/reset-password.
 */
class FirstAdminSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('users')->insert([
            'name'          => 'Default Admin',
            'username'      => 'admin',
            'email'         => 'admin@example.com',
            'password_hash' => password_hash('ChangeMe123!', PASSWORD_BCRYPT),
            'role'          => 'admin',
            'status'        => 'active',
        ]);
    }
}
