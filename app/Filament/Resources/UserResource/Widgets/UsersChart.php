<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class UsersChart extends ApexChartWidget
{
    protected static ?string $chartId = 'usersChart';

    protected static ?string $heading = 'User Growth';

    protected function getOptions(): array
    {
        // Ambil data user per bulan (tahun ini)
        $usersPerMonth = User::query()
            ->selectRaw('COUNT(*) as total, EXTRACT(MONTH FROM created_at) as month')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Pastikan semua bulan ada meskipun 0
        $data = [];
        $categories = [];
        foreach (range(1, 12) as $m) {
            $categories[] = now()->startOfYear()->month($m)->format('M');
            $data[] = $usersPerMonth[$m] ?? 0;
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => [
                    'show' => true,
                ],
                'zoom' => [
                    'enabled' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'New Users',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => ['text' => 'Users'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f97316'],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.4,
                    'opacityTo' => 0.1,
                ],
            ],
            'markers' => [
                'size' => 5,
            ],
               'dataLabels' => [
                'enabled' => false, // angka di chart hilang
            ],
        ];
    }
}
