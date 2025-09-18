<?php

namespace App\Filament\Widgets;

use App\Models\Blog;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CountCardsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::role('customer')->count())
                ->icon(Heroicon::Users),
            Stat::make('Total FAQs', Faq::count())
                ->icon(Heroicon::QuestionMarkCircle),
            Stat::make('Total Testimonials', Testimonial::count())
                ->icon(Heroicon::Star),
            Stat::make('Total Blogs', Blog::count())
                ->icon(Heroicon::BookOpen),
        ];
    }
}
