<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\StatecityList;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Business Operations';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    // ── Permissions ───────────────────────────────────────────────────
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_client') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_client') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_client') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_client') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_client') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_client') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Basic Info')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Client / Principal Employer Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('contact_person')
                        ->label('Contact Person')
                        ->maxLength(255),
                ]),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('mobile')
                        ->label('Mobile')
                        ->tel()
                        ->maxLength(15),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                ]),

                Forms\Components\Textarea::make('address')
                    ->label('Registered Address')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Location')
                ->description('Country defaults to India. Use the toggle to type locations or select from the master list.')
                ->schema([
                    Forms\Components\TextInput::make('country')
                        ->label('Country')
                        ->default('India')
                        ->maxLength(100)
                        ->required(),

                    Forms\Components\Toggle::make('location_manual_entry')
                        ->label('Enter state, district, taluka & village manually')
                        ->helperText('Off: choose from list. On: type any value.')
                        ->default(false)
                        ->live(),

                    Forms\Components\Group::make()
                        ->hidden(fn (Get $get) => $get('location_manual_entry'))
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('state_dropdown')
                                    ->label('State')
                                    ->options(fn () => StatecityList::stateOptions())
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('district_dropdown', null);
                                        $set('taluka_dropdown', null);
                                        $set('village_dropdown', null);
                                    }),

                                Forms\Components\Select::make('district_dropdown')
                                    ->label('District')
                                    ->options(fn (Get $get) => StatecityList::districtOptions($get('state_dropdown')))
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->disabled(fn (Get $get) => blank($get('state_dropdown')))
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('taluka_dropdown', null);
                                        $set('village_dropdown', null);
                                    }),

                                Forms\Components\Select::make('taluka_dropdown')
                                    ->label('Taluka')
                                    ->options(fn (Get $get) => StatecityList::talukaOptions(
                                        $get('state_dropdown'),
                                        $get('district_dropdown'),
                                    ))
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->disabled(fn (Get $get) => blank($get('district_dropdown')))
                                    ->afterStateUpdated(fn (Set $set) => $set('village_dropdown', null)),

                                Forms\Components\Select::make('village_dropdown')
                                    ->label('Village')
                                    ->options(fn (Get $get) => StatecityList::villageOptions(
                                        $get('state_dropdown'),
                                        $get('district_dropdown'),
                                        $get('taluka_dropdown'),
                                    ))
                                    ->searchable()
                                    ->native(false)
                                    ->disabled(fn (Get $get) => blank($get('taluka_dropdown'))),
                            ]),
                        ]),

                    Forms\Components\Group::make()
                        ->hidden(fn (Get $get) => ! $get('location_manual_entry'))
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('state_manual')
                                    ->label('State')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('district_manual')
                                    ->label('District')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('taluka_manual')
                                    ->label('Taluka')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('village_manual')
                                    ->label('Village')
                                    ->maxLength(255),
                            ]),
                        ]),

                    Forms\Components\TextInput::make('pincode')
                        ->label('PIN code')
                        ->maxLength(12)
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Forms\Components\Section::make('Statutory Details')->schema([
                Forms\Components\TextInput::make('gst_no')
                    ->label('GST Number')
                    ->maxLength(15)
                    ->placeholder('22BBBBB0000B1Z5'),
            ]),

            Forms\Components\Section::make('Document Uploads')
                ->description('Upload files (PDF/Images) or paste text content.')
                ->icon('heroicon-o-paper-clip')
                ->columns(2)
                ->collapsible()
                ->schema([
                    // PAN Document
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('pan_document_mode')
                            ->label('PAN Document Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('pan_document_path')
                            ->label('PAN Document File')
                            ->disk('public')
                            ->directory('client-docs/pan') // Updated to client-docs
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('pan_document_mode') === 'file'),
                        Forms\Components\Textarea::make('pan_document_text')
                            ->label('PAN Document Content')
                            ->placeholder('Type or paste PAN details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('pan_document_mode') === 'text'),
                    ])->columnSpan(1),

                    // GST Certificate
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('gst_certificate_mode')
                            ->label('GST Certificate Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('gst_certificate_path')
                            ->label('GST Certificate File')
                            ->disk('public')
                            ->directory('client-docs/gst') // Updated to client-docs
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('gst_certificate_mode') === 'file'),
                        Forms\Components\Textarea::make('gst_certificate_text')
                            ->label('GST Certificate Content')
                            ->placeholder('Type or paste GST details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('gst_certificate_mode') === 'text'),
                    ])->columnSpan(1),

                    // User Attachment 1
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_user_attachment_1_mode')
                            ->label('User Attachment 1 Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_user_attachment_1_path')
                            ->label('User Attachment 1 File')
                            ->disk('public')
                            ->directory('client-docs/attachments') // Updated to client-docs
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_user_attachment_1_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_user_attachment_1_text')
                            ->label('User Attachment 1 Content')
                            ->placeholder('Type or paste details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_user_attachment_1_mode') === 'text'),
                    ])->columnSpan(1),

                    // User Attachment 2
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_user_attachment_2_mode')
                            ->label('User Attachment 2 Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_user_attachment_2_path')
                            ->label('User Attachment 2 File')
                            ->disk('public')
                            ->directory('client-docs/attachments') // Updated to client-docs
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_user_attachment_2_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_user_attachment_2_text')
                            ->label('User Attachment 2 Content')
                            ->placeholder('Type or paste details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_user_attachment_2_mode') === 'text'),
                    ])->columnSpan(1),

                    // User Attachment 3
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_user_attachment_3_mode')
                            ->label('User Attachment 3 Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_user_attachment_3_path')
                            ->label('User Attachment 3 File')
                            ->disk('public')
                            ->directory('client-docs/attachments') // Updated to client-docs
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_user_attachment_3_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_user_attachment_3_text')
                            ->label('User Attachment 3 Content')
                            ->placeholder('Type or paste details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_user_attachment_3_mode') === 'text'),
                    ])->columnSpan(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact Person')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('Mobile'),

                Tables\Columns\TextColumn::make('state')
                    ->label('State')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('district')
                    ->label('District')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pincode')
                    ->label('PIN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('gst_no')
                    ->label('GST No.')
                    ->copyable(),

                Tables\Columns\TextColumn::make('contracts_count')
                    ->label('Contracts')
                    ->counts('contracts')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            // 'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    /**
     * Map persisted location columns into form virtual fields (dropdown vs manual).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function hydrateLocationVirtualFields(array $data): array
    {
        $manual = (bool) ($data['location_manual_entry'] ?? false);
        if ($manual) {
            $data['state_manual'] = $data['state'] ?? null;
            $data['district_manual'] = $data['district'] ?? null;
            $data['taluka_manual'] = $data['taluka'] ?? null;
            $data['village_manual'] = $data['village'] ?? null;
            $data['state_dropdown'] = null;
            $data['district_dropdown'] = null;
            $data['taluka_dropdown'] = null;
            $data['village_dropdown'] = null;
        } else {
            $data['state_dropdown'] = $data['state'] ?? null;
            $data['district_dropdown'] = $data['district'] ?? null;
            $data['taluka_dropdown'] = $data['taluka'] ?? null;
            $data['village_dropdown'] = $data['village'] ?? null;
            $data['state_manual'] = null;
            $data['district_manual'] = null;
            $data['taluka_manual'] = null;
            $data['village_manual'] = null;
        }

        return $data;
    }

    /**
     * Merge virtual location fields into persisted columns and remove virtual keys.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function collapseLocationVirtualFields(array $data): array
    {
        $manual = (bool) ($data['location_manual_entry'] ?? false);
        $data['state'] = $manual ? ($data['state_manual'] ?? null) : ($data['state_dropdown'] ?? null);
        $data['district'] = $manual ? ($data['district_manual'] ?? null) : ($data['district_dropdown'] ?? null);
        $data['taluka'] = $manual ? ($data['taluka_manual'] ?? null) : ($data['taluka_dropdown'] ?? null);
        $data['village'] = $manual ? ($data['village_manual'] ?? null) : ($data['village_dropdown'] ?? null);
        unset(
            $data['state_dropdown'],
            $data['state_manual'],
            $data['district_dropdown'],
            $data['district_manual'],
            $data['taluka_dropdown'],
            $data['taluka_manual'],
            $data['village_dropdown'],
            $data['village_manual'],
        );

        return $data;
    }
}