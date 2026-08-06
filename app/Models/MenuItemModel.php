<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Menu items.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.5 / Module 4
 * Table: menu_items
 */
class MenuItemModel extends Model
{
    protected $table            = 'menu_items';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'status',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $validationRules  = [
        'category_id' => 'required|integer|is_not_unique[menu_categories.id]',
        'name'        => 'required|max_length[150]',
        'description' => 'permit_empty|max_length[1000]',
        'price'       => 'required|decimal|greater_than_equal_to[0]',
        'image'       => 'permit_empty|max_length[255]',
        'status'      => 'permit_empty|in_list[active,inactive]',
    ];
    protected $skipValidation   = false;
}
