<?php

namespace Database\Factories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Topic> */
class TopicFactory extends Factory
{
    protected $model = Topic::class;

    public function definition(): array
    {
        $suffix = Str::lower(Str::random(10));
        $name = "Synthetic Topic {$suffix}";

        return [
            'area' => 'vocabulary',
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Clearly synthetic topic content for automated tests.',
            'position' => 0,
            'priority' => 2,
            'status' => 'draft',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => 'active']);
    }

    public function grammar(): static
    {
        return $this->state(fn (): array => ['area' => 'grammar']);
    }
}
