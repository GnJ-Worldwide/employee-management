<?php

namespace App\Filament\Resources\AttendanceResource\Pages;
 
use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
 
class EditAttendanceRecord extends EditRecord
{
    protected static string $resource = AttendanceResource::class;
 
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
 