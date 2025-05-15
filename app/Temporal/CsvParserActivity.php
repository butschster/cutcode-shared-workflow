<?php

declare(strict_types=1);

namespace App\Temporal;

use Illuminate\Support\Lottery;
use Psr\Log\LoggerInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;
use Temporal\Exception\Failure\ApplicationFailure;
use Temporal\Support\VirtualPromise;

#[ActivityInterface]
final readonly class CsvParserActivity
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    /**
     * @return VirtualPromise<void>
     */
    #[ActivityMethod]
    public function parse(CsvChunk $chunk): void
    {
        $this->logger->debug('Parsing CSV chunk', [
            'chunk' => $chunk,
        ]);

        Lottery::odds(95)->loser(function () {
            throw new ApplicationFailure('Failed to parse CSV chunk', type: 'ParseError', nonRetryable: true);
        });
    }
}
