<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id'  => Workspace::factory(),
            'user_id'       => User::factory(),
            'content'       => $this->faker->realTextBetween(100, 500),
            'hashtags'      => ['#تسويق', '#محتوى'],
            'platform'      => $this->faker->randomElement(['instagram', 'facebook', 'tiktok', 'snapchat', 'x']),
            'content_type'  => 'post',
            'dialect'       => 'fos',
            'status'        => 'draft',
            'scheduled_for' => null,
            'published_at'  => null,
            'social_account_id' => null,
            'metadata'      => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function scheduled(): static
    {
        return $this->state([
            'status'        => 'scheduled',
            'scheduled_for' => now()->addDay(),
        ]);
    }

    public function published(): static
    {
        return $this->state([
            'status'       => 'published',
            'published_at' => now(),
        ]);
    }

    public function instagram(): static
    {
        return $this->state(['platform' => 'instagram', 'content_type' => 'post']);
    }

    public function facebook(): static
    {
        return $this->state(['platform' => 'facebook', 'content_type' => 'post']);
    }
}
