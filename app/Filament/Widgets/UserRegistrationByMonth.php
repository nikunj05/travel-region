<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class UserRegistrationByMonth extends ChartWidget
{
    protected ?string $heading = 'User Registration By Month';

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

        // registered users count by month name
        switch ($activeFilter) {
            case 'today':
                $userCount = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->whereDate('created_at', today())
                    ->groupBy('date')
                    ->pluck('count', 'date')
                    ->toArray();

                $labels = array_keys($userCount);
                $data = array_values($userCount);
                break;

            case 'week':
                $userCount = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date')
                    ->toArray();

                $labels = array_keys($userCount);
                $data = array_values($userCount);
                break;

            case 'month':
                $userCount = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date')
                    ->toArray();

                $labels = array_keys($userCount);
                $data = array_values($userCount);
                break;

            case 'year':
            default:
                $userCount = User::selectRaw('MONTH(created_at) as month_number, MONTHNAME(created_at) as month_name, COUNT(*) as count')
                    ->whereYear('created_at', now()->year)
                    ->groupBy('month_number', 'month_name')
                    ->orderBy('month_number')
                    ->pluck('count', 'month_name')
                    ->toArray();

                $labels = array_keys($userCount);
                $data = array_values($userCount);
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Users',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
