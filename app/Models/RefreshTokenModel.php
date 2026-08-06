<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Hashed refresh tokens for JWT rotation.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 5 / Module 1
 * Table: refresh_tokens
 */
class RefreshTokenModel extends Model
{
    protected $table            = 'refresh_tokens';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'token_hash',
        'expires_at',
        'revoked',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
    protected $validationRules  = [
        'user_id'    => 'required|integer',
        'token_hash' => 'required|max_length[255]',
        'expires_at' => 'required|valid_date',
    ];
    protected $skipValidation   = false;
}
