<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoringMiddleware {
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response {
        if (!config('app.performance_monitoring.enabled', true)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Enable query logging for performance monitoring
        if (config('app.log_queries', false)) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        // Monitor performance after request is processed
        $this->monitorPerformance($request, $startTime, $startMemory);

        return $response;
    }

    /**
     * Monitor and log performance metrics
     */
    private function monitorPerformance($request, $startTime, $startMemory) {
        try {
            $executionTime = (microtime(true) - $startTime) * 1000; // ms
            $memoryUsage = (memory_get_usage() - $startMemory) / 1024 / 1024; // MB
            $queryLog = config('app.log_queries', false) ? DB::getQueryLog() : [];
            $queryCount = count($queryLog);

            // Get thresholds from config
            $slowQueryThreshold = config('app.performance_monitoring.slow_query_threshold', 1000); // ms
            $highMemoryThreshold = config('app.performance_monitoring.high_memory_threshold', 128); // MB

            // Log slow requests
            if ($executionTime > $slowQueryThreshold) {
                Log::warning('Slow request detected', [
                    'endpoint' => $request->path(),
                    'method' => $request->method(),
                    'execution_time' => round($executionTime, 2) . 'ms',
                    'query_count' => $queryCount,
                    'slow_queries' => $this->getSlowQueries($queryLog, 100), // queries > 100ms
                ]);
            }

            // Log high memory usage
            if ($memoryUsage > $highMemoryThreshold) {
                Log::warning('High memory usage detected', [
                    'endpoint' => $request->path(),
                    'method' => $request->method(),
                    'memory_usage' => round($memoryUsage, 2) . 'MB',
                    'query_count' => $queryCount,
                ]);
            }

            // Log queries with N+1 problem (high query count)
            if ($queryCount > 50) {
                Log::warning('Potential N+1 query problem', [
                    'endpoint' => $request->path(),
                    'method' => $request->method(),
                    'query_count' => $queryCount,
                    'execution_time' => round($executionTime, 2) . 'ms',
                    'suggestion' => 'Consider using eager loading or query optimization',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Performance monitoring failed', [
                'error' => $e->getMessage(),
                'endpoint' => $request->path()
            ]);
        }
    }

    /**
     * Get slow queries from query log
     */
    private function getSlowQueries(array $queryLog, int $thresholdMs = 100): array {
        $slowQueries = [];

        foreach ($queryLog as $query) {
            if (isset($query['time']) && $query['time'] > $thresholdMs) {
                $slowQueries[] = [
                    'query' => $query['query'],
                    'time' => round($query['time'], 2) . 'ms',
                    'bindings' => $query['bindings'] ?? [],
                ];
            }
        }

        return $slowQueries;
    }
}
