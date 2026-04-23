<?php

namespace App\Http\Controllers;

use App\Actions\Workspace\CreateWorkspaceAction;
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// WS-01: first-run onboarding — step 1 workspace, step 2 social connect
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

    public function show(Request $request): Response|RedirectResponse
    {
        $user      = $request->user();
        $workspace = $user->activeWorkspaces()->first();

        if ($workspace) {
            // If session flag is set, user just created their first workspace → step 2
            if ($request->session()->get('onboarding_step') === 2) {
                return Inertia::render('Onboarding/Index', [
                    'step'          => 2,
                    'dialects'      => self::DIALECTS,
                    'businessTypes' => self::BUSINESS_TYPES,
                ]);
            }

            // Already fully onboarded
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/Index', [
            'step'          => 1,
            'dialects'      => self::DIALECTS,
            'businessTypes' => self::BUSINESS_TYPES,
        ]);
    }

    public function storeWorkspace(CreateWorkspaceRequest $request, CreateWorkspaceAction $action): RedirectResponse
    {
        $user      = $request->user();
        $workspace = $action->execute(
            user:           $user,
            name:           $request->string('name')->trim()->toString(),
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
        $request->session()->forget('onboarding_step');

        return redirect()->route('dashboard')
            ->with('flash.success', 'مرحباً بك في صدى! يمكنك ربط حساباتك لاحقاً من الإعدادات.');
    }
}
