<?php

declare(strict_types=1);

namespace App\Temporal;

use Temporal\Activity\ActivityOptions;
use Temporal\Promise;
use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
final class CsvParserWorkflow
{
    private array $chunks;
    private int $totalToFetch = 0;

    #[WorkflowMethod('csv_parser.parse')]
    public function parse(string $s3Path)
    {
        yield Workflow::newUntypedActivityStub(
            options: ActivityOptions::new()
                ->withTaskQueue('splitter')
                ->withStartToCloseTimeout('60 minutes'),
        )->execute('Hello');

        $chunks = yield Workflow::newUntypedActivityStub(
            options: ActivityOptions::new()
                ->withTaskQueue('splitter')
                ->withStartToCloseTimeout('60 minutes'),
        )->execute(name: 'Split', args: [$s3Path], returnType: CsvChunks::class);

        $this->chunks = $chunks->chunks;

        $batchSize = 10;

        $parserChunks = \array_chunk($chunks->chunks, $batchSize);

        $parser = Workflow::newActivityStub(
            class: CsvParserActivity::class,
            options: ActivityOptions::new()
                ->withStartToCloseTimeout('60 minutes'),
        );

        $errors = [];

        $this->totalToFetch = \count($parserChunks);

        foreach ($parserChunks as $i => $chunk) {
            yield Promise::all(
                \array_map(
                    fn(CsvChunk $chunk) => $parser->parse($chunk)->then(
                        onRejected: static function ($error) use ($i, $chunk, &$errors) {
                            $errors[] = [
                                'chunk' => $chunk,
                                'error' => $error,
                            ];
                        },
                    ),
                    $chunk,
                ),
            );

            $this->totalToFetch--;
        }
        // Handle errors
    }

    #[Workflow\QueryMethod]
    public function totalChunks(): int
    {
        return \count($this->chunks);
    }

    #[Workflow\QueryMethod]
    public function totalToFetch(): int
    {
        return $this->totalToFetch;
    }
}
