<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSettingsService
{
    private const CACHE_KEY    = 'site_settings:all';
    private const CACHE_PUBLIC = 'site_settings:public';
    private const CACHE_TTL    = 3600; // 1 hour

    /**
     * Get a single setting value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    /**
     * Get all settings as a flat key => value map (with storage URLs for images).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SiteSetting::all()
                ->mapWithKeys(fn (SiteSetting $s) => [
                    $s->key => $this->resolveValue($s),
                ])
                ->all();
        });
    }

    /**
     * Get only public settings (safe to expose to frontend).
     *
     * @return array<string, mixed>
     */
    public function public(): array
    {
        return Cache::remember(self::CACHE_PUBLIC, self::CACHE_TTL, function () {
            return SiteSetting::where('is_public', true)
                ->get()
                ->mapWithKeys(fn (SiteSetting $s) => [
                    $s->key => $this->resolveValue($s),
                ])
                ->all();
        });
    }

    /**
     * Get all settings grouped, with full metadata (for admin page).
     *
     * @return array<string, list<array{key: string, value: mixed, type: string, label_ar: string, is_public: bool}>>
     */
    public function grouped(): array
    {
        $rows = SiteSetting::orderBy('group')->orderBy('sort_order')->get();

        $groups = [];
        foreach ($rows as $s) {
            $groups[$s->group][] = [
                'key'       => $s->key,
                'value'     => $this->resolveValue($s),
                'type'      => $s->type,
                'label_ar'  => $s->label_ar,
                'is_public' => $s->is_public,
            ];
        }
        return $groups;
    }

    /**
     * Update a single setting value.
     */
    public function set(string $key, ?string $value): void
    {
        SiteSetting::where('key', $key)->update(['value' => $value]);
        $this->flush();
    }

    /**
     * Bulk update multiple settings at once.
     *
     * @param array<string, string|null> $data
     */
    public function bulkSet(array $data): void
    {
        foreach ($data as $key => $value) {
            SiteSetting::where('key', $key)->update(['value' => $value]);
        }
        $this->flush();
    }

    /**
     * Store an uploaded image and update the setting.
     */
    public function storeImage(string $key, \Illuminate\Http\UploadedFile $file): string
    {
        // Delete old file if exists
        $old = SiteSetting::where('key', $key)->value('value');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        $path = $file->store('site', 'public');
        $this->set($key, (string) $path);
        return Storage::url((string) $path);
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_PUBLIC);
    }

    private function resolveValue(SiteSetting $s): mixed
    {
        if ($s->type === 'bool') {
            return filter_var($s->value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($s->type === 'image' && $s->value) {
            return Storage::url($s->value);
        }

        return $s->value;
    }
}
