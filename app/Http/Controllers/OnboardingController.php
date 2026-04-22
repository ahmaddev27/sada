<?php

namespace App\Http\Controllers;

use App\Actions\Workspace\CreateWorkspaceAction;
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// WS-01: first-run onboarding after registration
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

        // Already has workspace — go to dashboard (onboarding done)
        if ($workspace) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding/Index', [
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

        $user->notify(new WelcomeNotification());

        return redirect()->route('dashboard')
            ->with('flash.success', 'مرحباً بك في صدى! تم إنشاء مساحة عملك.');
    }
}
