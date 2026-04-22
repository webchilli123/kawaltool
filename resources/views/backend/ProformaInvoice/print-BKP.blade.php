<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proforma Invoice #{{ $record->pi_no }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 20px;
        }
        .header {
            border-bottom: 2px solid #000;
            margin-bottom: 12px;
            padding-bottom: 10px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }
        .header-main {
            display: flex;
            align-items: center;
        }
        .header-left {
            width: 20%;
            text-align: center;
        }
        .header-left img {
            max-height: 80px;
        }
        .header-right-full {
            width: 80%;
            text-align: center;
        }
         .header-right-full .company-name {
            font-size: 30px;
            color: #E9031E;
            font-weight: 900;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
            .header-right-full .company-deal {
            font-size: 15px;
            font-style: italic;
            margin-top: 2px;
        }

        .header-right-full .company-address {
            font-size: 14px;
            margin-top: 4px;
            /* font-weight: 900; */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
        }
        th {
            background: #f1f1f1;
        }
        .no-border td {
            border: none;
        }
        .text-right {
            text-align: right;
        }

    .footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    border-top: 2px solid #000;
    padding: 8px 10px;
    font-size: 11px;
    font-weight: 700;
    color: #000;
    box-sizing: border-box;
}

.footer-top {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.footer-left,
.footer-right {
    width: 45%;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.footer-right {
    text-align: right;
}

.footer-center {
    text-align: center;
    margin-top: 6px;
}
        @media print {
            .no-print { display: none; }
            body {
        margin-bottom: 140px; /* space for footer */
    }
        }
    </style>
</head>

<body>

<div class="no-print" style="text-align:right;">
    <button onclick="window.print()">🖨 Print</button>
</div>

<div class="header">
    <div class="header-top">
        <div><b>GST:</b> {{ $company->gst_number }}</div>
        <div><b>📞</b> {{ $company->phone_number }}</div>
    </div>

    <div class="header-main">
        <div class="header-left">
            {{-- <img src="{{ url($company->logo_for_pdf) }}"> --}}
            <img src="{{ asset('files/Company/logo-dark.png') }}">
        </div>
        <div class="header-right-full">
              <div class="company-name">
                    {{ strtoupper($company->name) }}
                </div>
              <div class="company-address">
                    <strong>Dealer:</strong> VE COMMERCIAL. LTD
                </div>

                <div class="company-deal">
                    (A VOLVO GROUP AND EICHER MOTORS JOINT VENTURE)
                </div>
        </div>
    </div>
</div>

<table class="no-border">
    <tr>
        <td><b>Date:</b> {{ \Carbon\Carbon::parse($record->date)->format('d-m-Y') }}</td>
        <td style="text-align:end"><b>PI No:</b> {{ $record->pi_no }}</td>
    </tr>
<tr>
    <td colspan="8" style="font-weight: 700; color: #000;">
        To,<br>

        {{ optional($record->complaint->party)->name }}<br>

        @if(optional($record->complaint->party)->address)
            {{ optional($record->complaint->party)->address }}<br>
        @endif

        @if(optional($record->complaint->party)->mobile)
            {{ optional($record->complaint->party)->mobile }}<br>
        @endif

        @if(optional($record->complaint->party)->gstin)
            GST No.:- {{ optional($record->complaint->party)->gstin }}
        @endif
    </td>
</tr>


</table>

@php
    $subTotal = 0;
    $cgstTotal = 0;
    $sgstTotal = 0;
    $igstTotal = 0;
    $isSameState = optional($record->complaint->party)->state_id == optional($company)->state_id;
@endphp

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Rate</th>
            <th>GST</th>
            <th>Amount</th>
        </tr>
    </thead>

    <tbody>
    @foreach ($record->proformaInvoiceItem as $k => $item)
        @php
            $subTotal += $item->amount;
            $cgstTotal += $item->cgst;
            $sgstTotal += $item->sgst;
            $igstTotal += $item->igst;
        @endphp
        <tr>
            <td>{{ $k + 1 }}</td>
            <td>{{ $item->product->item->getDisplayName() }}</td>
            <td>{{ $item->qty }}</td>
            <td>{{ number_format($item->rate, 2) }}</td>
            <td>
                @if($isSameState)
                    CGST: {{ number_format($item->cgst, 2) }}<br>
                    SGST: {{ number_format($item->sgst, 2) }}
                @else
                    IGST: {{ number_format($item->igst, 2) }}
                @endif
            </td>
            <td class="text-right">{{ number_format($item->amount, 2) }}</td>
        </tr>
    @endforeach

    @if ($isSameState)
        <tr><td colspan="5" class="text-right">CGST Total</td><td class="text-right">{{ number_format($cgstTotal, 2) }}</td></tr>
        <tr><td colspan="5" class="text-right">SGST Total</td><td class="text-right">{{ number_format($sgstTotal, 2) }}</td></tr>
    @else
        <tr><td colspan="5" class="text-right">IGST Total</td><td class="text-right">{{ number_format($igstTotal, 2) }}</td></tr>
    @endif

    <tr>
        <td colspan="5" class="text-right"><b>Total GST</b></td>
        <td class="text-right"><b>{{ number_format($cgstTotal + $sgstTotal + $igstTotal, 2) }}</b></td>
    </tr>

    @if($record->freight)
        <tr><td colspan="5" class="text-right">Freight</td><td class="text-right">{{ number_format($record->freight, 2) }}</td></tr>
    @endif

    @if($record->discount)
        <tr><td colspan="5" class="text-right">Discount</td><td class="text-right">{{ number_format($record->discount, 2) }}</td></tr>
    @endif

    <tr>
        <td colspan="5" class="text-right"><b>Grand Total</b></td>
        <td class="text-right">
            <b>{{ number_format(
                $subTotal + $cgstTotal + $sgstTotal + $igstTotal + ($record->freight ?? 0) - ($record->discount ?? 0),
            2) }}</b>
        </td>
    </tr>
    </tbody>
</table>

@if($company->bank_name)
<div style="margin-top:15px;font-size:12px;">
    RTGS Detail as per below:<br><br>
    <b>Account Holder Name: {{ $company->account_name }} <br>
        Bank Name: {{ $company->bank_name }} <br>
        A/C No: {{ $company->account_number }}<br>
        RTGS Code: {{ $company->ifsc_code }}</b>
    </div>
    @endif
    <div style="margin-top:15px;font-size:12px;">
    For Sunrise Power Technologies<br><br>
    <b>Authorized Signatory <br>
        Ludhiana (Punjab) <br>
        Mobile: 98554-70213, 98140-00213  <br>
        Email: info@sunrisepower.in, Sunrisegenset@gmail.com</b>
    </div>

   <div class="footer">

    <div class="footer-top">
        <!-- LEFT -->
        <div class="footer-left">
            Marketing Office-cum-Show Room:- SCO 8 & 9,<br>
            Dholewal Flyover Market, G.T Road,<br>
            Ludhiana – 141 003 (Pb.) INDIA
        </div>

        <!-- RIGHT -->
        <div class="footer-right">
            Works: 1228, Mittal Kanda Street,<br>
            Near G.S Auto, Dhandari Kalan,<br>
            Indl Area C, G.T Road,<br>
            Ludhiana – 141 014 (Pb.) INDIA<br>
            mail: info@sunrisepower.in,<br>
            sunrisegenset@gmail.com
        </div>
    </div>

    <div class="footer-center">
        Mobile: +91-8195086555, 9814000213 |
        Tel: +91-161-4067213 |
        Web: www.sunrisepower.in
    </div>

</div>

</body>
</html>
