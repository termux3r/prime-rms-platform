<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Bills/invoices for completed orders.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.8 / Module 7
 * Table: bills
 */
class BillModel extends Model
{
    protected $table            = 'bills';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'order_id',
        'total_amount',
        'payment_status',
        'payment_method',
        'paid_at',
        'created_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
    protected $dateFormat       = 'datetime';
    protected $validationRules  = [
        'order_id'       => 'required|integer',
        'total_amount'   => 'required|decimal|greater_than_equal_to[0]',
        'payment_status' => 'permit_empty|in_list[unpaid,paid]',
        'payment_method' => 'permit_empty|in_list[cash,card,mobile]',
        'paid_at'        => 'permit_empty|valid_date',
    ];
    protected $skipValidation   = false;
}
