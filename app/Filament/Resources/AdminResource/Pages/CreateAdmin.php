<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = AdminResource::collapseLocationVirtualFields($data);

        // Ensure gst_state is resolved even if JS was disabled
        if (!empty($data['gst_registration_no']) && empty($data['gst_state'])) {
            $data['gst_state'] = \App\Models\Admin::resolveGstState($data['gst_registration_no']);
        }
        return $data;
    }
}