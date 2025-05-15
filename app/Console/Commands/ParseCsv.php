<?php

namespace App\Console\Commands;

use App\Temporal\CsvParserWorkflow;
use Illuminate\Console\Command;
use Temporal\Client\WorkflowClientInterface;

class ParseCsv extends Command
{
    protected $signature = 'app:parse-csv';
    protected $description = 'Parse CSV file';

    public function handle(WorkflowClientInterface $client)
    {
        $wf = $client->newWorkflowStub(
            CsvParserWorkflow::class,
        );

        $client->start($wf, 'path/to/file.csv');
    }
}
