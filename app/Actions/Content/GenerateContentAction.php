<?php

// CG-01→CG-11

namespace App\Actions\Content;

use App\Models\AiGeneration;
use App\Models\BrandIdentity;
use App\Models\Workspace;
use App\Services\Ai\ContentGenerationService;
use Illuminate\Support\Facades\Auth;

class GenerateContentAction
{
    public function __construct(
        private readonly ContentGenerationService $service,
    ) {}

    /**
     * @param array<string, mixed> $params
     * @return array{
     *   variations: array<int, array{title: string, body: string, tags: string[], char_count: int}>,
     *   tokens_charged: int,
     * }
     */
    public function execute(Workspace $workspace, array $params): array
    {
        $user = Auth::user();

        if ($user === null) {
            throw new \RuntimeException('يجب تسجيل الدخول.');
        }

        // CG-10: check token balance before generating
        $tokensRequired = 40;

        if ($user->token_balance < $tokensRequired) {
            throw new \RuntimeException('رصيد التوكنز غير كافٍ. يرجى شحن المزيد.');
        }

        // Attach brand identity if requested
        $brandIdentity = null;
        if ($params['use_brand'] ?? true) {
            $brandIdentity = BrandIdentity::where('workspace_id', $workspace->id)->first();
        }

        $params['brand_identity'] = $brandIdentity ? [
            'description' => $brandIdentity->description,
            'tone'        => $brandIdentity->tone,
            'banned_words'=> $brandIdentity->banned_words ?? [],
        ] : null;

        $variations = $this->service->generate($params);

        // Record token usage
        AiGeneration::create([
            'workspace_id'        => $workspace->id,
            'user_id'             => $user->id,
            'agent_type'          => 'content_generator',
            'dialect'             => (string) ($params['dialect'] ?? 'fos'),
            'platform'            => (string) ($params['platform'] ?? 'instagram'),
            'content_type'        => (string) ($params['content_type'] ?? 'post'),
            'prompt'              => mb_substr((string) ($params['prompt'] ?? ''), 0, 500),
            'input_tokens'        => 0,
            'output_tokens'       => 0,
            'sada_tokens_charged' => $tokensRequired,
        ]);

        $user->decrement('token_balance', $tokensRequired);

        return [
            'variations'    => $variations,
            'tokens_charged'=> $tokensRequired,
        ];
    }
}
