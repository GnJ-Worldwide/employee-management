<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LedgerTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'ledgerTransactions';

    protected static ?string $title = 'Ledger Transactions';

    public function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([

    //             Forms\Components\DatePicker::make('transaction_date')
    //                 ->label('Transaction Date')
    //                 ->required(),

    //             Forms\Components\Select::make('contract_id')
    //                 ->label('Contract')
    //                 ->relationship('contract', 'contract_title')
    //                 ->searchable()
    //                 ->preload()
    //                 ->nullable(),

    //             Forms\Components\Select::make('type')
    //                 ->label('Transaction Type')
    //                 ->options([
    //                     'debit' => 'Debit',
    //                     'credit' => 'Credit',
    //                 ])
    //                 ->helperText('Debit = Money Given / Expense | Credit = Money Received / Settlement')
    //                 ->required()
    //                 ->native(false),

    //             Forms\Components\TextInput::make('category')
    //                 ->label('Category')
    //                 ->placeholder('Advance, Salary, Material Purchase')
    //                 ->helperText('Examples: Advance, Salary, Material Purchase, Expense Settlement, Transport, Food, Site Expense, Bonus, Penalty')
    //                 ->maxLength(255),

    //             Forms\Components\TextInput::make('expense_type')
    //                 ->label('Expense Type')
    //                 ->placeholder('Plumbing, Electrical, Civil')
    //                 ->helperText('Examples: Plumbing, Electrical, Civil, Painting, Hotel, Fuel')
    //                 ->maxLength(255),

    //             Forms\Components\TextInput::make('amount')
    //                 ->label('Amount')
    //                 ->numeric()
    //                 ->prefix('₹')
    //                 ->required(),

    //             Forms\Components\TextInput::make('vendor_name')
    //                 ->label('Vendor / Store Name')
    //                 ->placeholder('Shree Plumbing Store')
    //                 ->maxLength(255),

    //             Forms\Components\TextInput::make('bill_number')
    //                 ->label('Bill Number')
    //                 ->placeholder('INV-2025-001')
    //                 ->maxLength(255),

    //             Forms\Components\FileUpload::make('bill_image')
    //                 ->label('Bill Image / Invoice')
    //                 ->directory('ledger-bills')
    //                 ->disk('public')
    //                 ->imagePreviewHeight('150')
    //                 ->downloadable()
    //                 ->openable()
    //                 ->acceptedFileTypes([
    //                     'image/jpeg',
    //                     'image/png',
    //                     'image/webp',
    //                     'application/pdf',
    //                 ])
    //                 ->helperText('Upload bill image, receipt, or invoice PDF.'),

    //             Forms\Components\TextInput::make('reference_no')
    //                 ->label('Reference Number')
    //                 ->placeholder('ADV-001 / TXN-2025-001')
    //                 ->maxLength(255),

    //             Forms\Components\Textarea::make('description')
    //                 ->label('Description / Notes')
    //                 ->placeholder('Employee purchased plumbing material for site work')
    //                 ->columnSpanFull(),

    //         ])
    //         ->columns(2);
    // }
    {
    return $form
        ->schema([

            Forms\Components\DatePicker::make('transaction_date')
                ->label('Transaction Date')
                ->required(),

            Forms\Components\Select::make('employee_id')
                ->relationship('employee', 'first_name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('contract_id')
                ->label('Contract')
                ->relationship('contract', 'contract_title')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Select::make('type')
                ->options([
                    'debit' => 'Debit',
                    'credit' => 'Credit',
                ])
                ->required(),

            Forms\Components\TextInput::make('category'),

            Forms\Components\TextInput::make('expense_type'),

            Forms\Components\TextInput::make('amount')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('vendor_name'),

            Forms\Components\TextInput::make('bill_number'),

            Forms\Components\FileUpload::make('bill_image')
                ->directory('ledger-bills')
                ->disk('public'),

            Forms\Components\TextInput::make('reference_no'),

            Forms\Components\Textarea::make('description')
                ->columnSpanFull(),

        ])
        ->columns(2);
}

    public function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([

    //             Tables\Columns\TextColumn::make('transaction_date')
    //                 ->label('Date')
    //                 ->date()
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('contract.contract_title')
    //                 ->label('Contract')
    //                 ->searchable()
    //                 ->sortable(),

    //             Tables\Columns\BadgeColumn::make('type')
    //                 ->label('Type')
    //                 ->colors([
    //                     'danger' => 'debit',
    //                     'success' => 'credit',
    //                 ]),

    //             Tables\Columns\TextColumn::make('category')
    //                 ->searchable()
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('expense_type')
    //                 ->label('Expense Type')
    //                 ->toggleable(),

    //             Tables\Columns\TextColumn::make('amount')
    //                 ->money('INR')
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('balance')
    //                 ->money('INR')
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('vendor_name')
    //                 ->label('Vendor')
    //                 ->toggleable(),

    //             Tables\Columns\TextColumn::make('bill_number')
    //                 ->label('Bill No')
    //                 ->toggleable(),

    //             Tables\Columns\ImageColumn::make('bill_image')
    //                 ->label('Bill')
    //                 ->disk('public')
    //                 ->square()
    //                 ->toggleable(),

    //         ])
    //         ->headerActions([
    //             Tables\Actions\CreateAction::make(),
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //             Tables\Actions\DeleteAction::make(),
    //         ]);
    // }
    {
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('transaction_date')
                ->date()
                ->sortable(),

            Tables\Columns\TextColumn::make('employee.name')
                ->label('Employee')
                ->searchable(),

            Tables\Columns\TextColumn::make('contract.contract_title')
                ->label('Contract'),

            Tables\Columns\BadgeColumn::make('type')
                ->colors([
                    'danger' => 'debit',
                    'success' => 'credit',
                ]),

            Tables\Columns\TextColumn::make('category'),

            Tables\Columns\TextColumn::make('expense_type'),

            Tables\Columns\TextColumn::make('amount')
                ->money('INR'),

            Tables\Columns\TextColumn::make('balance')
                ->money('INR'),

            Tables\Columns\TextColumn::make('vendor_name'),

            Tables\Columns\TextColumn::make('bill_number'),

            Tables\Columns\ImageColumn::make('bill_image')
                ->disk('public'),

        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
}
}