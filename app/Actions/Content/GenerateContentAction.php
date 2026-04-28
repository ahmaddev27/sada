<?php

// CG-01→CG-11

namespace App\Actions\Content;

use App\Models\AiGeneration;
use App\Models\BrandIdentity;
use App\Models\Workspace;
use App\Services\Ai\ContentGenerationService;
use App\Services\TokenService;
use Illuminate\Support\Facades\Auth;

class GenerateContentAction
{
    public function __construct(
        private readonly ContentGenerationService $service,
        private readonly TokenService $tokens,
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

        if (! $this->tokens->hasBalance($user, $tokensRequired)) {
            throw new \RuntimeException('رصيد التوكنز غير كافٍ. يرجى شحن المزيد.');
        }

        // Attach brand identity if requested
        $brandIdentity = null;
        if ($params['use_brand'] ?? true) {
            $brandIdentity = BrandIdentity::where('workspace_id', $workspace->id)->first();
        }

        $params['brand_identity']   = $brandIdentity ? [
            'description' => $brandIdentity->description,
            'tone'        => $brandIdentity->tone,
            'banned_words'=> $brandIdentity->banned_words ?? [],
        ] : null;
        $params['entity_type']    = $workspace->entity_type ?? 'business';
        $params['workspace_type'] = $workspace->business_type ?? null;

        $variations = $this->service->generate($params);

        // Record usage then deduct atomically via TokenService (BIL-02, BIL-04)
        $generation = AiGeneration::create([
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

        $this->tokens->deduct(
            $user,
            $tokensRequired,
            'توليد محتوى بالذكاء الاصطناعي',
            AiGeneration::class,
            $generation->id,
        );

        return [
            'variations'    => $variations,
            'tokens_charged'=> $tokensRequired,
        ];
    }
}
