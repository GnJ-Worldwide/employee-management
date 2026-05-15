<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\PermissionRegistrar;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->hidden(
                    fn() => in_array($this->record->name, RoleResource::SYSTEM_ROLES, true)
                ),
        ];
    }

    protected function afterSave(): void
    {
        // The resource's `saveRelationshipsUsing` has already synced the permissions.
        // We just need to flush Spatie's cache so changes take effect immediately.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

}