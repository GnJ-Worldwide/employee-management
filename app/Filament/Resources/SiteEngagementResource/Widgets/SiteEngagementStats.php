<?php

namespace App\Filament\Resources\SiteEngagementResource\Widgets;

use App\Models\Employee;
use App\Models\EmployeeSiteEngagement;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteEngagementStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = today();

        return [

            Stat::make(
                'Total Active Employees',
                Employee::active()->count()
            )
                ->description('Currently active workforce')
                ->color('primary')
                ->icon('heroicon-m-users'),

            Stat::make(
                'Currently Engaged',
                Employee::engaged()->count()
            )
                ->description('Assigned to sites')
                ->color('success')
                ->icon('heroicon-m-link'),

            Stat::make(
                'Available Workforce',
                Employee::available()->count()
            )
                ->description('Ready for deployment')
                ->color('gray')
                ->icon('heroicon-m-check-circle'),

            Stat::make(
                'Permanent Employees',
                Employee::active()
                    ->where('is_permanent', true)
                    ->count()
            )
                ->description('Permanent workforce')
                ->color('info')
                ->icon('heroicon-m-shield-check'),

            Stat::make(
                'Active Vendors',
                Vendor::whereHas('employees', function ($q) {
                    $q->where('engagement_status', 'Engaged');
                })->count()
            )
                ->description('Sites with manpower')
                ->color('warning')
                ->icon('heroicon-m-building-office'),

            Stat::make(
                "Today's Engagements",
                EmployeeSiteEngagement::whereDate('engaged_date', $today)
                    ->count()
            )
                ->description('Employees engaged today')
                ->color('success')
                ->icon('heroicon-m-arrow-trending-up'),

            Stat::make(
                "Today's Releases",
                EmployeeSiteEngagement::whereDate('released_date', $today)
                    ->count()
            )
                ->description('Employees released today')
                ->color('danger')
                ->icon('heroicon-m-arrow-uturn-left'),
        ];
    }
}