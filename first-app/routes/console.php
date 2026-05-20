<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:sync-mysql-to-pgsql
    {--mysql-host=127.0.0.1}
    {--mysql-port=3306}
    {--mysql-database=news_platform}
    {--mysql-username=root}
    {--mysql-password=}
    {--truncate : Truncate target PostgreSQL tables before import}', function () {
    config([
        'database.connections.mysql_sync' => [
            'driver' => 'mysql',
            'host' => (string) $this->option('mysql-host'),
            'port' => (string) $this->option('mysql-port'),
            'database' => (string) $this->option('mysql-database'),
            'username' => (string) $this->option('mysql-username'),
            'password' => (string) $this->option('mysql-password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],
    ]);

    DB::purge('mysql_sync');
    DB::reconnect('mysql_sync');
    DB::purge('pgsql');
    DB::reconnect('pgsql');

    $source = DB::connection('mysql_sync');
    $target = DB::connection('pgsql');

    $tables = [
        'users',
        'categories',
        'reports',
        'report_media',
        'votes',
        'flags',
        'notifications',
        'audit_logs',
    ];

    if ((bool) $this->option('truncate')) {
        $existingTargets = array_values(array_filter($tables, fn (string $table) => Schema::connection('pgsql')->hasTable($table)));

        if ($existingTargets !== []) {
            $quoted = implode(', ', array_map(fn (string $table) => '"'.$table.'"', $existingTargets));
            $target->statement('TRUNCATE TABLE '.$quoted.' RESTART IDENTITY CASCADE');
            $this->warn('Target PostgreSQL tables truncated.');
        }
    }

    foreach ($tables as $table) {
        if (! Schema::connection('mysql_sync')->hasTable($table)) {
            $this->warn("Skipping {$table}: source table not found in MySQL.");
            continue;
        }

        if (! Schema::connection('pgsql')->hasTable($table)) {
            $this->warn("Skipping {$table}: target table not found in PostgreSQL.");
            continue;
        }

        $rows = $source->table($table)->get()->map(fn ($row) => (array) $row)->all();

        if ($rows === []) {
            $this->line("{$table}: 0 rows");
            continue;
        }

        $target->table($table)->insert($rows);
        $this->info("{$table}: imported ".count($rows).' rows');
    }

    $verifiedCount = $target->table('reports')->where('status', 'verified')->count();
    $this->info("Done. PostgreSQL verified reports: {$verifiedCount}");
})->purpose('Copy app data from MySQL to PostgreSQL');
