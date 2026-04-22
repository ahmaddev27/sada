<?php

// CG-01→CG-11

namespace App\Http\Controllers;

use App\Actions\Content\GenerateContentAction;
use App\Actions\Content\SavePostAction;
use App\Http\Requests\Content\GenerateContentRequest;
use App\Http\Requests\Content\SavePostRequest;
use App\Models\SocialAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GenerateController extends Controller
{
    // CG-01→CG-04: generation page
    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        $socialAccounts = $workspace
            ? SocialAccount::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->where('status', 'healthy')
                ->get(['id', 'provider', 'account_name', 'provider_account_id'])
            : collect();

        return Inertia::render('Generate/Index', [
            'socialAccounts' => $socialAccounts,
        ]);
    }

    // CG-05: generate 3 variations via AI
    public function generate(
        GenerateContentRequest $request,
        GenerateContentAction $action,
    ): JsonResponse {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace) {
            return response()->json(['error' => 'اختر مساحة عمل أولاً.'], 422);
        }

        try {
            $result = $action->execute($workspace, $request->validated());

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'فشل توليد المحتوى. حاول مرة أخرى.'], 500);
        }
    }

    // CG-08: save generated content as draft / schedule / publish
    public function save(
        SavePostRequest $request,
        SavePostAction $action,
    ): RedirectResponse {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace) {
            return back()->with('flash', ['error' => 'اختر مساحة عمل أولاً.']);
        }

        $post = $action->execute($workspace, $request->validated());

        $messages = [
            'draft'    => 'تم حفظ المنشور كمسودة.',
            'schedule' => 'تم جدولة المنشور بنجاح.',
            'publish'  => 'تم نشر المنشور بنجاح.',
        ];

        return redirect()->route('generate.index')
            ->with('flash', ['success' => $messages[$request->input('action')] ?? 'تم الحفظ.']);
    }
}
