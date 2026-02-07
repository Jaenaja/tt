<!-- resources/views/bets/history.blade.php -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการเดิมพัน</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับหน้าหลัก</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">ประวัติการเดิมพัน</h1>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">วันที่</th>
                            <th class="px-4 py-3">ชื่อลูกค้า</th>
                            <th class="px-4 py-3">ประเภท</th>
                            <th class="px-4 py-3">เลข</th>
                            <th class="px-4 py-3">จำนวนเงิน</th>
                            <th class="px-4 py-3">สถานะ</th>
                            <th class="px-4 py-3">เงินจ่าย</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $bet)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $bet->bet_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $bet->customer_name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-sm {{ $bet->bet_type === 'three_digit' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $bet->bet_type === 'three_digit' ? '3 หลัก' : '2 หลัก' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-lg">{{ $bet->number }}</td>
                            <td class="px-4 py-3">{{ number_format($bet->amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-sm
                                    {{ $bet->status === 'won' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $bet->status === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $bet->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                    @if($bet->status === 'won') ถูกรางวัล
                                    @elseif($bet->status === 'lost') ไม่ถูก
                                    @else รอผล
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold {{ $bet->status === 'won' ? 'text-green-600' : '' }}">
                                {{ $bet->payout ? number_format($bet->payout, 2) : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-500">ยังไม่มีประวัติการเดิมพัน</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $history->links() }}
            </div>
        </div>
    </div>
</body>
</html>