<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendor extends EditRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return VendorResource::hydrateLocationVirtualFields($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return VendorResource::collapseLocationVirtualFields($data);
    }
}
