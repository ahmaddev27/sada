<?php

namespace Database\Factories;

use App\Models\SocialAccount;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialAccountFactory extends Factory
{
    protected $model = SocialAccount::class;

    public function definition(): array
    {
        return [
            'workspace_id'        => Workspace::factory(),
            'provider'            => $this->faker->randomElement(['instagram', 'facebook']),
            'provider_account_id' => (string) $this->faker->randomNumber(8, true),
            'account_name'        => $this->faker->company(),
            'account_picture_url' => null,
            'access_token'        => 'EAAtest' . $this->faker->lexify('??????????????????????'),
            'refresh_token'       => null,
            'token_expires_at'    => now()->addDays(60),
            'status'              => 'healthy',
            'scopes'              => ['instagram_basic', 'pages_manage_posts'],
            'metadata'            => [],
        ];
    }

    public function expired(): static
    {
        return $this->state(['status' => 'expired', 'token_expires_at' => now()->subDay()]);
    }

    public function revoked(): static
    {
        return $this->state(['status' => 'revoked']);
    }

    public function instagram(): static
    {
        return $this->state([
            'provider' => 'instagram',
            'scopes'   => ['instagram_basic', 'instagram_content_publish'],
        ]);
    }

    public function facebook(): static
    {
        return $this->state([
            'provider' => 'facebook',
            'scopes'   => ['pages_manage_posts', 'pages_read_engagement'],
        ]);
    }
}
