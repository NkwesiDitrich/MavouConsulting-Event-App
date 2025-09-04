<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
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
        return [
            'name' => fake()->unique()->sentence(3),
            'slogan' => fake()->sentence(5),
            'description' => fake()->text,
            'location' => fake()->city,
            'start_time' => fake()->dateTimeBetween('now', '+1 month'),
            'end_time' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'event_type' => fake()->randomElement(['online', 'in-person', 'hybrid']),
            'audience' => fake()->randomElement(['public', 'private']),
            'image_url' => fake()->optional()->url(),
            'organizer_id' => \App\Models\User::factory(),
        ];
    }
}