<?php

namespace App\Filament\Resources\LedgerTransactionResource\Pages;

use App\Filament\Resources\LedgerTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLedgerTransaction extends CreateRecord
{
    protected static string $resource = LedgerTransactionResource::class;
}
