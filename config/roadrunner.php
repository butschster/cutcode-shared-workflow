<?php

declare(strict_types=1);

use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunnerLaravel\Grpc\GrpcWorker;
use Spiral\RoadRunnerLaravel\Http\HttpWorker;
use Spiral\RoadRunnerLaravel\Queue\QueueWorker;
use Spiral\RoadRunnerLaravel\Temporal\Config\ClientConfig;
use Spiral\RoadRunnerLaravel\Temporal\Config\ConnectionConfig;
use Spiral\RoadRunnerLaravel\Temporal\Config\TlsConfig;
use Spiral\RoadRunnerLaravel\Temporal\TemporalWorker;
use Temporal\Client\ClientOptions;
use Temporal\Worker\WorkerFactoryInterface as TemporalWorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;

return [
    'cache' => [
        'storage' => 'cache',
    ],

    'grpc' => [
        'services' => [
            // GreeterInterface::class => new Greeter::class,
        ],
    ],

    'temporal' => [
        'clients' => [
            'default' => new ClientConfig(
                connection: new ConnectionConfig(
                    address: env('TEMPORAL_ADDRESS', '127.0.0.1:7233'),
                    tls: new TlsConfig(
                        serverName: env('TEMPORAL_TLS_SERVER_NAME'),
                    ),
                    authToken: env('TEMPORAL_AUTH_TOKEN'),
                ),
                options: (new ClientOptions)->withNamespace(
                    namespace: env('TEMPORAL_NAMESPACE', 'default'),
                ),
            ),
        ],
        'defaultWorker' => env('TEMPORAL_TASK_QUEUE', TemporalWorkerFactoryInterface::DEFAULT_TASK_QUEUE),
        'workers' => [
            'default' => WorkerOptions::new()->withIdentity(
                env('TEMPORAL_IDENTITY', 'cut-code'),
            ),
        ],
        'declarations' => [
            \App\Temporal\CsvParserWorkflow::class,
            \App\Temporal\CsvParserActivity::class,
            \App\Temporal\CsvSplitterActivity::class,
        ],
    ],

    'workers' => [
        Mode::MODE_HTTP => HttpWorker::class,
        Mode::MODE_JOBS => QueueWorker::class,
        Mode::MODE_GRPC => GrpcWorker::class,
        Mode::MODE_TEMPORAL => TemporalWorker::class,
        // Mode::MODE_TEMPORAL => ...,
    ],
];
