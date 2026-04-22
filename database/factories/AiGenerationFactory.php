<?php

namespace Database\Factories;

use App\Models\AiGeneration;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AiGeneration> */
class AiGenerationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id'        => Workspace::factory(),
            'user_id'             => User::factory(),
            'agent_type'          => 'content_generator',
            'dialect'             => 'fos',
            'platform'            => 'instagram',
            'content_type'        => 'post',
            'prompt'              => 'اكتب منشوراً تسويقياً عن منتج جديد',
            'input_tokens'        => $this->faker->numberBetween(200, 800),
            'output_tokens'       => $this->faker->numberBetween(300, 1200),
            'sada_tokens_charged' => 40,
            'cached'              => false,
        ];
    }

    public function cached(): static
    {
        return $this->state(['cached' => true, 'sada_tokens_charged' => 0]);
    }
}
