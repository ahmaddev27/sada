<?php

namespace Database\Factories;

use App\Models\BrandIdentity;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BrandIdentity> */
class BrandIdentityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id'   => Workspace::factory(),
            'description'    => $this->faker->sentence(10),
            'tone'           => $this->faker->randomElement(['ودّية', 'عصرية', 'رسمية']),
            'target_audience'=> null,
            'banned_words'   => [],
            'example_posts'  => [],
        ];
    }
}
