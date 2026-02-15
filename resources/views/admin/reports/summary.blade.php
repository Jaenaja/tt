<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>

    <!-- ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">

    <script>
        // Theme initialization
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .premium-card {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .premium-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .premium-card:hover::before {
            opacity: 1;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">

    <div class="container mx-auto px-4 py-8">

        {{-- Header --}}
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-8 mb-8 animate-fade-in-up border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2">🎰 สรุปผลงวด</h1>
                    <p class="text-xl text-slate-800 dark:text-slate-300">
                        วันที่ {{ \Carbon\Carbon::parse($draw->draw_date)->locale('th')->translatedFormat('j F Y') }}
                    </p>
                </div>
                <div class="text-right flex items-center gap-4">
                    @if($draw->is_announced)
                        <span
                            class="inline-block px-6 py-3 bg-emerald-500 text-white rounded-full font-bold text-lg shadow-lg">
                            ✓ ประกาศผลแล้ว
                        </span>
                    @else
                        <span
                            class="inline-block px-6 py-3 bg-amber-500 text-white rounded-full font-bold text-lg shadow-lg">
                            ⏳ กำลังขาย
                        </span>
                    @endif

                    {{-- Theme Toggle --}}
                    <button id="themeToggle"
                        class="no-print relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                        <span
                            class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                    </button>

                    {{-- ปุ่มพิมพ์ --}}
                    <button onclick="window.print()"
                        class="no-print px-4 py-2 bg-slate-600 dark:bg-slate-700 text-white rounded-lg font-semibold hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                        🖨️ พิมพ์
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div
                class="transition-all duration-300 premium-card bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-950/40 dark:to-purple-900/20 rounded-2xl shadow-lg p-6 border border-purple-200 dark:border-purple-800 animate-fade-in-up">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-4xl">📋</span>
                    <span class="text-sm text-slate-700 dark:text-slate-400">รายการทั้งหมด</span>
                </div>
                <div class="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-1">
                    {{ number_format($totalTransactions) }}
                </div>
                <div class="text-sm text-slate-700 dark:text-slate-400">รายการเดิมพัน</div>
            </div>

            <div class="transition-all duration-300 premium-card bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950/40 dark:to-blue-900/20 rounded-2xl shadow-lg p-6 border border-blue-200 dark:border-blue-800 animate-fade-in-up"
                style="animation-delay: 0.1s">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-4xl">💰</span>
                    <span class="text-sm text-slate-700 dark:text-slate-400">ยอดแทงรวม</span>
                </div>
                <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                    {{ number_format($totalSales, 0) }}
                </div>
                <div class="text-sm text-slate-700 dark:text-slate-400">บาท</div>
            </div>

            @if($draw->is_announced && $result)
                <div class="transition-all duration-300 premium-card bg-gradient-to-br {{ $result['profit_loss'] >= 0 ? 'from-emerald-50 to-emerald-100 dark:from-emerald-950/40 dark:to-emerald-900/20' : 'from-red-50 to-red-100 dark:from-red-950/40 dark:to-red-900/20' }} rounded-2xl shadow-lg p-6 border {{ $result['profit_loss'] >= 0 ? 'border-emerald-200 dark:border-emerald-800' : 'border-red-200 dark:border-red-800' }} animate-fade-in-up"
                    style="animation-delay: 0.2s">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-4xl">{{ $result['profit_loss'] >= 0 ? '📈' : '📉' }}</span>
                        <span class="text-sm text-slate-700 dark:text-slate-400">กำไร/ขาดทุน</span>
                    </div>
                    <div
                        class="text-4xl font-bold {{ $result['profit_loss'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} mb-1">
                        {{ number_format(abs($result['profit_loss']), 0) }}
                    </div>
                    <div class="text-sm text-slate-700 dark:text-slate-400">
                        {{ $result['profit_loss'] >= 0 ? 'กำไร' : 'ขาดทุน' }}
                    </div>
                </div>

                <div class="transition-all duration-300 premium-card bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-950/40 dark:to-amber-900/20 rounded-2xl shadow-lg p-6 border border-amber-200 dark:border-amber-800 animate-fade-in-up"
                    style="animation-delay: 0.3s">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-4xl">🏆</span>
                        <span class="text-sm text-slate-700 dark:text-slate-400">ผู้ถูกรางวัล</span>
                    </div>
                    <div class="text-4xl font-bold text-amber-600 dark:text-amber-400 mb-1">
                        {{ number_format($result['winners_count']) }}
                    </div>
                    <div class="text-sm text-slate-700 dark:text-slate-400">คน</div>
                </div>
            @else
                <div class="transition-all duration-300 premium-card bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 col-span-2 animate-fade-in-up"
                    style="animation-delay: 0.2s">
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <span class="text-6xl mb-4 block animate-pulse">⏳</span>
                            <div class="text-2xl font-bold text-slate-900 dark:text-white mb-2">รอประกาศผล</div>
                            <div class="text-sm text-slate-700 dark:text-slate-400">ข้อมูลรางวัลจะแสดงหลังประกาศผล</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Heatmap Section --}}
        <div
            class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 mb-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <span>🔥</span> Heatmap วิเคราะห์ความเสี่ยง
            </h2>

            {{-- 2-Digit Heatmap --}}
            <div class="mb-12">
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-4">
                    📊 2 ตัว (00-99) - ยอดจ่ายสูงสุด (Liability)
                </h3>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
                    <div id="twoDigitHeatmap" style="width: 100%; height: 600px;"></div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">
                    💡 สีเขียว = ปลอดภัย | สีส้ม = เฝ้าระวัง | สีแดง = อันตราย (ควรปิดรับหรือลดวงเงิน)
                </p>
            </div>

            {{-- 3-Digit Heatmap --}}
            <div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-4">
                    📊 3 ตัว (000-999) - ยอดจ่ายสูงสุด (Liability รวมโต๊ด)
                </h3>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
                    <div id="threeDigitHeatmap" style="width: 100%; height: 700px;"></div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">
                    💡 รวมยอดจ่ายจากทั้ง 3 ตัวตรง และ 3 ตัวโต๊ดที่สัมพันธ์กัน
                </p>
            </div>
        </div>

        {{-- Top Exposure Tables --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Top 2-Digit Exposure --}}
            <div
                class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span>⚠️</span> Top 10 เลขเสี่ยง (2 ตัว)
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-100 dark:bg-slate-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">อันดับ
                                </th>
                                <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">เลข
                                </th>
                                <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">
                                    ยอดจ่าย</th>
                                <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold">สถานะ
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($topTwoDigitExposure as $index => $item)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $index < 3 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }} font-bold">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-2xl font-bold text-slate-900 dark:text-white">{{ $item['number'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span
                                            class="text-lg font-semibold {{ $item['liability'] >= 10000 ? 'text-red-600 dark:text-red-400' : ($item['liability'] >= 5000 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                            {{ number_format($item['liability'], 0) }} ฿
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item['status'] === 'อั้น')
                                            <span
                                                class="inline-block px-3 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-full text-sm font-bold">
                                                🛑 อั้น
                                            </span>
                                        @elseif($item['status'] === 'จ่ายครึ่ง')
                                            <span
                                                class="inline-block px-3 py-1 bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 rounded-full text-sm font-bold">
                                                ⚠️ จ่ายครึ่ง
                                            </span>
                                        @else
                                            <span
                                                class="inline-block px-3 py-1 bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded-full text-sm font-bold">
                                                ✅ ปกติ
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top 3-Digit Exposure --}}
            <div
                class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span>⚠️</span> Top 10 เลขเสี่ยง (3 ตัว)
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-100 dark:bg-slate-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">อันดับ
                                </th>
                                <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">เลข
                                </th>
                                <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">
                                    ยอดจ่าย</th>
                                <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold">สถานะ
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($topThreeDigitExposure as $index => $item)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $index < 3 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }} font-bold">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-2xl font-bold text-slate-900 dark:text-white">{{ $item['number'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span
                                            class="text-lg font-semibold {{ $item['liability'] >= 10000 ? 'text-red-600 dark:text-red-400' : ($item['liability'] >= 5000 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                            {{ number_format($item['liability'], 0) }} ฿
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item['status'] === 'อั้น')
                                            <span
                                                class="inline-block px-3 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-full text-sm font-bold">
                                                🛑 อั้น
                                            </span>
                                        @elseif($item['status'] === 'จ่ายครึ่ง')
                                            <span
                                                class="inline-block px-3 py-1 bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 rounded-full text-sm font-bold">
                                                ⚠️ จ่ายครึ่ง
                                            </span>
                                        @else
                                            <span
                                                class="inline-block px-3 py-1 bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded-full text-sm font-bold">
                                                ✅ ปกติ
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ประวัติการแทงของงวดนี้ --}}
        <div
            class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800 mb-8">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <span>📝</span> ประวัติการแทงของงวดนี้
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">เวลา</th>
                            <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">ชื่อลูกค้า
                            </th>
                            <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold">เลข</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">บน</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">ล่าง</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">โต๊ด</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">รวม</th>
                            @if($draw->is_announced)
                                <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold">ผล</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($betsHistory as $bet)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                    {{ \Carbon\Carbon::parse($bet->created_at)->format('H:i:s') }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $bet->customer_name }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xl font-bold text-slate-900 dark:text-white">{{ $bet->number }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-200">
                                    {{ $bet->amount_top > 0 ? number_format($bet->amount_top, 0) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-200">
                                    {{ $bet->amount_bottom > 0 ? number_format($bet->amount_bottom, 0) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-200">
                                    {{ $bet->amount_toad > 0 ? number_format($bet->amount_toad, 0) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-blue-600 dark:text-blue-400">
                                    {{ number_format($bet->amount_top + $bet->amount_bottom + $bet->amount_toad, 0) }} ฿
                                </td>
                                @if($draw->is_announced)
                                    <td class="px-4 py-3 text-center">
                                        @if($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad)
                                            <span
                                                class="inline-block px-3 py-1 bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded-full text-sm font-bold">
                                                ✅ ถูก
                                                {{ number_format($bet->payout_top + $bet->payout_bottom + $bet->payout_toad, 0) }} ฿
                                            </span>
                                        @else
                                            <span
                                                class="inline-block px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-full text-sm">
                                                ไม่ถูก
                                            </span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $draw->is_announced ? 8 : 7 }}"
                                    class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    ไม่มีข้อมูลการแทงในงวดนี้
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ปุ่มกลับ --}}
        <div class="text-center no-print">
            <a href="{{ route('admin.reports.index') }}"
                class="inline-block px-8 py-3 bg-slate-600 dark:bg-slate-700 text-white rounded-lg font-semibold hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors shadow-lg">
                ← กลับหน้ารายการงวด
            </a>
        </div>
    </div>

    {{-- JavaScript --}}
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
            // Re-render charts with new theme
            renderCharts();
        });

        // ข้อมูลจาก Controller
        const twoDigitData = {!! json_encode($twoDigitHeatmapData) !!};
        const threeDigitData = {!! json_encode($threeDigitHeatmapData) !!};
        const maxTwoDigit = {{ $maxTwoDigit }};
        const maxThreeDigit = {{ $maxThreeDigit }};

        function renderCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#e2e8f0' : '#1e293b';
            const bgColor = isDark ? '#0f172a' : '#ffffff';

            // 2-Digit Heatmap
            const chart2D = echarts.init(document.getElementById('twoDigitHeatmap'));
            chart2D.setOption({
                tooltip: {
                    position: 'top',
                    formatter: function (params) {
                        const x = params.data[0];
                        const y = params.data[1];
                        const number = String(y * 10 + x).padStart(2, '0');
                        const value = params.data[2];
                        return `เลข <strong>${number}</strong><br/>ยอดจ่าย: <strong>${value.toLocaleString('th-TH')}</strong> บาท`;
                    }
                },
                grid: {
                    height: '85%',
                    top: '5%',
                    left: '3%',
                    right: '15%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                    splitArea: { show: true },
                    axisLabel: {
                        color: textColor,
                        fontSize: 14,
                        fontWeight: 'bold'
                    }
                },
                yAxis: {
                    type: 'category',
                    data: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                    splitArea: { show: true },
                    axisLabel: {
                        color: textColor,
                        fontSize: 14,
                        fontWeight: 'bold'
                    }
                },
                visualMap: {
                    min: 0,
                    max: maxTwoDigit,
                    calculable: true,
                    orient: 'vertical',
                    right: '0%',
                    top: 'center',
                    inRange: {
                        color: ['#10b981', '#fbbf24', '#f59e0b', '#ef4444', '#dc2626']
                    },
                    text: ['สูง', 'ต่ำ'],
                    textStyle: {
                        color: textColor,
                        fontSize: 12
                    },
                    formatter: function (value) {
                        return value.toLocaleString('th-TH') + ' ฿';
                    }
                },
                series: [{
                    name: 'Liability',
                    type: 'heatmap',
                    data: twoDigitData,
                    label: {
                        show: true,
                        formatter: function (params) {
                            const x = params.data[0];
                            const y = params.data[1];
                            return String(y * 10 + x).padStart(2, '0');
                        },
                        color: '#000',
                        fontSize: 11,
                        fontWeight: 'bold'
                    },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            });

            // 3-Digit Heatmap
            const chart3D = echarts.init(document.getElementById('threeDigitHeatmap'));

            // สร้าง labels สำหรับ x และ y axis
            const xLabels = Array.from({ length: 40 }, (_, i) => i);
            const yLabels = Array.from({ length: 25 }, (_, i) => i);

            chart3D.setOption({
                tooltip: {
                    position: 'top',
                    formatter: function (params) {
                        const x = params.data[0];
                        const y = params.data[1];
                        const index = y * 40 + x;
                        if (index <= 999) {
                            const number = String(index).padStart(3, '0');
                            const value = params.data[2];
                            return `เลข <strong>${number}</strong><br/>ยอดจ่าย: <strong>${value.toLocaleString('th-TH')}</strong> บาท`;
                        }
                        return '';
                    }
                },
                grid: {
                    height: '85%',
                    top: '5%',
                    left: '3%',
                    right: '15%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: xLabels,
                    splitArea: { show: true },
                    axisLabel: {
                        show: false
                    }
                },
                yAxis: {
                    type: 'category',
                    data: yLabels,
                    splitArea: { show: true },
                    axisLabel: {
                        show: false
                    }
                },
                visualMap: {
                    min: 0,
                    max: maxThreeDigit,
                    calculable: true,
                    orient: 'vertical',
                    right: '0%',
                    top: 'center',
                    inRange: {
                        color: ['#10b981', '#fbbf24', '#f59e0b', '#ef4444', '#dc2626']
                    },
                    text: ['สูง', 'ต่ำ'],
                    textStyle: {
                        color: textColor,
                        fontSize: 12
                    },
                    formatter: function (value) {
                        return value.toLocaleString('th-TH') + ' ฿';
                    }
                },
                series: [{
                    name: 'Liability',
                    type: 'heatmap',
                    data: threeDigitData,
                    label: {
                        show: false
                    },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            });

            // Responsive
            window.addEventListener('resize', function () {
                chart2D.resize();
                chart3D.resize();
            });
        }

        // Initial render
        renderCharts();
    </script>
</body>

</html>