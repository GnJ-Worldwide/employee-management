<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\InvoiceComplianceChecklist;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\HtmlString;

class ComplianceChecklistRelationManager extends RelationManager
{
    protected static string $relationship = 'complianceChecklists';
    protected static ?string $title = 'HR Clearance Checklist';

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Build the form schema for one checklist item inside the slide-over.
     * Field names are namespaced as  items.<key>.field  so the whole
     * 17-item form lives in a single  $data['items']  array.
     */
    private static function itemSchema(string $key, string $label): Forms\Components\Fieldset
    {
        return Forms\Components\Fieldset::make($label)
            ->schema([
                Forms\Components\Select::make("items.{$key}.status")
                    ->label('Status')
                    ->options([
                        'Yes' => '✅  Yes',
                        'No' => '❌  No',
                        'NA' => '—  Not Applicable',
                    ])
                    ->default('NA')
                    ->required()
                    ->selectablePlaceholder(false),

                Forms\Components\TextInput::make("items.{$key}.reference_number")
                    ->label('Licence / Policy / Code / TRRN No.')
                    ->placeholder('e.g. TRRN19876543210')
                    ->maxLength(255),

                Forms\Components\DatePicker::make("items.{$key}.validity_date")
                    ->label('Date of Compliance / Valid Upto')
                    ->native(false)
                    ->displayFormat('d M Y'),

                Forms\Components\TextInput::make("items.{$key}.remarks")
                    ->label('Remarks')
                    ->maxLength(500),
            ])
            ->columns(4);
    }

    /**
     * Build the complete form schema grouped by tabs.
     */
    private static function buildFormSchema(): array
    {
        $tabs = [];

        foreach (InvoiceComplianceChecklist::CHECKLIST_GROUPS as $groupLabel => $keys) {
            $itemSchemas = [];
            foreach ($keys as $key) {
                $label = InvoiceComplianceChecklist::CHECKLIST_ITEMS[$key] ?? $key;
                $itemSchemas[] = self::itemSchema($key, $label);
            }

            $tabs[] = Forms\Components\Tabs\Tab::make($groupLabel)
                ->schema($itemSchemas);
        }

        return [
            Forms\Components\Tabs::make('Checklist Groups')
                ->tabs($tabs)
                ->columnSpanFull(),
        ];
    }

    /**
     * Load existing checklist data into the form's fill-data shape.
     */
    private function buildFillData(): array
    {
        $existing = $this->ownerRecord
            ->complianceChecklists()
            ->get()
            ->keyBy('check_item_key');

        $items = [];
        foreach (array_keys(InvoiceComplianceChecklist::CHECKLIST_ITEMS) as $key) {
            $row = $existing->get($key);
            $items[$key] = [
                'status' => $row?->status ?? 'NA',
                'reference_number' => $row?->reference_number ?? null,
                'validity_date' => $row?->validity_date?->format('Y-m-d') ?? null,
                'remarks' => $row?->remarks ?? null,
            ];
        }

        return ['items' => $items];
    }

    // ── Table ──────────────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('check_item_key')

            // Override query order to match CHECKLIST_ITEMS definition order
            ->modifyQueryUsing(function ($query) {
                $keys = array_keys(InvoiceComplianceChecklist::CHECKLIST_ITEMS);
                // MySQL / SQLite compatible ordering via FIELD()
                if (count($keys)) {
                    $placeholders = implode(',', array_fill(0, count($keys), '?'));
                    $query->orderByRaw(
                        "FIELD(check_item_key, {$placeholders})",
                        $keys
                    );
                }
                return $query;
            })

            ->columns([
                // ── #  ─────────────────────────────────────────────────────
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),

                // ── Checkpoint ─────────────────────────────────────────────
                Tables\Columns\TextColumn::make('check_item_key')
                    ->label('Check Point')
                    ->formatStateUsing(
                        fn(string $state) =>
                        InvoiceComplianceChecklist::CHECKLIST_ITEMS[$state]
                        ?? ucwords(str_replace('_', ' ', $state))
                    )
                    ->wrap(),

                // ── Status Y / N / NA ──────────────────────────────────────
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Y/N')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Yes' => 'success',
                        'No' => 'danger',
                        'NA' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'Yes' => 'Yes ✅',
                        'No' => 'No ❌',
                        'NA' => 'N/A —',
                        default => $state,
                    }),

                // ── Reference number ───────────────────────────────────────
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Licence / Policy / Code No.')
                    ->placeholder('—')
                    ->copyable()
                    ->limit(30),

                // ── Validity / compliance date ─────────────────────────────
                Tables\Columns\TextColumn::make('validity_date')
                    ->label('Date of Compliance / Valid Upto')
                    ->date('d M Y')
                    ->placeholder('—'),

                // ── Remarks ────────────────────────────────────────────────
                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->placeholder('—')
                    ->limit(35)
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── Last updated ───────────────────────────────────────────
                Tables\Columns\TextColumn::make('updated_by')
                    ->label('Updated By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ── Summary stats in the table header ─────────────────────────
            ->description(function () {
                $rows = $this->ownerRecord->complianceChecklists;
                $total = count(InvoiceComplianceChecklist::CHECKLIST_ITEMS);
                $yes = $rows->where('status', 'Yes')->count();
                $no = $rows->where('status', 'No')->count();
                $na = $rows->where('status', 'NA')->count();
                $pending = $total - $rows->count();

                return new HtmlString(
                    view('filament.components.compliance-summary', compact(
                        'total',
                        'yes',
                        'no',
                        'na',
                        'pending'
                    ))->render()
                );
            })

            // ── Header Actions ─────────────────────────────────────────────
            ->headerActions([

                // ── Fill / Update Checklist ────────────────────────────────
                Action::make('fillChecklist')
                    ->label('Fill / Update Checklist')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->visible(fn() => !$this->ownerRecord->isTerminal())
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->modalHeading('HR Clearance Checklist')
                    // ->modalDescription(
                    //     'Complete all compliance check points for this invoice. '
                    //     . 'Items marked "No" will block the invoice from being approved.'
                    // )
                    ->form(fn() => self::buildFormSchema())
                    ->fillForm(fn() => $this->buildFillData())
                    ->action(function (array $data): void {
                        $invoice = $this->ownerRecord;
                        $updatedBy = auth()->user()->name;
                        $now = now();

                        foreach ($data['items'] as $key => $values) {
                            $invoice->complianceChecklists()->updateOrCreate(
                                ['check_item_key' => $key],
                                [
                                    'status' => $values['status'] ?? 'NA',
                                    'reference_number' => $values['reference_number'] ?? null,
                                    'validity_date' => $values['validity_date'] ?? null,
                                    'remarks' => $values['remarks'] ?? null,
                                    'updated_by' => $updatedBy,
                                    'updated_at' => $now,
                                ]
                            );
                        }

                        Notification::make()
                            ->success()
                            ->title('Checklist saved successfully.')
                            ->body('All ' . count($data['items']) . ' check points have been updated.')
                            ->send();
                    })
                    ->successNotificationTitle('Checklist saved.'),

                // ── Seed (first-time init) ─────────────────────────────────
                Action::make('seedChecklist')
                    ->label('Initialise Checklist')
                    ->icon('heroicon-o-plus-circle')
                    ->color('gray')
                    ->tooltip('Creates all 17 checklist rows with default N/A status. Only needed if they are missing.')
                    ->requiresConfirmation()
                    ->modalHeading('Initialise HR Clearance Checklist?')
                    ->modalDescription('This will create all 17 checklist rows for this invoice with a default status of "N/A". Existing rows will not be overwritten.')
                    ->visible(fn() => !$this->ownerRecord->isTerminal())
                    ->action(function (): void {
                        InvoiceComplianceChecklist::seedForInvoice($this->ownerRecord->id);
                        Notification::make()
                            ->success()
                            ->title('Checklist initialised.')
                            ->body('17 checklist items are ready to be filled in.')
                            ->send();
                    }),
            ])

            // ── Row Actions ────────────────────────────────────────────────
            ->actions([

                // Quick inline edit for a single row
                Tables\Actions\Action::make('editRow')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->visible(fn() => !$this->ownerRecord->isTerminal())
                    ->form(function (InvoiceComplianceChecklist $record): array {
                        $label = InvoiceComplianceChecklist::CHECKLIST_ITEMS[$record->check_item_key]
                            ?? $record->check_item_key;

                        return [
                            Forms\Components\Placeholder::make('item_label')
                                ->label('Check Point')
                                ->content($label)
                                ->columnSpanFull(),

                            Forms\Components\Select::make('status')
                                ->label('Status Y/N')
                                ->options([
                                    'Yes' => '✅  Yes',
                                    'No' => '❌  No',
                                    'NA' => '—  Not Applicable',
                                ])
                                ->required()
                                ->selectablePlaceholder(false),

                            Forms\Components\TextInput::make('reference_number')
                                ->label('Licence / Policy / Code / TRRN No.')
                                ->maxLength(255),

                            Forms\Components\DatePicker::make('validity_date')
                                ->label('Date of Compliance / Valid Upto')
                                ->native(false)
                                ->displayFormat('d M Y'),

                            Forms\Components\Textarea::make('remarks')
                                ->label('Remarks')
                                ->rows(2),
                        ];
                    })
                    ->fillForm(fn(InvoiceComplianceChecklist $record) => $record->only([
                        'status',
                        'reference_number',
                        'validity_date',
                        'remarks',
                    ]))
                    ->action(function (InvoiceComplianceChecklist $record, array $data): void {
                        $record->update([
                            ...$data,
                            'updated_by' => auth()->user()->name,
                        ]);
                        Notification::make()->success()->title('Check point updated.')->send();
                    }),
            ])

            // Disable default CRUD — checklist rows are managed via actions only
            ->paginated(false);
    }
}