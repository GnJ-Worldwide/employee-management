<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\PermissionRegistrar;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Because the 'module_permissions' field in the Resource is set to `dehydrated(false)`, 
        // it is automatically stripped out before reaching here. We only need to ensure 
        // the guard_name is set.
        $data['guard_name'] ??= 'web';

        return $data;
    }

    protected function afterCreate(): void
    {
        // The resource's `saveRelationshipsUsing` has already synced the permissions.
        // We just need to flush Spatie's cache so changes take effect immediately.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }


}