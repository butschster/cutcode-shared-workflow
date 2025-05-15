<?php

namespace App\Console\Commands;

use App\Temporal\CsvParserWorkflow;
use Illuminate\Console\Command;
use Temporal\Client\WorkflowClientInterface;

class ParseCsv extends Command
{
    protected $signature = 'app:parse-csv {file}';
    protected $description = 'Parse CSV file';

    public function handle(WorkflowClientInterface $client)
    {
        $client->newWorkflowStub(
            CsvParserWorkflow::class,
        );
    }
}
