<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class GenerateDatabaseList extends Command
{
    protected $signature = 'db:generate-list';

    protected $description = 'Generate struktur database ke file Markdown tanpa tabel double';

    public function handle()
    {
        $excludedTables = [
            'migrations',
            'password_reset_tokens',
            'personal_access_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
        ];

        /**
         * Ambil semua tabel dari database
         */
        $tables = collect(Schema::getTables())
            ->map(function ($table) {
                /**
                 * Laravel biasanya mengembalikan:
                 * [
                 *   "name" => "users",
                 *   "schema" => "...",
                 * ]
                 *
                 * Tapi untuk jaga-jaga, kita handle beberapa kemungkinan format.
                 */
                if (is_array($table)) {
                    return $table['name'] ?? $table['table'] ?? null;
                }

                if (is_object($table)) {
                    return $table->name ?? $table->table ?? null;
                }

                return $table;
            })
            ->filter()
            ->unique()
            ->values();

        $content = "# Database Structure\n\n";
        $content .= "Generated from Laravel Schema Facade.\n\n";

        foreach ($tables as $tableName) {
            if (in_array($tableName, $excludedTables)) {
                continue;
            }

            $content .= "## {$tableName}\n\n";
            $content .= "| No | Column | Type |\n";
            $content .= "|---:|---|---|\n";

            $columns = Schema::getColumns($tableName);

            foreach ($columns as $index => $column) {
                $columnName = $column['name'] ?? '-';
                $columnType = $column['type_name'] ?? $column['type'] ?? '-';

                $number = $index + 1;

                $content .= "| {$number} | {$columnName} | {$columnType} |\n";
            }

            $content .= "\n";
        }

        File::put(base_path('database-list.md'), $content);

        $this->info('Database structure berhasil dibuat tanpa tabel double: database-list.md');
    }
}
