<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class UserOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get user registrations for the last 7 days
        $userRegistrations = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();

        // Fill missing days with 0
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData[] = User::whereDate('created_at', $date)->count();
        }

        // Calculate new users today
        $newUsersToday = User::whereDate('created_at', today())->count();
        $changeDescription = "{$newUsersToday} new users today";

        return [
            Stat::make('Total Users', User::count())
                ->description($changeDescription)
                ->descriptionIcon('heroicon-o-user-group', IconPosition::Before)
                ->chart($chartData)
                ->color('success')
        ];
    }
}
