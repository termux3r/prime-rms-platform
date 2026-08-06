<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Food categories.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.4 / Module 3
 * Table: menu_categories
 */
class MenuCategoryModel extends Model
{
    protected $table            = 'menu_categories';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'name',
        'description',
        'status',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $dateFormat       = 'datetime';
    protected $validationRules  = [
        'name'        => 'required|max_length[100]|is_unique[menu_categories.name,id,{id}]',
        'description' => 'permit_empty|max_length[1000]',
        'status'      => 'permit_empty|in_list[active,inactive]',
    ];
    protected $validationMessages = [
        'name' => [
            'is_unique' => 'Category name already exists',
        ],
    ];
    protected $skipValidation     = false;

    public function getProtectedFields(): array
{
    return array_merge($this->skipValidation, $this->deletedField);
}
}