<?php

namespace Tests\Unit;
use Tests\TestCase;
use App\Jobs\ProcessTask;
use Illuminate\Support\Facades\Queue;
use App\Models\Task;

class TaskQueueTest extends TestCase
{
    public function test_high_priority_task_dispatched_to_queue()
    {
        Queue::fake();

        $task = Task::factory()->create(['priority' => 'high']);

        ProcessTask::dispatch($task);

        Queue::assertPushed(ProcessTask::class, function ($job) use ($task) {
            return $job->task->id === $task->id;
        });
    }

    public function test_queue_job_processed_correctly()
    {
        $task = Task::factory()->create(['priority' => 'high']);

        // Assume ProcessTask job updates the status of the task to 'processing'
        ProcessTask::dispatch($task);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'processing'
        ]);
    }
}
