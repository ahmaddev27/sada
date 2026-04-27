<?php

// System health & diagnostics for admins

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AdminSystemController extends Controller
{
    public function index(): Response
    {
        // Database ping
        $dbStatus = $this->checkDb();

        // Redis / Cache ping
        $cacheStatus = $this->checkCache();

        // Queue stats via Horizon (graceful fallback)
        $queueStats = $this->checkQueue();

        // Disk space
        $diskFree  = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());
        $diskUsedPct = $diskTotal > 0 ? round((1 - $diskFree / $diskTotal) * 100, 1) : 0;

        $info = [
            'laravel_version' => app()->version(),
            'php_version'     => PHP_VERSION,
            'env'             => config('app.env'),
            'debug'           => config('app.debug') ? 'مفعّل' : 'معطّل',
            'timezone'        => config('app.timezone'),
            'queue_driver'    => config('queue.default'),
            'cache_driver'    => config('cache.default'),
            'db_driver'       => config('database.default'),
        ];

        return Inertia::render('Admin/System/Index', [
            'dbStatus'    => $dbStatus,
            'cacheStatus' => $cacheStatus,
            'queueStats'  => $queueStats,
            'disk'        => [
                'free_gb'   => round($diskFree / (1024 ** 3), 1),
                'total_gb'  => round($diskTotal / (1024 ** 3), 1),
                'used_pct'  => $diskUsedPct,
            ],
            'info' => $info,
        ]);
    }

    public function clearCache(Request $request): RedirectResponse
    {
        $what = $request->string('what', 'app')->toString();

        $cleared = match ($what) {
            'config' => tap('إعدادات', fn () => Artisan::call('config:clear')),
            'view'   => tap('عروض',    fn () => Artisan::call('view:clear')),
            'route'  => tap('مسارات',  fn () => Artisan::call('route:clear')),
            default  => tap('التطبيق', fn () => Artisan::call('cache:clear')),
        };

        AdminLog::create([
            'admin_id' => $request->user()->id,
            'action'   => 'clear_cache',
            'payload'  => ['type' => $what],
        ]);

        return back()->with('success', "تم مسح كاش {$cleared} بنجاح.");
    }

    public function optimize(Request $request): RedirectResponse
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        AdminLog::create([
            'admin_id' => $request->user()->id,
            'action'   => 'optimize',
            'payload'  => [],
        ]);

        return back()->with('success', 'تم تحسين الأداء: config + route + view cached.');
    }

    private function checkDb(): array
    {
        try {
            $start = microtime(true);
            DB::selectOne('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 1);

            return ['status' => 'ok', 'latency_ms' => $latency];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'admin_health_check_' . now()->timestamp;
            Cache::put($key, 'ok', 5);
            $ok = Cache::get($key) === 'ok';
            Cache::forget($key);

            return ['status' => $ok ? 'ok' : 'degraded'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            // Try Horizon stats if available
            if (class_exists(\Laravel\Horizon\Contracts\MetricsRepository::class)) {
                $metrics = app(\Laravel\Horizon\Contracts\MetricsRepository::class);
                return [
                    'driver'    => 'horizon',
                    'status'    => 'ok',
                    'processed' => $metrics->throughput() ?? 0,
                ];
            }

            // Fallback: count jobs table if using database driver
            if (config('queue.default') === 'database') {
                $pending  = DB::table('jobs')->count();
                $failed   = DB::table('failed_jobs')->count();
                return ['driver' => 'database', 'status' => 'ok', 'pending' => $pending, 'failed' => $failed];
            }

            return ['driver' => config('queue.default'), 'status' => 'unknown'];
        } catch (Throwable $e) {
            return ['driver' => config('queue.default'), 'status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
