<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mushak-6.3 — {{ $customerBill->bill_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', Arial, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
        }

        .no-print {
            margin: 10px;
            display: flex;
            gap: 8px;
        }

        .no-print button {
            padding: 6px 18px;
            font-size: 13px;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #333;
        }

        .btn-print {
            background: #1a6b60;
            color: #fff;
            border-color: #1a6b60;
        }

        .btn-close {
            background: #6c757d;
            color: #fff;
            border-color: #6c757d;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 14mm 14mm;
        }

        .top-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .top-row td {
            vertical-align: top;
        }

        .govt-block {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.6;
        }

        .mushak-box {
            border: 1px solid #000;
            font-size: 15px;
            font-weight: 700;
            padding: 6px 10px;
            display: inline-block;
            white-space: nowrap;
        }

        .title-block {
            text-align: center;
            margin: 10px 0 16px;
        }

        .title-block .doc-title {
            font-size: 18px;
            font-weight: 700;
            text-decoration: underline;
        }

        .title-block .rule-ref {
            font-size: 11px;
            margin-top: 3px;
        }

        table.meta {
            width: 100%;
            border-collapse: collapse;
        }

        table.meta td {
            padding: 3px 4px;
            font-size: 12px;
            vertical-align: top;
            line-height: 1.7;
        }

        table.meta td.lbl {
            white-space: nowrap;
            width: 1%;
        }

        table.meta td.colon {
            width: 1%;
            padding-right: 6px;
        }

        table.meta tr.spacer td {
            padding: 5px 0;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 16px;
        }

        table.items th,
        table.items td {
            border: 1px solid #000;
        }

        table.items th {
            font-size: 10.5px;
            font-weight: 700;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.3;
        }

        table.items td {
            font-size: 11px;
            padding: 5px 6px;
            vertical-align: top;
            word-wrap: break-word;
            height: 26px;
        }

        table.items td.r {
            text-align: right;
        }

        table.items td.c {
            text-align: center;
        }

        table.items tfoot td {
            font-weight: 700;
        }

        table.items td.blank-fill {
            background: #fbe0cf;
        }

        .sig-wrap {
            width: 100%;
            margin-top: 40px;
        }

        .sig-wrap td {
            font-size: 12px;
            line-height: 2.2;
            vertical-align: top;
            padding: 1px 4px;
        }

        .footnote {
            margin-top: 18px;
            font-size: 10.5px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }

            .page {
                width: 100%;
                min-height: 0;
                margin: 0;
                padding: 4mm 10mm;
            }

            @page {
                size: A4 portrait;
                margin: 6mm 10mm;
            }
        }
    </style>
</head>

<body>

    {{-- Print / Close buttons (hidden on print) --}}
    <div class="no-print">
        <button class="btn-print" onclick="window.print()"><i>&#128424;</i> Print</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    @php
        // Static, NBR-registration details for the issuing company — no such
        // fields exist on the Company model yet, so kept fixed here.
        $companyName = 'NAS Freights and Logistics Ltd.';
        $companyBin = '007485303-0203';
        $companyAddress = '339/B (8th Floor), Impulse, Hospital Road, Tejgaon I/A, Dhaka-1208';

        $firstItem = $customerBill->items->first();
        $firstBooking = $firstItem?->booking;

        $subTotal = $customerBill->items->sum('line_amount');
        $totalDem = $customerBill->items->sum('demurrage_amount');
        $preTaxValue = $subTotal + $totalDem;
        $vatPct = (float) ($customerBill->vat_percent ?? 0);
        $vatAmt = (float) ($customerBill->vat_amount ?? 0);
        $totalInclVat = $preTaxValue + $vatAmt;

        $vehicle = $firstItem
            ? \App\Models\NasFreights\NasFreightsVehicle::where('vehicle_number', $firstItem->item_code)->first()
            : null;
        $vehicleDesc = trim(($vehicle?->vehicle_type ?? '') . ' ' . ($firstItem?->item_code ?? ''));
        $destination = $firstItem?->location ?? '';

        // Real NBR forms pad the item grid with blank ruled rows so the sheet
        // fills a full page even for a single-line invoice.
        $blankRowCount = 8;
    @endphp

    <div class="page">

        <table class="top-row">
            <tr>
                <td style="width:15%"></td>
                <td style="width:70%" class="govt-block">
                    গণপ্রজাতন্ত্রী বাংলাদেশ সরকার<br>
                    জাতীয় রাজস্ব বোর্ড
                </td>
                <td style="width:15%; text-align:right">
                    <span class="mushak-box">মূসক – ৬.৩</span>
                </td>
            </tr>
        </table>

        <div class="title-block">
            <div class="doc-title">কর চালানপত্র</div>
            <div class="rule-ref">[বিধি ৪০ এর উপ-বিধি (১) এর দফা (গ) ও (চ) দ্রষ্টব্য]</div>
        </div>

        <table class="meta">
            <tr>
                <td class="lbl">নিবন্ধিত ব্যক্তির নাম</td>
                <td class="colon">:</td>
                <td>{{ $companyName }}</td>
            </tr>
            <tr>
                <td class="lbl">নিবন্ধিত ব্যক্তির বিআইএন</td>
                <td class="colon">:</td>
                <td>{{ $companyBin }}</td>
            </tr>
            <tr>
                <td class="lbl">চালানপত্র ইস্যুর ঠিকানা</td>
                <td class="colon">:</td>
                <td>{{ $companyAddress }}</td>
            </tr>
            <tr class="spacer">
                <td colspan="3"></td>
            </tr>
        </table>

        <table class="meta">
            <tr>
                <td class="lbl" style="width:14%">ক্রেতার নাম</td>
                <td class="colon">:</td>
                <td style="width:36%">{{ $customerBill->customer_name }}</td>
                <td class="lbl" style="width:14%">চালানপত্র নম্বর</td>
                <td class="colon">:</td>
                <td></td>
            </tr>
            <tr>
                <td class="lbl">ক্রেতার বিআইএন</td>
                <td class="colon">:</td>
                <td>{{ $customer?->tin_bin_nid ?? '' }}</td>
                <td class="lbl">ইস্যুর তারিখ</td>
                <td class="colon">:</td>
                <td>{{ $customerBill->bill_date?->format('d-M-y') }}</td>
            </tr>
            <tr>
                <td class="lbl">ক্রেতার ঠিকানা</td>
                <td class="colon">:</td>
                <td>{!! nl2br(e($customerBill->customer_address)) !!}</td>
                <td class="lbl">ইস্যুর সময়</td>
                <td class="colon">:</td>
                <td>{{ now()->format('h:i A') }}</td>
            </tr>
            <tr>
                <td class="lbl">সরবরাহের গন্তব্যস্থল</td>
                <td class="colon">:</td>
                <td>{{ $destination }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="lbl">যানবাহনের প্রকৃতি ও নাম্বার</td>
                <td class="colon">:</td>
                <td>{{ $vehicleDesc }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:6%">ক্রমিক নং</th>
                    <th style="width:19%">পণ্য বা সেবার বর্ণনা (প্রযোজ্য ক্ষেত্রে ব্র্যান্ড নামসহ)</th>
                    <th style="width:8%">সরবরাহের একক</th>
                    <th style="width:6%">পরিমাণ</th>
                    <th style="width:9%">একক মূল্য<sup>১</sup> (টাকায়)</th>
                    <th style="width:9%">মোট মূল্য (টাকায়)</th>
                    <th style="width:7%">সম্পূরক শুল্কের হার</th>
                    <th style="width:9%">সম্পূরক শুল্কের পরিমান (টাকায়)</th>
                    <th style="width:8%">মূল্য সংযোজন করের হার/সুনির্দিষ্ট কর</th>
                    <th style="width:9%">মূল্য সংযোজন কর/সুনির্দিষ্ট করের পরিমান (টাকায়)</th>
                    <th style="width:10%">সকল প্রকার শুল্ক ও করসহ মূল্য</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="c">১</td>
                    <td>Vat on Transport<br>Bill No: {{ $customerBill->bill_no }} DT:{{ $customerBill->bill_date?->format('d.m.Y') }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="r">Tk. {{ number_format($preTaxValue, 0) }}</td>
                    <td></td>
                    <td></td>
                    <td class="c">{{ number_format($vatPct, 0) }}%</td>
                    <td class="r">Tk. {{ number_format($vatAmt, 0) }}</td>
                    <td class="r">Tk. {{ number_format($totalInclVat, 0) }}</td>
                </tr>
                @for ($i = 0; $i < $blankRowCount; $i++)
                    <tr>
                        <td></td>
                        <td class="blank-fill"></td>
                        <td class="blank-fill"></td>
                        <td class="blank-fill"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:center">সর্বমোট=</td>
                    <td class="r">Tk. {{ number_format($preTaxValue, 0) }}</td>
                    <td></td>
                    <td class="r">Tk. -</td>
                    <td></td>
                    <td class="r">Tk. {{ number_format($vatAmt, 0) }}</td>
                    <td class="r">Tk. {{ number_format($totalInclVat, 0) }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="sig-wrap">
            <tr>
                <td style="width:45%">প্রতিষ্ঠান কর্তৃপক্ষের দায়িত্বপ্রাপ্ত ব্যক্তির নাম</td>
                <td style="width:2%">:</td>
                <td style="width:53%">Pinto Ranjan Das</td>
            </tr>
            <tr>
                <td>পদবি</td>
                <td>:</td>
                <td>Deputy Manager</td>
            </tr>
            <tr>
                <td>স্বাক্ষর</td>
                <td>:</td>
                <td></td>
            </tr>
            <tr>
                <td>সিল</td>
                <td>:</td>
                <td></td>
            </tr>
        </table>

        <div class="footnote"><sup>১</sup>সকল প্রকার কর ব্যতীত মূল্য।</div>

    </div>

</body>

</html>
