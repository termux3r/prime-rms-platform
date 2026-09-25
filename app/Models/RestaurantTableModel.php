<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Physical restaurant tables.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.6 / Module 5
 * Table: restaurant_tables
 */
class RestaurantTableModel extends Model
{
    protected $table            = 'restaurant_tables';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'table_number',
        'capacity',
        'status',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $validationRules  = [
        'id'           => 'permit_empty|is_natural_no_zero',
        'table_number' => 'required|max_length[20]|is_unique[restaurant_tables.table_number,id,{id}]',
        'capacity'     => 'required|integer|greater_than[0]',
        'status'       => 'permit_empty|in_list[available,occupied]',
    ];
    protected $validationMessages = [
        'table_number' => [
            'is_unique' => 'Table number already exists',
        ],
    ];
    protected $skipValidation     = false;
}
