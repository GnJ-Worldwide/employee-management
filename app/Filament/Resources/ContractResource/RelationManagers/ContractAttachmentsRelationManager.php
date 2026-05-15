<?php

namespace App\Filament\Resources\ContractResource\RelationManagers;

use App\Models\ContractAttachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContractAttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'contractAttachments';
    protected static ?string $title = 'Attachments';

    
    // ── Shared document type options ───────────────────────────────────────
    private static function documentTypeOptions(): array
    {
        return [
            'invoice' => 'Invoice',
            'invoice_verification_measurement_sheet' => 'Invoice Verification Measurement Sheet',
            'abstract_report' => 'Abstract Report',
            'material_cost_reconciliation_report' => 'Material Cost Reconciliation Report',
            'check_list_establishment_report' => 'Check List Establishment Report',
            'site_expansion_report' => 'Site Expansion Report',
            'user_attachment_1' => 'User Attachment 1',
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            // ── Document Type ──────────────────────────────────────────────
            Forms\Components\Select::make('document_type')
                ->label('Document Type')
                ->options(self::documentTypeOptions())
                ->required()
                ->columnSpanFull(),

            // ── Input Mode Toggle ──────────────────────────────────────────
            Forms\Components\ToggleButtons::make('input_mode')
                ->label('How would you like to provide this document?')
                ->options([
                    'file' => 'Upload a File',
                    'text' => 'Enter Text Manually',
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

            // ── File Upload ────────────────────────────────────────────────
            Forms\Components\FileUpload::make('file_path')
                ->label('Upload Document')
                ->disk('public')
                ->directory('contract-attachments')
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->downloadable()
                ->openable()
                ->maxSize(5120)
                ->storeFileNamesIn('original_filename')
                ->required(fn(Get $get) => $get('input_mode') === 'file')
                ->visible(fn(Get $get) => $get('input_mode') === 'file')
                ->columnSpanFull(),

            // ── Manual Text Entry ──────────────────────────────────────────
            Forms\Components\Textarea::make('text_content')
                ->label('Document Content')
                ->placeholder("Type or paste the document content here...")
                ->helperText('Plain text only. This content will be stored in the database.')
                ->rows(10)
                ->required(fn(Get $get) => $get('input_mode') === 'text')
                ->visible(fn(Get $get) => $get('input_mode') === 'text')
                ->columnSpanFull(),

            // ── Remarks ────────────────────────────────────────────────────
            Forms\Components\Textarea::make('remarks')
                ->label('Remarks (optional)')
                ->rows(2)
                ->columnSpanFull(),

        ])->columns(1);
    }

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
                        fn(string $state) => self::documentTypeOptions()[$state]
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Attachment'),
            ])
            ->actions([

                // ── View Text Content ──────────────────────────────────────
                Tables\Actions\Action::make('view_text')
                    ->label('View Content')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn(ContractAttachment $r) => $r->input_mode === 'text' && !empty($r->text_content))
                    ->modalHeading('Document Content')
                    ->modalContent(
                        fn(ContractAttachment $r) => view(
                            'filament.modals.text-content-preview',
                            ['content' => $r->text_content]
                        )
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                // ── Verify ────────────────────────────────────────────────
                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn(ContractAttachment $r) => $r->status === 'Pending')
                    ->requiresConfirmation()
                    ->action(function (ContractAttachment $record): void {
                        $record->update([
                            'status' => 'Verified',
                            'verified_by' => auth()->user()->name,
                            'verified_at' => now(),
                        ]);
                        Notification::make()->success()->title('Attachment verified.')->send();
                    }),

                // ── Flag Discrepancy ──────────────────────────────────────
                Tables\Actions\Action::make('discrepancy')
                    ->label('Flag Discrepancy')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn(ContractAttachment $r) => $r->status !== 'Discrepancy')
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Describe the discrepancy')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (ContractAttachment $record, array $data): void {
                        $record->update([
                            'status' => 'Discrepancy',
                            'verified_by' => auth()->user()->name,
                            'verified_at' => now(),
                            'remarks' => $data['remarks'],
                        ]);
                        Notification::make()->danger()->title('Discrepancy flagged.')->send();
                    }),

                // ── Download ──────────────────────────────────────────────
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn(ContractAttachment $r) => $r->input_mode === 'file' && !empty($r->file_path))
                    ->url(fn(ContractAttachment $r) => asset('storage/' . $r->file_path))
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn(ContractAttachment $r) => $r->status === 'Pending'),
            ]);
    }
}