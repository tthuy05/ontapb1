<?php

namespace Tests\Feature\SprintOne;

use App\Models\Topic;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageTopicTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_topic(): void
    {
        $response = $this->asOwner()->post(route('manage.topics.store'), $this->payload());

        $topic = Topic::query()->sole();
        $response->assertRedirectToRoute('manage.topics.edit', $topic)
            ->assertSessionHas('status', 'Topic created.');
        $this->assertDatabaseHas('topics', [
            'slug' => 'education-study',
            'area' => 'vocabulary',
            'priority' => 1,
            'status' => 'draft',
        ]);
    }

    public function test_topic_validation_rejects_missing_invalid_and_duplicate_values(): void
    {
        Topic::factory()->create(['slug' => 'education-study']);

        $response = $this->asOwner()->from(route('manage.topics.create'))->post(
            route('manage.topics.store'),
            $this->payload([
                'name' => '',
                'area' => 'admin',
                'slug' => 'education-study',
                'priority' => 9,
                'status' => 'published',
            ]),
        );

        $response->assertRedirectToRoute('manage.topics.create')
            ->assertSessionHasErrors(['name', 'area', 'slug', 'priority', 'status']);
        $this->assertDatabaseCount('topics', 1);
    }

    public function test_owner_can_update_a_topic(): void
    {
        $topic = Topic::factory()->create();

        $response = $this->asOwner()->put(route('manage.topics.update', $topic), $this->payload([
            'name' => 'Home and daily life',
            'slug' => 'home-daily-life',
            'position' => 30,
        ]));

        $response->assertRedirectToRoute('manage.topics.edit', $topic);
        $this->assertDatabaseHas('topics', [
            'id' => $topic->id,
            'name' => 'Home and daily life',
            'slug' => 'home-daily-life',
            'position' => 30,
        ]);
    }

    public function test_owner_can_activate_and_deactivate_a_topic(): void
    {
        $topic = Topic::factory()->create(['status' => 'draft']);

        $this->asOwner()->patch(route('manage.topics.status.update', $topic), ['status' => 'active'])
            ->assertSessionHas('status', 'Topic status updated.');
        $this->assertDatabaseHas('topics', ['id' => $topic->id, 'status' => 'active']);

        $this->asOwner()->patch(route('manage.topics.status.update', $topic), ['status' => 'inactive']);
        $this->assertDatabaseHas('topics', ['id' => $topic->id, 'status' => 'inactive']);
    }

    public function test_referenced_topic_cannot_be_deleted_and_is_preserved(): void
    {
        $topic = Topic::factory()->create(['status' => 'draft']);
        Vocabulary::factory()->for($topic)->create();

        $this->asOwner()->delete(route('manage.topics.destroy', $topic), ['confirm_delete' => '1'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('topics', ['id' => $topic->id]);
    }

    public function test_topic_index_filters_and_escapes_owner_content(): void
    {
        $dangerous = '<script>alert("topic")</script>';
        Topic::factory()->create([
            'area' => 'grammar',
            'name' => $dangerous,
            'slug' => 'unsafe-topic',
            'status' => 'active',
        ]);
        Topic::factory()->create(['area' => 'vocabulary', 'name' => 'Hidden by filter']);

        $response = $this->asOwner()->get(route('manage.topics.index', [
            'area' => 'grammar',
            'status' => 'active',
        ]));

        $response->assertOk()
            ->assertSee($dangerous)
            ->assertDontSee($dangerous, false)
            ->assertDontSee('Hidden by filter');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'area' => 'vocabulary',
            'name' => 'Education and study',
            'slug' => 'education-study',
            'description' => 'Synthetic topic for tests.',
            'position' => 10,
            'priority' => 1,
            'status' => 'draft',
        ], $overrides);
    }
}
