<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and get token
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('api_token')->plainTextToken;
    }

    public function test_user_can_get_all_projects()
    {
        Project::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_project()
    {
        $projectData = [
            'title' => 'Test Project',
            'description' => 'This is a test project',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/projects', $projectData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'Test Project',
                    'description' => 'This is a test project',
                    'user_id' => $this->user->id,
                ]
            ]);

        $this->assertDatabaseHas('projects', [
            'title' => 'Test Project',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_view_project()
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'My Project',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/projects/' . $project->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $project->id,
                    'title' => 'My Project',
                    'user_id' => $this->user->id,
                ]
            ]);
    }

    public function test_user_can_update_own_project()
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/projects/' . $project->id, [
                'title' => 'Updated Project',
                'description' => 'Updated description',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $project->id,
                    'title' => 'Updated Project',
                    'description' => 'Updated description',
                ]
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Updated Project',
        ]);
    }

    public function test_user_cannot_update_others_project()
    {
        $otherUser = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/projects/' . $project->id, [
                'title' => 'Updated Project',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_project()
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/projects/' . $project->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_user_cannot_delete_others_project()
    {
        $otherUser = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/projects/' . $project->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}