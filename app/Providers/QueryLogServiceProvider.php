<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Só habilita logs de query em ambiente não production ou se configurado
        if (config('app.env') !== 'production' || config('app.log_queries', false)) {
            DB::listen(function ($query) {
                $sql = $query->sql;
                $bindings = $query->bindings;
                $time = $query->time;

                // Substitui placeholders pelos valores reais
                foreach ($bindings as $binding) {
                    $value = is_numeric($binding) ? $binding : "'{$binding}'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }

                // Monta o contexto do log
                $context = [
                    'sql' => $sql,
                    'time' => $time . 'ms',
                    'connection' => $query->connectionName,
                ];

                // Log todas as queries no canal 'queries'
                Log::channel('queries')->info('Query executada', $context);

                // Log queries lentas (> 100ms) no canal 'slow_queries'
                if ($time > 100) {
                    Log::channel('slow_queries')->warning('Query lenta detectada', array_merge($context, [
                        'threshold' => '100ms',
                        'url' => request()->fullUrl(),
                        'user_id' => auth()->id(),
                    ]));
                }
            });
        }
    }
}
