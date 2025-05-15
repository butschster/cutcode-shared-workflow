<?php

declare(strict_types=1);

namespace App\Temporal;

use Spiral\RoadRunnerLaravel\Temporal\Attribute\AssignWorker;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;
use Temporal\Support\VirtualPromise;

#[AssignWorker(taskQueue: 'splitter')]
#[ActivityInterface]
final readonly class CsvSplitterActivity
{
    /**
     * @return VirtualPromise<CsvChunks>
     */
    #[ActivityMethod]
    public function split(string $s3Path): CsvChunks
    {
        $chunks = [];
        $randNumber = \random_int(10, 1000);

        for ($i = 0; $i < $randNumber; $i++) {
            $chunks[] = new CsvChunk(
                s3Path: $s3Path,
                number: $i,
                offset: $i * 1000,
                length: 1 * 1024 * 1024,
                rows: \random_int(100, 200),
            );
        }

        return new CsvChunks(chunks: $chunks);
    }
}
