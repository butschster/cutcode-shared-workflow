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
        yield Workflow::newUntypedActivityStub(
            options: ActivityOptions::new()
                ->withTaskQueue('splitter')
                ->withStartToCloseTimeout('10 minutes'),
        )->execute('Hello');

        $chunks = yield Workflow::newActivityStub(
            class: CsvSplitterActivity::class,
            options: ActivityOptions::new()
                ->withTaskQueue('splitter')
                ->withStartToCloseTimeout('10 minutes'),
        )->split($s3Path);

        $batchSize = 10;

        $errors = [];

        $parser = Workflow::newActivityStub(
            class: CsvParserActivity::class,
            options: ActivityOptions::new()
                ->withTaskQueue('csv_parser')
                ->withStartToCloseTimeout('1 minute'),
        );

        $batches = \array_chunk($chunks->chunks, $batchSize);

        foreach ($batches as $batch) {
            yield Promise::all(
                \array_map(
                    static fn($chunk) => $parser->parse($chunk)->then(
                        onRejected: static function () use (
                            &$errors,
                            $chunk,
                        ) {
                            $errors[] = $chunk;
                        },
                    ),
                    $batch,
                ),
            );
        }

        if ($errors) {
            // Handle errors
        }
    }
}
