<?php

namespace App\Services;

use CodeIgniter\Config\Services;

/**
 * Thin wrapper for writing audit_logs rows inside the caller's transaction.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 2 (cross-cutting)
 * Implement the methods listed in that document's matching "Prompt" block (Section 8).
 * Wrap every multi-table write in $this->db->transStart() / transComplete().
 */
class AuditService
{
    protected $db;
    protected $auditModel;

    public function __construct()
    {
        $this->db        = db_connect();
        $this->auditModel = new \App\Models\AuditLogModel();
    }

    /**
     * Write an audit log entry. Assumes the caller has already started a transaction.
     */
    public function log(
        int $userId,
        string $action,
        string $entityType,
        int $entityId,
        ?array $meta = null
    ): void {
        $this->auditModel->insert([
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'meta'        => $meta ? json_encode($meta) : null,
        ]);
    }
}
