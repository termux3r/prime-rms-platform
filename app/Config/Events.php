<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        $value = ini_get('zlib.output_compression');

        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) || (int) $value > 0) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }

    // Zero-config database auto-provisioning on Vercel
    if ((getenv('VERCEL') || isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) && ENVIRONMENT !== 'testing') {
        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('users')) {
                $migrate = \Config\Services::migrations();
                $migrate->latest();

                // Seed default admin
                if ($db->tableExists('users') && $db->table('users')->countAll() === 0) {
                    $db->table('users')->insert([
                        'name'          => 'Default Admin',
                        'username'      => 'admin',
                        'email'         => 'admin@example.com',
                        'password_hash' => password_hash('ChangeMe123!', PASSWORD_BCRYPT),
                        'role'          => 'admin',
                        'status'        => 'active',
                    ]);
                }

                // Seed default tables
                if ($db->tableExists('restaurant_tables') && $db->table('restaurant_tables')->countAll() === 0) {
                    for ($i = 1; $i <= 6; $i++) {
                        $db->table('restaurant_tables')->insert([
                            'table_number' => 'T' . $i,
                            'capacity'     => 4,
                            'status'       => 'available',
                        ]);
                    }
                }

                // Seed sample menu categories & items
                if ($db->tableExists('menu_categories') && $db->table('menu_categories')->countAll() === 0) {
                    $db->table('menu_categories')->insertBatch([
                        ['name' => 'Main Dishes', 'display_order' => 1, 'status' => 'active'],
                        ['name' => 'Beverages', 'display_order' => 2, 'status' => 'active'],
                        ['name' => 'Desserts', 'display_order' => 3, 'status' => 'active'],
                    ]);
                    $catRow = $db->table('menu_categories')->where('name', 'Main Dishes')->get()->getRow();
                    $bevRow = $db->table('menu_categories')->where('name', 'Beverages')->get()->getRow();
                    $catId = $catRow ? $catRow->id : 1;
                    $bevId = $bevRow ? $bevRow->id : 2;
                    $db->table('menu_items')->insertBatch([
                        ['category_id' => $catId, 'name' => 'Classic Burger', 'price' => 12.50, 'status' => 'active'],
                        ['category_id' => $catId, 'name' => 'Italian Pizza', 'price' => 15.00, 'status' => 'active'],
                        ['category_id' => $bevId, 'name' => 'Fresh Lemonade', 'price' => 4.50, 'status' => 'active'],
                        ['category_id' => $bevId, 'name' => 'Espresso', 'price' => 3.00, 'status' => 'active'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Auto-provisioning database error: ' . $e->getMessage());
        }
    }
});
