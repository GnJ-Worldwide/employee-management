<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceComplianceChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicePrintController extends Controller
{
    /**
     * Render the printable invoice view.
     *
     * URL: /invoices/{invoice}/print
     *
     * Protected: only authenticated users with the right permission
     * can access this route. The middleware check mirrors what
     * Filament already does on the resource.
     */
    public function __invoke(Invoice $invoice)
    {
        // Auth guard — reuse Filament's auth
        abort_unless(
            Auth::check() && Auth::user()->can('view_any_invoice'),
            403,
            'You are not authorised to view this invoice.'
        );

        // Eager-load everything we need in one query
        $invoice->loadMissing([
            'contract',
            'complianceDocuments',
            'complianceChecklists',
        ]);

        $contract = $invoice->contract;

        // Documents — sorted by document_type for consistent ordering
        $documents = $invoice->complianceDocuments->sortBy('document_type');

        // Checklist — keyed by check_item_key for O(1) lookup in the template
        $checklistMap = $invoice->complianceChecklists->keyBy('check_item_key');

        return view('invoices.print', compact(
            'invoice',
            'contract',
            'documents',
            'checklistMap',
        ));
    }
}