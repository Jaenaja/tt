<!-- resources/views/admin/reports/statistics.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติและกราฟ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ป้องกัน Tailwind CDN reload */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับหน้าหลัก</a>
        </div>
        <!-- เพิ่มส่วนนี้ใน resources/views/admin/reports/statistics.blade.php -->
        <!-- วางไว้หลังกราฟและก่อน </div> สุดท้าย -->


        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 สถิติและกราฟ</h1>
            <p class="text-gray-600">วิเคราะห์ข้อมูลหวยในมุมมองต่างๆ</p>
        </div>
        <!-- รายการงวดที่ผ่านมา -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📋 รายการงวดที่ผ่านมาทั้งหมด</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                วันที่งวด
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                เลข 2 ตัวบน
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                เลข 2 ตัวล่าง
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ยอดแทง
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ยอดจ่าย
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                กำไร/ขาดทุน
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                จำนวนใบ
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ดูรายละเอียด
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pastDraws as $draw)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ thai_date($draw['draw_date']) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($draw['draw_date'])->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        class="px-3 py-1 inline-flex text-xl leading-5 font-bold rounded-full bg-blue-100 text-blue-800">
                                        {{ $draw['result_2_top'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        class="px-3 py-1 inline-flex text-xl leading-5 font-bold rounded-full bg-green-100 text-green-800">
                                        {{ $draw['result_2_bottom'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-blue-600">
                                        {{ number_format($draw['total_bet'], 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-orange-600">
                                        {{ number_format($draw['total_payout'], 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div
                                        class="text-sm font-bold {{ $draw['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $draw['profit'] >= 0 ? '+' : '' }}{{ number_format($draw['profit'], 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ number_format($draw['bet_count']) }} ใบ
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('admin.reports.summary', $draw['id']) }}"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        ดูสรุป
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center">
                                    <div class="text-gray-400">
                                        <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-lg font-medium">ยังไม่มีข้อมูลงวดที่ประกาศผล</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- สรุปสถิติรวม -->
            @if(count($pastDraws) > 0)
                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                    <div class="text-center">
                        <p class="text-xs text-gray-600 mb-1">จำนวนงวดทั้งหมด</p>
                        <p class="text-2xl font-bold text-gray-800">{{ count($pastDraws) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-600 mb-1">ยอดแทงรวม</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ number_format(collect($pastDraws)->sum('total_bet'), 2) }}
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-600 mb-1">ยอดจ่ายรวม</p>
                        <p class="text-2xl font-bold text-orange-600">
                            {{ number_format(collect($pastDraws)->sum('total_payout'), 2) }}
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-600 mb-1">กำไร/ขาดทุนรวม</p>
                        @php
                            $totalProfit = collect($pastDraws)->sum('profit');
                        @endphp
                        <p class="text-2xl font-bold {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $totalProfit >= 0 ? '+' : '' }}{{ number_format($totalProfit, 2) }}
                        </p>
                    </div>
                </div>
            @endif
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
                <div style="position: relative; height: 200px;">
                    <canvas id="betTypeChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">🎯 อัตรากำไร</h3>
                <div style="position: relative; height: 200px;">
                    <canvas id="profitRateChart"></canvas>
                </div>
            </div>
        </div>

        <!-- กราฟ 10 งวดล่าสุด -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📉 กราฟ 10 งวดล่าสุด</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="recentDrawsChart"></canvas>
            </div>
        </div>

        <!-- กราฟรายเดือน -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📅 สถิติรายเดือน (6 เดือนล่าสุด)</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- เลขออกบ่อย -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
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
        // ป้องกันการสร้างกราฟซ้ำซ้อน
        (function () {
            'use strict';

            // ตรวจสอบว่ากราฟถูกสร้างไปแล้วหรือยัง
            if (window.chartsCreated) {
                console.log('Charts already created, skipping...');
                return;
            }

            // รอให้ DOM พร้อม
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCharts);
            } else {
                initCharts();
            }

            function initCharts() {
                try {
                    // สัดส่วนการแทงแต่ละประเภท
                    const betTypeCanvas = document.getElementById('betTypeChart');
                    if (betTypeCanvas && !betTypeCanvas.dataset.chartCreated) {
                        betTypeCanvas.dataset.chartCreated = 'true';
                        new Chart(betTypeCanvas.getContext('2d'), {
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
                    }

                    // อัตรากำไรแต่ละประเภท
                    const profitRateCanvas = document.getElementById('profitRateChart');
                    if (profitRateCanvas && !profitRateCanvas.dataset.chartCreated) {
                        profitRateCanvas.dataset.chartCreated = 'true';
                        new Chart(profitRateCanvas.getContext('2d'), {
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
                                        ticks: {
                                            callback: function (value) {
                                                return value + '%';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // กราฟ 10 งวดล่าสุด
                    const recentDrawsCanvas = document.getElementById('recentDrawsChart');
                    if (recentDrawsCanvas && !recentDrawsCanvas.dataset.chartCreated) {
                        recentDrawsCanvas.dataset.chartCreated = 'true';
                        new Chart(recentDrawsCanvas.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: {!! json_encode($recentDraws->pluck('date')->reverse()->values()) !!},
                                datasets: [
                                    {
                                        label: 'ยอดแทง',
                                        data: {!! json_encode($recentDraws->pluck('total_bet')->reverse()->values()) !!},
                                        borderColor: '#3b82f6',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        tension: 0.4,
                                        fill: true
                                    },
                                    {
                                        label: 'ยอดจ่าย',
                                        data: {!! json_encode($recentDraws->pluck('total_payout')->reverse()->values()) !!},
                                        borderColor: '#f59e0b',
                                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                        tension: 0.4,
                                        fill: true
                                    },
                                    {
                                        label: 'กำไร',
                                        data: {!! json_encode($recentDraws->pluck('profit')->reverse()->values()) !!},
                                        borderColor: '#10b981',
                                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                        tension: 0.4,
                                        fill: true
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
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function (value) {
                                                return value.toLocaleString();
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // กราฟรายเดือน
                    const monthlyCanvas = document.getElementById('monthlyChart');
                    if (monthlyCanvas && !monthlyCanvas.dataset.chartCreated) {
                        monthlyCanvas.dataset.chartCreated = 'true';
                        new Chart(monthlyCanvas.getContext('2d'), {
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
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function (value) {
                                                return value.toLocaleString();
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // ทำเครื่องหมายว่าสร้างกราฟเสร็จแล้ว
                    window.chartsCreated = true;
                    console.log('All charts created successfully');

                } catch (error) {
                    console.error('Error creating charts:', error);
                }
            }
        })();
    </script>
</body>

</html>