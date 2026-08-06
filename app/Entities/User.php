<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Never let password_hash leave the API -- hidden here, not just by controller convention.
 */
class User extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'       => 'integer',
        'status'   => 'string',
        'role'     => 'string',
    ];
    protected $hidden = ['password_hash'];
}
