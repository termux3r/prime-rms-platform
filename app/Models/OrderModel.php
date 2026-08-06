<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Customer orders.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.7 / Module 6
 * Table: orders
 */
class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'table_id',
        'cashier_id',
        'status',
        'total_amount',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $validationRules  = [
        'table_id'     => 'required|integer',
        'cashier_id'   => 'required|integer',
        'status'       => 'permit_empty|in_list[pending,completed,paid,cancelled]',
        'total_amount' => 'permit_empty|decimal|greater_than_equal_to[0]',
    ];
    protected $skipValidation   = false;
}
