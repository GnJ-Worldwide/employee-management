<?php

namespace App\Filament\Resources\LedgerTransactionResource\Pages;

use App\Filament\Resources\LedgerTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLedgerTransaction extends EditRecord
{
    protected static string $resource = LedgerTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
