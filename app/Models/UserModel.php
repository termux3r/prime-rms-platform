<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Staff accounts.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.2 / Module 2
 * Table: users
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = \App\Entities\User::class;
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'name',
        'username',
        'email',
        'password_hash',
        'role',
        'status',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $validationRules  = [
        'id'            => 'permit_empty|is_natural_no_zero',
        'name'          => 'required|max_length[100]',
        'username'      => 'required|max_length[50]|is_unique[users.username,id,{id}]',
        'email'         => 'permit_empty|valid_email|max_length[150]|is_unique[users.email,id,{id}]',
        'password_hash' => 'required|max_length[255]',
        'role'          => 'required|in_list[admin,cashier]',
        'status'        => 'required|in_list[active,inactive]',
    ];
    protected $validationMessages = [
        'username' => [
            'is_unique' => 'Username already exists.',
        ],
        'email' => [
            'is_unique' => 'Email already exists.',
        ],
    ];
    protected $skipValidation     = false;
}
