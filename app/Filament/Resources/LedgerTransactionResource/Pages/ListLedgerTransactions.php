<?php

namespace App\Filament\Resources\LedgerTransactionResource\Pages;

use App\Filament\Resources\LedgerTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLedgerTransactions extends ListRecords
{
    protected static string $resource = LedgerTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
