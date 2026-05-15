<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\InvoiceComplianceChecklist;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // ── 🖨 Print / Download ────────────────────────────────────────
            Action::make('print')
                ->label('Print / Download')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn() => route('invoices.print', $this->record))
                ->openUrlInNewTab(),

            // ── ✅ Approve ─────────────────────────────────────────────────
            Action::make('approve')
                ->label('Approve Invoice')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === 'Pending_Compliance')
                ->action(function (): void {
                    $invoice = $this->record;

                    if (!$invoice->isChecklistCleared()) {
                        Notification::make()
                            ->danger()
                            ->title('Cannot Approve')
                            ->body('One or more checklist items are marked "No". Resolve them before approving.')
                            ->send();
                        return;
                    }

                    $invoice->update([
                        'status' => 'Approved',
                        'approved_by' => auth()->user()->name,
                        'approved_on' => now()->toDateString(),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Invoice approved.')
                        ->send();

                    $this->refreshFormData(['status', 'approved_by', 'approved_on']);
                }),

            // ── 💰 Mark as Paid ────────────────────────────────────────────
            Action::make('markPaid')
                ->label('Mark as Paid')
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === 'Approved')
                ->action(function (): void {
                    $this->record->update([
                        'status' => 'Paid',
                        'paid_by' => auth()->user()->name,
                        'paid_on' => now()->toDateString(),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Invoice marked as Paid.')
                        ->send();

                    $this->refreshFormData(['status', 'paid_by', 'paid_on']);
                }),

            // ── 🚀 Send to Compliance ──────────────────────────────────────
            Action::make('sendToCompliance')
                ->label('Send to Compliance')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === 'Submitted')
                ->action(function (): void {
                    // Seed checklist rows so the HR team has all 17 items ready
                    InvoiceComplianceChecklist::seedForInvoice($this->record->id);

                    $this->record->update(['status' => 'Pending_Compliance']);

                    Notification::make()
                        ->success()
                        ->title('Invoice sent to compliance review.')
                        ->body('17 checklist items have been initialised.')
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            // ── ❌ Reject ──────────────────────────────────────────────────
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Reason for Rejection')
                        ->required()
                        ->rows(3),
                ])
                ->visible(fn() => in_array($this->record->status, ['Submitted', 'Pending_Compliance']))
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => 'Rejected',
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    Notification::make()
                        ->danger()
                        ->title('Invoice rejected.')
                        ->send();

                    $this->refreshFormData(['status', 'rejection_reason']);
                }),

            // ── Edit (only when Submitted) ─────────────────────────────────
            Actions\EditAction::make()
                ->visible(fn() => $this->record->isEditable()),
        ];
    }
}