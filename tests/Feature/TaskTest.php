<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and get token
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('api_token')->plainTextToken;
        
        // Create a project owned by the user
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_create_task()
    {
        $assignedUser = User::factory()->create();
        
        $taskData = [
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'assigned_to' => $assignedUser->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/projects/' . $this->project->id . '/tasks', $taskData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'project_id' => $this->project->id,
            'assigned_to' => $assignedUser->id,
        ]);
    }

    public function test_user_can_get_project_tasks()
    {
        Task::factory()->count(3)->create([
            'project_id' => $this->project->id,
            'assigned_to' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/projects/' . $this->project->id . '/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_update_assigned_task()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => $this->user->id,
            'title' => 'Original Task',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/tasks/' . $task->id, [
                'title' => 'Updated Task',
                'description' => 'Updated description',
                'assigned_to' => $this->user->id,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
        ]);
    }

    public function test_user_can_change_task_status()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => $this->user->id,
            'is_completed' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/tasks/' . $task->id . '/status', [
                'is_completed' => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => true,
        ]);
    }

    public function test_user_can_delete_task_from_own_project()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => User::factory()->create()->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_assigned_user_cannot_delete_task()
    {
        $assignedUser = User::factory()->create();
        $assignedToken = $assignedUser->createToken('api_token')->plainTextToken;
        
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assigned_to' => $assignedUser->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $assignedToken)
            ->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}