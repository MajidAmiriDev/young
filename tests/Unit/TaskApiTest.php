<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_task()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'title' => 'Test Task',
                     'priority' => 'high',
                 ]);
    }

    public function test_get_tasks()
    {
        $user = User::factory()->create();

        Task::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_update_task()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->putJson('/api/tasks/'.$task->id, [
            'title' => 'Updated Task',
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'title' => 'Updated Task',
                     'status' => 'completed',
                 ]);
    }

    public function test_delete_task()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->deleteJson('/api/tasks/'.$task->id);

        $response->assertStatus(204);
    }
}
