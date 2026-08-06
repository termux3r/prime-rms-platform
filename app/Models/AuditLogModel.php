<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Append-only audit trail.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 2 (cross-cutting)
 * Table: audit_logs
 */
class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'meta',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
    protected $dateFormat       = 'datetime';
    protected $validationRules  = [
        'user_id'     => 'permit_empty|integer',
        'action'      => 'required|max_length[100]',
        'entity_type' => 'required|max_length[50]',
        'entity_id'   => 'required|integer',
        'meta'        => 'permit_empty',
    ];
    protected $skipValidation   = false;
}
