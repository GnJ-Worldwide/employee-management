<?php

namespace App\Filament\Resources\ContractResource\RelationManagers;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';
    protected static ?string $title = 'Monthly Invoices';

    

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Vendor Invoice Number')
                    ->required()
                    ->maxLength(100),

                Forms\Components\DatePicker::make('invoice_date')
                    ->label('Invoice Date')
                    ->required()
                    ->native(false),
            ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\DatePicker::make('billing_period_start')
                    ->label('Billing Period — From')
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('billing_period_end')
                    ->label('Billing Period — To')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('billing_period_start'),
            ]),

            Forms\Components\TextInput::make('amount_billed')
                ->label('Amount Billed (₹)')
                ->required()
                ->numeric()
                ->prefix('₹')
                ->minValue(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice No.')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_period_start')
                    ->label('Billing Period')
                    ->formatStateUsing(fn (Invoice $record) =>
                        $record->billing_period_start->format('d M Y') .
                        ' → ' .
                        $record->billing_period_end->format('d M Y')
                    ),

                Tables\Columns\TextColumn::make('amount_billed')
                    ->label('Amount (₹)')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Submitted'          => 'info',
                        'Pending_Compliance' => 'warning',
                        'Approved'           => 'primary',
                        'Paid'               => 'success',
                        'Rejected'           => 'danger',
                    })
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', $state)),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Submit Invoice')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['submitted_on'] = now()->toDateString();
                        $data['status'] = 'Submitted';
                        return $data;
                    }),
            ])
            ->actions([
                // Navigate to the full InvoiceResource view page for workflow actions
                Tables\Actions\Action::make('view_full')
                    ->label('View Details')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Invoice $record) => InvoiceResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Invoice $record) => $record->status === 'Submitted'),
            ]);
    }
}