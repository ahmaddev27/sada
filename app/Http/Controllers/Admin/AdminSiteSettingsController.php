<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSiteSettingsController extends Controller
{
    public function __construct(
        private readonly SiteSettingsService $settings,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/SiteSettings/Index', [
            'groups' => $this->settings->grouped(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings'   => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->settings->bulkSet($data['settings']);

        return back()->with('flash', ['success' => 'تم حفظ الإعدادات بنجاح.']);
    }

    public function uploadImage(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
        ]);

        $allowedImageKeys = ['logo_path', 'logo_dark_path', 'favicon_path', 'og_image_path'];

        if (! in_array($key, $allowedImageKeys, true)) {
            return response()->json(['error' => 'مفتاح الصورة غير مسموح به.'], 422);
        }

        $url = $this->settings->storeImage($key, $request->file('image'));

        return response()->json(['url' => $url]);
    }
}
