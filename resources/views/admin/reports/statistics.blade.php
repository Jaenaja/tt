<!-- resources/views/admin/reports/statistics.blade.php -->
<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติและกราฟ - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        * {
            font-family: 'Sarabun', sans-serif;
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb & Header -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center mb-4">
                <nav class="text-sm text-slate-600 dark:text-slate-400">
                    <a href="{{ route('dashboard') }}"
                        class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                    <span class="mx-2">›</span>
                    <span class="text-slate-900 dark:text-white font-semibold">สถิติและกราฟ</span>
                </nav>
                <button id="themeToggle"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                    <span
                        class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                </button>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">📊 สถิติและกราฟ</h1>
                <p class="text-slate-600 dark:text-slate-400">วิเคราะห์ข้อมูลหวยในมุมมองต่างๆ</p>
            </div>
        </div>

        <!-- รายการงวดที่ผ่านมา -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">📋 รายการงวดที่ผ่านมาทั้งหมด</h2>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                            <th
                                class="px-6 py-3 text-left text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider">
                                วันที่งวด
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider">
                                เลข 2 ตัวบน
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider">
                                เลข 2 ตัวล่าง
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider">
                                ยอดแทง
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider">
                                ยอดจ่าย
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider">
                                กำไร/ขาดทุน
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider">
                                จำนวนใบ
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-bold text-slate-900 dark:text-slate-200 uppercase tracking-wider">
                                ดูรายละเอียด
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        @forelse($pastDraws as $draw)
                            <tr class="transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ thai_date($draw['draw_date']) }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-500">
                                        {{ \Carbon\Carbon::parse($draw['draw_date'])->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        class="px-3 py-1 inline-flex text-xl leading-5 font-bold rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300">
                                        {{ $draw['result_2_top'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        class="px-3 py-1 inline-flex text-xl leading-5 font-bold rounded-full bg-teal-100 dark:bg-teal-900/40 text-teal-800 dark:text-teal-300">
                                        {{ $draw['result_2_bottom'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                        {{ number_format($draw['total_bet'], 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-orange-600 dark:text-orange-400">
                                        {{ number_format($draw['total_payout'], 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div
                                        class="text-sm font-bold {{ $draw['profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $draw['profit'] >= 0 ? '+' : '' }}{{ number_format($draw['profit'], 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-violet-100 dark:bg-violet-900/40 text-violet-800 dark:text-violet-300">
                                        {{ number_format($draw['bet_count']) }} ใบ
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('admin.reports.summary', $draw['id']) }}"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-emerald-600 dark:bg-emerald-700 hover:bg-emerald-700 dark:hover:bg-emerald-600 transition-all">
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
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="text-slate-400 dark:text-slate-500">
                                        <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-lg font-medium text-slate-600 dark:text-slate-400">
                                            ยังไม่มีข้อมูลงวดที่ประกาศผล</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- สรุปสถิติรวม -->
            @if(count($pastDraws) > 0)
                <div
                    class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="text-center">
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">จำนวนงวดทั้งหมด</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ count($pastDraws) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">ยอดแทงรวม</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format(collect($pastDraws)->sum('total_bet'), 2) }}
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">ยอดจ่ายรวม</p>
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                            {{ number_format(collect($pastDraws)->sum('total_payout'), 2) }}
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">กำไร/ขาดทุนรวม</p>
                        @php
                            $totalProfit = collect($pastDraws)->sum('profit');
                        @endphp
                        <p
                            class="text-2xl font-bold {{ $totalProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $totalProfit >= 0 ? '+' : '' }}{{ number_format($totalProfit, 2) }}
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- สถิติภาพรวม -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">💰 ยอดรวมทั้งหมด</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-slate-400">ยอดแทงรวม</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($betTypeStats['top']['total_bet'] + $betTypeStats['bottom']['total_bet'] + $betTypeStats['toad']['total_bet'], 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-slate-400">ยอดจ่ายรวม</span>
                        <span class="font-bold text-orange-600 dark:text-orange-400">
                            {{ number_format($betTypeStats['top']['total_payout'] + $betTypeStats['bottom']['total_payout'] + $betTypeStats['toad']['total_payout'], 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">กำไรสุทธิ</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                            {{ number_format(($betTypeStats['top']['total_bet'] + $betTypeStats['bottom']['total_bet'] + $betTypeStats['toad']['total_bet']) - ($betTypeStats['top']['total_payout'] + $betTypeStats['bottom']['total_payout'] + $betTypeStats['toad']['total_payout']), 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">📈 สัดส่วนการแทง</h3>
                <div style="position: relative; height: 200px;">
                    <canvas id="betTypeChart"></canvas>
                </div>
            </div>

            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">🎯 อัตรากำไร</h3>
                <div style="position: relative; height: 200px;">
                    <canvas id="profitRateChart"></canvas>
                </div>
            </div>
        </div>

        <!-- กราฟ 10 งวดล่าสุด -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">📉 กราฟ 10 งวดล่าสุด</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="recentDrawsChart"></canvas>
            </div>
        </div>

        <!-- กราฟรายเดือน -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">📅 สถิติรายเดือน (6 เดือนล่าสุด)</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- เลขออกบ่อย -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">🔢 เลขออกบ่อย Top 10</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @foreach($frequentNumbers as $index => $item)
                    <div
                        class="text-center p-4 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl border-2 border-emerald-200 dark:border-emerald-700">
                        <div class="text-4xl font-bold text-emerald-600 dark:text-emerald-400">{{ $item['number'] }}</div>
                        <div class="text-sm text-slate-600 dark:text-slate-400 mt-2">ออก {{ $item['count'] }} ครั้ง</div>
                        <div class="text-xs text-slate-500 dark:text-slate-500">อันดับ {{ $index + 1 }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        themeToggle.addEventListener('click', () => {
            if (htmlElement.classList.contains('dark')) {
                htmlElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                htmlElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        });

        // === ORIGINAL CHART LOGIC - DO NOT MODIFY ===
        (function () {
            'use strict';

            if (window.chartsCreated) {
                console.log('Charts already created, skipping...');
                return;
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCharts);
            } else {
                initCharts();
            }

            function initCharts() {
                try {
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