<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

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
        $data = AdminResource::collapseLocationVirtualFields($data);

        // Re-resolve state whenever GST changes on edit too
        if (!empty($data['gst_registration_no'])) {
            $data['gst_state'] = \App\Models\Admin::resolveGstState($data['gst_registration_no']);
        }
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return AdminResource::hydrateLocationVirtualFields($data);
    }
}