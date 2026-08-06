<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Line items on an order.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.7 / Module 6
 * Table: order_items
 */
class OrderItemModel extends Model
{
    protected $table            = 'order_items';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'order_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];
    protected $useTimestamps    = false;
    protected $validationRules  = [
        'order_id'     => 'required|integer',
        'menu_item_id' => 'required|integer',
        'quantity'     => 'required|integer|greater_than[0]',
        'unit_price'   => 'required|decimal|greater_than_equal_to[0]',
        'subtotal'     => 'required|decimal|greater_than_equal_to[0]',
    ];
    protected $skipValidation   = false;
}
