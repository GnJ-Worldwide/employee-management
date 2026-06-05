<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteEngagementResource\Pages;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SiteEngagementResource extends Resource
{
    protected static ?string $model              = Employee::class;
    protected static ?string $navigationIcon     = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel    = 'Site Engagement';
    protected static ?string $navigationGroup    = 'Business Operations';
    protected static ?int    $navigationSort     = 5;
    protected static ?string $slug               = 'site-engagements';
    protected static ?string $recordTitleAttribute = 'full_name';

    // ── Permissions ────────────────────────────────────────────────────────

    public static function canViewAny(): bool  { return auth()->user()->can('view_any_employee'); }
    public static function canCreate(): bool   { return false; } // employees are created in EmployeeResource
    public static function canEdit($r): bool   { return false; }
    public static function canDelete($r): bool { return false; }

    // ── No form needed (no create/edit here) ──────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    // ── Table ──────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(                                   // ← ADD THIS
                fn (Builder $query) => $query
                    ->where('emp_status', 'active')
                    ->with(['currentVendor', 'currentContract'])
            )
            ->columns([

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable('first_name')
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('designation')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('trade')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Highly Skilled' => 'success',
                        'Skilled'        => 'info',
                        'Semi-Skilled'   => 'warning',
                        default          => 'gray',
                    }),

                Tables\Columns\TextColumn::make('engagement_status')
                    ->label('Engagement')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Engaged'   => 'success',
                        'Available' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('currentVendor.name')
                    ->label('Current Site')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('currentContract.work_order_code')
                    ->label('Work Order')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('activeEngagement.engaged_date')
                    ->label('Engaged Since')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mobile_number')
                    ->label('Mobile')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('first_name')
            ->filters([
                Tables\Filters\SelectFilter::make('engagement_status')
                    ->options([
                        'Available' => 'Available',
                        'Engaged'   => 'Engaged',
                    ]),

                Tables\Filters\SelectFilter::make('current_vendor_id')
                    ->label('Site / Vendor')
                    ->options(fn() => \App\Models\Vendor::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('designation')
                    ->options(EmployeeResource::designationOptions() ?? [])
                    ->searchable(),
            ])

            // ── Per-row actions (single employee) ─────────────────────────
            ->actions([
                Tables\Actions\Action::make('engage')
                    ->label('Engage')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->visible(fn(Employee $r) => $r->isAvailable())
                    ->form(fn() => self::engageFormSchema())
                    ->action(function (Employee $record, array $data) {
                        self::performEngage(collect([$record]), $data);
                    }),

                Tables\Actions\Action::make('release')
                    ->label('Release')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn(Employee $r) => $r->isEngaged())
                    ->form(fn() => self::releaseFormSchema())
                    ->action(function (Employee $record, array $data) {
                        self::performRelease(collect([$record]), $data);
                    }),

                Tables\Actions\Action::make('transfer')
                    ->label('Transfer')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('info')
                    ->visible(fn(Employee $r) => $r->isEngaged())
                    ->form(fn(Employee $r) => self::transferFormSchema($r->currentVendor?->name))
                    ->action(function (Employee $record, array $data) {
                        self::performTransfer(collect([$record]), $data);
                    }),
            ])

            // ── Bulk actions (multiple employees at once) ──────────────────
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    // ── Bulk Engage ────────────────────────────────────────
                    Tables\Actions\BulkAction::make('bulk_engage')
                        ->label('Engage Selected')
                        ->icon('heroicon-o-link')
                        ->color('success')
                        ->deselectRecordsAfterCompletion()
                        ->form(fn() => self::engageFormSchema())
                        ->action(function (Collection $records, array $data) {
                            self::performEngage($records, $data);
                        }),

                    // ── Bulk Release ───────────────────────────────────────
                    Tables\Actions\BulkAction::make('bulk_release')
                        ->label('Release Selected')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Release Selected Employees')
                        ->modalDescription('All selected employees that are currently engaged will be released.')
                        ->deselectRecordsAfterCompletion()
                        ->form(fn() => self::releaseFormSchema())
                        ->action(function (Collection $records, array $data) {
                            self::performRelease($records, $data);
                        }),

                    // ── Bulk Transfer ──────────────────────────────────────
                    Tables\Actions\BulkAction::make('bulk_transfer')
                        ->label('Transfer Selected')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('info')
                        ->deselectRecordsAfterCompletion()
                        ->form(fn() => self::transferFormSchema())
                        ->action(function (Collection $records, array $data) {
                            self::performTransfer($records, $data);
                        }),

                ]),
            ]);
    }

    // ── Reusable form schemas ──────────────────────────────────────────────

    private static function engageFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Assign to Site')->schema([
                Forms\Components\Select::make('vendor_id')
                    ->label('Site / Vendor')
                    ->options(fn() => Vendor::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live(),

                Forms\Components\Select::make('contract_id')
                    ->label('Contract (optional)')
                    ->options(function (Forms\Get $get) {
                        $vendorId = $get('vendor_id');
                        if (!$vendorId) return [];
                        return Contract::where('vendor_id', $vendorId)
                            ->where('overall_status', 'Active')
                            ->orderBy('work_order_code')
                            ->get()
                            ->mapWithKeys(fn($c) => [
                                $c->id => "{$c->work_order_code} — {$c->contract_title}",
                            ]);
                    })
                    ->placeholder('Select a vendor first')
                    ->searchable(),

                Forms\Components\DatePicker::make('engaged_date')
                    ->label('Engagement Date')
                    ->required()
                    ->default(today())
                    ->native(false),

                Forms\Components\Textarea::make('remarks')
                    ->label('Remarks')
                    ->rows(2)
                    ->maxLength(500),
            ]),
        ];
    }

    private static function releaseFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Release Details')->schema([
                Forms\Components\DatePicker::make('released_date')
                    ->label('Release Date')
                    ->required()
                    ->default(today())
                    ->native(false),

                Forms\Components\Textarea::make('remarks')
                    ->label('Release Remarks')
                    ->rows(2)
                    ->maxLength(500),
            ]),
        ];
    }

    private static function transferFormSchema(?string $currentSiteName = null): array
    {
        return [
            Forms\Components\Section::make('Release from Current Site')
                ->description($currentSiteName ? "Currently at: {$currentSiteName}" : 'Each employee will be released from their current site.')
                ->icon('heroicon-o-arrow-uturn-left')
                ->schema([
                    Forms\Components\DatePicker::make('release_date')
                        ->label('Release Date')
                        ->required()
                        ->default(today())
                        ->native(false),

                    Forms\Components\Textarea::make('release_remarks')
                        ->label('Release Remarks')
                        ->rows(2)
                        ->maxLength(500),
                ]),

            Forms\Components\Section::make('Engage at New Site')
                ->icon('heroicon-o-link')
                ->schema([
                    Forms\Components\Select::make('new_vendor_id')
                        ->label('New Site / Vendor')
                        ->options(fn() => Vendor::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('new_contract_id')
                        ->label('New Contract (optional)')
                        ->options(function (Forms\Get $get) {
                            $vendorId = $get('new_vendor_id');
                            if (!$vendorId) return [];
                            return Contract::where('vendor_id', $vendorId)
                                ->where('overall_status', 'Active')
                                ->orderBy('work_order_code')
                                ->get()
                                ->mapWithKeys(fn($c) => [
                                    $c->id => "{$c->work_order_code} — {$c->contract_title}",
                                ]);
                        })
                        ->placeholder('Select a vendor first')
                        ->searchable(),

                    Forms\Components\DatePicker::make('engage_date')
                        ->label('Engagement Date')
                        ->required()
                        ->default(today())
                        ->native(false),

                    Forms\Components\Textarea::make('engage_remarks')
                        ->label('Engagement Remarks')
                        ->rows(2)
                        ->maxLength(500),
                ]),
        ];
    }

    // ── Reusable action performers ─────────────────────────────────────────

    private static function performEngage(Collection $records, array $data): void
    {
        $skipped = 0;
        $engaged = 0;

        foreach ($records as $employee) {
            try {
                $employee->engageToSite(
                    vendorId:    $data['vendor_id'],
                    contractId:  $data['contract_id'] ?? null,
                    engagedDate: $data['engaged_date'],
                    remarks:     $data['remarks'] ?? null,
                );
                $engaged++;
            } catch (ValidationException) {
                $skipped++; // already engaged — skip silently
            }
        }

        $vendorName = Vendor::find($data['vendor_id'])?->name;

        Notification::make()
            ->title('Engagement Complete')
            ->body(
                "{$engaged} employee(s) engaged at {$vendorName}."
                . ($skipped ? " {$skipped} skipped (already engaged)." : '')
            )
            ->success()
            ->send();
    }

    private static function performRelease(Collection $records, array $data): void
    {
        $skipped  = 0;
        $released = 0;

        foreach ($records as $employee) {
            try {
                $employee->releaseFromSite(
                    releasedDate: $data['released_date'],
                    remarks:      $data['remarks'] ?? null,
                );
                $released++;
            } catch (ValidationException) {
                $skipped++; // already available — skip silently
            }
        }

        Notification::make()
            ->title('Release Complete')
            ->body(
                "{$released} employee(s) released."
                . ($skipped ? " {$skipped} skipped (not engaged)." : '')
            )
            ->success()
            ->send();
    }

    private static function performTransfer(Collection $records, array $data): void
    {
        $skipped    = 0;
        $transferred = 0;

        foreach ($records as $employee) {
            try {
                $employee->transferToSite(
                    newVendorId:    $data['new_vendor_id'],
                    newContractId:  $data['new_contract_id'] ?? null,
                    releaseDate:    $data['release_date'],
                    engageDate:     $data['engage_date'],
                    releaseRemarks: $data['release_remarks'] ?? null,
                    engageRemarks:  $data['engage_remarks'] ?? null,
                );
                $transferred++;
            } catch (ValidationException) {
                $skipped++; // not engaged — cannot transfer
            }
        }

        $vendorName = Vendor::find($data['new_vendor_id'])?->name;

        Notification::make()
            ->title('Transfer Complete')
            ->body(
                "{$transferred} employee(s) transferred to {$vendorName}."
                . ($skipped ? " {$skipped} skipped (not currently engaged)." : '')
            )
            ->success()
            ->send();
    }

    // ── Pages ──────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteEngagements::route('/'),
            'view'  => Pages\ViewSiteEngagement::route('/{record}'),
        ];
    }

    // ── Navigation badge: count of currently engaged employees ─────────────

    public static function getNavigationBadge(): ?string
    {
        return (string) Employee::engaged()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'success';
    }

    public static function getNavigationBadgeTooltip(): string
    {
        return 'Currently engaged employees';
    }

}