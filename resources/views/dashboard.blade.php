<!-- resources/views/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800">ระบบจัดการหวย</h1>
            <p class="text-gray-600">วันที่: {{ now()->format('d/m/Y') }}</p>
        </div>

        <!-- Sales Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">ยอดขายรวม</h3>
                <p class="text-4xl font-bold" id="totalSales">{{ number_format($totalSales ?? 0, 2) }}</p>
                <p class="text-sm mt-2">บาท</p>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">ยอดขาย 2 หลัก</h3>
                <p class="text-4xl font-bold" id="twoDigitSales">{{ number_format($twoDigitSales ?? 0, 2) }}</p>
                <p class="text-sm mt-2">บาท</p>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">ยอดขาย 3 หลัก</h3>
                <p class="text-4xl font-bold" id="threeDigitSales">{{ number_format($threeDigitSales ?? 0, 2) }}</p>
                <p class="text-sm mt-2">บาท</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Two Digit Statistics -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">สถิติเลข 2 หลัก (Top 10)</h3>
                <canvas id="twoDigitChart"></canvas>
            </div>

            <!-- Three Digit Statistics -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">สถิติเลข 3 หลัก (Top 10)</h3>
                <canvas id="threeDigitChart"></canvas>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <a href="{{ route('bets.index') }}"
                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-4 px-6 rounded-lg text-center transition">
                เพิ่มการเดิมพัน
            </a>
            <a href="{{ route('lottery.index') }}"
                class="bg-green-500 hover:bg-green-600 text-white font-semibold py-4 px-6 rounded-lg text-center transition">
                ผลหวย
            </a>
            <a href="{{ route('bets.history') }}"
                class="bg-purple-500 hover:bg-purple-600 text-white font-semibold py-4 px-6 rounded-lg text-center transition">
                ประวัติ
            </a>
            <a href="{{ route('bets.statistics') }}"
                class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-4 px-6 rounded-lg text-center transition">
                สถิติ
            </a>
        </div>

        <!-- Recent Bets -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">รายการเดิมพันล่าสุด</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">ชื่อลูกค้า</th>
                            <th class="px-4 py-3">ประเภท</th>
                            <th class="px-4 py-3">เลข</th>
                            <th class="px-4 py-3">จำนวนเงิน</th>
                            <th class="px-4 py-3">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody id="recentBets">
                        @forelse($recentBets ?? [] as $bet)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $bet->customer_name }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 rounded {{ $bet->bet_type === 'three_digit' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $bet->bet_type === 'three_digit' ? '3 หลัก' : '2 หลัก' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-bold">{{ $bet->number }}</td>
                                <td class="px-4 py-3">{{ number_format($bet->amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded 
                                            {{ $bet->status === 'won' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $bet->status === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $bet->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        @if($bet->status === 'won') ถูกรางวัล
                                        @elseif($bet->status === 'lost') ไม่ถูก
                                        @else รอผล
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-center text-gray-500">ยังไม่มีรายการเดิมพัน</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Chart configuration
        const chartConfig = {
            type: 'bar',
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        // Two Digit Chart
        const twoDigitCtx = document.getElementById('twoDigitChart').getContext('2d');
        new Chart(twoDigitCtx, {
            ...chartConfig,
            data: {
                labels: {!! json_encode($twoDigitStats->pluck('number') ?? []) !!},
                datasets: [{
                    label: 'จำนวนครั้ง',
                    data: {!! json_encode($twoDigitStats->pluck('frequency') ?? []) !!},
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }]
            }
        });

        // Three Digit Chart
        const threeDigitCtx = document.getElementById('threeDigitChart').getContext('2d');
        new Chart(threeDigitCtx, {
            ...chartConfig,
            data: {
                labels: {!! json_encode($threeDigitStats->pluck('number') ?? []) !!},
                datasets: [{
                    label: 'จำนวนครั้ง',
                    data: {!! json_encode($threeDigitStats->pluck('frequency') ?? []) !!},
                    backgroundColor: 'rgba(168, 85, 247, 0.5)',
                    borderColor: 'rgba(168, 85, 247, 1)',
                    borderWidth: 1
                }]
            }
        });

        // Real-time updates (every 10 seconds)
        setInterval(() => {
            fetch('/api/sales/realtime')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalSales').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalSales);
                    document.getElementById('twoDigitSales').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.twoDigitSales);
                    document.getElementById('threeDigitSales').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.threeDigitSales);
                });
        }, 10000);
    </script>
</body>

</html>