<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Carbon\Carbon;

class LogViewerController extends Controller
{
    /**
     * Exibe a página de visualização de logs
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'queries'); // queries ou slow_queries

        // Busca as datas disponíveis primeiro
        $availableDates = $this->getAvailableDates($type);

        // Se não veio data na requisição, usa a primeira disponível
        $date = $request->get('date');
        if (!$date && count($availableDates) > 0) {
            $date = $availableDates[0];
        } elseif (!$date) {
            $date = now()->format('Y-m-d');
        }

        $logs = $this->getLogs($type, $date);
        $statistics = $this->getStatistics($logs);

        return Inertia::render('Logs/Index', [
            'type' => $type,
            'date' => $date,
            'logs' => $logs,
            'statistics' => $statistics,
            'availableDates' => $availableDates,
        ]);
    }

    /**
     * Busca as datas disponíveis para logs
     */
    private function getAvailableDates(string $type): array
    {
        $path = storage_path("logs/queries");

        if (!File::exists($path)) {
            return [];
        }

        $files = File::files($path);
        $dates = [];

        // Padrão específico para cada tipo
        if ($type === 'queries') {
            $pattern = '/^queries-(\d{4}-\d{2}-\d{2})\.log$/';
        } else {
            $pattern = '/^slow-queries-(\d{4}-\d{2}-\d{2})\.log$/';
        }

        foreach ($files as $file) {
            $filename = $file->getFilename();

            // Usa regex completo para evitar match em arquivos errados
            if (preg_match($pattern, $filename, $matches)) {
                $dates[] = $matches[1]; // A data está no grupo 1
            }
        }

        return array_unique(array_reverse($dates));
    }

    /**
     * Lê e parseia os logs de um dia específico
     */
    private function getLogs(string $type, string $date): array
    {
        $filename = $type === 'queries' ? 'queries' : 'slow-queries';
        $filepath = storage_path("logs/queries/{$filename}-{$date}.log");

        if (!File::exists($filepath)) {
            return [];
        }

        $content = File::get($filepath);
        $lines = explode("\n", $content);
        $logs = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // Parse da linha de log do Laravel
            // Formato: [2025-10-14 10:30:45] local.INFO: Query executada {"sql":"...","time":"..."}
            preg_match('/\[(.*?)\]\s+\w+\.(\w+):\s+(.*?)\s+(\{.*\})/', $line, $matches);

            if (count($matches) >= 5) {
                $context = json_decode($matches[4], true);

                $logs[] = [
                    'timestamp' => $matches[1],
                    'level' => strtolower($matches[2]),
                    'message' => $matches[3],
                    'sql' => $context['sql'] ?? '',
                    'time' => $context['time'] ?? '',
                    'connection' => $context['connection'] ?? '',
                    'url' => $context['url'] ?? null,
                    'user_id' => $context['user_id'] ?? null,
                ];
            }
        }

        return array_reverse($logs); // Mais recentes primeiro
    }

    /**
     * Calcula estatísticas dos logs
     */
    private function getStatistics(array $logs): array
    {
        if (empty($logs)) {
            return [
                'total' => 0,
                'average_time' => 0,
                'max_time' => 0,
                'min_time' => 0,
                'slowest_query' => null,
            ];
        }

        $times = array_map(function ($log) {
            return (float) str_replace('ms', '', $log['time']);
        }, $logs);

        $slowestIndex = array_search(max($times), $times);

        return [
            'total' => count($logs),
            'average_time' => round(array_sum($times) / count($times), 2) . 'ms',
            'max_time' => max($times) . 'ms',
            'min_time' => min($times) . 'ms',
            'slowest_query' => $logs[$slowestIndex] ?? null,
        ];
    }

    /**
     * Limpa logs antigos
     */
    public function clear(Request $request)
    {
        $type = $request->get('type', 'queries');
        $date = $request->get('date');

        if (!$date) {
            return redirect()->route('logs.index')
                ->with('error', 'Data não especificada');
        }

        $filename = $type === 'queries' ? 'queries' : 'slow-queries';
        $filepath = storage_path("logs/queries/{$filename}-{$date}.log");

        if (File::exists($filepath)) {
            File::delete($filepath);

            // Busca as datas disponíveis após a exclusão
            $availableDates = $this->getAvailableDates($type);

            // Se ainda existem datas disponíveis, redireciona para a primeira
            // Senão, redireciona sem data específica
            if (count($availableDates) > 0) {
                return redirect()->route('logs.index', [
                    'type' => $type,
                    'date' => $availableDates[0] // Primeira data disponível
                ])->with('success', 'Log excluído com sucesso');
            } else {
                return redirect()->route('logs.index', [
                    'type' => $type
                ])->with('success', 'Log excluído com sucesso. Nenhum log disponível.');
            }
        }

        return redirect()->route('logs.index')
            ->with('error', 'Arquivo não encontrado');
    }
}
