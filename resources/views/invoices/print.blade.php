<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #111;
            background: #fff;
            padding: 10px;
        }

        /* ── Header ── */
        .header {
            background-color: #008080;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .header img  { width: 80px; margin-right: 16px; }
        .header h2   { color: white; font-size: 20px; }
        .header p    { color: #d0f0f0; font-size: 12px; }

        /* ── Tables ── */
        table.info {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 14px;
        }
        table.info td,
        table.info th {
            border: 1px solid #333;
            padding: 4px 6px;
            vertical-align: top;
        }
        table.info th {
            background-color: #0066cc;
            color: white;
            text-align: left;
            padding: 5px 6px;
            font-size: 13px;
        }
        table.info tr:nth-child(even) { background-color: #f5f5f5; }
        table.info b { font-weight: 600; }

        /* Status badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success  { background:#d1fae5; color:#065f46; }
        .badge-warning  { background:#fef3c7; color:#92400e; }
        .badge-danger   { background:#fee2e2; color:#991b1b; }
        .badge-info     { background:#dbeafe; color:#1e40af; }
        .badge-primary  { background:#ede9fe; color:#4c1d95; }
        .badge-gray     { background:#f3f4f6; color:#374151; }

        /* Checklist status */
        .cl-yes { color: #065f46; font-weight: 700; }
        .cl-no  { color: #991b1b; font-weight: 700; }
        .cl-na  { color: #6b7280; }

        /* Document links */
        .doc-link {
            display: inline-block;
            padding: 2px 10px;
            background: #0066cc;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 11px;
        }
        .no-doc { color: #9ca3af; font-style: italic; font-size: 11px; }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 16px;
            font-size: 11px;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }

        /* Amount formatting */
        .amount { text-align: right; font-family: monospace; }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 10mm; }
        }
    </style>
</head>
<body>

{{-- ── Print Button (hidden on print) ──────────────────────────────── --}}
<div class="no-print" style="margin-bottom:12px; display:flex; gap:10px;">
    <button onclick="window.print()"
        style="padding:8px 20px;background:#0066cc;color:white;border:none;border-radius:6px;cursor:pointer;font-size:14px;">
        🖨 Print / Save as PDF
    </button>
    <a href="{{ url()->previous() }}"
        style="padding:8px 20px;background:#6b7280;color:white;border-radius:6px;text-decoration:none;font-size:14px;">
        ← Back
    </a>
</div>

{{-- ── Header ──────────────────────────────────────────────────────── --}}
<div class="header">
    {{-- Replace with your actual logo path --}}
    @if(file_exists(public_path('images/logo.png')))
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
    @endif
    <div>
        <h2>{{ config('app.name', 'Blown Insulation Services') }}</h2>
        <p>Invoice &amp; Compliance Report</p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     SECTION 1 — GENERAL INFORMATION
══════════════════════════════════════════════════════════════════ --}}
<table class="info">
    <tr>
        <th colspan="4">GENERAL INFORMATION</th>
    </tr>
    <tr>
        <td><b>Invoice Number</b></td>
        <td colspan="3">{{ $invoice->invoice_number }}</td>
    </tr>
    <tr>
        <td><b>Invoice Status</b></td>
        <td colspan="3">
            @php
                $statusColor = match ($invoice->status) {
                    'Submitted' => 'info',
                    'Pending_Compliance' => 'warning',
                    'Approved' => 'primary',
                    'Paid' => 'success',
                    'Rejected' => 'danger',
                    default => 'gray',
                };
            @endphp
            <span class="badge badge-{{ $statusColor }}">
                {{ str_replace('_', ' ', $invoice->status) }}
            </span>
        </td>
    </tr>

    {{-- Contract / Vendor / Client --}}
    <tr>
        <td><b>Work Order / PO Number</b></td>
        <td>{{ $contract->work_order_code }}</td>
        <td><b>Contract Title</b></td>
        <td>{{ $contract->contract_title }}</td>
    </tr>
    <tr>
        <td><b>Vendor Name</b></td>
        <td>{{ $contract->vendor_name }}</td>
        <td><b>Vendor GST No.</b></td>
        <td>{{ $contract->vendor_gst_no ?? '—' }}</td>
    </tr>
    <tr>
        <td><b>Client / Principal Employer</b></td>
        <td>{{ $contract->client_name }}</td>
        <td><b>Client GST No.</b></td>
        <td>{{ $contract->client_gst_no ?? '—' }}</td>
    </tr>

    {{-- Billing Period --}}
    <tr>
        <td><b>Bill Start Date</b></td>
        <td>{{ $invoice->billing_period_start->format('d-m-Y') }}</td>
        <td><b>Bill End Date</b></td>
        <td>{{ $invoice->billing_period_end->format('d-m-Y') }}</td>
    </tr>

    {{-- Invoice Details --}}
    <tr>
        <td><b>Invoice Date</b></td>
        <td>{{ $invoice->invoice_date->format('d-m-Y') }}</td>
        <td><b>Amount Billed (₹)</b></td>
        <td class="amount"><b>₹ {{ number_format($invoice->amount_billed, 2) }}</b></td>
    </tr>

    {{-- Work Order Validity --}}
    <tr>
        <td><b>Work Order Valid From</b></td>
        <td>{{ $contract->start_date->format('d-m-Y') }}</td>
        <td><b>Work Order Valid To</b></td>
        <td>{{ $contract->end_date->format('d-m-Y') }}</td>
    </tr>

    {{-- Nature of Work --}}
    <tr>
        <td><b>Nature of Work / Scope</b></td>
        <td colspan="3">{{ $contract->nature_of_work ?? '—' }}</td>
    </tr>

    {{-- Workflow Dates --}}
    <tr>
        <td><b>Submitted On</b></td>
        <td>{{ $invoice->submitted_on?->format('d-m-Y') ?? '—' }}</td>
        <td><b>Approved On</b></td>
        <td>{{ $invoice->approved_on?->format('d-m-Y') ?? '—' }}</td>
    </tr>
    @if($invoice->approved_by)
        <tr>
            <td><b>Approved By</b></td>
            <td colspan="3">{{ $invoice->approved_by }}</td>
        </tr>
    @endif
    @if($invoice->paid_on)
        <tr>
            <td><b>Paid On</b></td>
            <td>{{ $invoice->paid_on->format('d-m-Y') }}</td>
            <td><b>Paid By</b></td>
            <td>{{ $invoice->paid_by ?? '—' }}</td>
        </tr>
    @endif
    @if($invoice->rejection_reason)
        <tr>
            <td><b>Rejection Reason</b></td>
            <td colspan="3" style="color:#991b1b;">{{ $invoice->rejection_reason }}</td>
        </tr>
    @endif
</table>


{{-- ══════════════════════════════════════════════════════════════════
     SECTION 2 — COMPLIANCE DOCUMENTS
══════════════════════════════════════════════════════════════════ --}}
<table class="info">
    <tr>
        <th colspan="4">COMPLIANCE DOCUMENTS</th>
    </tr>
    <tr>
        <td width="25%"><b>Document Type</b></td>
        <td width="10%"><b>Mode</b></td>
        <td width="30%"><b>File / Content</b></td>
        <td width="35%"><b>Status &amp; Remarks</b></td>
    </tr>

    @forelse($documents as $doc)
        <tr>
            <td><b>{{ $doc->document_type_label }}</b></td>
            <td>
                @if($doc->input_mode === 'file')
                    <span class="badge badge-primary">📎 File</span>
                @else
                    <span class="badge badge-info">📝 Text</span>
                @endif
            </td>
            <td>
                @if($doc->input_mode === 'file' && $doc->file_path)
                    <a class="doc-link" href="{{ asset('storage/' . $doc->file_path) }}" target="_blank">
                        View / Download
                    </a>
                    @if($doc->original_filename)
                        <br><small style="color:#555;">{{ $doc->original_filename }}</small>
                    @endif
                @elseif($doc->input_mode === 'text' && $doc->text_content)
                    <span style="font-size:11px;color:#374151;">{{ Str::limit($doc->text_content, 80) }}</span>
                @else
                    <span class="no-doc">No content uploaded</span>
                @endif
            </td>
            <td>
                @php
                    $docColor = match ($doc->status) {
                        'Verified' => 'success',
                        'Discrepancy' => 'danger',
                        default => 'warning',
                    };
                @endphp
                <span class="badge badge-{{ $docColor }}">{{ $doc->status }}</span>
                @if($doc->verified_by)
                    <br><small>By: {{ $doc->verified_by }}</small>
                @endif
                @if($doc->verified_at)
                    <br><small>{{ $doc->verified_at->format('d-m-Y H:i') }}</small>
                @endif
                @if($doc->remarks)
                    <br><small style="color:#555;"><em>{{ $doc->remarks }}</em></small>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" style="text-align:center;color:#9ca3af;font-style:italic;">
                No compliance documents uploaded for this invoice.
            </td>
        </tr>
    @endforelse
</table>


{{-- ══════════════════════════════════════════════════════════════════
     SECTION 3 — HR CLEARANCE CHECKLIST
══════════════════════════════════════════════════════════════════ --}}
<table class="info">
    <tr>
        <th colspan="5">
            CHECK LIST — HR CLEARANCE<br>
            <small style="font-weight:400;">Statutory Compliance Status</small>
        </th>
    </tr>
    <tr>
        <td width="4%"><b>S.No.</b></td>
        <td><b>Check Points</b></td>
        <td width="10%" style="text-align:center;"><b>Status Y/N</b></td>
        <td width="22%"><b>Licence No. / Policy No. / Code No.</b></td>
        <td width="18%"><b>Date of Compliance / Valid Upto</b></td>
    </tr>

    @php $sno = 1; @endphp
    @foreach(\App\Models\InvoiceComplianceChecklist::CHECKLIST_ITEMS as $key => $label)
        @php $row = $checklistMap[$key] ?? null; @endphp
        <tr>
            <td style="text-align:center;">{{ $sno++ }}</td>
            <td>{{ $label }}</td>
            <td style="text-align:center;">
                @if(!$row)
                    <span class="cl-na">—</span>
                @elseif($row->status === 'Yes')
                    <span class="cl-yes">✅ Yes</span>
                @elseif($row->status === 'No')
                    <span class="cl-no">❌ No</span>
                @else
                    <span class="cl-na">N/A</span>
                @endif
            </td>
            <td>{{ $row?->reference_number ?? '—' }}</td>
            <td>{{ $row?->validity_date?->format('d-m-Y') ?? '—' }}</td>
        </tr>
        @if($row?->remarks)
            <tr style="background:#fffbeb;">
                <td></td>
                <td colspan="4" style="font-size:11px;color:#78350f;padding-left:16px;">
                    <em>Remarks: {{ $row->remarks }}</em>
                </td>
            </tr>
        @endif
    @endforeach

    {{-- Summary row --}}
    @php
        $total = count(\App\Models\InvoiceComplianceChecklist::CHECKLIST_ITEMS);
        $yesCount = collect($checklistMap)->where('status', 'Yes')->count();
        $noCount = collect($checklistMap)->where('status', 'No')->count();
        $naCount = collect($checklistMap)->where('status', 'NA')->count();
        $pending = $total - count($checklistMap);
    @endphp
    <tr style="background:#f0f9ff;">
        <td colspan="5" style="text-align:center;font-size:12px;padding:6px;">
            <b>Summary:</b>
            &nbsp;Total: <b>{{ $total }}</b>
            &nbsp;|&nbsp; ✅ Yes: <b style="color:#065f46;">{{ $yesCount }}</b>
            &nbsp;|&nbsp; ❌ No: <b style="color:#991b1b;">{{ $noCount }}</b>
            &nbsp;|&nbsp; N/A: <b style="color:#6b7280;">{{ $naCount }}</b>
            @if($pending > 0)
                &nbsp;|&nbsp; ⏳ Pending: <b style="color:#92400e;">{{ $pending }}</b>
            @endif
        </td>
    </tr>
</table>


{{-- ══════════════════════════════════════════════════════════════════
     SECTION 4 — VENDOR & CLIENT ADDRESS
══════════════════════════════════════════════════════════════════ --}}
@if($contract->vendor_address || $contract->client_address)
    <table class="info">
        <tr>
            <th colspan="4">ADDRESS DETAILS</th>
        </tr>
        <tr>
            <td width="15%"><b>Vendor Address</b></td>
            <td width="35%">{{ $contract->vendor_address ?? '—' }}</td>
            <td width="15%"><b>Client Address</b></td>
            <td width="35%">{{ $contract->client_address ?? '—' }}</td>
        </tr>
    </table>
@endif


{{-- ── Footer ──────────────────────────────────────────────────────── --}}
<div class="footer">
    Invoice printed on {{ now()->format('d-M-Y H:i:s A') }}
    &nbsp;|&nbsp; Generated by {{ config('app.name') }}
</div>

</body>
</html>