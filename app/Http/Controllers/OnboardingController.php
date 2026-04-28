<?php

namespace App\Http\Controllers;

use App\Actions\Workspace\CreateWorkspaceAction;
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// WS-01: first-run onboarding — step 1 workspace, step 2 social connect, step 3 completion
class OnboardingController extends Controller
{
    private const DIALECTS = [
        ['value' => 'sa',  'label' => 'السعودية 🇸🇦'],
        ['value' => 'ae',  'label' => 'الإمارات 🇦🇪'],
        ['value' => 'kw',  'label' => 'الكويت 🇰🇼'],
        ['value' => 'qa',  'label' => 'قطر 🇶🇦'],
        ['value' => 'bh',  'label' => 'البحرين 🇧🇭'],
        ['value' => 'om',  'label' => 'عُمان 🇴🇲'],
        ['value' => 'fos', 'label' => 'فصحى'],
    ];

    private const BUSINESS_TYPES = [
        'متجر إلكتروني',
        'مطعم وكافيه',
        'عيادة وصحة',
        'خدمات احترافية',
        'عقارات',
        'سياحة وسفر',
        'تعليم وتدريب',
        'وكالة تسويق',
        'أخرى',
    ];

    private const PERSONA_NICHES = [
        'نمط الحياة والفاشون',
        'طبخ وأكل',
        'رياضة ولياقة بدنية',
        'تقنية وتكنولوجيا',
        'ترفيه وكوميديا',
        'سفر وسياحة',
        'تعليم وتطوير الذات',
        'أخرى',
    ];

    public function show(Request $request): Response|RedirectResponse
    {
        $user      = $request->user();
        $workspace = $user->activeWorkspaces()->first();

        if ($workspace) {
            $step = $request->session()->get('onboarding_step');

            if ($step === 2) {
                return Inertia::render('Onboarding/Index', [
                    'step'          => 2,
                    'dialects'      => self::DIALECTS,
                    'businessTypes' => self::BUSINESS_TYPES,
                    'personaNiches' => self::PERSONA_NICHES,
                ]);
            }

            if ($step === 3) {
                return Inertia::render('Onboarding/Index', [
                    'step'          => 3,
                    'dialects'      => self::DIALECTS,
                    'businessTypes' => self::BUSINESS_TYPES,
                    'personaNiches' => self::PERSONA_NICHES,
                ]);
            }

            // Already fully onboarded
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/Index', [
            'step'          => 1,
            'dialects'      => self::DIALECTS,
            'businessTypes' => self::BUSINESS_TYPES,
            'personaNiches' => self::PERSONA_NICHES,
        ]);
    }

    public function storeWorkspace(CreateWorkspaceRequest $request, CreateWorkspaceAction $action): RedirectResponse
    {
        $user      = $request->user();
        $workspace = $action->execute(
            user:           $user,
            name:           $request->string('name')->trim()->toString(),
            entityType:     $request->string('entity_type')->toString() ?: 'business',
            businessType:   $request->string('business_type')->toString() ?: null,
            countries:      $request->array('countries') ?: ['sa'],
            defaultDialect: $request->string('default_dialect')->toString() ?: 'sa',
        );

        $request->session()->put('current_workspace_id', $workspace->id);
        $request->session()->put('onboarding_step', 2);

        $user->notify(new WelcomeNotification());

        return redirect()->route('onboarding');
    }

    public function skip(Request $request): RedirectResponse
    {
        $request->session()->put('onboarding_step', 3);

        return redirect()->route('onboarding');
    }

    public function complete(Request $request): RedirectResponse
    {
        $request->session()->forget('onboarding_step');

        return redirect()->route('dashboard')
            ->with('flash.success', 'مرحباً بك في صدى! ابدأ بتوليد أول منشور.');
    }
}
