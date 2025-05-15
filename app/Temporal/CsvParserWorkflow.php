<?php

declare(strict_types=1);

namespace App\Temporal;

use Temporal\Activity\ActivityOptions;
use Temporal\Promise;
use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
final readonly class CsvParserWorkflow
{
    #[WorkflowMethod('csv_parser.parse')]
    public function parse(string $s3Path)
    {
        $chunks = yield Workflow::newActivityStub(
            class: CsvSplitterActivity::class,
            options: ActivityOptions::new()->withStartToCloseTimeout('10 minutes'),
        )->split($s3Path);

        $batchSize = 10;

        $errors = [];

        $promises = [];
        foreach ($chunks->chunks as $i => $chunk) {
            $promises[] = Workflow::newActivityStub(
                class: CsvParserActivity::class,
                options: ActivityOptions::new()->withStartToCloseTimeout('1 minute'),
            )->parse($chunk)->then(onRejected: static function () use (&$errors, $chunk) {
                $errors[] = $chunk;
            });
        }

        $batches = \array_chunk($promises, $batchSize);

        foreach ($batches as $batch) {
            $results = yield Promise::all($batch);
        }

        if ($errors) {

        }
    }
}
