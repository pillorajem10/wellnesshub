<?php

namespace App\Console\Commands;

use App\Services\TypesenseService;
use Illuminate\Console\Command;

class ReindexTypesense extends Command
{
    protected $signature = 'typesense:reindex';

    protected $description = 'Reindex all records into Typesense';

    public function handle(TypesenseService $typesense): int
    {
        set_time_limit(0);

        $this->info('Starting Typesense reindex...');

        $typesense->reindexAll();

        $this->info('Typesense reindex completed.');

        return self::SUCCESS;
    }
}
