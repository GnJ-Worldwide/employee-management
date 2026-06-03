<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Business Operations';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'contract_title';

    // ── Permissions ────────────────────────────────────────────────────────
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_contract') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_contract') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_contract') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_contract') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_contract') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_contract') ?? false;
    }

    // ── Form ───────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Contract')
                ->tabs([

                    // ── Tab 1: Contract Details ────────────────────────────
                    Forms\Components\Tabs\Tab::make('Contract Details')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('work_order_code')
                                    ->label('Work Order / PO Number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100)
                                    ->placeholder('e.g. WO-2024-1052'),

                                Forms\Components\TextInput::make('contract_title')
                                    ->label('Contract Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g. Annual Housekeeping Services — HQ'),
                            ]),

                            Forms\Components\Textarea::make('nature_of_work')
                                ->label('Nature of Work / Scope')
                                ->rows(3)
                                ->maxLength(2000),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->required()
                                    ->label('Start Date')
                                    ->native(false),

                                Forms\Components\DatePicker::make('end_date')
                                    ->required()
                                    ->label('End Date')
                                    ->native(false)
                                    ->afterOrEqual('start_date'),

                                Forms\Components\Select::make('overall_status')
                                    ->label('Status')
                                    ->options([
                                        'Active' => 'Active',
                                        'Expired' => 'Expired',
                                        'Terminated' => 'Terminated',
                                    ])
                                    ->default('Active')
                                    ->required(),
                            ]),
                        ]),

                    // ── Tab 2: Vendor Details ──────────────────────────────
                    Forms\Components\Tabs\Tab::make('Vendor Details')
                        ->icon('heroicon-o-building-office')
                        ->schema([
                            Forms\Components\Select::make('vendor_id')
                                ->label('Vendor / Contractor')
                                ->relationship('vendor', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpanFull()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('gst_no')->maxLength(15),
                                    Forms\Components\TextInput::make('mobile')->tel(),
                                    Forms\Components\Textarea::make('address')->rows(2),
                                ])
                                ->editOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('gst_no')->maxLength(15),
                                    Forms\Components\TextInput::make('mobile')->tel(),
                                    Forms\Components\Textarea::make('address')->rows(2),
                                ]),
                        ]),

                    // ── Tab 3: Client Details ──────────────────────────────
                    Forms\Components\Tabs\Tab::make('Client Details')
                        ->icon('heroicon-o-building-library')
                        ->schema([
                            Forms\Components\Select::make('client_id')
                                ->label('Client / Principal Employer')
                                ->relationship('client', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpanFull()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('gst_no')->maxLength(15),
                                    Forms\Components\TextInput::make('mobile')->tel(),
                                    Forms\Components\Textarea::make('address')->rows(2),
                                ])
                                ->editOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('gst_no')->maxLength(15),
                                    Forms\Components\TextInput::make('mobile')->tel(),
                                    Forms\Components\Textarea::make('address')->rows(2),
                                ]),
                        ]),

                    // // ── Tab 4: Foundational Documents ─────────────────────
                    // Forms\Components\Tabs\Tab::make('Attachments')
                    //     ->icon('heroicon-o-paper-clip')
                    //     ->schema([
                    //         Forms\Components\Placeholder::make('attachments_note')
                    //             ->label('')
                    //             ->content('For invoice-related attachments (Measurement Sheets, Abstract Reports, etc.) use the Attachments panel below after saving this contract.')
                    //             ->columnSpanFull(),
                    //     ]),

                ])
                ->columnSpanFull(),
        ]);
    }

    // ── Table ──────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('work_order_code')
                    ->label('WO / PO No.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('contract_title')
                    ->label('Contract Title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('overall_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Active' => 'success',
                        'Expired' => 'warning',
                        'Terminated' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('invoices_count')
                    ->label('Invoices')
                    ->counts('invoices')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('overall_status')
                    ->options([
                        'Active' => 'Active',
                        'Expired' => 'Expired',
                        'Terminated' => 'Terminated',
                    ]),

                Tables\Filters\Filter::make('active_period')
                    ->label('Currently Active')
                    ->query(
                        fn(Builder $q) => $q
                            ->where('overall_status', 'Active')
                            ->where('start_date', '<=', now())
                            ->where('end_date', '>=', now())
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ── Relations ──────────────────────────────────────────────────────────
    public static function getRelations(): array
    {
        $relations = [];

        // Only show InvoicesRelationManager if user can view invoices
        if (auth()->user()?->can('view_any_invoice')) {
            $relations[] = RelationManagers\InvoicesRelationManager::class;
        }

        // Only show ContractAttachmentsRelationManager if user can view attachments
        if (auth()->user()?->can('view_any_contract_attachment')) {
            $relations[] = RelationManagers\ContractAttachmentsRelationManager::class;
        }

        return $relations;
    }

    // ── Pages ──────────────────────────────────────────────────────────────
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'view' => Pages\ViewContract::route('/{record}'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }

    // ── Mutate before save ─────────────────────────────────────────────────
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->user()->name;
        return $data;
    }
}