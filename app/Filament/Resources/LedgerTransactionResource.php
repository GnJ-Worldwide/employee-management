<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgerTransactionResource\Pages;
use App\Filament\Resources\LedgerTransactionResource\RelationManagers;
use App\Models\LedgerTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\EmployeeSiteEngagement;
use Filament\Forms\Set;

class LedgerTransactionResource extends Resource
{
    protected static ?string $model = LedgerTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    // protected static ?string $navigationLabel = 'HR Transactions';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('transaction_date')
                    ->label('Transaction Date')
                    ->required(),

                Forms\Components\Select::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $engagement = EmployeeSiteEngagement::query()
                            ->where('employee_id', $state)
                            ->where('status', 'Engaged')
                            ->latest()
                            ->first();

                        $set(
                            'contract_id',
                            $engagement?->contract_id
                        );
                    }),

                Forms\Components\Select::make('contract_id')
                    ->label('Contract')
                    ->relationship('contract', 'contract_title')
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Select::make('type')
                    ->label('Transaction Type')
                    ->options([
                        'debit'  => 'Debit',
                        'credit' => 'Credit',
                    ])
                    ->helperText(
                        'Debit = Money Given / Expense | Credit = Money Received / Settlement'
                    )
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('category')
                    ->label('Category')
                    ->placeholder('Advance, Salary, Material Purchase')
                    ->helperText(
                        'Examples: Advance, Salary, Material Purchase, Expense Settlement, Transport, Food, Site Expense, Bonus, Penalty'
                    )
                    ->maxLength(255),

                Forms\Components\TextInput::make('expense_type')
                    ->label('Expense Type')
                    ->placeholder('Plumbing, Electrical, Civil')
                    ->helperText(
                        'Examples: Plumbing, Electrical, Civil, Painting, Hotel, Fuel'
                    )
                    ->maxLength(255),

                Forms\Components\TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->prefix('₹')
                    ->required(),

                Forms\Components\TextInput::make('vendor_name')
                    ->label('Vendor / Store Name')
                    ->placeholder('Shree Plumbing Store')
                    ->maxLength(255),

                Forms\Components\TextInput::make('bill_number')
                    ->label('Bill Number')
                    ->placeholder('INV-2025-001')
                    ->maxLength(255),

                Forms\Components\FileUpload::make('bill_image')
                    ->label('Bill Image / Invoice')
                    ->directory('ledger-bills')
                    ->disk('public')
                    ->imagePreviewHeight('150')
                    ->downloadable()
                    ->openable()
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'application/pdf',
                    ])
                    ->helperText(
                        'Upload bill image, receipt, or invoice PDF.'
                    ),

                Forms\Components\TextInput::make('reference_no')
                    ->label('Reference Number')
                    ->placeholder('ADV-001 / TXN-2025-001')
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Description / Notes')
                    ->placeholder(
                        'Employee purchased plumbing material for site work'
                    )
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d-m-Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contract.contract_title')
                    ->label('Contract')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'danger'  => 'debit',
                        'success' => 'credit',
                    ]),

                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expense_type')
                    ->label('Expense Type')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor_name')
                    ->label('Vendor')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bill_number')
                    ->label('Bill No')
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('bill_image')
                    ->label('Bill')
                    ->disk('public')
                    ->square()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('Reference No')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'debit'  => 'Debit',
                        'credit' => 'Credit',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedgerTransactions::route('/'),
            'create' => Pages\CreateLedgerTransaction::route('/create'),
            'edit' => Pages\EditLedgerTransaction::route('/{record}/edit'),
        ];
    }
}
