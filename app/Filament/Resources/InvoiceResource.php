<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Contract;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Business Operations';
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'invoice_number';

    // ── Permissions ────────────────────────────────────────────────────────
    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_any_invoice');
    }
    public static function canCreate(): bool
    {
        return auth()->user()->can('create_invoice');
    }
    public static function canEdit($r): bool
    {
        return auth()->user()->can('update_invoice') && $r->isEditable();
    }
    public static function canDelete($r): bool
    {
        return auth()->user()->can('delete_invoice') && $r->status === 'Submitted';
    }

    // ── Form ───────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Invoice Details')
                ->schema([
                    Forms\Components\Select::make('contract_id')
                        ->label('Contract')
                        ->relationship(
                            name: 'contract',
                            titleAttribute: 'contract_title',
                            modifyQueryUsing: fn(Builder $query) => $query->where('overall_status', 'Active'),
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->getOptionLabelFromRecordUsing(
                            fn(Contract $r) =>
                            "[{$r->work_order_code}] {$r->contract_title}"
                        ),

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
                ]),

            // Read-only status info (visible only on edit)
            Forms\Components\Section::make('Workflow Status')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('status')
                            ->disabled(),
                        Forms\Components\DatePicker::make('submitted_on')
                            ->disabled()
                            ->native(false),
                        Forms\Components\DatePicker::make('approved_on')
                            ->disabled()
                            ->native(false),
                    ]),
                ])
                ->visibleOn('edit'),
        ]);
    }

    // ── Table ──────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract.work_order_code')
                    ->label('Contract / WO')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice No.')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contract.vendor_name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_period_start')
                    ->label('Billing Period')
                    ->formatStateUsing(
                        fn($state, Invoice $record) =>
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
                    ->color(fn(string $state) => match ($state) {
                        'Submitted' => 'info',
                        'Pending_Compliance' => 'warning',
                        'Approved' => 'primary',
                        'Paid' => 'success',
                        'Rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => str_replace('_', ' ', $state)),

                Tables\Columns\TextColumn::make('submitted_on')
                    ->label('Submitted On')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('submitted_on', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Submitted' => 'Submitted',
                        'Pending_Compliance' => 'Pending Compliance',
                        'Approved' => 'Approved',
                        'Paid' => 'Paid',
                        'Rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('contract_id')
                    ->label('Contract')
                    ->relationship('contract', 'contract_title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn(Invoice $r) => $r->isEditable()),
            ]);
    }

    // ── Relations ──────────────────────────────────────────────────────────
    public static function getRelations(): array
    {
        $relations = [];

        if (auth()->user()?->can('view_any_compliance_checklist')) {
            $relations[] = RelationManagers\ComplianceChecklistRelationManager::class;
        }

        if (auth()->user()?->can('view_any_compliance_document')) {
            $relations[] = RelationManagers\ComplianceDocumentsRelationManager::class;
        }

        return $relations;
    }

    // ── Pages ──────────────────────────────────────────────────────────────
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}