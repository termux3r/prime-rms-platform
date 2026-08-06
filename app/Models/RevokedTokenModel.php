<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Blocklisted JWT jti values (logout).
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 5 / Module 1
 * Table: revoked_tokens
 */
class RevokedTokenModel extends Model
{
    protected $table            = 'revoked_tokens';
    protected $primaryKey       = 'jti';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'jti',
        'expires_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'revoked_at';
    protected $updatedField     = '';
    protected $validationRules  = [
        'jti'        => 'required|max_length[36]',
        'expires_at' => 'required|valid_date',
    ];
    protected $skipValidation   = false;
}
