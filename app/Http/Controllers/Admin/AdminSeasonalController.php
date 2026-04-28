<?php

// SE-01→SE-08: Admin CRUD for seasonal occasions and their content templates

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeasonalOccasion;
use App\Models\SeasonalTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class AdminSeasonalController extends Controller
{
    public function index(): Response
    {
        $occasions = SeasonalOccasion::withCount(['templates' => fn ($q) => $q->where('active', true)])
            ->orderBy('sort_order')
            ->orderBy('date')
            ->get()
            ->map(fn ($o) => [
                'id'                 => $o->id,
                'key'                => $o->key,
                'name'               => $o->name,
                'subtitle'           => $o->subtitle,
                'date'               => $o->date->toDateString(),
                'end_date'           => $o->end_date?->toDateString(),
                'icon'               => $o->icon,
                'color'              => $o->color,
                'countries'          => $o->countries,
                'featured'           => $o->featured,
                'hashtags'           => $o->hashtags,
                'is_recurring'       => $o->is_recurring,
                'type'               => $o->type,
                'active'             => $o->active,
                'sort_order'         => $o->sort_order,
                'templates_count'    => $o->templates_count,
            ]);

        return Inertia::render('Admin/Seasonal/Index', [
            'occasions' => $occasions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key'          => 'required|string|unique:seasonal_occasions,key|regex:/^[a-z0-9_]+$/',
            'name'         => 'required|string|max:100',
            'subtitle'     => 'nullable|string|max:200',
            'date'         => 'required|date',
            'end_date'     => 'nullable|date|after_or_equal:date',
            'icon'         => 'required|string|max:30',
            'color'        => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'countries'    => 'required|array|min:1',
            'countries.*'  => 'in:sa,ae,kw,qa,bh,om',
            'featured'     => 'boolean',
            'hashtags'     => 'nullable|array',
            'hashtags.*'   => 'string|max:100',
            'is_recurring' => 'boolean',
            'type'         => 'required|in:islamic,national,commercial',
            'sort_order'   => 'integer|min:0',
        ]);

        SeasonalOccasion::create(array_merge($data, ['active' => true]));

        $this->flushCache();

        return back()->with('flash', ['success' => 'تم إضافة المناسبة بنجاح.']);
    }

    public function update(Request $request, SeasonalOccasion $occasion): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'subtitle'     => 'nullable|string|max:200',
            'date'         => 'required|date',
            'end_date'     => 'nullable|date|after_or_equal:date',
            'icon'         => 'required|string|max:30',
            'color'        => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'countries'    => 'required|array|min:1',
            'countries.*'  => 'in:sa,ae,kw,qa,bh,om',
            'featured'     => 'boolean',
            'hashtags'     => 'nullable|array',
            'hashtags.*'   => 'string|max:100',
            'is_recurring' => 'boolean',
            'type'         => 'required|in:islamic,national,commercial',
            'sort_order'   => 'integer|min:0',
        ]);

        $occasion->update($data);

        $this->flushCache();

        return back()->with('flash', ['success' => 'تم تحديث المناسبة بنجاح.']);
    }

    public function toggleActive(SeasonalOccasion $occasion): RedirectResponse
    {
        $occasion->update(['active' => ! $occasion->active]);

        $this->flushCache();

        $msg = $occasion->active ? 'تم تفعيل المناسبة.' : 'تم إيقاف المناسبة.';

        return back()->with('flash', ['success' => $msg]);
    }

    public function destroy(SeasonalOccasion $occasion): RedirectResponse
    {
        $occasion->delete();

        $this->flushCache();

        return back()->with('flash', ['success' => 'تم حذف المناسبة.']);
    }

    // ── Templates ─────────────────────────────────────────────────────────────

    public function templates(SeasonalOccasion $occasion): Response
    {
        $templates = $occasion->templates()
            ->get()
            ->map(fn ($t) => [
                'id'               => $t->id,
                'platform'         => $t->platform,
                'content_template' => $t->content_template,
                'hashtags'         => $t->hashtags,
                'tone'             => $t->tone,
                'active'           => $t->active,
                'sort_order'       => $t->sort_order,
            ]);

        return Inertia::render('Admin/Seasonal/Templates', [
            'occasion'  => [
                'id'   => $occasion->id,
                'key'  => $occasion->key,
                'name' => $occasion->name,
                'icon' => $occasion->icon,
            ],
            'templates' => $templates,
        ]);
    }

    public function storeTemplate(Request $request, SeasonalOccasion $occasion): RedirectResponse
    {
        $data = $request->validate([
            'platform'         => 'required|in:instagram,facebook,tiktok,snapchat,x,all',
            'content_template' => 'required|string|max:2000',
            'hashtags'         => 'nullable|array',
            'hashtags.*'       => 'string|max:100',
            'tone'             => 'nullable|string|max:50',
            'sort_order'       => 'integer|min:0',
        ]);

        $occasion->templates()->create(array_merge($data, ['active' => true]));

        return back()->with('flash', ['success' => 'تم إضافة القالب بنجاح.']);
    }

    public function updateTemplate(Request $request, SeasonalOccasion $occasion, SeasonalTemplate $template): RedirectResponse
    {
        abort_if($template->seasonal_occasion_id !== $occasion->id, 403);

        $data = $request->validate([
            'platform'         => 'required|in:instagram,facebook,tiktok,snapchat,x,all',
            'content_template' => 'required|string|max:2000',
            'hashtags'         => 'nullable|array',
            'hashtags.*'       => 'string|max:100',
            'tone'             => 'nullable|string|max:50',
            'sort_order'       => 'integer|min:0',
        ]);

        $template->update($data);

        return back()->with('flash', ['success' => 'تم تحديث القالب.']);
    }

    public function destroyTemplate(SeasonalOccasion $occasion, SeasonalTemplate $template): RedirectResponse
    {
        abort_if($template->seasonal_occasion_id !== $occasion->id, 403);

        $template->delete();

        return back()->with('flash', ['success' => 'تم حذف القالب.']);
    }

    private function flushCache(): void
    {
        Cache::forget('seasonal:occasions:active');
    }
}
