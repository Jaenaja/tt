<!-- resources/views/admin/reports/summary.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปผลงวด {{ $draw->draw_date->format('d/m/') . ($draw->draw_date->format('Y') + 543 - 2500) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.draws') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับ</a>
            <a href="{{ route('admin.reports.pdf', $draw->id) }}" target="_blank"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
                📄 Export PDF
            </a>
        </div>

        <!-- ผลหวย -->
        <div class="bg-gradient-to-r from-yellow-400 to-orange-400 rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">📊 สรุปผลงวด
                {{ $draw->draw_date->format('d/m/') . ($draw->draw_date->format('Y') + 543 - 2500) }}
            </h1>
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white bg-opacity-90 rounded-lg p-4 text-center">
                    <p class="text-sm font-semibold text-gray-700">3 ตัวบน</p>
                    <p class="text-5xl font-bold text-purple-600">{{ $draw->result_3_top }}</p>
                </div>
                <div class="bg-white bg-opacity-90 rounded-lg p-4 text-center">
                    <p class="text-sm font-semibold text-gray-700">2 ตัวบน</p>
                    <p class="text-5xl font-bold text-blue-600">{{ $draw->result_2_top }}</p>
                </div>
                <div class="bg-white bg-opacity-90 rounded-lg p-4 text-center">
                    <p class="text-sm font-semibold text-gray-700">2 ตัวล่าง</p>
                    <p class="text-5xl font-bold text-green-600">{{ $draw->result_2_bottom }}</p>
                </div>
            </div>
        </div>

        <!-- สรุปยอดรวม -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-sm text-gray-600">ยอดแทงทั้งหมด</p>
                <p class="text-4xl font-bold text-blue-600">{{ number_format($totalBetAmount, 2) }}</p>
                <p class="text-xs text-gray-500">บาท</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-sm text-gray-600">ยอดจ่ายรางวัล</p>
                <p class="text-4xl font-bold text-orange-600">{{ number_format($totalPayout, 2) }}</p>
                <p class="text-xs text-gray-500">บาท</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-sm text-gray-600">กำไร/ขาดทุน</p>
                <p class="text-4xl font-bold {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 2) }}
                </p>
                <p class="text-xs text-gray-500">บาท</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-sm text-gray-600">จำนวนผู้ถูกรางวัล</p>
                <p class="text-4xl font-bold text-purple-600">{{ $winners->count() }}</p>
                <p class="text-xs text-gray-500">คน</p>
            </div>
        </div>

        <!-- สถิติแยกตามประเภท -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">📈 สถิติแยกตามประเภท</h2>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-700 font-semibold mb-2">บน</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_top_bet'], 2) }}</p>
                    <p class="text-xs text-gray-500">แทง</p>
                    <p class="text-2xl font-bold text-orange-600 mt-2">
                        {{ number_format($stats['total_top_payout'], 2) }}
                    </p>
                    <p class="text-xs text-gray-500">จ่าย</p>
                    <p class="text-sm text-green-600 mt-2">ถูก {{ $stats['winners_top'] }} ใบ</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-gray-700 font-semibold mb-2">ล่าง</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['total_bottom_bet'], 2) }}</p>
                    <p class="text-xs text-gray-500">แทง</p>
                    <p class="text-2xl font-bold text-orange-600 mt-2">
                        {{ number_format($stats['total_bottom_payout'], 2) }}
                    </p>
                    <p class="text-xs text-gray-500">จ่าย</p>
                    <p class="text-sm text-green-600 mt-2">ถูก {{ $stats['winners_bottom'] }} ใบ</p>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <p class="text-sm text-gray-700 font-semibold mb-2">โต๊ด</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_toad_bet'], 2) }}</p>
                    <p class="text-xs text-gray-500">แทง</p>
                    <p class="text-2xl font-bold text-orange-600 mt-2">
                        {{ number_format($stats['total_toad_payout'], 2) }}
                    </p>
                    <p class="text-xs text-gray-500">จ่าย</p>
                    <p class="text-sm text-green-600 mt-2">ถูก {{ $stats['winners_toad'] }} ใบ</p>
                </div>
            </div>
        </div>

        <!-- รายการต้องจ่ายเงิน -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">💰 รายการต้องจ่ายเงิน</h2>

            @if($winners->count() > 0)
                <div class="space-y-4">
                    @foreach($winners as $winner)
                        <div class="border-2 border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">👤 {{ $winner['customer_name'] }}</h3>
                                    <p class="text-sm text-gray-600">แทงรวม: {{ number_format($winner['total_bet'], 2) }} บาท
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">ต้องจ่าย</p>
                                    <p class="text-3xl font-bold text-green-600">{{ number_format($winner['total_win'], 2) }}
                                    </p>
                                    <p class="text-xs {{ $winner['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        กำไร {{ $winner['net'] >= 0 ? '+' : '' }}{{ number_format($winner['net'], 2) }}
                                    </p>
                                </div>
                            </div>

                            <div class="border-t pt-3">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-2 py-1 text-left">เลข</th>
                                            <th class="px-2 py-1 text-right">แทง</th>
                                            <th class="px-2 py-1 text-right">ถูก</th>
                                            <th class="px-2 py-1 text-left">รายละเอียด</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($winner['details'] as $detail)
                                            @if($detail['win_amount'] > 0)
                                                <tr class="border-t">
                                                    <td class="px-2 py-1 font-bold text-lg">{{ $detail['number'] }}</td>
                                                    <td class="px-2 py-1 text-right">{{ number_format($detail['bet_amount'], 2) }}</td>
                                                    <td class="px-2 py-1 text-right font-bold text-green-600">
                                                        {{ number_format($detail['win_amount'], 2) }}
                                                    </td>
                                                    <td class="px-2 py-1 text-xs">
                                                        @if($detail['win_types']['top'] > 0)
                                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded mr-1">บน
                                                                {{ number_format($detail['win_types']['top'], 2) }}</span>
                                                        @endif
                                                        @if($detail['win_types']['bottom'] > 0)
                                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded mr-1">ล่าง
                                                                {{ number_format($detail['win_types']['bottom'], 2) }}</span>
                                                        @endif
                                                        @if($detail['win_types']['toad'] > 0)
                                                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">โต๊ด
                                                                {{ number_format($detail['win_types']['toad'], 2) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <p class="text-2xl">🎉</p>
                    <p class="mt-2">ไม่มีผู้ถูกรางวัล</p>
                </div>
            @endif
        </div>
    </div>
</body>

</html>