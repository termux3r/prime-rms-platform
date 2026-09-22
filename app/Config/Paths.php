<?php

namespace Config;

/**
 * Paths
 *
 * Holds the paths that are used by the system to locate the main
 * directories, app, system, etc.
 *
 * NOTE: This class is required prior to Autoloader instantiation,
 *       and does not extend BaseConfig.
 */
class Paths
{
    public string $systemDirectory   = __DIR__ . '/../../vendor/codeigniter4/framework/system';
    public string $appDirectory      = __DIR__ . '/../';
    public string $publicDirectory   = __DIR__ . '/../../public';
    public string $writableDirectory = __DIR__ . '/../../writable';
    public string $testsDirectory    = __DIR__ . '/../../tests';
    public string $viewDirectory     = __DIR__ . '/../Views';
    public string $envDirectory      = __DIR__ . '/../../';
    public string $configDirectory   = __DIR__;

    public function __construct()
    {
        if (getenv('VERCEL') || isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
            $this->writableDirectory = '/tmp/writable';
            foreach (['cache', 'logs', 'session', 'debugbar', 'uploads'] as $subdir) {
                if (!is_dir("/tmp/writable/{$subdir}")) {
                    @mkdir("/tmp/writable/{$subdir}", 0777, true);
                }
            }
        }
    }
}