<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class UserByGender extends ChartWidget
{
    protected ?string $heading = 'User By Gender';

    protected ?string $maxHeight = '250px';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter ?? 'today';

        // users gender count
        $userCount = User::selectRaw('gender, COUNT(*) as count')
            ->whereNotNull('gender')
            ->when($activeFilter, function ($query) use ($activeFilter) {
                if ($activeFilter === 'today') {
                    $query->whereDate('created_at', today());
                } elseif ($activeFilter === 'week') {
                    // last 7 full days including today
                    $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()]);
                } elseif ($activeFilter === 'month') {
                    // last 30 full days including today
                    $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()]);
                } elseif ($activeFilter === 'year') {
                    $query->whereYear('created_at', now()->year);
                }
            })
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Users by Gender',
                    'data' => array_values($userCount),
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                    ]
                ]
            ],
            'labels' => array_keys($userCount),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
