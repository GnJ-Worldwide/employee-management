<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\Admin;
use App\Models\StatecityList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Admins';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any_admin') ?? false;
    }
    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_admin') ?? false;
    }
    public static function canEdit($r): bool
    {
        return auth()->user()?->can('update_admin') ?? false;
    }
    public static function canDelete($r): bool
    {
        return auth()->user()?->can('delete_admin') ?? false;
    }
    public static function canView($r): bool
    {
        return auth()->user()?->can('view_admin') ?? false;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  FORM
    // ──────────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── SECTION 1 : General Information ──────────────────────────────
            Forms\Components\Section::make('General Information')
                ->description('Basic details about the admin entity.')
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('admin_name')
                        ->label('Admin Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('register_office')
                        ->label('Register Office')
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('office')
                        ->label('Office')
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('contact_no')
                        ->label('Contact No.')
                        ->tel()
                        ->required()
                        ->minLength(10)
                        ->maxLength(10)
                        ->rules(['digits:10'])
                        ->helperText('10-digit mobile number only.')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('email')
                        ->label('Email ID')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    // GST No. — triggers live state resolution
                    Forms\Components\TextInput::make('gst_registration_no')
                        ->label('GST Registration No.')
                        ->maxLength(15)
                        ->minLength(15)
                        ->rules(['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'])
                        ->helperText('15-character GST number. State will be auto-filled.')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if ($state && strlen(trim($state)) >= 2) {
                                $resolved = Admin::resolveGstState($state);
                                $set('gst_state', $resolved ?? 'Unknown / Invalid Code');
                            } else {
                                $set('gst_state', null);
                            }
                        })
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('gst_state')
                        ->label('State (from GST)')
                        ->disabled()      // read-only — auto-fetched
                        ->dehydrated()    // still saves to DB
                        ->placeholder('Will auto-fill after GST No. entry.')
                        ->helperText('Automatically resolved from the GST number.')
                        ->columnSpan(1),
                ]),

            // ── SECTION 2 : Permanent Address ────────────────────────────────
            Forms\Components\Section::make('Permanent Address')
                ->description('Registered permanent address of the admin.')
                ->icon('heroicon-o-map-pin')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Forms\Components\TextInput::make('country')
                        ->label('Country')
                        ->default('India')
                        ->required()
                        ->maxLength(100)
                        ->columnSpan(1),

                    Forms\Components\Toggle::make('location_manual_entry')
                        ->label('Enter state, district, taluka & village/city manually')
                        ->helperText('Off: choose from list. On: type any value.')
                        ->default(false)
                        ->live()
                        ->columnSpan(1),

                    Forms\Components\Group::make()
                        ->hidden(fn (Get $get) => $get('location_manual_entry'))
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('address_state_dropdown')
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
                                    ->options(fn (Get $get) => StatecityList::districtOptions($get('address_state_dropdown')))
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->disabled(fn (Get $get) => blank($get('address_state_dropdown')))
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('taluka_dropdown', null);
                                        $set('village_dropdown', null);
                                    }),

                                Forms\Components\Select::make('taluka_dropdown')
                                    ->label('Taluka')
                                    ->options(fn (Get $get) => StatecityList::talukaOptions(
                                        $get('address_state_dropdown'),
                                        $get('district_dropdown'),
                                    ))
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->disabled(fn (Get $get) => blank($get('district_dropdown')))
                                    ->afterStateUpdated(fn (Set $set) => $set('village_dropdown', null)),

                                Forms\Components\Select::make('village_dropdown')
                                    ->label('Village / City')
                                    ->options(fn (Get $get) => StatecityList::villageOptions(
                                        $get('address_state_dropdown'),
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
                                Forms\Components\TextInput::make('address_state_manual')
                                    ->label('State')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('district_manual')
                                    ->label('District')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('taluka_manual')
                                    ->label('Taluka')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('village_manual')
                                    ->label('Village / City')
                                    ->maxLength(150),
                            ]),
                        ]),

                    Forms\Components\TextInput::make('pin_code')
                        ->label('Pin Code')
                        ->maxLength(6)
                        ->minLength(6)
                        ->rules(['nullable', 'digits:6'])
                        ->columnSpan(1),
                ]),

            // ── SECTION 3 : Bank & Other Information ─────────────────────────
            Forms\Components\Section::make('Bank & Other Information')
                ->description('Bank account details for transactions.')
                ->icon('heroicon-o-banknotes')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Forms\Components\TextInput::make('account_number')
                        ->label('Account Number')
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('bank_ifsc_code')
                        ->label('Bank IFSC Code')
                        ->maxLength(11)
                        ->minLength(11)
                        ->rules(['nullable', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'])
                        ->helperText('11-character IFSC code (e.g. SBIN0001234).')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('bank_name')
                        ->label('Bank Name')
                        ->maxLength(150)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('bank_micr')
                        ->label('Bank MICR')
                        ->maxLength(9)
                        ->minLength(9)
                        ->rules(['nullable', 'digits:9'])
                        ->helperText('9-digit MICR code.')
                        ->columnSpan(1),
                ]),

            // ── SECTION 4 : Statutory & Regulatory Attachments ───────────────
            Forms\Components\Section::make('Statutory & Regulatory Attachments')
                ->description('Provide documents via file upload or plain text. File uploads must be PDF (Max 5 MB each).')
                ->icon('heroicon-o-document-text')
                ->columns(2)
                ->collapsible()
                ->schema([
                    // MOA
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_moa_mode')
                            ->label('MOA Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_moa')
                            ->label('Memorandum of Association (PDF)')
                            ->disk('public')
                            ->directory('admin-docs/moa')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_moa_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_moa_text')
                            ->label('MOA Content')
                            ->placeholder('Type or paste MOA details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_moa_mode') === 'text'),
                    ])->columnSpan(1),

                    // Incorporation
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_incorporation_mode')
                            ->label('Incorporation Cert Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_incorporation')
                            ->label('Certificate of Incorporation (PDF)')
                            ->disk('public')
                            ->directory('admin-docs/incorporation')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_incorporation_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_incorporation_text')
                            ->label('Incorporation Cert Content')
                            ->placeholder('Type or paste Incorporation Cert details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_incorporation_mode') === 'text'),
                    ])->columnSpan(1),

                    // PAN Card
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_pan_card_mode')
                            ->label('PAN Card Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_pan_card')
                            ->label('PAN Card (PDF)')
                            ->disk('public')
                            ->directory('admin-docs/pan-card')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_pan_card_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_pan_card_text')
                            ->label('PAN Card Content')
                            ->placeholder('Type or paste PAN details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_pan_card_mode') === 'text'),
                    ])->columnSpan(1),

                    // PF Registration
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_pf_registration_mode')
                            ->label('PF Registration Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_pf_registration')
                            ->label('PF Registration (PDF)')
                            ->disk('public')
                            ->directory('admin-docs/pf-registration')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_pf_registration_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_pf_registration_text')
                            ->label('PF Registration Content')
                            ->placeholder('Type or paste PF Registration details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_pf_registration_mode') === 'text'),
                    ])->columnSpan(1),

                    // ESIC Registration
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_esic_registration_mode')
                            ->label('ESIC Registration Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_esic_registration')
                            ->label('ESIC Registration (PDF)')
                            ->disk('public')
                            ->directory('admin-docs/esic-registration')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_esic_registration_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_esic_registration_text')
                            ->label('ESIC Registration Content')
                            ->placeholder('Type or paste ESIC Registration details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_esic_registration_mode') === 'text'),
                    ])->columnSpan(1),

                    // GST Registration
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_gst_registration_mode')
                            ->label('GST Registration Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_gst_registration')
                            ->label('GST Registration (PDF)')
                            ->disk('public')
                            ->directory('admin-docs/gst-registration')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_gst_registration_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_gst_registration_text')
                            ->label('GST Registration Content')
                            ->placeholder('Type or paste GST Registration details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_gst_registration_mode') === 'text'),
                    ])->columnSpan(1),

                    // MSME Registration
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_msme_registration_mode')
                            ->label('MSME Registration Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_msme_registration')
                            ->label('MSME Registration (PDF)')
                            ->disk('public')
                            ->directory('admin-docs/msme-registration')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_msme_registration_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_msme_registration_text')
                            ->label('MSME Registration Content')
                            ->placeholder('Type or paste MSME Registration details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_msme_registration_mode') === 'text'),
                    ])->columnSpan(1),

                    // ISO Registration
                    Forms\Components\Group::make()->schema([
                        Forms\Components\ToggleButtons::make('doc_iso_registration_mode')
                            ->label('ISO Registration Input')
                            ->options(['file' => 'Upload File', 'text' => 'Enter Text'])
                            ->icons(['file' => 'heroicon-o-paper-clip', 'text' => 'heroicon-o-pencil-square'])
                            ->colors(['file' => 'primary', 'text' => 'info'])
                            ->default('file')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('doc_iso_registration')
                            ->label('ISO Registration (PDF)')
                            ->disk('public')
                            ->directory('admin-docs/iso-registration')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->visible(fn(Get $get) => $get('doc_iso_registration_mode') === 'file'),
                        Forms\Components\Textarea::make('doc_iso_registration_text')
                            ->label('ISO Registration Content')
                            ->placeholder('Type or paste ISO Registration details here.')
                            ->rows(3)
                            ->visible(fn(Get $get) => $get('doc_iso_registration_mode') === 'text'),
                    ])->columnSpan(1),
                ]),

        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  TABLE
    // ──────────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('admin_name')
                    ->label('Admin Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_no')
                    ->label('Contact No.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('gst_registration_no')
                    ->label('GST No.')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('gst_state')
                    ->label('State (GST)')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('address_state')
                    ->label('State')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('district')
                    ->label('District')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pin_code')
                    ->label('PIN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('gst_state')
                    ->label('State (from GST)')
                    ->options(Admin::$gstStateCodes)
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  INFOLIST  (View page)
    // ──────────────────────────────────────────────────────────────────────────
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Infolists\Components\Section::make('General Information')
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('admin_name')->label('Admin Name'),
                    Infolists\Components\TextEntry::make('register_office')->label('Register Office')->placeholder('—'),
                    Infolists\Components\TextEntry::make('office')->label('Office')->placeholder('—'),
                    Infolists\Components\TextEntry::make('contact_no')->label('Contact No.'),
                    Infolists\Components\TextEntry::make('email')->label('Email ID'),
                    Infolists\Components\TextEntry::make('gst_registration_no')->label('GST Registration No.')->placeholder('—'),
                    Infolists\Components\TextEntry::make('gst_state')->label('State (from GST)')->badge()->color('info')->placeholder('—'),
                ]),

            Infolists\Components\Section::make('Permanent Address')
                ->icon('heroicon-o-map-pin')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('country')->label('Country'),
                    Infolists\Components\TextEntry::make('address_state')->label('State')->placeholder('—'),
                    Infolists\Components\TextEntry::make('district')->label('District')->placeholder('—'),
                    Infolists\Components\TextEntry::make('taluka')->label('Taluka')->placeholder('—'),
                    Infolists\Components\TextEntry::make('village_city')->label('Village / City')->placeholder('—'),
                    Infolists\Components\TextEntry::make('pin_code')->label('Pin Code')->placeholder('—'),
                ]),

            Infolists\Components\Section::make('Bank & Other Information')
                ->icon('heroicon-o-banknotes')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Infolists\Components\TextEntry::make('account_number')->label('Account Number')->placeholder('—'),
                    Infolists\Components\TextEntry::make('bank_ifsc_code')->label('IFSC Code')->placeholder('—'),
                    Infolists\Components\TextEntry::make('bank_name')->label('Bank Name')->placeholder('—'),
                    Infolists\Components\TextEntry::make('bank_micr')->label('MICR Code')->placeholder('—'),
                ]),

                Infolists\Components\Section::make('Statutory & Regulatory Attachments')
                ->icon('heroicon-o-document-text')
                ->columns(2)
                ->collapsible()
                ->schema([
                    // MOA
                    Infolists\Components\Group::make()->schema([
                        Infolists\Components\TextEntry::make('doc_moa_mode')
                            ->label('MOA Format')
                            ->badge()
                            ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                            ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                        Infolists\Components\TextEntry::make('doc_moa')
                            ->label('MOA PDF')
                            ->placeholder('Not uploaded')
                            ->visible(fn(Admin $record) => $record->doc_moa_mode === 'file'),
                        Infolists\Components\TextEntry::make('doc_moa_text')
                            ->label('MOA Text Content')
                            ->placeholder('Not provided')
                            ->visible(fn(Admin $record) => $record->doc_moa_mode === 'text'),
                    ])->columnSpan(1),

                    // Incorporation
                    Infolists\Components\Group::make()->schema([
                        Infolists\Components\TextEntry::make('doc_incorporation_mode')
                            ->label('Incorporation Cert Format')
                            ->badge()
                            ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                            ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                        Infolists\Components\TextEntry::make('doc_incorporation')
                            ->label('Incorporation Cert PDF')
                            ->placeholder('Not uploaded')
                            ->visible(fn(Admin $record) => $record->doc_incorporation_mode === 'file'),
                        Infolists\Components\TextEntry::make('doc_incorporation_text')
                            ->label('Incorporation Cert Text')
                            ->placeholder('Not provided')
                            ->visible(fn(Admin $record) => $record->doc_incorporation_mode === 'text'),
                    ])->columnSpan(1),

                    // PAN Card
                    Infolists\Components\Group::make()->schema([
                        Infolists\Components\TextEntry::make('doc_pan_card_mode')
                            ->label('PAN Card Format')
                            ->badge()
                            ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                            ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                        Infolists\Components\TextEntry::make('doc_pan_card')
                            ->label('PAN Card PDF')
                            ->placeholder('Not uploaded')
                            ->visible(fn(Admin $record) => $record->doc_pan_card_mode === 'file'),
                        Infolists\Components\TextEntry::make('doc_pan_card_text')
                            ->label('PAN Card Text')
                            ->placeholder('Not provided')
                            ->visible(fn(Admin $record) => $record->doc_pan_card_mode === 'text'),
                    ])->columnSpan(1),

                    // PF Registration
                    Infolists\Components\Group::make()->schema([
                        Infolists\Components\TextEntry::make('doc_pf_registration_mode')
                            ->label('PF Registration Format')
                            ->badge()
                            ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                            ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                        Infolists\Components\TextEntry::make('doc_pf_registration')
                            ->label('PF Registration PDF')
                            ->placeholder('Not uploaded')
                            ->visible(fn(Admin $record) => $record->doc_pf_registration_mode === 'file'),
                        Infolists\Components\TextEntry::make('doc_pf_registration_text')
                            ->label('PF Registration Text')
                            ->placeholder('Not provided')
                            ->visible(fn(Admin $record) => $record->doc_pf_registration_mode === 'text'),
                    ])->columnSpan(1),

                    // ESIC Registration
                    Infolists\Components\Group::make()->schema([
                        Infolists\Components\TextEntry::make('doc_esic_registration_mode')
                            ->label('ESIC Registration Format')
                            ->badge()
                            ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                            ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                        Infolists\Components\TextEntry::make('doc_esic_registration')
                            ->label('ESIC Registration PDF')
                            ->placeholder('Not uploaded')
                            ->visible(fn(Admin $record) => $record->doc_esic_registration_mode === 'file'),
                        Infolists\Components\TextEntry::make('doc_esic_registration_text')
                            ->label('ESIC Registration Text')
                            ->placeholder('Not provided')
                            ->visible(fn(Admin $record) => $record->doc_esic_registration_mode === 'text'),
                    ])->columnSpan(1),

                    // GST Registration
                    Infolists\Components\Group::make()->schema([
                        Infolists\Components\TextEntry::make('doc_gst_registration_mode')
                            ->label('GST Registration Format')
                            ->badge()
                            ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                            ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                        Infolists\Components\TextEntry::make('doc_gst_registration')
                            ->label('GST Registration PDF')
                            ->placeholder('Not uploaded')
                            ->visible(fn(Admin $record) => $record->doc_gst_registration_mode === 'file'),
                        Infolists\Components\TextEntry::make('doc_gst_registration_text')
                            ->label('GST Registration Text')
                            ->placeholder('Not provided')
                            ->visible(fn(Admin $record) => $record->doc_gst_registration_mode === 'text'),
                    ])->columnSpan(1),

                    // MSME Registration
                    Infolists\Components\Group::make()->schema([
                        Infolists\Components\TextEntry::make('doc_msme_registration_mode')
                            ->label('MSME Registration Format')
                            ->badge()
                            ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                            ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                        Infolists\Components\TextEntry::make('doc_msme_registration')
                            ->label('MSME Registration PDF')
                            ->placeholder('Not uploaded')
                            ->visible(fn(Admin $record) => $record->doc_msme_registration_mode === 'file'),
                        Infolists\Components\TextEntry::make('doc_msme_registration_text')
                            ->label('MSME Registration Text')
                            ->placeholder('Not provided')
                            ->visible(fn(Admin $record) => $record->doc_msme_registration_mode === 'text'),
                    ])->columnSpan(1),

                    // ISO Registration
                    Infolists\Components\Group::make()->schema([
                        Infolists\Components\TextEntry::make('doc_iso_registration_mode')
                            ->label('ISO Registration Format')
                            ->badge()
                            ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text')
                            ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info'),
                        Infolists\Components\TextEntry::make('doc_iso_registration')
                            ->label('ISO Registration PDF')
                            ->placeholder('Not uploaded')
                            ->visible(fn(Admin $record) => $record->doc_iso_registration_mode === 'file'),
                        Infolists\Components\TextEntry::make('doc_iso_registration_text')
                            ->label('ISO Registration Text')
                            ->placeholder('Not provided')
                            ->visible(fn(Admin $record) => $record->doc_iso_registration_mode === 'text'),
                    ])->columnSpan(1),
                ]),

        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  PAGES
    // ──────────────────────────────────────────────────────────────────────────
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            // 'view'   => Pages\ViewAdmin::route('/{record}'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
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
            $data['address_state_manual'] = $data['address_state'] ?? null;
            $data['district_manual'] = $data['district'] ?? null;
            $data['taluka_manual'] = $data['taluka'] ?? null;
            $data['village_manual'] = $data['village_city'] ?? null;
            $data['address_state_dropdown'] = null;
            $data['district_dropdown'] = null;
            $data['taluka_dropdown'] = null;
            $data['village_dropdown'] = null;
        } else {
            $data['address_state_dropdown'] = $data['address_state'] ?? null;
            $data['district_dropdown'] = $data['district'] ?? null;
            $data['taluka_dropdown'] = $data['taluka'] ?? null;
            $data['village_dropdown'] = $data['village_city'] ?? null;
            $data['address_state_manual'] = null;
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

        $data['address_state'] = $manual ? ($data['address_state_manual'] ?? null) : ($data['address_state_dropdown'] ?? null);
        $data['district'] = $manual ? ($data['district_manual'] ?? null) : ($data['district_dropdown'] ?? null);
        $data['taluka'] = $manual ? ($data['taluka_manual'] ?? null) : ($data['taluka_dropdown'] ?? null);
        $data['village_city'] = $manual ? ($data['village_manual'] ?? null) : ($data['village_dropdown'] ?? null);

        unset(
            $data['address_state_dropdown'],
            $data['address_state_manual'],
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