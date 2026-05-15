<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\ComplianceDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ComplianceDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'complianceDocuments';
    protected static ?string $title = 'Compliance Documents';

    // ── Shared document-type options (single source of truth) ─────────────
    protected static array $documentTypeOptions = [
        'form_a' => 'Form A',
        'form_b' => 'Form B',
        'form_c' => 'Form C',
        'form_d' => 'Form D',
        'ecr_pf' => 'ECR PF',
        'challan_and_copy_of_remittance_pf' => 'Challan and Copy of Remittance PF',
        'ecr_esic' => 'ECR ESIC',
        'challan_and_copy_of_remittance_esic' => 'Challan and Copy of Remittance ESIC',
        'bank_statement' => 'Bank Statement',
        'annual_return' => 'Annual Return',
        'bonus_register' => 'Bonus Register',
        'lwf_challan_and_remittance' => 'LWF Challan and Remittance',
        'challan_and_copy_of_remittance_pt' => 'Challan and Copy of Remittance PT',
        'user_attachment_1' => 'User Attachment 1',
        'user_attachment_2' => 'User Attachment 2',
        'user_attachment_3' => 'User Attachment 3',
        'other' => 'Other',
    ];

    // ── Form (used by the single CreateAction) ─────────────────────────────
    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Select::make('document_type')
                ->label('Document Type')
                ->options(static::$documentTypeOptions)
                ->required()
                ->columnSpanFull(),

            Forms\Components\ToggleButtons::make('input_mode')
                ->label('How would you like to provide the document?')
                ->options([
                    'file' => 'Upload a File',
                    'text' => 'Enter Text',
                ])
                ->icons([
                    'file' => 'heroicon-o-paper-clip',
                    'text' => 'heroicon-o-pencil-square',
                ])
                ->colors([
                    'file' => 'primary',
                    'text' => 'info',
                ])
                ->default('file')
                ->inline()
                ->live()
                ->required()
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('file_path')
                ->label('Upload Document')
                ->disk('public')
                ->directory('compliance-docs')
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->downloadable()
                ->openable()
                ->maxSize(5120)
                ->storeFileNamesIn('original_filename')
                ->required(fn(Get $get) => $get('input_mode') === 'file')
                ->visible(fn(Get $get) => $get('input_mode') === 'file')
                ->columnSpanFull(),

            Forms\Components\Textarea::make('text_content')
                ->label('Content')
                ->placeholder("Paste or type your document content here.")
                ->helperText('Supports plain text.')
                ->rows(10)
                ->required(fn(Get $get) => $get('input_mode') === 'text')
                ->visible(fn(Get $get) => $get('input_mode') === 'text')
                ->columnSpanFull(),

            Forms\Components\Textarea::make('remarks')
                ->label('Remarks (optional)')
                ->rows(2)
                ->columnSpanFull(),

        ])->columns(1);
    }

    // ── Table ──────────────────────────────────────────────────────────────
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_type')
            ->columns([

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Document Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(
                        fn(string $state) => static::$documentTypeOptions[$state]
                        ?? ucfirst(str_replace('_', ' ', $state))
                    ),

                Tables\Columns\TextColumn::make('input_mode')
                    ->label('Content')
                    ->badge()
                    ->color(fn(string $state) => $state === 'file' ? 'primary' : 'info')
                    ->formatStateUsing(fn(string $state) => $state === 'file' ? '📎 File' : '📝 Text'),

                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Filename')
                    ->limit(35)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('text_content')
                    ->label('Text Preview')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Verified' => 'success',
                        'Discrepancy' => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('verified_by')
                    ->label('Verified By')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('remarks')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ── Header Actions ─────────────────────────────────────────────
            ->headerActions([

                // ── ① Bulk Upload ──────────────────────────────────────────
                Tables\Actions\Action::make('bulk_upload')
                    ->label('Bulk Upload')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->visible(fn() => !$this->ownerRecord->isTerminal())

                    // Renders as a wide side-sheet instead of a cramped modal
                    ->slideOver()

                    ->modalHeading('Bulk Upload Compliance Documents')
                    ->modalDescription(
                        'All document types are pre-loaded below. ' .
                        'Expand the rows you need, fill them in, then click "Upload All". ' .
                        'Rows left empty are skipped automatically — nothing extra to add or remove.'
                    )
                    ->modalSubmitActionLabel('Upload All')
                    ->form([

                        Forms\Components\Repeater::make('documents')
                            ->label('')
                            ->schema([

                                // ── Row header: type label (read-only) + mode toggle ──
                                Forms\Components\Grid::make(2)->schema([

                                    // The type is pre-set; disable editing but keep the
                                    // value in the submitted payload via ->dehydrated().
                                    Forms\Components\Select::make('document_type')
                                        ->label('Document Type')
                                        ->options(static::$documentTypeOptions)
                                        ->disabled()
                                        ->dehydrated()
                                        ->required(),

                                    Forms\Components\ToggleButtons::make('input_mode')
                                        ->label('Input Mode')
                                        ->options([
                                            'file' => 'File(s)',
                                            'text' => 'Text',
                                        ])
                                        ->icons([
                                            'file' => 'heroicon-o-paper-clip',
                                            'text' => 'heroicon-o-pencil-square',
                                        ])
                                        ->colors([
                                            'file' => 'primary',
                                            'text' => 'info',
                                        ])
                                        ->default('file')
                                        ->inline()
                                        ->live()
                                        ->required(),
                                ]),

                                // ── Multiple file upload ───────────────────────────────
                                Forms\Components\FileUpload::make('file_paths')
                                    ->label('Upload Files')
                                    ->disk('public')
                                    ->directory('compliance-docs')
                                    ->multiple()
                                    ->reorderable()
                                    ->appendFiles()
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(5120)
                                    ->maxFiles(20)
                                    ->helperText('PDF or image, max 5 MB each. Each file becomes its own record.')
                                    // Not required — empty rows are just skipped
                                    ->visible(fn(Get $get) => $get('input_mode') === 'file')
                                    ->columnSpanFull(),

                                // ── Text entry ────────────────────────────────────────
                                Forms\Components\Textarea::make('text_content')
                                    ->label('Content')
                                    ->placeholder('Paste or type your document content here.')
                                    ->rows(6)
                                    // Not required — empty rows are just skipped
                                    ->visible(fn(Get $get) => $get('input_mode') === 'text')
                                    ->columnSpanFull(),

                                // ── Optional remarks ──────────────────────────────────
                                Forms\Components\TextInput::make('remarks')
                                    ->label('Remarks (optional — applied to all files in this row)')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)

                            // ── Pre-populate one row per document type ─────────────
                            ->default(
                                array_map(
                                    fn(string $key) => [
                                        'document_type' => $key,
                                        'input_mode' => 'file',
                                        'file_paths' => [],
                                        'text_content' => null,
                                        'remarks' => null,
                                    ],
                                    array_keys(static::$documentTypeOptions)
                                )
                            )

                            // ── Repeater behaviour ─────────────────────────────────
                            ->collapsible()           // let users collapse rows they don't need
                            ->addable(false)          // types are pre-set; no manual adding
                            ->deletable(false)        // prevent accidental removal of a type
                            ->reorderableWithButtons(false)

                            // Dynamic label shows type name + a ✅ when content is present
                            ->itemLabel(function (array $state): string {
                                $type = $state['document_type'] ?? null;
                                $mode = $state['input_mode'] ?? 'file';

                                $label = $type
                                    ? (static::$documentTypeOptions[$type] ?? ucfirst(str_replace('_', ' ', $type)))
                                    : 'Unknown';

                                $modeIcon = $mode === 'text' ? '📝' : '📎';

                                $filled = ($mode === 'file' && !empty($state['file_paths']))
                                    || ($mode === 'text' && !empty(trim($state['text_content'] ?? '')));

                                $statusBadge = $filled ? '  ✅' : '';

                                return "{$modeIcon}  {$label}{$statusBadge}";
                            })
                            ->columnSpanFull(),
                    ])

                    // ── Action handler ─────────────────────────────────────
                    ->action(function (array $data): void {
                        $created = 0;

                        foreach ($data['documents'] as $entry) {
                            $docType = $entry['document_type'];
                            $mode = $entry['input_mode'] ?? 'file';
                            $remarks = $entry['remarks'] ?? null;

                            if ($mode === 'file') {
                                $filePaths = $entry['file_paths'] ?? [];

                                // Skip this row if no files were uploaded
                                if (empty($filePaths)) {
                                    continue;
                                }

                                foreach ($filePaths as $storedPath) {
                                    $this->ownerRecord->complianceDocuments()->create([
                                        'document_type' => $docType,
                                        'input_mode' => 'file',
                                        'file_path' => $storedPath,
                                        'original_filename' => basename($storedPath),
                                        'remarks' => $remarks,
                                        'status' => 'Pending',
                                    ]);
                                    $created++;
                                }
                            } else {
                                $text = trim($entry['text_content'] ?? '');

                                // Skip this row if no text was entered
                                if (empty($text)) {
                                    continue;
                                }

                                $this->ownerRecord->complianceDocuments()->create([
                                    'document_type' => $docType,
                                    'input_mode' => 'text',
                                    'text_content' => $text,
                                    'remarks' => $remarks,
                                    'status' => 'Pending',
                                ]);
                                $created++;
                            }
                        }

                        if ($created === 0) {
                            Notification::make()
                                ->warning()
                                ->title('Nothing was uploaded.')
                                ->body('Expand at least one row and add a file or text before submitting.')
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title("{$created} document(s) uploaded successfully.")
                            ->send();
                    }),

                // ── ② Single-add (original — preserved) ───────────────────
                Tables\Actions\CreateAction::make()
                    ->label('Add Single Document')
                    ->icon('heroicon-o-plus')
                    ->color('gray')
                    ->visible(fn() => !$this->ownerRecord->isTerminal()),
            ])

            // ── Row Actions ────────────────────────────────────────────────
            ->actions([

                // ── View Text Content ──────────────────────────────────────
                Tables\Actions\Action::make('view_text')
                    ->label('View Content')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn(ComplianceDocument $r) => $r->input_mode === 'text' && !empty($r->text_content))
                    ->modalHeading('Document Text Content')
                    ->modalContent(
                        fn(ComplianceDocument $r) => view(
                            'filament.modals.text-content-preview',
                            ['content' => $r->text_content]
                        )
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                // ── Verify ─────────────────────────────────────────────────
                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn(ComplianceDocument $r) => $r->status === 'Pending')
                    ->requiresConfirmation()
                    ->action(function (ComplianceDocument $record): void {
                        $record->update([
                            'status' => 'Verified',
                            'verified_by' => auth()->user()->name,
                            'verified_at' => now(),
                        ]);
                        Notification::make()->success()->title('Document verified.')->send();
                    }),

                // ── Flag Discrepancy ───────────────────────────────────────
                Tables\Actions\Action::make('discrepancy')
                    ->label('Flag Discrepancy')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn(ComplianceDocument $r) => $r->status !== 'Discrepancy')
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Describe the discrepancy')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (ComplianceDocument $record, array $data): void {
                        $record->update([
                            'status' => 'Discrepancy',
                            'verified_by' => auth()->user()->name,
                            'verified_at' => now(),
                            'remarks' => $data['remarks'],
                        ]);
                        Notification::make()->danger()->title('Discrepancy flagged.')->send();
                    }),

                // ── Download ───────────────────────────────────────────────
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn(ComplianceDocument $r) => $r->input_mode === 'file' && !empty($r->file_path))
                    ->url(fn(ComplianceDocument $r) => asset('storage/' . $r->file_path))
                    ->openUrlInNewTab(),

                // ── Delete ─────────────────────────────────────────────────
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(ComplianceDocument $r) => $r->status === 'Pending'),
            ]);
    }
}