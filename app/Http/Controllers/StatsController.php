<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        $total = Report::count();

        $byStatus = Report::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'status_label' => $this->statusLabel($row->status),
                'total' => $row->total,
            ]);

        $byCategory = Report::selectRaw('categories.id, categories.name, count(reports.id) as total')
            ->leftJoin('categories', 'reports.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'category_id' => $row->id,
                'category' => $row->name,
                'total' => $row->total,
            ]);

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $key = $month->format('Y-m');
            $months[$key] = [
                'month' => $key,
                'label' => $month->format('M Y'),
                'total' => 0,
            ];
        }

        Report::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as total")
            ->where('created_at', '>=', now()->startOfMonth()->subMonths(5))
            ->groupBy('month')
            ->get()
            ->each(function ($row) use (&$months) {
                if (isset($months[$row->month])) {
                    $months[$row->month]['total'] = $row->total;
                }
            });

        return response()->json([
            'total' => $total,
            'by_status' => $byStatus,
            'by_category' => $byCategory,
            'by_month' => array_values($months),
        ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'dilaporkan' => 'Dilaporkan',
            'diverifikasi' => 'Diverifikasi',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            default => $status,
        };
    }
}
