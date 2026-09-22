<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 *
 * Connection details are read from .env (database.default.*).
 * The values below are the fallback defaults only.
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection (PostgreSQL).
     * Override any field via .env: database.default.hostname, etc.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'        => '',
        'hostname'   => 'localhost',
        'username'   => 'postgres',
        'password'   => '',
        'database'   => 'rms',
        'schema'     => 'public',
        'DBDriver'   => 'Postgre',
        'DBPrefix'   => '',
        'pConnect'   => false,
        'DBDebug'    => true,
        'charset'    => 'utf8',
        'swapPre'    => '',
        'failover'   => [],
        'port'       => 5432,
        'dateFormat' => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'        => '',
        'hostname'   => 'localhost',
        'username'   => 'postgres',
        'password'   => '11223344',
        'database'   => 'rms_test',
        'schema'     => 'public',
        'DBDriver'   => 'Postgre',
        'DBPrefix'   => '',
        'pConnect'   => false,
        'DBDebug'    => true,
        'charset'    => 'utf8',
        'swapPre'    => '',
        'failover'   => [],
        'port'       => 5432,
        'dateFormat' => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // Support standard PostgreSQL URL (Neon, Supabase, Vercel Postgres, Railway)
        $dbUrl = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL') ?: ($_ENV['DATABASE_URL'] ?? ($_ENV['POSTGRES_URL'] ?? null));
        if ($dbUrl) {
            $parsed = parse_url($dbUrl);
            if ($parsed) {
                $this->default['hostname'] = $parsed['host'] ?? $this->default['hostname'];
                $this->default['port']     = isset($parsed['port']) ? (int) $parsed['port'] : 5432;
                $this->default['username'] = isset($parsed['user']) ? urldecode($parsed['user']) : $this->default['username'];
                $this->default['password'] = isset($parsed['pass']) ? urldecode($parsed['pass']) : $this->default['password'];
                $this->default['database'] = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $this->default['database'];
                $this->default['DBDriver'] = 'Postgre';
            }
        }

        // Use the 'tests' group when running automated tests
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
