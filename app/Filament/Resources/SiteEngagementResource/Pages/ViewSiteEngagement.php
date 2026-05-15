<?php
namespace App\Filament\Resources\SiteEngagementResource\Pages;
 
use App\Filament\Resources\SiteEngagementResource;
use App\Models\Contract;
use App\Models\Vendor;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Validation\ValidationException;
 
class ViewSiteEngagement extends ViewRecord
{
    protected static string $resource = SiteEngagementResource::class;
 
    protected function getHeaderActions(): array
    {
        return [
            // Engage — shown when Available
            Actions\Action::make('engage')
                ->label('Engage')
                ->icon('heroicon-o-link')
                ->color('success')
                ->visible(fn() => $this->record->isAvailable())
                ->form([
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
                        ->rows(2)->maxLength(500),
                ])
                ->action(function (array $data) {
                    try {
                        $this->record->engageToSite(
                            vendorId:    $data['vendor_id'],
                            contractId:  $data['contract_id'] ?? null,
                            engagedDate: $data['engaged_date'],
                            remarks:     $data['remarks'] ?? null,
                        );
                        $this->refreshFormData(['engagement_status', 'current_vendor_id', 'current_contract_id']);
                        Notification::make()->title('Employee Engaged')->success()->send();
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title('Cannot Engage')
                            ->body(collect($e->errors())->flatten()->first())
                            ->danger()->send();
                    }
                }),
 
            // Release — shown when Engaged
            Actions\Action::make('release')
                ->label('Release')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->visible(fn() => $this->record->isEngaged())
                ->form([
                    Forms\Components\DatePicker::make('released_date')
                        ->label('Release Date')
                        ->required()
                        ->default(today())
                        ->native(false),
 
                    Forms\Components\Textarea::make('remarks')
                        ->label('Remarks')
                        ->rows(2)->maxLength(500),
                ])
                ->action(function (array $data) {
                    try {
                        $this->record->releaseFromSite(
                            releasedDate: $data['released_date'],
                            remarks:      $data['remarks'] ?? null,
                        );
                        $this->refreshFormData(['engagement_status', 'current_vendor_id', 'current_contract_id']);
                        Notification::make()->title('Employee Released')->success()->send();
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title('Cannot Release')
                            ->body(collect($e->errors())->flatten()->first())
                            ->danger()->send();
                    }
                }),
 
            // Transfer — shown when Engaged
            Actions\Action::make('transfer')
                ->label('Transfer')
                ->icon('heroicon-o-arrows-right-left')
                ->color('info')
                ->visible(fn() => $this->record->isEngaged())
                ->form(fn() => [
                    Forms\Components\Section::make('Release from ' . ($this->record->currentVendor?->name ?? 'current site'))
                        ->schema([
                            Forms\Components\DatePicker::make('release_date')
                                ->required()->default(today())->native(false),
                            Forms\Components\Textarea::make('release_remarks')
                                ->rows(2)->maxLength(500),
                        ]),
 
                    Forms\Components\Section::make('Engage at New Site')->schema([
                        Forms\Components\Select::make('new_vendor_id')
                            ->label('New Site / Vendor')
                            ->options(fn() => Vendor::orderBy('name')->pluck('name', 'id'))
                            ->searchable()->required()->live(),
 
                        Forms\Components\Select::make('new_contract_id')
                            ->label('New Contract (optional)')
                            ->options(function (Forms\Get $get) {
                                $vendorId = $get('new_vendor_id');
                                if (!$vendorId) return [];
                                return Contract::where('vendor_id', $vendorId)
                                    ->where('overall_status', 'Active')
                                    ->get()
                                    ->mapWithKeys(fn($c) => [
                                        $c->id => "{$c->work_order_code} — {$c->contract_title}",
                                    ]);
                            })
                            ->placeholder('Select a vendor first')->searchable(),
 
                        Forms\Components\DatePicker::make('engage_date')
                            ->required()->default(today())->native(false),
 
                        Forms\Components\Textarea::make('engage_remarks')
                            ->rows(2)->maxLength(500),
                    ]),
                ])
                ->action(function (array $data) {
                    try {
                        $this->record->transferToSite(
                            newVendorId:    $data['new_vendor_id'],
                            newContractId:  $data['new_contract_id'] ?? null,
                            releaseDate:    $data['release_date'],
                            engageDate:     $data['engage_date'],
                            releaseRemarks: $data['release_remarks'] ?? null,
                            engageRemarks:  $data['engage_remarks'] ?? null,
                        );
                        $this->refreshFormData(['engagement_status', 'current_vendor_id', 'current_contract_id']);
                        Notification::make()->title('Transfer Complete')->success()->send();
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title('Transfer Failed')
                            ->body(collect($e->errors())->flatten()->first())
                            ->danger()->send();
                    }
                }),
 
            Actions\Action::make('edit_employee')
                ->label('Edit Employee')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->url(fn() => EmployeeResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
 
    // ── View page infolist: engagement summary + history ──────────────────
 
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
 
            // ── Current status strip ───────────────────────────────────────
            Infolists\Components\Section::make()
                ->schema([
                    Infolists\Components\ImageEntry::make('doc_photo')
                        ->label('')->disk('public')->circular()->height(72)->columnSpan(1),
 
                    Infolists\Components\Group::make([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Employee')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
 
                        Infolists\Components\TextEntry::make('engagement_status')
                            ->label('Engagement')
                            ->badge()
                            ->color(fn(string $state) => match ($state) {
                                'Engaged'   => 'success',
                                'Available' => 'gray',
                            }),
 
                        Infolists\Components\TextEntry::make('designation')
                            ->badge()->color('primary'),
                    ])->columnSpan(2),
 
                    Infolists\Components\Group::make([
                        Infolists\Components\TextEntry::make('currentVendor.name')
                            ->label('Current Site')
                            ->placeholder('—')
                            ->icon('heroicon-o-building-office'),
 
                        Infolists\Components\TextEntry::make('currentContract.work_order_code')
                            ->label('Work Order')
                            ->badge()->color('gray')
                            ->placeholder('—'),
 
                        Infolists\Components\TextEntry::make('activeEngagement.engaged_date')
                            ->label('Engaged Since')
                            ->date('d M Y')
                            ->placeholder('—')
                            ->icon('heroicon-o-calendar'),
                    ])->columnSpan(2),
                ])
                ->columns(5),
 
            // ── Full engagement history ────────────────────────────────────
            Infolists\Components\Section::make('Engagement History')
                ->icon('heroicon-o-clock')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('siteEngagements')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('vendor.name')
                                ->label('Site / Vendor')
                                ->weight('semibold'),
 
                            Infolists\Components\TextEntry::make('contract.work_order_code')
                                ->label('Work Order')
                                ->badge()->color('gray')
                                ->placeholder('—'),
 
                            Infolists\Components\TextEntry::make('engaged_date')
                                ->label('Engaged On')
                                ->date('d M Y'),
 
                            Infolists\Components\TextEntry::make('released_date')
                                ->label('Released On')
                                ->date('d M Y')
                                ->placeholder('Still Active'),
 
                            Infolists\Components\TextEntry::make('status')
                                ->badge()
                                ->color(fn(string $state) => match ($state) {
                                    'Engaged'  => 'success',
                                    'Released' => 'gray',
                                }),
 
                            Infolists\Components\TextEntry::make('engagement_remarks')
                                ->label('Remarks')
                                ->placeholder('—')
                                ->columnSpanFull(),
                        ])
                        ->columns(5)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
 