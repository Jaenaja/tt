<!-- resources/views/admin/draw-results.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลรางวัล - งวด {{ thai_date_full($draw->draw_date) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.draws') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับ</a>
            <a href="{{ route('admin.reports.summary', $draw->id) }}"
                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg">
                📊 ดูสรุปและพิมพ์ PDF
            </a>
        </div>

        <!-- ผลหวย -->
        <div class="bg-gradient-to-r from-yellow-400 to-orange-400 rounded-lg shadow-lg p-6 mb-6 text-gray-900">
            <h1 class="text-3xl font-bold mb-4">🏆 ผลรางวัล</h1>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="bg-white bg-opacity-90 rounded-lg p-4">
                    <p class="text-sm font-semibold text-gray-700">3 ตัวบน</p>
                    <p class="text-5xl font-bold text-purple-600">{{ $draw->result_3_top }}</p>
                </div>
                <div class="bg-white bg-opacity-90 rounded-lg p-4">
                    <p class="text-sm font-semibold text-gray-700">2 ตัวบน</p>
                    <p class="text-5xl font-bold text-blue-600">{{ $draw->result_2_top }}</p>
                </div>
                <div class="bg-white bg-opacity-90 rounded-lg p-4">
                    <p class="text-sm font-semibold text-gray-700">2 ตัวล่าง</p>
                    <p class="text-5xl font-bold text-green-600">{{ $draw->result_2_bottom }}</p>
                </div>
            </div>
            <p class="text-center mt-4 text-sm">
                งวดวันที่:
                <strong>{{ thai_date_full($draw->draw_date) }}</strong> |
                ประกาศโดย: <strong>{{ $draw->announcedBy->name }}</strong> |
                เมื่อ: {{ $draw->announced_at->format('d/m/Y H:i') }}
            </p>
        </div>

        <!-- สรุปยอด -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-sm text-gray-600">จำนวนรายการ</p>
                <p class="text-3xl font-bold text-blue-600">{{ number_format($summary['total_bets']) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-sm text-gray-600">ยอดแทงรวม</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($summary['total_amount'], 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-sm text-gray-600">ยอดจ่ายรางวัล</p>
                <p class="text-3xl font-bold text-orange-600">{{ number_format($summary['total_payout'], 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-sm text-gray-600">กำไร/ขาดทุน</p>
                <p class="text-3xl font-bold {{ $summary['total_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $summary['total_profit'] >= 0 ? '+' : '' }}{{ number_format($summary['total_profit'], 2) }}
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-sm text-gray-600">จำนวนผู้ถูกรางวัล</p>
                <p class="text-3xl font-bold text-purple-600">{{ number_format($summary['winners_count']) }}</p>
            </div>
        </div>

        <!-- รายการเดิมพันทั้งหมด -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📋 รายการเดิมพันทั้งหมด</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">ลูกค้า</th>
                            <th class="px-3 py-2 text-center">เลข</th>
                            <th class="px-3 py-2 text-right">บน</th>
                            <th class="px-3 py-2 text-right">ล่าง</th>
                            <th class="px-3 py-2 text-right">โต๊ด</th>
                            <th class="px-3 py-2 text-right">แทงรวม</th>
                            <th class="px-3 py-2 text-right">ได้รางวัล</th>
                            <th class="px-3 py-2 text-right">กำไร/ขาดทุน</th>
                            <th class="px-3 py-2 text-center">สถานะ</th>
                            <th class="px-3 py-2 text-left">สร้างโดย</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($draw->bets->groupBy('customer_name') as $customerName => $customerBets)
                            @php
                                $customerTotal = $customerBets->sum('total_amount');
                                $customerPayout = $customerBets->sum('total_payout');
                                $customerProfit = $customerPayout - $customerTotal;
                            @endphp
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="5" class="px-3 py-2">👤 {{ $customerName }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($customerTotal, 2) }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($customerPayout, 2) }}</td>
                                <td
                                    class="px-3 py-2 text-right {{ $customerProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $customerProfit >= 0 ? '+' : '' }}{{ number_format($customerProfit, 2) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                            @foreach($customerBets as $bet)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 pl-8 text-gray-400">└─</td>
                                    <td class="px-3 py-2 text-center">
                                        <span
                                            class="font-bold text-lg {{ strlen($bet->number) === 2 ? 'text-blue-600' : 'text-purple-600' }}">
                                            {{ $bet->number }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{ $bet->amount_top > 0 ? number_format($bet->amount_top, 2) : '-' }}
                                        @if($bet->is_win_top)
                                            <br><span class="text-green-600 text-xs">✓
                                                {{ number_format($bet->payout_top, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{ $bet->amount_bottom > 0 ? number_format($bet->amount_bottom, 2) : '-' }}
                                        @if($bet->is_win_bottom)
                                            <br><span class="text-green-600 text-xs">✓
                                                {{ number_format($bet->payout_bottom, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{ $bet->amount_toad > 0 ? number_format($bet->amount_toad, 2) : '-' }}
                                        @if($bet->is_win_toad)
                                            <br><span class="text-green-600 text-xs">✓
                                                {{ number_format($bet->payout_toad, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right">{{ number_format($bet->total_amount, 2) }}</td>
                                    <td
                                        class="px-3 py-2 text-right font-semibold {{ $bet->total_payout > 0 ? 'text-green-600' : '' }}">
                                        {{ $bet->total_payout > 0 ? number_format($bet->total_payout, 2) : '-' }}
                                    </td>
                                    <td
                                        class="px-3 py-2 text-right {{ $bet->net_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $bet->net_profit >= 0 ? '+' : '' }}{{ number_format($bet->net_profit, 2) }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad)
                                            <span
                                                class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">ถูกรางวัล</span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">ไม่ถูก</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-600">
                                        {{ $bet->creator->name }}<br>
                                        {{ $bet->created_at->format('d/m/y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>