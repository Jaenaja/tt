<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>สรุปผลงวดวันที่ {{ \Carbon\Carbon::parse($draw->draw_date)->locale('th')->translatedFormat('j F Y') }}
    </title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>

    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700;800&display=swap"
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

        .progress-bar {
            transition: width 1s ease-out;
        }

        /* Accordion Styles - กระชับลง */
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-out;
        }

        .accordion-content.active {
            max-height: 3000px;
            overflow-y: auto;
        }

        .rotate-icon {
            transition: transform 0.3s ease;
        }

        .rotate-icon.active {
            transform: rotate(180deg);
        }

        /* Select2 Dark Mode - แก้ไข CSS ให้ถูกต้อง */
        .select2-container--default .select2-selection--multiple {
            background-color: rgb(248 250 252) !important;
            border: 1px solid rgb(203 213 225) !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem !important;
            min-height: 42px !important;
        }

        .dark .select2-container--default .select2-selection--multiple {
            background-color: rgb(30 41 59) !important;
            border-color: rgb(51 65 85) !important;
            color: white !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: rgb(16 185 129) !important;
            color: white !important;
            border: none !important;
            border-radius: 0.375rem !important;
            padding: 4px 8px !important;
            margin: 2px !important;
        }

        .dark .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: rgb(16 185 129) !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white !important;
            margin-right: 5px !important;
            font-weight: bold !important;
        }

        .select2-container--default .select2-selection--multiple .select2-search__field {
            color: rgb(30 41 59) !important;
        }

        .dark .select2-container--default .select2-selection--multiple .select2-search__field {
            color: white !important;
        }

        .select2-dropdown {
            background-color: white !important;
            border: 1px solid rgb(203 213 225) !important;
            border-radius: 0.5rem !important;
        }

        .dark .select2-dropdown {
            background-color: rgb(30 41 59) !important;
            border-color: rgb(51 65 85) !important;
        }

        .select2-results__option {
            padding: 8px 12px !important;
            color: rgb(30 41 59) !important;
        }

        .dark .select2-results__option {
            color: white !important;
        }

        .select2-results__option--highlighted {
            background-color: rgb(16 185 129) !important;
            color: white !important;
        }

        .select2-search__field {
            outline: none !important;
        }
    </style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">

    <div class="container mx-auto px-4 py-8">

        {{-- Breadcrumbs --}}
        <nav class="mb-6 text-sm text-slate-600 dark:text-slate-400">
            <a href="{{ route('dashboard') }}"
                class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.reports.index') }}"
                class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">จัดการความเสี่ยง</a>
            <span class="mx-2">›</span>
            <span class="text-slate-900 dark:text-white font-semibold">สรุปผลรวม</span>
        </nav>

        {{-- Header --}}
        <div
            class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-8 mb-8 animate-fade-in-up border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2">🎰 สรุปผลงวด</h1>
                    <p class="text-xl text-slate-800 dark:text-slate-300">
                        วันที่ {{ \Carbon\Carbon::parse($draw->draw_date)->locale('th')->translatedFormat('j F Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
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

                    <button id="themeToggle"
                        class="no-print relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                        <span
                            class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                    </button>

                    <a href="{{ route('admin.risk-settings') }}"
                        class="no-print px-4 py-2 bg-slate-600 dark:bg-slate-700 text-white rounded-lg font-semibold hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                        ⚙️ ตั้งค่า
                    </a>

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
                <div class="transition-all duration-300 premium-card bg-gradient-to-br {{ $result['net_profit'] >= 0 ? 'from-emerald-50 to-emerald-100 dark:from-emerald-950/40 dark:to-emerald-900/20' : 'from-red-50 to-red-100 dark:from-red-950/40 dark:to-red-900/20' }} rounded-2xl shadow-lg p-6 border {{ $result['net_profit'] >= 0 ? 'border-emerald-200 dark:border-emerald-800' : 'border-red-200 dark:border-red-800' }} animate-fade-in-up"
                    style="animation-delay: 0.2s">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-4xl">{{ $result['net_profit'] >= 0 ? '📈' : '📉' }}</span>
                        <span class="text-sm text-slate-700 dark:text-slate-400">กำไรสุทธิ</span>
                    </div>
                    <div
                        class="text-4xl font-bold {{ $result['net_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} mb-1">
                        {{ number_format(abs($result['net_profit']), 0) }}
                    </div>
                    <div class="text-sm text-slate-700 dark:text-slate-400">
                        {{ $result['net_profit'] >= 0 ? 'กำไร' : 'ขาดทุน' }} (หัก Com
                        {{ number_format($settings['commission_rate'], 0) }}%)
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
                    <div class="text-sm text-slate-700 dark:text-slate-400">คน (Payout Ratio
                        {{ number_format($result['payout_ratio'], 1) }}%)
                    </div>
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

        {{-- ผลรางวัลที่ออก --}}
        @if($draw->is_announced)
            <div class="bg-gradient-to-r from-amber-400 to-orange-500 rounded-2xl shadow-xl p-8 mb-8 text-white">
                <h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
                    <span>🎉</span> รางวัลที่ออก
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white bg-opacity-20 backdrop-blur-lg rounded-xl p-6 text-center">
                        <div class="text-sm opacity-90 mb-2">รางวัลที่ 1 (6 หลัก)</div>
                        <div class="text-5xl font-bold tracking-wider">{{ $draw->result_3_top ?? 'XXX' }}XXX</div>
                    </div>
                    <div class="bg-white bg-opacity-20 backdrop-blur-lg rounded-xl p-6 text-center">
                        <div class="text-sm opacity-90 mb-2">3 ตัวบน</div>
                        <div class="text-5xl font-bold tracking-wider">{{ $draw->result_3_top ?? 'XXX' }}</div>
                    </div>
                    <div class="bg-white bg-opacity-20 backdrop-blur-lg rounded-xl p-6 text-center">
                        <div class="text-sm opacity-90 mb-2">2 ตัวบน</div>
                        <div class="text-5xl font-bold tracking-wider">{{ $draw->result_2_top ?? 'XX' }}</div>
                    </div>
                    <div class="bg-white bg-opacity-20 backdrop-blur-lg rounded-xl p-6 text-center">
                        <div class="text-sm opacity-90 mb-2">2 ตัวล่าง</div>
                        <div class="text-5xl font-bold tracking-wider">{{ $draw->result_2_bottom ?? 'XX' }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Heatmap Section --}}
        <div
            class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 mb-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                <span>🔥</span> Heatmap วิเคราะห์ความเสี่ยง
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                เพดานยอดจ่าย: 2 ตัว = {{ number_format($settings['max_payout_2_digit'], 0) }} ฿ |
                3 ตัว = {{ number_format($settings['max_payout_3_digit'], 0) }} ฿
            </p>

            {{-- 2-Digit Heatmap --}}
            <div class="mb-12">
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-4">
                    📊 2 ตัว (00-99) - ยอดจ่ายสูงสุด (Liability)
                </h3>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
                    <div id="twoDigitHeatmap" style="width: 100%; height: 600px;"></div>
                </div>
                <div class="flex items-center gap-4 mt-4 text-sm flex-wrap">
                    <span class="flex items-center gap-2">
                        <span class="w-4 h-4 bg-slate-300 dark:bg-slate-600 rounded"></span>
                        <span class="text-slate-700 dark:text-slate-300">⬜ ไม่มีคนซื้อ</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-4 h-4 bg-emerald-500 rounded"></span>
                        <span class="text-slate-700 dark:text-slate-300">✅ ปกติ (&lt; 50%)</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-4 h-4 bg-orange-500 rounded"></span>
                        <span class="text-slate-700 dark:text-slate-300">⚠️ เฝ้าระวัง (50-99%)</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-4 h-4 bg-red-500 rounded"></span>
                        <span class="text-slate-700 dark:text-slate-300">🛑 อั้น/ตัดยอด (≥ 100%)</span>
                    </span>
                </div>
            </div>

            {{-- 3-Digit Heatmap --}}
            <div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-4">
                    📊 3 ตัว (000-999) - ยอดจ่ายสูงสุด (Liability รวมโต๊ด)
                </h3>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
                    <div id="threeDigitHeatmap" style="width: 100%; height: 700px;"></div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-4">
                    💡 รวมยอดจ่ายจากทั้ง 3 ตัวตรง และ 3 ตัวโต๊ดที่สัมพันธ์กันทั้งหมด
                </p>
            </div>
        </div>

        {{-- Top Exposure Tables (Compact) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Top 2-Digit Exposure --}}
            <div
                class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span>⚠️</span> Top 10 เลขเสี่ยง (2 ตัว)
                </h2>
                <div class="space-y-2">
                    @foreach($topTwoDigitExposure as $index => $item)
                        <div
                            class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs {{ $index < 3 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }} font-bold">
                                        {{ $index + 1 }}
                                    </span>
                                    <span
                                        class="text-2xl font-bold text-slate-900 dark:text-white">{{ $item['number'] }}</span>
                                    <span class="text-xs text-slate-600 dark:text-slate-400">({{ $item['bet_count'] }}
                                        ใบ)</span>
                                </div>
                                <div class="text-right">
                                    <div
                                        class="text-sm font-bold {{ $item['status'] === 'critical' ? 'text-red-600 dark:text-red-400' : ($item['status'] === 'warning' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                        {{ number_format($item['liability'], 0) }} ฿
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-400">
                                        {{ number_format($item['percentage'], 1) }}%
                                    </div>
                                </div>
                            </div>

                            <div class="relative w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="progress-bar absolute top-0 left-0 h-full rounded-full {{ $item['status'] === 'critical' ? 'bg-red-500' : ($item['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                    style="width: {{ min($item['percentage'], 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top 3-Digit Exposure --}}
            <div
                class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span>⚠️</span> Top 10 เลขเสี่ยง (3 ตัว)
                </h2>
                <div class="space-y-2">
                    @foreach($topThreeDigitExposure as $index => $item)
                        <div
                            class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs {{ $index < 3 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }} font-bold">
                                        {{ $index + 1 }}
                                    </span>
                                    <span
                                        class="text-2xl font-bold text-slate-900 dark:text-white">{{ $item['number'] }}</span>
                                    <span class="text-xs text-slate-600 dark:text-slate-400">({{ $item['bet_count'] }}
                                        ใบ)</span>
                                </div>
                                <div class="text-right">
                                    <div
                                        class="text-sm font-bold {{ $item['status'] === 'critical' ? 'text-red-600 dark:text-red-400' : ($item['status'] === 'warning' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                        {{ number_format($item['liability'], 0) }} ฿
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-400">
                                        {{ number_format($item['percentage'], 1) }}%
                                    </div>
                                </div>
                            </div>

                            <div class="relative w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="progress-bar absolute top-0 left-0 h-full rounded-full {{ $item['status'] === 'critical' ? 'bg-red-500' : ($item['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                    style="width: {{ min($item['percentage'], 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- สรุปผลงานรายบุคคล (Accordion) - กระชับลง --}}
        @if($draw->is_announced && count($customerSummary) > 0)
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800 mb-8"
                x-data="{ searchCustomer: '' }">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-1 flex items-center gap-2">
                    <span>💰</span> สรุปผลงานรายบุคคล
                </h2>
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-4">
                    รายละเอียดยอดจ่ายแยกตามลูกค้าแต่ละคน (ส่วนลด {{ number_format($settings['commission_rate'], 0) }}%)
                </p>

                {{-- ช่องค้นหาชื่อลูกค้า --}}
                <div class="mb-4">
                    <input type="text" x-model="searchCustomer" placeholder="🔍 ค้นหาชื่อลูกค้า..."
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white text-sm">
                </div>

                {{-- Accordion List - กระชับลง --}}
                <div class="space-y-2">
                    @foreach($customerSummary as $summary)
                        <div x-show="searchCustomer === '' || '{{ strtolower($summary['customer_name']) }}'.includes(searchCustomer.toLowerCase())"
                            x-data="{ open: false }"
                            class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">

                            {{-- Accordion Header - ลด padding --}}
                            <button @click="open = !open"
                                class="w-full p-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-750 transition-colors flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 rotate-icon"
                                        :class="{ 'active': open }" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-left">
                                        <div class="font-bold text-base text-slate-900 dark:text-white">
                                            {{ $summary['customer_name'] }}
                                        </div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400">
                                            {{ $summary['created_by'] }} |
                                            {{ \Carbon\Carbon::parse($summary['created_at'])->format('d/m H:i') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($summary['net_amount'] < 0)
                                        <div class="text-xl font-bold text-red-600 dark:text-red-400">
                                            จ่าย {{ number_format(abs($summary['net_amount']), 0) }}
                                        </div>
                                    @else
                                        <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                                            รับ {{ number_format($summary['net_amount'], 0) }}
                                        </div>
                                    @endif
                                    <div class="text-xs text-slate-600 dark:text-slate-400">
                                        {{ count($summary['winning_numbers']) }} เลขถูก
                                    </div>
                                </div>
                            </button>

                            {{-- Accordion Content - ลด padding --}}
                            <div x-show="open" x-transition
                                class="p-3 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 overflow-y-auto"
                                style="max-height: 3000px;">

                                {{-- สรุปการเงิน - ลด padding --}}
                                <div class="grid grid-cols-3 gap-2 mb-3">
                                    <div
                                        class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                                        <div class="text-xs text-slate-700 dark:text-slate-400">ยอดซื้อ</div>
                                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                            {{ number_format($summary['total_bet_before_discount'], 0) }}
                                        </div>
                                    </div>
                                    <div
                                        class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded border border-amber-200 dark:border-amber-800">
                                        <div class="text-xs text-slate-700 dark:text-slate-400">ส่วนลด</div>
                                        <div class="text-lg font-bold text-amber-600 dark:text-amber-400">
                                            -{{ number_format($summary['discount'], 0) }}</div>
                                    </div>
                                    <div
                                        class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded border border-emerald-200 dark:border-emerald-800">
                                        <div class="text-xs text-slate-700 dark:text-slate-400">หลังหัก</div>
                                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ number_format($summary['total_bet_after_discount'], 0) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    <div
                                        class="p-2 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
                                        <div class="text-xs text-slate-700 dark:text-slate-400">รางวัล</div>
                                        <div class="text-lg font-bold text-red-600 dark:text-red-400">
                                            {{ number_format($summary['total_payout'], 0) }}
                                        </div>
                                    </div>
                                    <div
                                        class="p-2 bg-{{ $summary['net_amount'] < 0 ? 'red' : 'emerald' }}-50 dark:bg-{{ $summary['net_amount'] < 0 ? 'red' : 'emerald' }}-900/20 rounded border border-{{ $summary['net_amount'] < 0 ? 'red' : 'emerald' }}-200 dark:border-{{ $summary['net_amount'] < 0 ? 'red' : 'emerald' }}-800">
                                        <div class="text-xs text-slate-700 dark:text-slate-400">สุทธิ</div>
                                        <div
                                            class="text-lg font-bold text-{{ $summary['net_amount'] < 0 ? 'red' : 'emerald' }}-600 dark:text-{{ $summary['net_amount'] < 0 ? 'red' : 'emerald' }}-400">
                                            {{ $summary['net_amount'] < 0 ? 'จ่าย' : 'รับ' }}
                                            {{ number_format(abs($summary['net_amount']), 0) }}
                                        </div>
                                    </div>
                                </div>

                                {{-- เลขที่ถูกรางวัล - กระชับลง --}}
                                @if(count($summary['winning_numbers']) > 0)
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-2">🏆 เลขที่ถูก</h4>
                                        <div class="space-y-1">
                                            @foreach($summary['winning_numbers'] as $win)
                                                <div
                                                    class="flex items-center justify-between p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded border border-emerald-200 dark:border-emerald-800">
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $win['number'] }}</span>
                                                        <span
                                                            class="text-xs text-slate-700 dark:text-slate-300">({{ $win['win_type'] }})</span>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-xs text-slate-600 dark:text-slate-400">แทง
                                                            {{ number_format($win['bet_amount'], 0) }}
                                                        </div>
                                                        <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                                            {{ number_format($win['payout'], 0) }} ฿
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ประวัติการแทงของงวดนี้ --}}
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800 mb-8"
            id="betsHistorySection">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <span>📝</span> ประวัติการแทงของงวดนี้
            </h2>

            {{-- Filter & Sort Form พร้อม Multi-select --}}
            <form method="GET" action="{{ route('admin.reports.summary', $draw->id) }}" class="no-print mb-6"
                id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    {{-- Multi-select ชื่อลูกค้า --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            ชื่อลูกค้า (เลือกได้หลายคน)
                        </label>
                        <select name="customer_names[]" id="customerSelect" multiple="multiple" class="w-full">
                            @foreach($customerNames as $name)
                                <option value="{{ $name }}" {{ in_array($name, request('customer_names', [])) ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            ค้นหาเลข
                        </label>
                        <input type="text" name="search_number" value="{{ request('search_number') }}"
                            placeholder="เช่น 123, 45"
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white">
                    </div>

                    @if($draw->is_announced)
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                สถานะรางวัล
                            </label>
                            <select name="win_status"
                                class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white">
                                <option value="">ทั้งหมด</option>
                                <option value="won" {{ request('win_status') === 'won' ? 'selected' : '' }}>ถูกรางวัล</option>
                                <option value="lost" {{ request('win_status') === 'lost' ? 'selected' : '' }}>ไม่ถูกรางวัล
                                </option>
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            ประเภทเลข
                        </label>
                        <select name="number_type"
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white">
                            <option value="">ทั้งหมด</option>
                            <option value="2_digit" {{ request('number_type') === '2_digit' ? 'selected' : '' }}>เลข 2 ตัว
                            </option>
                            <option value="3_digit" {{ request('number_type') === '3_digit' ? 'selected' : '' }}>เลข 3 ตัว
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition-colors">
                        🔍 ค้นหา
                    </button>
                    <a href="{{ route('admin.reports.summary', $draw->id) }}"
                        class="px-6 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-semibold transition-colors">
                        ล้างค่า
                    </a>
                </div>
            </form>

            {{-- Table พร้อมแยกสีเลข --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">เวลา</th>
                            <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">ชื่อลูกค้า
                            </th>
                            <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold text-xs">
                                บันทึกโดย</th>
                            <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold">เลข</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">บน</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">ล่าง</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">โต๊ด</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">รวม</th>
                            @if($draw->is_announced)
                                <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold">ผล</th>
                            @else
                                <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold no-print">
                                    จัดการ</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($betsHistory as $bet)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                data-bet-id="{{ $bet->id }}">
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                    {{ \Carbon\Carbon::parse($bet->created_at)->format('H:i:s') }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $bet->customer_name }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-400">
                                    {{ $bet->creator ? $bet->creator->name : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{-- แยกสีเลข: 2 ตัว = emerald, 3 ตัว = violet --}}
                                    <span
                                        class="text-xl font-bold {{ strlen($bet->number) === 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-violet-600 dark:text-violet-400' }}">
                                        {{ $bet->number }}
                                    </span>
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
                                                ✅ {{ number_format($bet->payout_top + $bet->payout_bottom + $bet->payout_toad, 0) }}
                                                ฿
                                            </span>
                                        @else
                                            <span
                                                class="inline-block px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-full text-sm">
                                                ไม่ถูก
                                            </span>
                                        @endif
                                    </td>
                                @else
                                    <td class="px-4 py-3 text-center no-print">
                                        {{-- ใช้ Form แทน AJAX เพื่อแก้ปัญหา 405 --}}
                                        <form action="{{ route('admin.reports.bets.delete', $bet->id) }}" method="POST"
                                            class="inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete(this)"
                                                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-semibold transition-colors">
                                                🗑️ ลบ
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $draw->is_announced ? 9 : 9 }}"
                                    class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    ไม่มีข้อมูลการแทงที่ตรงกับเงื่อนไข
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($betsHistory->hasPages())
                <div class="no-print mt-6">
                    {{ $betsHistory->appends(request()->query())->links() }}
                </div>
            @endif
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
        themeToggle.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
            renderCharts();
        });

        // Initialize Select2 สำหรับ Multi-select
        $(document).ready(function () {
            $('#customerSelect').select2({
                placeholder: "เลือกชื่อลูกค้า (หลายคน)",
                allowClear: true,
                width: '100%'
            });
        });

        // Auto Scroll - แก้ให้เด้งจริงๆ
        window.addEventListener('load', function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('customer_names') || urlParams.has('search_number') ||
                urlParams.has('win_status') || urlParams.has('number_type')) {
                setTimeout(() => {
                    const section = document.getElementById('betsHistorySection');
                    if (section) {
                        section.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }, 500);
            }
        });

        // Delete Function ใช้ Form แทน AJAX
        function confirmDelete(button) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'ใช่, ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }

        // ข้อมูลจาก Controller
        const twoDigitData = {!! json_encode($twoDigitHeatmapData) !!};
        console.log(twoDigitData);
        const threeDigitData = {!! json_encode($threeDigitHeatmapData) !!};
        const maxTwoDigit = {{ $maxTwoDigit > 0 ? $maxTwoDigit : 1 }};
        const maxThreeDigit = {{ $maxThreeDigit > 0 ? $maxThreeDigit : 1 }};
        const maxPayout2D = {{ $settings['max_payout_2_digit'] }};
        const maxPayout3D = {{ $settings['max_payout_3_digit'] }};

        function renderCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#e2e8f0' : '#1e293b';
            const threshold50_2D = maxPayout2D * 0.5;
            const threshold100_2D = maxPayout2D;
            const threshold50_3D = maxPayout3D * 0.5;
            const threshold100_3D = maxPayout3D;

            // 2-Digit Heatmap - แก้ไข Tooltip ให้แสดงจำนวนใบถูก
            const chart2D = echarts.init(document.getElementById('twoDigitHeatmap'));
            chart2D.setOption({
                tooltip: {
                    position: 'top',
                    formatter: function (params) {
                        const x = params.data[0];
                        const y = params.data[1];
                        const number = String(y * 10 + x).padStart(2, '0');
                        const value = params.data[2];
                        const count = params.data[3];

                        // แก้ไข: ถ้า value = 0 หรือ count = 0 แสดงว่าไม่มีคนซื้อ
                        if (value === 0 && count === 0) {
                            return `เลข <strong>${number}</strong><br/>ยังไม่มีคนซื้อ`;
                        }

                        const percentage = ((value / maxPayout2D) * 100).toFixed(1);
                        return `เลข <strong>${number}</strong><br/>` +
                            `ยอดจ่าย: <strong>${value.toLocaleString('th-TH')}</strong> บาท<br/>` +
                            `จำนวนใบ: <strong>${count}</strong> ใบ<br/>` +
                            `เปอร์เซ็นต์: <strong>${percentage}%</strong> ของเพดาน`;
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
                    type: 'continuous',
                    min: 0,
                    max: Math.max(maxTwoDigit, maxPayout2D),
                    dimension: 2,
                    calculable: true,
                    orient: 'vertical',
                    right: '0%',
                    top: 'center',
                    // pieces: [
                    //     { value: 0, color: '#e2e8f0', label: '⬜ ไม่มีคนซื้อ' }, // เทาอ่อน
                    //     { min: 0.01, max: threshold50_2D - 0.01, color: '#10b981', label: '✅ ปกติ' }, // เขียว
                    //     { min: threshold50_2D, max: threshold100_2D - 0.01, color: '#f97316', label: '⚠️ ระวัง' }, // ส้ม
                    //     { min: threshold100_2D, color: '#dc2626', label: '🛑 อั้น' } // แดง
                    // ],
                    // การกำหนดสีเอง (ล่างขึ้นบน: น้อยไปมาก)
                    inRange: {
                        color: [
                            '#f1f5f9', // 0: เทาอ่อนมากๆ (ไม่มีคนซื้อ)
                            '#86efac', // น้อย: เขียวพาสเทล (ปกติ)
                            '#fde047', // กลาง: เหลืองแดด (เริ่มขยับ)
                            '#fb923c', // มาก: ส้ม (เฝ้าระวัง)
                            '#ef4444'  // สูงสุด: แดง (อั้น/ตัดยอด)
                        ]
                    },
                    text: ['🛑 เสี่ยงสูง', '⬜ เสี่ยงต่ำ'], // ข้อความหัว-ท้ายแถบสี
                    textStyle: {
                        color: textColor,
                        fontSize: 12
                    }
                },
                series: [{
                    name: 'Liability',
                    type: 'heatmap',
                    data: twoDigitData.map(item => item),
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

            // 3-Digit Heatmap - แก้ไข Tooltip เหมือนกัน
            const chart3D = echarts.init(document.getElementById('threeDigitHeatmap'));
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
                            const count = params.data[3];

                            if (value === 0 && count === 0) {
                                return `เลข <strong>${number}</strong><br/>ยังไม่มีคนซื้อ`;
                            }

                            const percentage = ((value / maxPayout3D) * 100).toFixed(1);
                            return `เลข <strong>${number}</strong><br/>` +
                                `ยอดจ่าย: <strong>${value.toLocaleString('th-TH')}</strong> บาท<br/>` +
                                `จำนวนใบ: <strong>${count}</strong> ใบ<br/>` +
                                `เปอร์เซ็นต์: <strong>${percentage}%</strong> ของเพดาน`;
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
                    axisLabel: { show: false }
                },
                yAxis: {
                    type: 'category',
                    data: yLabels,
                    splitArea: { show: true },
                    axisLabel: { show: false }
                },
                visualMap: {
                    min: 0,
                    max: Math.max(maxThreeDigit, maxPayout3D),
                    dimension: 2,
                    calculable: true,
                    orient: 'vertical',
                    right: '0%',
                    top: 'center',
                    // pieces: [
                    //     { value: 0, color: isDark ? '#475569' : '#cbd5e1', label: 'ไม่มีคนซื้อ' },
                    //     { min: threshold100_3D, color: '#dc2626', label: '≥ 100% (อั้น)' },
                    //     { min: threshold50_3D, max: threshold100_3D, color: '#f97316', label: '50-99% (ระวัง)' },
                    //     { min: 0.01, max: threshold50_3D, color: '#10b981', label: '< 50% (ปกติ)' }
                    // ],
                    inRange: {
                        color: [
                            '#f1f5f9', // 0: เทาอ่อนมากๆ (ไม่มีคนซื้อ)
                            '#86efac', // น้อย: เขียวพาสเทล (ปกติ)
                            '#fde047', // กลาง: เหลืองแดด (เริ่มขยับ)
                            '#fb923c', // มาก: ส้ม (เฝ้าระวัง)
                            '#ef4444'  // สูงสุด: แดง (อั้น/ตัดยอด)
                        ]
                    },
                    text: ['🛑 เสี่ยงสูง', '⬜ เสี่ยงต่ำ'], // ข้อความหัว-ท้ายแถบสี
                    textStyle: {
                        color: textColor,
                        fontSize: 12
                    }
                },
                series: [{
                    name: 'Liability',
                    type: 'heatmap',
                    data: threeDigitData.map(item => item),
                    label: { show: false },
                    // label: {
                    //     show: true,
                    //     formatter: function (params) {
                    //         const x = params.data[0];
                    //         const y = params.data[1];
                    //         return String(y * 10 + x).padStart(2, '0');
                    //     },
                    //     formatter: function (params) {
                    //         // params.data[4] คือเลข $number ที่เราส่งมาจาก Controller
                    //         return params.data[6];
                    //     },
                    //     color: '#000',
                    //     fontSize: 8,
                    // },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            });

            window.addEventListener('resize', function () {
                chart2D.resize();
                chart3D.resize();
            });
        }

        renderCharts();
    </script>
</body>

</html>