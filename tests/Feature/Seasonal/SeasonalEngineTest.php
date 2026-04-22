<?php

// SE-01→SE-08: Seasonal engine tests

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

// ── helpers ──────────────────────────────────────────────────────────────────

function makeUserWithWorkspace(): User
{
    $user      = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $user->update(['current_workspace_id' => $workspace->id]);
    return $user;
}

function fakeTwoOccasions(): void
{
    Config::set('seasonal.occasions', [
        [
            'key'       => 'eid-fitr',
            'name'      => 'عيد الفطر',
            'subtitle'  => 'فطر مبارك',
            'date'      => now()->addDays(5)->format('Y-m-d'),
            'end_date'  => now()->addDays(7)->format('Y-m-d'),
            'icon'      => '🌙',
            'color'     => '#0F6F5C',
            'countries' => ['all'],
            'templates' => ['تهنئة', 'عرض خاص'],
            'featured'  => true,
            'hashtags'  => ['عيد_الفطر', 'عيد_مبارك'],
            'is_recurring' => true,
        ],
        [
            'key'       => 'national-day-sa',
            'name'      => 'اليوم الوطني السعودي',
            'subtitle'  => '94 عاماً من العطاء',
            'date'      => now()->addDays(30)->format('Y-m-d'),
            'end_date'  => null,
            'icon'      => '🇸🇦',
            'color'     => '#006C35',
            'countries' => ['SA'],
            'templates' => ['تهنئة وطنية'],
            'featured'  => false,
            'hashtags'  => ['اليوم_الوطني'],
            'is_recurring' => true,
        ],
    ]);
}

// ── SE-01: page renders for authenticated user ────────────────────────────────

it('SE-01: renders seasonal index page for authenticated user', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Seasonal/Index'));
});

// ── SE-01: guest is redirected ────────────────────────────────────────────────

it('SE-01: redirects unauthenticated visitor to login', function (): void {
    get('/seasonal')->assertRedirect('/login');
});

// ── SE-02: occasions are passed to the view ───────────────────────────────────

it('SE-02: passes occasions array to the view', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal')
        ->assertInertia(fn ($page) => $page
            ->has('occasions')
            ->has('upcoming')
            ->has('featured')
            ->has('country')
            ->has('today.gregorian')
            ->has('today.hijri')
        );
});

// ── SE-02: upcoming occasion is identified ────────────────────────────────────

it('SE-02: upcoming occasion is the soonest future occasion', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal')
        ->assertInertia(fn ($page) => $page
            ->where('upcoming.key', 'eid-fitr')
        );
});

// ── SE-04: featured filter works ─────────────────────────────────────────────

it('SE-04: featured occasions are separated', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal')
        ->assertInertia(fn ($page) => $page
            ->has('featured', 1)
            ->where('featured.0.key', 'eid-fitr')
        );
});

// ── SE-05: country filter returns only matching occasions ─────────────────────

it('SE-05: country=SA returns only Saudi occasions', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal?country=SA')
        ->assertInertia(fn ($page) => $page
            ->has('occasions', 1)
            ->where('occasions.0.key', 'national-day-sa')
            ->where('country', 'SA')
        );
});

// ── SE-05: country=all returns all occasions ──────────────────────────────────

it('SE-05: country=all returns every occasion', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal?country=all')
        ->assertInertia(fn ($page) => $page
            ->has('occasions', 2)
        );
});

// ── SE-06: countdown strings are attached ─────────────────────────────────────

it('SE-06: occasions have days_until, status, and countdown fields', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal')
        ->assertInertia(fn ($page) => $page
            ->has('occasions.0.days_until')
            ->has('occasions.0.status')
            ->has('occasions.0.countdown')
        );
});

// ── SE-06: active occasion gets "جارٍ الآن" countdown ───────────────────────

it('SE-06: active occasion shows جارٍ الآن in countdown', function (): void {
    Config::set('seasonal.occasions', [[
        'key'          => 'ramadan',
        'name'         => 'رمضان الكريم',
        'subtitle'     => 'شهر العطاء',
        'date'         => now()->subDays(2)->format('Y-m-d'),
        'end_date'     => now()->addDays(3)->format('Y-m-d'),
        'icon'         => '🌙',
        'color'        => '#0F6F5C',
        'countries'    => ['all'],
        'templates'    => [],
        'featured'     => true,
        'hashtags'     => ['رمضان_كريم'],
        'is_recurring' => true,
    ]]);

    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal')
        ->assertInertia(fn ($page) => $page
            ->where('occasions.0.status', 'active')
            ->where('occasions.0.countdown', 'جارٍ الآن')
        );
});

// ── SE-06: passed occasion gets "انتهى" countdown ────────────────────────────

it('SE-06: past occasion shows انتهى in countdown', function (): void {
    Config::set('seasonal.occasions', [[
        'key'          => 'old-event',
        'name'         => 'مناسبة قديمة',
        'subtitle'     => '',
        'date'         => now()->subDays(10)->format('Y-m-d'),
        'end_date'     => null,
        'icon'         => '📅',
        'color'        => '#C8965F',
        'countries'    => ['all'],
        'templates'    => [],
        'featured'     => false,
        'hashtags'     => [],
        'is_recurring' => false,
    ]]);

    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal')
        ->assertInertia(fn ($page) => $page
            ->where('occasions.0.status', 'passed')
            ->where('occasions.0.countdown', 'انتهى')
        );
});

// ── SE-03: generate redirects to /generate with occasion context ──────────────

it('SE-03: generate redirects to generate page with occasion query params', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal/eid-fitr/generate')
        ->assertRedirect()
        ->assertRedirectContains('/generate')
        ->assertRedirectContains('occasion_key=eid-fitr');
});

// ── SE-03: generate with unknown key redirects back to seasonal index ─────────

it('SE-03: generate with unknown key redirects to seasonal index', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal/unknown-key/generate')
        ->assertRedirect(route('seasonal.index'));
});

// ── SE-07: countdown_detail breakdown is attached to upcoming ─────────────────

it('SE-07: upcoming occasion has countdown_detail with days, hours, minutes', function (): void {
    fakeTwoOccasions();
    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal')
        ->assertInertia(fn ($page) => $page
            ->has('upcoming.countdown_detail.days')
            ->has('upcoming.countdown_detail.hours')
            ->has('upcoming.countdown_detail.minutes')
        );
});

// ── SE-07: no upcoming returns null ──────────────────────────────────────────

it('SE-07: returns null for upcoming when all occasions are passed', function (): void {
    Config::set('seasonal.occasions', [[
        'key'          => 'past-event',
        'name'         => 'مناسبة سابقة',
        'subtitle'     => '',
        'date'         => now()->subDays(5)->format('Y-m-d'),
        'end_date'     => null,
        'icon'         => '📅',
        'color'        => '#C8965F',
        'countries'    => ['all'],
        'templates'    => [],
        'featured'     => false,
        'hashtags'     => [],
        'is_recurring' => false,
    ]]);

    $user = makeUserWithWorkspace();

    actingAs($user)
        ->get('/seasonal')
        ->assertInertia(fn ($page) => $page->where('upcoming', null));
});
