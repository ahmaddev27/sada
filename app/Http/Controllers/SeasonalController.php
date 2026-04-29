<?php

// SE-01→SE-08: Seasonal engine — reads occasions from DB (with Redis cache)

namespace App\Http\Controllers;

use App\Models\SeasonalOccasion;
use App\Services\FeatureFlagService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SeasonalController extends Controller
{
    private const CACHE_TTL = 3600; // 1 hour

    public function index(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        if (! app(FeatureFlagService::class)->isEnabled('seasonal_engine')) {
            return redirect()->route('dashboard')
                ->with('flash', ['error' => 'محرك الحملات الموسمية معطّل مؤقتاً.']);
        }

        $country = $request->query('country', 'all');

        $occasions = $this->loadOccasions($country);

        $upcoming = $this->findUpcoming($occasions);
        $featured = array_values(array_filter($occasions, fn ($o) => $o['featured']));

        $today = Carbon::now();
        $today->locale('ar');

        return Inertia::render('Seasonal/Index', [
            'occasions' => $occasions,
            'upcoming'  => $upcoming,
            'featured'  => $featured,
            'country'   => $country,
            'today'     => [
                'gregorian' => $today->translatedFormat('j F Y'),
                'hijri'     => $this->toHijri($today),
            ],
        ]);
    }

    /**
     * SE-03: redirect to /generate with occasion context.
     */
    public function generate(string $key): \Illuminate\Http\RedirectResponse
    {
        /** @var SeasonalOccasion|null $occasion */
        $occasion = $this->getAllOccasions()->firstWhere('key', $key);

        if ($occasion === null) {
            return redirect()->route('seasonal.index');
        }

        return redirect()->route('generate.index', [
            'occasion_key'  => $key,
            'occasion_name' => $occasion->name,
            'hashtags'      => implode(',', $occasion->hashtags ?? []),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadOccasions(string $country): array
    {
        $raw = $this->getAllOccasions();

        /** @var array<int, SeasonalOccasion> $filtered */
        $filtered = $country === 'all'
            ? $raw->all()
            : $raw->filter(fn ($o) => in_array($country, $o->countries ?? []))->values()->all();

        $now = now();

        return array_map(function (SeasonalOccasion $o) use ($now): array {
            $date    = Carbon::parse($o->date);
            $endDate = $o->end_date ? Carbon::parse($o->end_date) : null;

            $daysUntil = (int) $now->copy()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);

            $status = match (true) {
                $endDate !== null && $now->between($date, $endDate) => 'active',
                ! $endDate && $daysUntil === 0                      => 'active',
                $daysUntil < 0                                      => 'passed',
                default                                              => 'upcoming',
            };

            $date->locale('ar');
            $countdown = match (true) {
                $status === 'active' => 'جارٍ الآن',
                $status === 'passed' => 'انتهى',
                $daysUntil === 0     => 'اليوم',
                $daysUntil === 1     => 'غداً',
                $daysUntil <= 30     => "بعد {$daysUntil} يوماً",
                default              => $date->translatedFormat('j M'),
            };

            return array_merge($o->toArray(), [
                'date'       => $o->date->toDateString(),
                'end_date'   => $o->end_date?->toDateString(),
                'days_until' => $daysUntil,
                'status'     => $status,
                'countdown'  => $countdown,
            ]);
        }, $filtered);
    }

    private function getAllOccasions(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('seasonal:occasions:active', self::CACHE_TTL, function () {
            return SeasonalOccasion::active()->orderBy('sort_order')->orderBy('date')->get();
        });
    }

    /**
     * @param array<int, array<string, mixed>> $occasions
     * @return array<string, mixed>|null
     */
    private function findUpcoming(array $occasions): ?array
    {
        $active   = array_filter($occasions, fn ($o) => $o['status'] === 'active');
        $upcoming = array_filter($occasions, fn ($o) => $o['status'] === 'upcoming');

        if (! empty($active)) {
            return reset($active);
        }

        if (empty($upcoming)) {
            return null;
        }

        usort($upcoming, fn ($a, $b) => (int) ($a['days_until'] ?? 0) <=> (int) ($b['days_until'] ?? 0));

        $next = reset($upcoming);

        $date = Carbon::parse((string) ($next['date'] ?? 'today'));
        $diff = now()->diff($date);
        $next['countdown_detail'] = [
            'days'    => max(0, (int) ($next['days_until'] ?? 0)),
            'hours'   => (int) $diff->h,
            'minutes' => (int) $diff->i,
        ];

        return $next;
    }

    private function toHijri(\Carbon\Carbon $date): string
    {
        $jd    = gregoriantojd($date->month, $date->day, $date->year);
        $l     = $jd - 1948440 + 10632;
        $n     = (int) (($l - 1) / 10631);
        $l     = $l - 10631 * $n + 354;
        $j     = (int) ((10985 - $l) / 5316) * (int) ((50 * $l) / 17719)
               + (int) ($l / 5670) * (int) ((43 * $l) / 15238);
        $l     = $l - (int) ((30 - $j) / 15) * (int) ((17719 * $j) / 50)
               - (int) ($j / 16) * (int) ((15238 * $j) / 43) + 29;
        $month = (int) ((24 * $l) / 709);
        $day   = $l - (int) ((709 * $month) / 24);
        $year  = 30 * $n + $j - 30;

        $MONTHS = ['محرم','صفر','ربيع الأول','ربيع الثاني','جمادى الأولى','جمادى الآخرة','رجب','شعبان','رمضان','شوال','ذو القعدة','ذو الحجة'];

        return "{$day} " . ($MONTHS[$month - 1] ?? '') . " {$year}هـ";
    }
}
