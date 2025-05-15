<?php

declare(strict_types=1);

namespace App\Temporal;

final readonly class CsvChunk
{
    public function __construct(
        public string $s3Path,
        public int $number,
        public int $offset,
        public int $length,
        public int $rows = 0,
    ) {}
}
