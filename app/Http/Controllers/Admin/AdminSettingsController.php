<?php

// Admin platform settings: feature flags & token pricing

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\TokenPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class AdminSettingsController extends Controller
{
    private const FLAGS_CACHE_KEY = 'admin:feature_flags';
    private const FLAGS_TTL = 3600; // 1 hour

    public function index(): Response
    {
        $flags    = $this->getFlags();
        $packages = TokenPackage::orderBy('sort_order')->get();

        return Inertia::render('Admin/Settings/Index', [
            'flags'    => $flags,
            'packages' => $packages,
        ]);
    }

    public function updateFlags(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'flags'           => ['required', 'array'],
            'flags.*.key'     => ['required', 'string', 'max:100'],
            'flags.*.enabled' => ['required', 'boolean'],
        ]);

        $current = $this->getFlags();
        $updated = collect($data['flags'])->keyBy('key')->map(fn ($f) => $f['enabled'])->toArray();

        $newFlags = array_merge(
            collect($current)->keyBy('key')->toArray(),
            collect($updated)->map(fn ($v, $k) => ['key' => $k, 'enabled' => $v])->toArray()
        );

        Cache::put(self::FLAGS_CACHE_KEY, array_values($newFlags), self::FLAGS_TTL);

        AdminLog::create([
            'admin_id' => $request->user()->id,
            'action'   => 'update_feature_flags',
            'payload'  => ['flags' => $updated],
        ]);

        return back()->with('success', 'تم حفظ إعدادات الميزات.');
    }

    public function updatePackage(Request $request, TokenPackage $package): RedirectResponse
    {
        $data = $request->validate([
            'price'      => ['required', 'numeric', 'min:1', 'max:99999'],
            'tokens'     => ['required', 'integer', 'min:100', 'max:10000000'],
            'is_active'  => ['required', 'boolean'],
            'is_popular' => ['required', 'boolean'],
        ]);

        $package->update($data);

        AdminLog::create([
            'admin_id'    => $request->user()->id,
            'action'      => 'update_token_package',
            'target_type' => 'token_package',
            'target_id'   => $package->id,
            'payload'     => $data,
        ]);

        return back()->with('success', 'تم تحديث الباقة.');
    }

    /** @return array<int, array{key: string, label: string, enabled: bool}> */
    private function getFlags(): array
    {
        return Cache::remember(self::FLAGS_CACHE_KEY, self::FLAGS_TTL, fn () => [
            ['key' => 'ai_generation',        'label' => 'توليد المحتوى بالذكاء الاصطناعي', 'enabled' => true],
            ['key' => 'paid_campaigns',       'label' => 'الحملات الإعلانية المدفوعة',       'enabled' => true],
            ['key' => 'tiktok_integration',   'label' => 'تكامل TikTok',                   'enabled' => false],
            ['key' => 'snapchat_integration', 'label' => 'تكامل Snapchat',                 'enabled' => false],
            ['key' => 'x_integration',        'label' => 'تكامل X (تويتر)',                'enabled' => false],
            ['key' => 'linkedin_integration', 'label' => 'تكامل LinkedIn',                 'enabled' => false],
            ['key' => 'seasonal_engine',      'label' => 'محرك الحملات الموسمية',           'enabled' => true],
            ['key' => 'analytics_ai',         'label' => 'تحليلات الذكاء الاصطناعي',        'enabled' => true],
            ['key' => 'billing',              'label' => 'نظام الفواتير والدفع',            'enabled' => true],
        ]);
    }
}
