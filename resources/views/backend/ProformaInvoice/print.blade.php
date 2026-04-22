<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Proforma Invoice #{{ $record->pi_no }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 20px;
        }

        /* ================= HEADER ================= */

        /* .header {
            margin-bottom: 10px;
        }

        .top-strip {
            display: flex;
            height: 22px;
            margin-bottom: 5px;
        }

        .gst-box {
            background: #c0392b;
            color: #fff;
            font-size: 12px;
            padding: 2px 10px;
            display: flex;
            align-items: center;
            font-weight: bold;
        }

        .top-grey {
            flex: 1;
            background: #8e8e8e;
        }

        .company-name-line {
            text-align: center;
            margin-top: 5px;
        }

        .sunrise {
            color: #E9031E;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 2px;
        }

        .international {
            color: #555;
            font-size: 20px;
            font-weight: 700;
            margin-left: 8px;
        }

        .heading-line {
            width: 60%;
            margin: 5px auto;
            border-bottom: 2px solid #999;
        }

        .description {
            text-align: center;
            font-size: 12px;
            color: #444;
            line-height: 1.4;
            padding: 0 40px;
        } */

        /* new css Header */

        .header {
            width: 100%;
            font-family: Arial, sans-serif;
        }

        /* TOP STRIP */
        .top-strip {
            display: flex;
            width: 100%;
            height: 30px;
        }

        .left-red {
            width: 30%;
            background: #c00000;
            color: #fff;
            font-weight: bold;
            font-size: 12px;
            display: flex;
            align-items: center;
            padding-left: 10px;
        }

        .right-grey {
            width: 70%;
            background: #d9d9d9;
        }

        /* MIDDLE SECTION */
        .middle-section {
            display: flex;
            width: 100%;
            margin-top: 5px;
        }

        /* LOGO */
        .logo-box {
            width: 30%;
            text-align: center;
        }

        .logo-box img {
            max-width: 100%;
            max-height: 80px;
        }

        /* COMPANY DETAILS */
        .company-details {
            width: 70%;
        }

        /* NAME LINE */
        /* .company-name-line {
    font-size: 22px;
    font-weight: bold;
}

.sunrise {
    color: #c00000;
}

.international {
    color: #000;
    margin-left: 5px;
} */

        .company-name-line {
            display: flex;
            align-items: flex-end;
        }

        /* SUNRISE */
        .sunrise {
            font-size: 28px;
            font-weight: 700;
            color: #c00000;
            line-height: 1;
            margin-right: 10px;
        }

        /* INTERNATIONAL */
        .international {
            flex: 1;
            /* TAKE FULL RIGHT SPACE */
            font-size: 16px;
            /* SAME SIZE */
            font-weight: 700;
            color: #000;
            line-height: 1;
            position: relative;
        }

        /* LINE FROM TEXT START TO RIGHT END */
        .international::before {
            content: "";
            position: absolute;
            top: -6px;
            left: 0;
            /* START FROM TEXT */
            right: 0;
            /* GO TILL RIGHT END */
            height: 2px;
            background: #000;
        }

        /* HEADING LINE */
        .heading-line {
            height: 3px;
            background: #c00000;
            margin: 5px 0;
        }

        /* DESCRIPTION */
        .description {
            font-size: 12px;
            line-height: 1.4;
        }

        /* BOTTOM LINE */
        .bottom-grey-line {
            height: 8px;
            background: #d9d9d9;
            margin-top: 5px;
        }

        /* ================= TABLE ================= */

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
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

        /* ================= FOOTER ================= */

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            border-top: 1.5px solid #999;
            padding: 6px 10px;
            font-size: 11px;
            color: #333;
            background: #fff;
        }

        .footer-center {
            text-align: center;
            margin-top: 5px;
        }

        /* PRINT FIX */
        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
                padding: 20px;
                padding-bottom: 120px;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="text-align:right;">
        <button onclick="window.print()">🖨 Print</button>
    </div>

    <!-- ================= HEADER ================= -->
    {{-- <div class="header">

    <div class="top-strip">
        <div class="gst-box">
            GSTIN : {{ $company->gst_number }}
        </div>
        <div class="top-grey"></div>
    </div>

    <div class="company-name-line">
        <span class="sunrise">SUNRISE</span>
        <span class="international">INTERNATIONAL (REGD.)</span>
    </div>

    <div class="heading-line"></div>

    <div class="description">
        <strong>SPECIALISTS IN :</strong> REPAIR, SPARE & SALE ALL KINDS OF LEYLAND, CUMMINS,
        CATER PILLER, MWM, KIRLOSKER DIESEL ENGINE & GENERATOR SETS,<br>
        <strong>MANUFACTURERS OF</strong> : 5 TO 1250 K.V.A. GENERATOR SETS
    </div>

</div> --}}

    <div class="header">

        <!-- TOP STRIP -->
        <div class="top-strip">
            <div class="left-red">
                GSTIN : {{ $company->gst_number }}
            </div>
            <div class="right-grey"></div>
        </div>

        <!-- SECOND ROW -->
        <div class="middle-section">

            <!-- LEFT LOGO (30%) -->
            <div class="logo-box">
                <img src="{{ asset('img/eicherLogo.png') }}" alt="Logo">
            </div>

            <!-- RIGHT CONTENT (70%) -->
            <div class="company-details">
                <div class="company-name-line">
                    <span class="sunrise">SUNRISE</span>
                    <span class="international">INTERNATIONAL (REGD.)</span>
                </div>

                <div class="heading-line"></div>

                <div class="description">
                    <strong>SPECIALISTS IN :</strong> REPAIR, SPARE & SALE ALL KINDS OF LEYLAND, CUMMINS,
                    CATER PILLER, MWM, KIRLOSKER DIESEL ENGINE & GENERATOR SETS,<br>
                    <strong>MANUFACTURERS OF</strong> : 5 TO 1250 K.V.A. GENERATOR SETS
                </div>
            </div>

        </div>

        <!-- BOTTOM LINE -->
        <div class="bottom-grey-line"></div>

    </div>

    <!-- ================= PARTY DETAILS ================= -->

    <table class="no-border">
        <tr>
            <td><b>Date:</b> {{ \Carbon\Carbon::parse($record->date)->format('d-m-Y') }}</td>
            <td style="text-align:end"><b>PI No:</b> {{ $record->pi_no }}</td>
        </tr>

        <tr>
            <td colspan="8" style="font-weight: 700;">
                To,<br>
                {{ optional($record->complaint->party)->name }}<br>

                @if (optional($record->complaint->party)->address)
                    {{ optional($record->complaint->party)->address }}<br>
                @endif

                @if (optional($record->complaint->party)->mobile)
                    {{ optional($record->complaint->party)->mobile }}<br>
                @endif

                @if (optional($record->complaint->party)->gstin)
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

    {{-- <h6 class="mb-1">Complaint Items</h6>
    <ul class="list-group">
        @foreach ($record->complaint->complaintItems as $item)
            <li class="list-group-item">
                <strong>{{ $item->product->sku ?? '-' }}</strong>
            </li>
        @endforeach
    </ul> --}}

    <h6 style="margin-bottom: 5px;">Complaint Item</h6>

    <ul style="list-style: none; padding: 0; margin: 0; font-size: 12px;">
        @foreach ($record->complaint->complaintItems as $item)
            <li
                style="
            padding: 6px 8px;
            border-bottom: 1px dashed #999;
            display: flex;
            justify-content: space-between;
        ">
                <span>
                    {{ $item->product->getDisplayName() ?? '-' }}
                </span>
            </li>
        @endforeach
    </ul>

    <!-- ================= ITEMS ================= -->

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
                    <td>{{ $item->product->getDisplayName() }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ number_format($item->rate, 2) }}</td>
                    <td>
                        @if ($isSameState)
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
                <tr>
                    <td colspan="5" class="text-right">CGST Total</td>
                    <td class="text-right">{{ number_format($cgstTotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right">SGST Total</td>
                    <td class="text-right">{{ number_format($sgstTotal, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="5" class="text-right">IGST Total</td>
                    <td class="text-right">{{ number_format($igstTotal, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="5" class="text-right"><b>Total GST</b></td>
                <td class="text-right"><b>{{ number_format($cgstTotal + $sgstTotal + $igstTotal, 2) }}</b></td>
            </tr>
            @if ($record->freight)
                <tr>
                    <td colspan="5" class="text-right">Freight</td>
                    <td class="text-right">{{ number_format($record->freight, 2) }}</td>
                </tr>
            @endif

            @if ($record->discount)
                <tr>
                    <td colspan="5" class="text-right">Discount</td>
                    <td class="text-right">{{ number_format($record->discount, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="5" class="text-right"><b>Grand Total</b></td>
                <td class="text-right">
                    <b>{{ number_format(
                        $subTotal + $cgstTotal + $sgstTotal + $igstTotal + ($record->freight ?? 0) - ($record->discount ?? 0),
                        2,
                    ) }}</b>
                </td>
            </tr>

        </tbody>
    </table>

    <!-- ================= SIGN ================= -->

    <div style="margin-top:15px;font-size:12px;">
        For Sunrise Power Technologies<br><br>
        <b>Authorized Signatory</b>
    </div>

    <!-- ================= FOOTER ================= -->

    <div class="footer">
        <div class="footer-center">
            1228, Mittal Kanda Street, Near G.S Auto, Dhandari Kalan, Ludhiana – 141010. <br>
            H.O. : SCO 8-9, Dholewal Flyover Market, G.T Road, Ludhiana – 141003.<br>
            Ph:. : 0161-4067213, Telefax: 0161-2535213 (M) 98140-00213 <br>
            E-mail : sunrisegenset@gmail.com, sunrisegenset@yahoo.com
        </div>
    </div>

</body>

</html>
