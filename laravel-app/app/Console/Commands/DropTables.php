<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class DropTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:drop-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar manualmente tablas conflictivas';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tables = ['publicaciones_tecnicas', 'publicaciones_cientificas'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
                $this->info("Tabla {$table} eliminada.");
            } else {
                $this->info("Tabla {$table} no existe.");
            }
        }

        return Command::SUCCESS;
    }
}