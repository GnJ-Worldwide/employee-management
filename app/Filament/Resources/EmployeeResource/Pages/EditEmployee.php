<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = EmployeeResource::collapseLocationVirtualFields($data);

        // If "Other" was selected, save the custom text input instead
        if (($data['academics'] ?? '') === 'Other') {
            $data['academics'] = $data['academics_other'] ?? 'Other';
        }

        unset($data['academics_other']);

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = EmployeeResource::hydrateLocationVirtualFields($data);

        // 1. Fetch the grouped options directly from the Resource
        $groupedOptions = EmployeeResource::academicsOptions();

        // 2. Flatten the grouped array to just get a single 1D array of the valid keys
        // Result: ['Before 10th', '10th Pass', '12th Pass', 'ITI Electrician', ...]
        $flatOptions = collect($groupedOptions)
            ->flatMap(fn($group) => array_keys($group))
            ->toArray();

        // 3. If the stored value isn't empty AND isn't in our standard options, 
        // it must be a custom string. Move it to the 'academics_other' text field.
        if (!empty($data['academics']) && !in_array($data['academics'], $flatOptions, true)) {
            $data['academics_other'] = $data['academics'];
            $data['academics'] = 'Other';
        }

        return $data;
    }
}