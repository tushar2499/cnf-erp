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
            font-size: 11px;
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
            padding: 10mm 12mm;
        }

        .top-tags {
            text-align: right;
            font-size: 11px;
            line-height: 1.5;
            margin-bottom: 4px;
        }

        .title-block {
            text-align: center;
            margin-bottom: 8px;
        }

        .title-block .govt {
            font-size: 13px;
            font-weight: 700;
        }

        .title-block .doc-title {
            font-size: 16px;
            font-weight: 700;
            text-decoration: underline;
            margin-top: 3px;
        }

        .title-block .rule-ref {
            font-size: 10px;
            margin-top: 2px;
        }

        table.meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.meta td {
            border: 1px solid #000;
            padding: 4px 7px;
            font-size: 11px;
            vertical-align: top;
            line-height: 1.6;
        }

        table.meta td.lbl {
            font-weight: 700;
            width: 24%;
            white-space: nowrap;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 8px;
        }

        table.items th {
            border: 1px solid #000;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.3;
        }

        table.items th.num {
            font-size: 9px;
            font-weight: 400;
        }

        table.items td {
            border: 1px solid #000;
            font-size: 10px;
            padding: 4px 5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        table.items td.r {
            text-align: right;
        }

        table.items td.c {
            text-align: center;
        }

        table.items tfoot td {
            font-weight: 700;
            background: #f0f0f0;
        }

        .sig-wrap {
            width: 100%;
            margin-top: 30px;
        }

        .sig-wrap td {
            font-size: 11px;
            vertical-align: bottom;
            padding: 2px 0;
        }

        .footnote {
            margin-top: 14px;
            font-size: 10px;
            font-style: italic;
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
    @endphp

    <div class="page">

        <div class="top-tags">
            প্রথম কপি<br>
            মূসক-৬.৩
        </div>

        <div class="title-block">
            <div class="govt">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার, জাতীয় রাজস্ব বোর্ড, ঢাকা।</div>
            <div class="doc-title">কর চালানপত্র</div>
            <div class="rule-ref">[বিধি ৪০ এর উপ-বিধি (১) এর দফা (গ) ও (চ) দ্রষ্টব্য]</div>
        </div>

        <table class="meta">
            <tr>
                <td class="lbl">নিবন্ধিত ব্যক্তির নাম</td>
                <td colspan="3">{{ $companyName }}</td>
            </tr>
            <tr>
                <td class="lbl">নিবন্ধিত ব্যক্তির বিআইএন</td>
                <td colspan="3">{{ $companyBin }}</td>
            </tr>
            <tr>
                <td class="lbl">চালানপত্রের ইস্যুর ঠিকানা</td>
                <td colspan="3">{{ $companyAddress }}</td>
            </tr>
            <tr>
                <td class="lbl">ক্রেতার নাম</td>
                <td style="width:26%">{{ $customerBill->customer_name }}</td>
                <td class="lbl">চালানপত্রের নম্বর</td>
                <td style="width:26%"></td>
            </tr>
            <tr>
                <td class="lbl">ক্রেতার বিআইএন</td>
                <td>{{ $customer?->tin_bin_nid ?? '' }}</td>
                <td class="lbl">ইস্যুর তারিখ</td>
                <td>{{ $customerBill->bill_date?->format('d.m.Y') }}</td>
            </tr>
            <tr>
                <td class="lbl">ক্রেতার ঠিকানা</td>
                <td>{!! nl2br(e($customerBill->customer_address)) !!}</td>
                <td class="lbl">ইস্যুর সময়</td>
                <td>{{ now()->format('h:i A') }}</td>
            </tr>
            <tr>
                <td class="lbl">সরবরাহের গন্তব্যস্থান</td>
                <td>{{ $destination }}</td>
                <td class="lbl">যানবাহনের প্রকৃতি ও নং</td>
                <td>{{ $vehicleDesc }}</td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:5%">ক্রমিক</th>
                    <th style="width:19%">পণ্য বা সেবার বর্ণনা (প্রযোজ্য ক্ষেত্রে ব্র্যান্ড নামসহ)</th>
                    <th style="width:8%">সরবরাহের একক</th>
                    <th style="width:7%">পরিমাণ</th>
                    <th style="width:10%">মোট মূল্য (টাকায়)</th>
                    <th style="width:8%">সম্পূরক শুল্কের হার</th>
                    <th style="width:9%">সম্পূরক শুল্কের পরিমাণ</th>
                    <th style="width:9%">মূসক সংযোজন কর হার/সুনির্দিষ্ট কর</th>
                    <th style="width:10%">মূসক সংযোজন কর নিরুপণ করা পরিমাণ</th>
                    <th style="width:15%">সকল প্রকার শুল্ক ও করসহ মূল্য</th>
                </tr>
                <tr>
                    <th class="num">(১)</th>
                    <th class="num">(২)</th>
                    <th class="num">(৩)</th>
                    <th class="num">(৪)</th>
                    <th class="num">(৫)</th>
                    <th class="num">(৬)</th>
                    <th class="num">(৭)</th>
                    <th class="num">(৮)</th>
                    <th class="num">(৯)</th>
                    <th class="num">(১০)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="c">১</td>
                    <td>Vat on Transport<br>Bill No: {{ $customerBill->bill_no }} DT:{{ $customerBill->bill_date?->format('d.m.Y') }}</td>
                    <td></td>
                    <td></td>
                    <td class="r">{{ number_format($preTaxValue, 2) }}</td>
                    <td></td>
                    <td></td>
                    <td class="c">{{ number_format($vatPct, 0) }}%</td>
                    <td class="r">{{ number_format($vatAmt, 2) }}</td>
                    <td class="r">{{ number_format($totalInclVat, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right">সর্বমোট</td>
                    <td class="r">{{ number_format($preTaxValue, 2) }}</td>
                    <td></td>
                    <td></td>
                    <td class="c">{{ number_format($vatPct, 0) }}%</td>
                    <td class="r">{{ number_format($vatAmt, 2) }}</td>
                    <td class="r">{{ number_format($totalInclVat, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="sig-wrap">
            <tr>
                <td style="width:55%">
                    প্রতিষ্ঠানের কর্তৃপক্ষের দায়িত্বপ্রাপ্ত ব্যক্তি: Pinto Ranjan Das.<br>
                    পদবী: Deputy Manager
                </td>
                <td style="width:45%; text-align:right">
                    সীল:
                </td>
            </tr>
        </table>

        <div class="footnote">* সকল প্রকার কর ব্যতীত মূল্য।</div>

    </div>

</body>

</html>
