<!-- resources/views/admin/reports/statistics.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติและกราฟ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับหน้าหลัก</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 สถิติและกราฟ</h1>
            <p class="text-gray-600">วิเคราะห์ข้อมูลหวยในมุมมองต่างๆ</p>
        </div>

        <!-- สถิติภาพรวม -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">💰 ยอดรวมทั้งหมด</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ยอดแทงรวม</span>
                        <span class="font-bold text-blue-600">
                            {{ number_format($betTypeStats['top']['total_bet'] + $betTypeStats['bottom']['total_bet'] + $betTypeStats['toad']['total_bet'], 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">ยอดจ่ายรวม</span>
                        <span class="font-bold text-orange-600">
                            {{ number_format($betTypeStats['top']['total_payout'] + $betTypeStats['bottom']['total_payout'] + $betTypeStats['toad']['total_payout'], 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between pt-2 border-t">
                        <span class="text-gray-700 font-semibold">กำไรสุทธิ</span>
                        <span class="font-bold text-green-600">
                            {{ number_format(($betTypeStats['top']['total_bet'] + $betTypeStats['bottom']['total_bet'] + $betTypeStats['toad']['total_bet']) - ($betTypeStats['top']['total_payout'] + $betTypeStats['bottom']['total_payout'] + $betTypeStats['toad']['total_payout']), 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">📈 สัดส่วนการแทง</h3>
                <canvas id="betTypeChart" height="200"></canvas>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">🎯 อัตรากำไร</h3>
                <canvas id="profitRateChart" height="200"></canvas>
            </div>
        </div>

        <!-- กราฟ 10 งวดล่าสุด -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📉 กราฟ 10 งวดล่าสุด</h2>
            <canvas id="recentDrawsChart" height="100"></canvas>
        </div>

        <!-- กราฟรายเดือน -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📅 สถิติรายเดือน (6 เดือนล่าสุด)</h2>
            <canvas id="monthlyChart" height="100"></canvas>
        </div>

        <!-- เลขออกบ่อย -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">🔢 เลขออกบ่อย Top 10</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @foreach($frequentNumbers as $index => $item)
                    <div
                        class="text-center p-4 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border-2 border-blue-200">
                        <div class="text-4xl font-bold text-blue-600">{{ $item['number'] }}</div>
                        <div class="text-sm text-gray-600 mt-2">ออก {{ $item['count'] }} ครั้ง</div>
                        <div class="text-xs text-gray-500">อันดับ {{ $index + 1 }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        // สัดส่วนการแทงแต่ละประเภท
        const betTypeCtx = document.getElementById('betTypeChart').getContext('2d');
        new Chart(betTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['บน', 'ล่าง', 'โต๊ด'],
                datasets: [{
                    data: [
                        {{ $betTypeStats['top']['total_bet'] }},
                        {{ $betTypeStats['bottom']['total_bet'] }},
                        {{ $betTypeStats['toad']['total_bet'] }}
                    ],
                    backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // อัตรากำไรแต่ละประเภท
        const profitRateCtx = document.getElementById('profitRateChart').getContext('2d');
        new Chart(profitRateCtx, {
            type: 'bar',
            data: {
                labels: ['บน', 'ล่าง', 'โต๊ด'],
                datasets: [{
                    label: 'กำไร %',
                    data: [
                        {{ $betTypeStats['top']['total_bet'] > 0 ? (($betTypeStats['top']['total_bet'] - $betTypeStats['top']['total_payout']) / $betTypeStats['top']['total_bet'] * 100) : 0 }},
                        {{ $betTypeStats['bottom']['total_bet'] > 0 ? (($betTypeStats['bottom']['total_bet'] - $betTypeStats['bottom']['total_payout']) / $betTypeStats['bottom']['total_bet'] * 100) : 0 }},
                        {{ $betTypeStats['toad']['total_bet'] > 0 ? (($betTypeStats['toad']['total_bet'] - $betTypeStats['toad']['total_payout']) / $betTypeStats['toad']['total_bet'] * 100) : 0 }}
                    ],
                    backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => value + '%' }
                    }
                }
            }
        });

        // กราฟ 10 งวดล่าสุด
        const recentDrawsCtx = document.getElementById('recentDrawsChart').getContext('2d');
        new Chart(recentDrawsCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($recentDraws->pluck('date')->reverse()->values()) !!},
                datasets: [
                    {
                        label: 'ยอดแทง',
                        data: {!! json_encode($recentDraws->pluck('total_bet')->reverse()->values()) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'ยอดจ่าย',
                        data: {!! json_encode($recentDraws->pluck('total_payout')->reverse()->values()) !!},
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'กำไร',
                        data: {!! json_encode($recentDraws->pluck('profit')->reverse()->values()) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // กราฟรายเดือน
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyStats->pluck('month')) !!},
                datasets: [
                    {
                        label: 'ยอดแทง',
                        data: {!! json_encode($monthlyStats->pluck('total_bet')) !!},
                        backgroundColor: '#3b82f6'
                    },
                    {
                        label: 'ยอดจ่าย',
                        data: {!! json_encode($monthlyStats->pluck('total_payout')) !!},
                        backgroundColor: '#f59e0b'
                    },
                    {
                        label: 'กำไร',
                        data: {!! json_encode($monthlyStats->pluck('profit')) !!},
                        backgroundColor: '#10b981'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>

</html>