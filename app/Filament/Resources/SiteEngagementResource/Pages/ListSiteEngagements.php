<?php
namespace App\Filament\Resources\SiteEngagementResource\Pages;
 
use App\Filament\Resources\SiteEngagementResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\SiteEngagementResource\Widgets\SiteEngagementStats;
 
class ListSiteEngagements extends ListRecords
{
    protected static string $resource = SiteEngagementResource::class;
 
    
    // No "Create" button — employees are managed in EmployeeResource
    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SiteEngagementStats::class,
        ];
    }
}