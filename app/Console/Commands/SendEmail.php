<?php

namespace App\Console\Commands;

use App\Temporal\SimpleWorkflow;
use App\Workflows\MyWorkflow;
use Illuminate\Console\Command;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;
use Workflow\Workflow;
use Workflow\WorkflowStub;

class SendEmail extends Command
{
    protected $signature = 'app:send-email';
    protected $description = 'Command description';

    public function handle(WorkflowClientInterface $client)
    {
        foreach (\range(0, 500) as $i) {
//            $stub = $client->newWorkflowStub(
//                SimpleWorkflow::class,
//                options: WorkflowOptions::new(),
//            );
//            $client->start($stub, 'Laravel - ' . $i);

            $workflow = WorkflowStub::make(MyWorkflow::class);
            $workflow->start('Laravel - ' . $i);
        }
    }
}
