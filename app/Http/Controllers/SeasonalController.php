<?php

// SE-01→SE-08: Seasonal engine

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeasonalController extends Controller
{
    public function index(Request $request): Response
    {
        $country = $request->query('country', 'all');

        $occasions = $this->loadOccasions($country);

        // SE-02: find the next upcoming occasion
        $upcoming = $this->findUpcoming($occasions);

        // SE-04: separate featured from the rest, sort by date
        $featured = array_values(array_filter($occasions, fn ($o) => $o['featured']));
        $all      = $occasions;

        $today = Carbon::now();
        $today->locale('ar');

        return Inertia::render('Seasonal/Index', [
            'occasions' => $all,
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
     * SE-03: generate content for a specific occasion — redirects to /generate
     *        with occasion context pre-filled via query params.
     */
    public function generate(string $key): \Illuminate\Http\RedirectResponse
    {
        /** @var array<int, array<string, mixed>> $all */
        $all      = config('seasonal.occasions', []);
        $occasion = null;
        foreach ($all as $o) {
            if (($o['key'] ?? '') === $key) {
                $occasion = $o;
                break;
            }
        }

        if ($occasion === null) {
            return redirect()->route('seasonal.index');
        }

        return redirect()->route('generate.index', [
            'occasion_key'  => $key,
            'occasion_name' => (string) ($occasion['name'] ?? ''),
            'hashtags'      => implode(',', (array) ($occasion['hashtags'] ?? [])),
        ]);
    }

    /**
     * @param string $country
     * @return array<int, array<string, mixed>>
     */
    private function loadOccasions(string $country): array
    {
        /** @var array<int, array<string, mixed>> $raw */
        $raw = config('seasonal.occasions', []);

        // Filter by country
        $filtered = $country === 'all'
            ? $raw
            : array_values(array_filter(
                $raw,
                fn (array $o) => in_array($country, (array) ($o['countries'] ?? []))
            ));

        $now = now();

        return array_map(function (array $o) use ($now): array {
            $date     = Carbon::parse((string) $o['date']);
            $endDate  = ! empty($o['end_date']) ? Carbon::parse((string) $o['end_date']) : null;

            $daysUntil = (int) $now->startOfDay()->diffInDays($date->startOfDay(), false);

            $status = match (true) {
                $endDate !== null && $now->between($date, $endDate) => 'active',
                ! $endDate && $daysUntil === 0                      => 'active',
                $daysUntil < 0                                      => 'passed',
                default                                              => 'upcoming',
            };

            $date->locale('ar');
            $countdown = match (true) {
                $status === 'active'  => 'جارٍ الآن',
                $status === 'passed'  => 'انتهى',
                $daysUntil === 0      => 'اليوم',
                $daysUntil === 1      => 'غداً',
                $daysUntil <= 30      => "بعد {$daysUntil} يوماً",
                default               => $date->translatedFormat('j M'),
            };

            return array_merge($o, [
                'days_until' => $daysUntil,
                'status'     => $status,
                'countdown'  => $countdown,
            ]);
        }, $filtered);
    }

    /**
     * SE-02: next upcoming (or active) occasion.
     *
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

        // Countdown breakdown for the hero
        $date     = Carbon::parse((string) ($next['date'] ?? 'today'));
        $diff     = now()->diff($date);
        $next['countdown_detail'] = [
            'days'    => max(0, (int) ($next['days_until'] ?? 0)),
            'hours'   => (int) $diff->h,
            'minutes' => (int) $diff->i,
        ];

        return $next;
    }

    private function toHijri(\Carbon\Carbon $date): string
    {
        // Simple Hijri approximation — for production use a proper Hijri library
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
