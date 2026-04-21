<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Workspace> */
class WorkspaceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'name'            => $this->faker->company(),
            'business_type'   => $this->faker->randomElement(['تجارة إلكترونية', 'مطعم', 'خدمات', 'عقارات']),
            'countries'       => ['sa'],
            'default_dialect' => $this->faker->randomElement(['sa', 'ae', 'kw', 'fos']),
            'logo_path'       => null,
            'archived_at'     => null,
        ];
    }
}
