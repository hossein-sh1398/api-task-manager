<?php

namespace Tests\Feature\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_authenticated_user_can_get_their_tasks(): void
    {
        $user = User::factory()->create(['name' => "Hossein"]);
        Task::factory()->count(10)->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks');


        $response->assertStatus(200)->assertJson(
            [
                'success' => true,
                'message' => 'عملیات با موفقیت انجام شد'
            ]
        )->assertJsonStructure([
            'success',
            'message',
            'data'
        ])->assertJsonFragment(['name' => 'Hossein']);

        $this->assertEquals($user->id, $response->json('data.tasks.0.user.id'));

        $this->assertCount(10, $response->json('data.tasks'));
    }

    public function test_authenticated_user_can_search_tasks()
    {
        $user = User::factory()->create(['name' => "Hossein"]);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Test Task']);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Other Task']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks?search=Test');

        $response->assertStatus(200)->assertJson(
            [
                'success' => true,
                'message' => 'عملیات با موفقیت انجام شد'
            ]
        )->assertJsonStructure([
            'success',
            'message',
            'data'
        ])->assertJsonCount(1, 'data.tasks')
            ->assertJsonFragment(['name' => 'Hossein'])
            ->assertJsonFragment(['title' => 'Test Task']);

        $this->assertEquals($user->id, $response->json('data.tasks.0.user.id'));
    }

    public function test_unauthenticated_user_cannot_access_tasks()
    {
        $response = $this->getJson('/api/v1/tasks');

        $response->assertStatus(401)->assertJson(['message' => "شما وارد سیستم نشده‌اید. لطفاً ابتدا لاگین کنید."]);
    }

    public function test_authenticated_user_can_sort_tasks()
    {
        $user = User::factory()->create(['name' => "Hossein"]);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'A Task']);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'B Task']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks?order=title&dir=desc');
        $response->assertStatus(200)
            ->assertJsonPath('data.tasks.0.title', 'B Task')
            ->assertJsonPath('data.tasks.1.title', 'A Task');
    }

    public function test_authenticated_user_can_filter_tasks_by_status()
    {
        $user = User::factory()->create(['name' => "Hossein"]);
        Task::factory()->create(['user_id' => $user->id, 'completed' => false]);
        Task::factory()->create(['user_id' => $user->id, 'completed' => true]);
        Task::factory()->create(['user_id' => $user->id, 'completed' => false]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks?status=pending');
        $response->assertStatus(200)
            ->assertJsonPath('data.tasks.0.completed', false)
            ->assertJsonPath('data.tasks.1.completed', false)
            ->assertJsonFragment(['completed' => false])
            ->assertJsonCount(2, 'data.tasks');
    }

    public function test_authenticated_user_can_created_task()
    {
        $user = User::factory()->create(['name' => "Hossein"]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/tasks', [
            'title' => 'task title',
            'description' => 'task body',
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ]);
        $response->assertStatus(201)
            ->assertJson(['message' => "وظیفه مورد نظر با موفقیت ایجاد شد"])
            ->assertJsonFragment(['name' => "Hossein"])
            ->assertJsonFragment(['title' => 'task title']);
        $this->assertEquals($user->id, $response->json('data.user.id'));
    }


    public function test_validation_errors_whene_created_task()
    {
        $user = User::factory()->create(['name' => "Hossein"]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/tasks', [
            'title' => '',
            'description' => '',
            'start_date' => '',
            'end_date' => '',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors([
            'title',
            'description',
            'start_date',
            'end_date'
        ])
            ->assertJsonFragment(['title' => ["فیلد عنوان الزامی است."]])
            ->assertJsonFragment(['description' => ["فیلد توضیحات الزامی است."]])
            ->assertJsonFragment(['start_date' => ["فیلد تاریخ شروع الزامی است."]])
            ->assertJsonFragment(['end_date' => ["فیلد تاریخ پایان الزامی است."]]);
    }

    public function test_user_can_show_task()
    {
        $user = User::factory()->create(['name' => "Hossein"]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'task Title'
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks/' . $task->id);
        $response->assertStatus(200)
            ->assertJson(['message' => "عملیات با موفقیت انجام شد"])
            ->assertJsonFragment(['name' => "Hossein"])
            ->assertJsonFragment(['title' => 'task Title'])
            ->assertJsonIsObject('data');
        $this->assertEquals($user->id, $response->json('data.user.id'));
    }

    public function test_user_cannot_view_task_belonging_to_another_user()
    {
        $hossein = User::factory()->create(['name' => "Hossein"]);
        $ali = User::factory()->create(['name' => "ali"]);

        $task = Task::factory()->create([
            'user_id' => $ali->id,
            'title' => 'task Title'
        ]);
        Sanctum::actingAs($hossein);

        $response = $this->getJson('/api/v1/tasks/' . $task->id);
        $response->assertStatus(403)
            ->assertJson(['message' => 'اجازه دسترسی به این قسمت از برنامه را ندارید']);
    }

    public function test_authenticated_user_can_updated_task()
    {
        $user = User::factory()->create(['name' => "Hossein"]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'task title',
            'description' => 'task description',
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/tasks/' . $task->id, [
            'title' => 'edited title',
            'description' => 'edited description',
            'start_date' => $task->start_date,
            'end_date' => $task->end_date,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => "وظیفه مورد نظر با موفقیت ویرایش شد"])
            ->assertJsonFragment(['name' => "Hossein"])
            ->assertJsonFragment(['title' => 'edited title'])
            ->assertJsonFragment(['description' => 'edited description']);
        $this->assertEquals($user->id, $response->json('data.user.id'));
    }


    public function test_validation_errors_whene_updated_task()
    {
        $user = User::factory()->create(['name' => "Hossein"]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'task title',
            'description' => 'task description',
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/tasks/' . $task->id, [
            'title' => '',
            'description' => '',
            'start_date' => '',
            'end_date' => '',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors([
            'title',
            'description',
            'start_date',
            'end_date'
        ])
            ->assertJsonFragment(['title' => ["فیلد عنوان الزامی است."]])
            ->assertJsonFragment(['description' => ["فیلد توضیحات الزامی است."]])
            ->assertJsonFragment(['start_date' => ["فیلد تاریخ شروع الزامی است."]])
            ->assertJsonFragment(['end_date' => ["فیلد تاریخ پایان الزامی است."]]);
    }

    public function test_user_can_not_deleted_another_user_tasks()
    {
        $hossein = User::factory()->create(['name' => "Hossein"]);
        $ali = User::factory()->create(['name' => "ali"]);

        $task = Task::factory()->create([
            'user_id' => $ali->id,
            'title' => 'task Title'
        ]);
        Sanctum::actingAs($hossein);

        $response = $this->deleteJson('/api/v1/tasks/' . $task->id);

        $response->assertStatus(403)->assertJson(['success' => false, 'message' => 'اجازه دسترسی به این قسمت از برنامه را ندارید']);
    }

    public function test_user_gets_404_when_no_tasks_exist()
    {
        $hossein = User::factory()->create(['name' => "Hossein"]);

        Sanctum::actingAs($hossein);

        $response = $this->deleteJson('/api/v1/tasks/2000');

        $response->assertStatus(404)->assertJson(['success' => false, 'message' => "هیچ نتیجه‌ای برای درخواست شما پیدا نشد."]);
    }

    public function test_user_can_delete_their_own_task()
    {
        $hossein = User::factory()->create(['name' => "Hossein"]);

        $task = Task::factory()->create([
            'user_id' => $hossein->id,
            'title' => 'task Title'
        ]);

        Sanctum::actingAs($hossein);

        $response = $this->deleteJson('/api/v1/tasks/' . $task->id);

        $response->assertStatus(200)->assertJson([
            'success' => true, 
            'message' => "وظیفه مورد نظر با موفقیت حذف شد", 
            'data' => null
        ]);
    }
   
    public function test_user_can_change_status_their_own_task()
    {
        $hossein = User::factory()->create(['name' => "Hossein"]);

        $task = Task::factory()->create([
            'user_id' => $hossein->id,
            'title' => 'task Title',
            'completed' => false,
        ]);

        Sanctum::actingAs($hossein);

        $response = $this->patchJson('/api/v1/tasks/' . $task->id. '/toggle');

        $response->assertStatus(200)->assertJson([
            'success' => true, 
            'message' => 'وظیفه مورد نظر با موفقیت ویرایش شد', 
            'data' => true
        ]);
    }
    public function test_user_can_not_change_status_other_user_task()
    {
        $user1 = User::factory()->create(['name' => "Hossein"]);
        $user2 = User::factory()->create(['name' => "ali"]);

        $task = Task::factory()->create([
            'user_id' => $user1->id,
            'title' => 'task Title'
        ]);

        Sanctum::actingAs($user2);

        $response = $this->patchJson('/api/v1/tasks/' . $task->id. '/toggle');

        $response->assertStatus(403);
    }
}
