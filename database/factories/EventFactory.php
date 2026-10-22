<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->name().' & '.fake()->name();

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title.'-'.fake()->unique()->numerify('####')),
            'upload_token' => Str::random(20),
            'bride_name' => fake()->firstName('female'),
            'groom_name' => fake()->firstName('male'),
            'status' => 'active',
        ];
    }
}
