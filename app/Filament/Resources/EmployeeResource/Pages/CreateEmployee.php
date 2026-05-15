<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = EmployeeResource::collapseLocationVirtualFields($data);

        // If "Other" was selected in academics, persist the free-text value instead
        if (($data['academics'] ?? '') === 'Other') {
            $data['academics'] = $data['academics_other'] ?? 'Other';
        }
        unset($data['academics_other']);

        // Tag who created this record
        $data['created_by'] = Auth::user()?->email ?? 'system';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}