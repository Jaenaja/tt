<!-- resources/views/admin/reports/pdf.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
        }

        body {
            font-family: 'THSarabunNew', sans-serif;
            font-size: 16px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
        }

        .result-box {
            background: #f0f0f0;
            padding: 10px;
            margin: 10px 0;
            text-align: center;
        }

        .result-number {
            font-size: 36px;
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .summary-table th {
            background-color: #4a5568;
            color: white;
        }

        .winner-box {
            border: 2px solid #333;
            padding: 10px;
            margin: 15px 0;
            page-break-inside: avoid;
        }

        .customer-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .amount-pay {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
            text-align: right;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 14px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #ddd;
            padding: 4px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-green {
            color: #48bb78;
        }

        .text-red {
            color: #f56565;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <!-- หน้าที่ 1: สรุปผล -->
    <div class="header">
        <div class="title">สรุปผลหวย</div>
        <div>งวดวันที่: {{ $draw->draw_date->format('d/m/') . ($draw->draw_date->format('Y') + 543) }}</div>
        <div>พิมพ์วันที่: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="result-box">
        <table style="width: 100%;">
            <tr>
                <td class="text-center">
                    <div>3 ตัวบน</div>
                    <div class="result-number">{{ $draw->result_3_top }}</div>
                </td>
                <td class="text-center">
                    <div>2 ตัวบน</div>
                    <div class="result-number">{{ $draw->result_2_top }}</div>
                </td>
                <td class="text-center">
                    <div>2 ตัวล่าง</div>
                    <div class="result-number">{{ $draw->result_2_bottom }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="summary-table">
        <tr>
            <th>รายการ</th>
            <th class="text-right">จำนวนเงิน (บาท)</th>
        </tr>
        <tr>
            <td>ยอดแทงทั้งหมด</td>
            <td class="text-right">{{ number_format($totalBetAmount, 2) }}</td>
        </tr>
        <tr>
            <td>ยอดจ่ายรางวัล</td>
            <td class="text-right">{{ number_format($totalPayout, 2) }}</td>
        </tr>
        <tr style="background-color: {{ $profit >= 0 ? '#c6f6d5' : '#fed7d7' }}; font-weight: bold;">
            <td>กำไร/ขาดทุน</td>
            <td class="text-right">{{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 2) }}</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- หน้าที่ 2 เป็นต้นไป: รายการต้องจ่าย -->
    <div class="header">
        <div class="title">รายการต้องจ่ายเงิน</div>
        <div>งวดวันที่: {{ $draw->draw_date->format('d/m/') . ($draw->draw_date->format('Y') + 543) }}</div>
    </div>

    @if($winners->count() > 0)
        @foreach($winners as $winner)
            <div class="winner-box">
                <table style="width: 100%;">
                    <tr>
                        <td>
                            <div class="customer-name">👤 {{ $winner['customer_name'] }}</div>
                            <div style="font-size: 14px; color: #666;">แทงรวม: {{ number_format($winner['total_bet'], 2) }} บาท
                            </div>
                        </td>
                        <td style="width: 200px;">
                            <div style="font-size: 12px; color: #666;">ต้องจ่าย</div>
                            <div class="amount-pay">{{ number_format($winner['total_win'], 2) }}</div>
                            <div style="font-size: 12px; color: {{ $winner['net'] >= 0 ? '#48bb78' : '#f56565' }};">
                                กำไร {{ $winner['net'] >= 0 ? '+' : '' }}{{ number_format($winner['net'], 2) }}
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>เลข</th>
                            <th class="text-right">ยอดแทง</th>
                            <th class="text-right">ได้รางวัล</th>
                            <th>รายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($winner['details'] as $bet)
                            @if($bet->total_payout > 0)
                                <tr>
                                    <td style="font-weight: bold; font-size: 16px;">{{ $bet->number }}</td>
                                    <td class="text-right">{{ number_format($bet->total_amount, 2) }}</td>
                                    <td class="text-right" style="font-weight: bold;">{{ number_format($bet->total_payout, 2) }}</td>
                                    <td style="font-size: 12px;">
                                        @if($bet->is_win_top && $bet->payout_top > 0)
                                            บน {{ number_format($bet->payout_top, 2) }}
                                        @endif
                                        @if($bet->is_win_bottom && $bet->payout_bottom > 0)
                                            ล่าง {{ number_format($bet->payout_bottom, 2) }}
                                        @endif
                                        @if($bet->is_win_toad && $bet->payout_toad > 0)
                                            โต๊ด {{ number_format($bet->payout_toad, 2) }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 50px; font-size: 20px; color: #666;">
            ไม่มีผู้ถูกรางวัล
        </div>
    @endif

    <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
        --- สิ้นสุดรายงาน ---
    </div>
</body>

</html>