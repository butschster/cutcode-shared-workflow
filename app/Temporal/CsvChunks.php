<?php

declare(strict_types=1);

namespace App\Temporal;

use Temporal\Internal\Marshaller\Meta\MarshalArray;

final readonly class CsvChunks
{
    public function __construct(
        #[MarshalArray(of: CsvChunk::class)]
        public array $chunks,
    ) {}
}
