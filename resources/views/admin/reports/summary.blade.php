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

        /* Back to Top Button - Glassmorphism */
        #backToTop {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
        }

        #backToTop.show {
            opacity: 1;
            visibility: visible;
        }

        #backToTop:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.45);
        }

        .dark #backToTop {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .dark #backToTop:hover {
            background: rgba(16, 185, 129, 0.25);
        }

        #backToTop svg {
            width: 24px;
            height: 24px;
            color: #10b981;
        }

        .dark #backToTop svg {
            color: #6ee7b7;
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
            <div
                class="transition-all duration-300 rounded-2xl shadow-xl p-8 mb-8 border
                                                                                                    bg-white border-emerald-200
                                                                                                    dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 dark:border-emerald-500/30">
                <h2
                    class="text-3xl font-bold mb-6 flex items-center gap-3
                                                                                                        text-emerald-600 dark:text-emerald-400">
                    <span>🎉</span> รางวัลที่ออก
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- 3 ตัวบน → สีม่วง (violet) ตรงกับ card ด้านล่าง --}}
                    <div
                        class="rounded-xl p-6 text-center border
                                                                                                            bg-violet-50 border-violet-200
                                                                                                            dark:bg-white/5 dark:border-violet-500/20">
                        <div
                            class="text-sm font-semibold tracking-wider uppercase mb-2
                                                                                                                text-violet-600 dark:text-violet-300/70">
                            3
                            ตัวบน
                        </div>
                        <div
                            class="text-6xl font-bold tracking-widest
                                                                                                                text-violet-600 dark:text-violet-400">
                            {{ $draw->result_3_top ?? 'XXX' }}
                        </div>
                    </div>

                    {{-- 2 ตัวบน → สีเขียว (emerald) ตรงกับ card ด้านล่าง --}}
                    <div
                        class="rounded-xl p-6 text-center border
                                                                                                            bg-emerald-50 border-emerald-200
                                                                                                            dark:bg-white/5 dark:border-emerald-500/20">
                        <div
                            class="text-sm font-semibold tracking-wider uppercase mb-2
                                                                                                                text-emerald-600 dark:text-emerald-300/70">
                            2
                            ตัวบน
                        </div>
                        <div
                            class="text-6xl font-bold tracking-widest
                                                                                                                text-emerald-600 dark:text-emerald-400">
                            {{ $draw->result_2_top ?? 'XX' }}
                        </div>
                    </div>

                    {{-- 2 ตัวล่าง → สีฟ้า (sky) ตรงกับ card ด้านล่าง --}}
                    <div
                        class="rounded-xl p-6 text-center border
                                                                                                            bg-sky-50 border-sky-200
                                                                                                            dark:bg-white/5 dark:border-sky-500/20">
                        <div
                            class="text-sm font-semibold tracking-wider uppercase mb-2
                                                                                                                text-sky-600 dark:text-sky-300/70">
                            2
                            ตัวล่าง
                        </div>
                        <div
                            class="text-6xl font-bold tracking-widest
                                                                                                                text-sky-600 dark:text-sky-400">
                            {{ $draw->result_2_bottom ?? 'XX' }}
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- Sales Summary Blocks --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- 3 ตัวบน --}}
            <div
                class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-5 border border-violet-200 dark:border-violet-800 animate-fade-in-up">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-3xl">🟣</span>
                    <span
                        class="text-xs font-bold px-2 py-1 bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 rounded-full">3
                        ตัวบน</span>
                </div>
                <div class="text-3xl font-bold text-violet-600 dark:text-violet-400 mb-1">
                    {{ number_format($sales['three_top']['total'], 0) }}
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">บาท (ยอดแทง)</span>

                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">จำนวน
                    {{ number_format($sales['three_top']['count']) }} รายการ
                </div>
            </div>

            {{-- 3 ตัวโต๊ด --}}
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-5 border border-amber-200 dark:border-amber-800 animate-fade-in-up"
                style="animation-delay:0.1s">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-3xl">🟡</span>
                    <span
                        class="text-xs font-bold px-2 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded-full">3
                        ตัวโต๊ด</span>
                </div>
                <div class="text-3xl font-bold text-amber-600 dark:text-amber-400 mb-1">
                    {{ number_format($sales['three_toad']['total'], 0) }}
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">บาท (ยอดแทง)</span>

                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">จำนวน
                    {{ number_format($sales['three_toad']['count']) }} รายการ
                </div>
            </div>

            {{-- 2 ตัวบน --}}
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-5 border border-emerald-200 dark:border-emerald-800 animate-fade-in-up"
                style="animation-delay:0.2s">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-3xl">🟢</span>
                    <span
                        class="text-xs font-bold px-2 py-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-full">2
                        ตัวบน</span>
                </div>
                <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mb-1">
                    {{ number_format($sales['two_top']['total'], 0) }}
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">บาท (ยอดแทง)</span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">จำนวน
                    {{ number_format($sales['two_top']['count']) }} รายการ
                </div>
            </div>

            {{-- 2 ตัวล่าง --}}
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-5 border border-sky-200 dark:border-sky-800 animate-fade-in-up"
                style="animation-delay:0.3s">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-3xl">🔵</span>
                    <span
                        class="text-xs font-bold px-2 py-1 bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 rounded-full">2
                        ตัวล่าง</span>
                </div>
                <div class="text-3xl font-bold text-sky-600 dark:text-sky-400 mb-1">
                    {{ number_format($sales['two_bottom']['total'], 0) }}
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">บาท (ยอดแทง)</span>

                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">จำนวน
                    {{ number_format($sales['two_bottom']['count']) }} รายการ
                </div>
            </div>
        </div>

        {{-- Heatmap Section --}}
        <div
            class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 mb-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-1 flex items-center gap-2">
                <span>🔥</span> Heatmap วิเคราะห์ความเสี่ยง
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">
                เพดาน: 2 ตัว = {{ number_format($settings['max_payout_2_digit'], 0) }} ฿ |
                3 ตัว = {{ number_format($settings['max_payout_3_digit'], 0) }} ฿
            </p>

            {{-- 2 ตัว Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                {{-- 2 ตัวบน --}}
                <div>
                    <h3 class="text-sm font-bold text-sky-700 dark:text-sky-300 mb-2 flex items-center gap-1">
                        🟢 2 ตัวบน (00-99)
                    </h3>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-2">
                        <div id="heatmap2Top" style="width:100%;height:320px;"></div>
                    </div>
                </div>
                {{-- 2 ตัวล่าง --}}
                <div>
                    <h3 class="text-sm font-bold text-violet-700 dark:text-violet-300 mb-2 flex items-center gap-1">
                        🔵 2 ตัวล่าง (00-99)
                    </h3>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-2">
                        <div id="heatmap2Bottom" style="width:100%;height:320px;"></div>
                    </div>
                </div>
            </div>

            {{-- 3 ตัว Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- 3 ตัวบน --}}
                <div>
                    <h3 class="text-sm font-bold text-emerald-700 dark:text-emerald-300 mb-2 flex items-center gap-1">
                        🟣 3 ตัวบน (000-999)
                    </h3>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-2">
                        <div id="heatmap3Top" style="width:100%;height:480px;"></div>
                    </div>
                </div>
                {{-- 3 ตัวโต๊ด --}}
                <div>
                    <h3 class="text-sm font-bold text-amber-700 dark:text-amber-300 mb-2 flex items-center gap-1">
                        🟡 3 ตัวโต๊ด (000-999)
                    </h3>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-2">
                        <div id="heatmap3Toad" style="width:100%;height:480px;"></div>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex items-center gap-4 mt-4 text-xs flex-wrap">
                <span class="flex items-center gap-1"><span
                        class="w-3 h-3 bg-slate-300 dark:bg-slate-600 rounded inline-block"></span>ไม่มีคนซื้อ</span>
                <span class="flex items-center gap-1"><span
                        class="w-3 h-3 bg-emerald-400 rounded inline-block"></span>ปกติ (&lt;50%)</span>
                <span class="flex items-center gap-1"><span
                        class="w-3 h-3 bg-orange-400 rounded inline-block"></span>เฝ้าระวัง (50-99%)</span>
                <span class="flex items-center gap-1"><span
                        class="w-3 h-3 bg-red-500 rounded inline-block"></span>อั้น/ตัดยอด (≥100%)</span>
            </div>
        </div>

        {{-- Top Exposure Tables (4 ประเภท Compact) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- Top 3 ตัวบน --}}
            <div
                class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-xl p-4 shadow-lg dark:shadow-2xl border border-violet-200 dark:border-violet-800">
                <h2 class="text-sm font-bold text-violet-700 dark:text-violet-300 mb-3 flex items-center gap-1">
                    🟣 Top 10 (3 ตัวบน)
                </h2>
                <div class="space-y-1">
                    @foreach($topThreeTopExposure as $index => $item)
                        <div
                            class="flex items-center justify-between px-2 py-1 rounded bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="text-xs w-4 h-4 flex items-center justify-center rounded-full font-bold {{ $index < 3 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">{{ $index + 1 }}</span>
                                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $item['number'] }}</span>
                                <span class="text-xs text-slate-400">({{ $item['bet_count'] }})</span>
                            </div>
                            <div class="text-right">
                                <div
                                    class="text-xs font-bold {{ $item['status'] === 'critical' ? 'text-red-500' : ($item['status'] === 'warning' ? 'text-amber-500' : 'text-emerald-500') }}">
                                    {{ number_format($item['liability'], 0) }}฿
                                </div>
                                <div class="text-xs text-slate-400">{{ number_format($item['percentage'], 0) }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top 3 ตัวโต๊ด --}}
            <div
                class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-xl p-4 shadow-lg dark:shadow-2xl border border-amber-200 dark:border-amber-800">
                <h2 class="text-sm font-bold text-amber-700 dark:text-amber-300 mb-3 flex items-center gap-1">
                    🟡 Top 10 (3 ตัวโต๊ด)
                </h2>
                <div class="space-y-1">
                    @foreach($topThreeToadExposure as $index => $item)
                        <div
                            class="flex items-center justify-between px-2 py-1 rounded bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="text-xs w-4 h-4 flex items-center justify-center rounded-full font-bold {{ $index < 3 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">{{ $index + 1 }}</span>
                                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $item['number'] }}</span>
                                <span class="text-xs text-slate-400">({{ $item['bet_count'] }})</span>
                            </div>
                            <div class="text-right">
                                <div
                                    class="text-xs font-bold {{ $item['status'] === 'critical' ? 'text-red-500' : ($item['status'] === 'warning' ? 'text-amber-500' : 'text-emerald-500') }}">
                                    {{ number_format($item['liability'], 0) }}฿
                                </div>
                                <div class="text-xs text-slate-400">{{ number_format($item['percentage'], 0) }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top 2 ตัวบน --}}
            <div
                class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-xl p-4 shadow-lg dark:shadow-2xl border border-emerald-200 dark:border-emerald-800">
                <h2 class="text-sm font-bold text-emerald-700 dark:text-emerald-300 mb-3 flex items-center gap-1">
                    🟢 Top 10 (2 ตัวบน)
                </h2>
                <div class="space-y-1">
                    @foreach($topTwoTopExposure as $index => $item)
                        <div
                            class="flex items-center justify-between px-2 py-1 rounded bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="text-xs w-4 h-4 flex items-center justify-center rounded-full font-bold {{ $index < 3 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">{{ $index + 1 }}</span>
                                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $item['number'] }}</span>
                                <span class="text-xs text-slate-400">({{ $item['bet_count'] }})</span>
                            </div>
                            <div class="text-right">
                                <div
                                    class="text-xs font-bold {{ $item['status'] === 'critical' ? 'text-red-500' : ($item['status'] === 'warning' ? 'text-amber-500' : 'text-emerald-500') }}">
                                    {{ number_format($item['liability'], 0) }}฿
                                </div>
                                <div class="text-xs text-slate-400">{{ number_format($item['percentage'], 0) }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top 2 ตัวล่าง --}}
            <div
                class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-xl p-4 shadow-lg dark:shadow-2xl border border-sky-200 dark:border-sky-800">
                <h2 class="text-sm font-bold text-sky-700 dark:text-sky-300 mb-3 flex items-center gap-1">
                    🔵 Top 10 (2 ตัวล่าง)
                </h2>
                <div class="space-y-1">
                    @foreach($topTwoBottomExposure as $index => $item)
                        <div
                            class="flex items-center justify-between px-2 py-1 rounded bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="text-xs w-4 h-4 flex items-center justify-center rounded-full font-bold {{ $index < 3 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">{{ $index + 1 }}</span>
                                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $item['number'] }}</span>
                                <span class="text-xs text-slate-400">({{ $item['bet_count'] }})</span>
                            </div>
                            <div class="text-right">
                                <div
                                    class="text-xs font-bold {{ $item['status'] === 'critical' ? 'text-red-500' : ($item['status'] === 'warning' ? 'text-amber-500' : 'text-emerald-500') }}">
                                    {{ number_format($item['liability'], 0) }}฿
                                </div>
                                <div class="text-xs text-slate-400">{{ number_format($item['percentage'], 0) }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- เลขเกิน 100% (อั้น) --}}
        @php
            $totalOver = count($overLimit2Top) + count($overLimit2Bottom) + count($overLimit3Top) + count($overLimit3Toad);
        @endphp
        <div
            class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-5 mb-8 shadow-xl dark:shadow-2xl border {{ $totalOver > 0 ? 'border-red-300 dark:border-red-700' : 'border-slate-200 dark:border-slate-800' }}">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <h2
                        class="text-lg font-bold {{ $totalOver > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-300' }} flex items-center gap-2">
                        🛑 เลขเกิน 100% (อั้น/ตัดยอด)
                    </h2>
                    @if($totalOver > 0)
                        <span
                            class="px-2 py-0.5 bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300 rounded-full text-xs font-bold">
                            {{ $totalOver }} เลข
                        </span>
                    @else
                        <span
                            class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 rounded-full text-xs font-bold">
                            ✅ ไม่มีเลขเกินเพดาน
                        </span>
                    @endif
                </div>
                <a href="{{ route('admin.reports.export-over-limit', $draw->id) }}"
                    class="no-print inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold text-sm transition-colors shadow">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="3" y="3" width="18" height="18" rx="2" fill="white" fill-opacity="0.25" />
                        <text x="12" y="16" text-anchor="middle" fill="white" font-size="11" font-weight="bold"
                            font-family="Arial">X</text>
                    </svg>
                    ดาวน์โหลด Excel เลขเกิน 100%
                </a>
            </div>

            @if($totalOver > 0)
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    {{-- 3 ตัวบน --}}
                    <div>
                        <div class="text-xs font-bold text-violet-600 dark:text-violet-400 mb-1.5 flex items-center gap-1">
                            🟣 3 ตัวบน
                            <span
                                class="bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 px-1.5 py-0.5 rounded-full">{{ count($overLimit3Top) }}</span>
                        </div>
                        @if(count($overLimit3Top) > 0)
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                @foreach($overLimit3Top as $item)
                                    <div
                                        class="flex items-center justify-between px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800 text-xs">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $item['number'] }}</span>
                                        <div class="text-right">
                                            <div class="font-bold text-red-600 dark:text-red-400">
                                                {{ number_format($item['liability'], 0) }}฿
                                            </div>
                                            <div class="text-red-500 dark:text-red-400">{{ number_format($item['percentage'], 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">ไม่มี</p>
                        @endif
                    </div>

                    {{-- 3 ตัวโต๊ด --}}
                    <div>
                        <div class="text-xs font-bold text-amber-600 dark:text-amber-400 mb-1.5 flex items-center gap-1">
                            🟡 3 ตัวโต๊ด
                            <span
                                class="bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 px-1.5 py-0.5 rounded-full">{{ count($overLimit3Toad) }}</span>
                        </div>
                        @if(count($overLimit3Toad) > 0)
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                @foreach($overLimit3Toad as $item)
                                    <div
                                        class="flex items-center justify-between px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800 text-xs">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $item['number'] }}</span>
                                        <div class="text-right">
                                            <div class="font-bold text-red-600 dark:text-red-400">
                                                {{ number_format($item['liability'], 0) }}฿
                                            </div>
                                            <div class="text-red-500 dark:text-red-400">{{ number_format($item['percentage'], 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">ไม่มี</p>
                        @endif
                    </div>

                    {{-- 2 ตัวบน --}}
                    <div>
                        <div
                            class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mb-1.5 flex items-center gap-1">
                            🟢 2 ตัวบน
                            <span
                                class="bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 px-1.5 py-0.5 rounded-full">{{ count($overLimit2Top) }}</span>
                        </div>
                        @if(count($overLimit2Top) > 0)
                            <div class="space-y-1">
                                @foreach($overLimit2Top as $item)
                                    <div
                                        class="flex items-center justify-between px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800 text-xs">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $item['number'] }}</span>
                                        <div class="text-right">
                                            <div class="font-bold text-red-600 dark:text-red-400">
                                                {{ number_format($item['liability'], 0) }}฿
                                            </div>
                                            <div class="text-red-500 dark:text-red-400">{{ number_format($item['percentage'], 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">ไม่มี</p>
                        @endif
                    </div>

                    {{-- 2 ตัวล่าง --}}
                    <div>
                        <div class="text-xs font-bold text-sky-600 dark:text-sky-400 mb-1.5 flex items-center gap-1">
                            🔵 2 ตัวล่าง
                            <span
                                class="bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 px-1.5 py-0.5 rounded-full">{{ count($overLimit2Bottom) }}</span>
                        </div>
                        @if(count($overLimit2Bottom) > 0)
                            <div class="space-y-1">
                                @foreach($overLimit2Bottom as $item)
                                    <div
                                        class="flex items-center justify-between px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800 text-xs">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $item['number'] }}</span>
                                        <div class="text-right">
                                            <div class="font-bold text-red-600 dark:text-red-400">
                                                {{ number_format($item['liability'], 0) }}฿
                                            </div>
                                            <div class="text-red-500 dark:text-red-400">{{ number_format($item['percentage'], 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">ไม่มี</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- สรุปผลงานรายบุคคล (Accordion) - กระชับลง --}}
        @if($draw->is_announced && count($customerSummary) > 0)
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800 mb-8"
                x-data="{ searchCustomer: '' }">
                <div class="flex items-start justify-between mb-1">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>💰</span> สรุปผลงานรายบุคคล
                    </h2>
                    <button onclick="exportCustomerSummaryExcel()"
                        class="no-print px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg font-semibold text-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="18" height="18" rx="2" fill="white" fill-opacity="0.25" />
                            <text x="12" y="16" text-anchor="middle" fill="white" font-size="11" font-weight="bold"
                                font-family="Arial">X</text>
                        </svg>
                        Export Excel
                    </button>
                </div>
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
                            <option value="2_top" {{ request('number_type') === '2_top' ? 'selected' : '' }}>2 ตัวบน
                            </option>
                            <option value="2_bottom" {{ request('number_type') === '2_bottom' ? 'selected' : '' }}>2
                                ตัวล่าง</option>
                            <option value="3_top" {{ request('number_type') === '3_top' ? 'selected' : '' }}>3 ตัวบน
                            </option>
                            <option value="3_toad" {{ request('number_type') === '3_toad' ? 'selected' : '' }}>3 ตัวโต๊ด
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2 flex-wrap">
                    <button type="submit"
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition-colors">
                        🔍 ค้นหา
                    </button>
                    <a href="{{ route('admin.reports.summary', $draw->id) }}"
                        class="px-6 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-semibold transition-colors">
                        ล้างค่า
                    </a>
                    <button type="button" onclick="exportSummaryExcel()"
                        class="no-print px-6 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg font-semibold transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="18" height="18" rx="2" fill="white" fill-opacity="0.25" />
                            <text x="12" y="16" text-anchor="middle" fill="white" font-size="11" font-weight="bold"
                                font-family="Arial">X</text>
                        </svg>
                        Export Excel
                    </button>
                </div>
            </form>

            {{-- Table พร้อมแยกสีเลข --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">ชื่อลูกค้า
                            </th>
                            <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold">เลข</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">บน</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">ล่าง</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">โต๊ด</th>
                            <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">รวม</th>
                            <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold text-xs">
                                บันทึกเมื่อ</th>
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
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $bet->customer_name }}
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
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-400">
                                    <div>{{ $bet->creator ? $bet->creator->name : '-' }}</div>
                                    <div>{{ \Carbon\Carbon::parse($bet->created_at)->format('d/m/y H:i') }}</div>
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
                                        <button
                                            onclick="deleteBet({{ $bet->id }}, '{{ $bet->customer_name }}', '{{ $bet->number }}')"
                                            class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-semibold transition-colors">
                                            🗑️ ลบ
                                        </button>
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

        async function deleteBet(id, customerName, number) {
            const { value: deleteCode } = await Swal.fire({
                title: '🔐 กรอกรหัสลบ',
                html: `
                    <p class="text-slate-600 dark:text-slate-400 mb-4">
                        ลบรายการ: <strong>${customerName} - ${number}</strong>
                    </p>
                    <input type="password" id="deleteCode" class="swal2-input" placeholder="รหัส 6 หลัก" maxlength="6" inputmode="numeric">
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'ยืนยันการลบ',
                cancelButtonText: 'ยกเลิก',
                didOpen: () => {
                    const input = document.getElementById('deleteCode');
                    input.setAttribute('autocomplete', 'off');
                    input.setAttribute('autocorrect', 'off');
                    input.setAttribute('autocapitalize', 'off');
                    input.setAttribute('spellcheck', 'false');
                    input.setAttribute('data-form-type', 'other');
                    input.setAttribute('data-lpignore', 'true');
                    input.setAttribute('data-1p-ignore', 'true');
                    input.focus();
                },
                preConfirm: () => {
                    const code = document.getElementById('deleteCode').value;
                    if (!code) {
                        Swal.showValidationMessage('กรุณากรอกรหัสลบ');
                        return false;
                    }
                    if (!/^\d{6}$/.test(code)) {
                        Swal.showValidationMessage('รหัสลบต้องเป็นตัวเลข 6 หลัก');
                        return false;
                    }
                    return code;
                }
            });

            if (!deleteCode) return;

            try {
                const response = await fetch(`{{ url('/admin/reports/bets') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        delete_code: deleteCode
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        title: 'ลบแล้ว!',
                        text: data.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    const section = document.getElementById('betsHistorySection');
                    if (section) {
                        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }

                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    Swal.fire('ผิดพลาด!', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('ผิดพลาด!', 'เกิดข้อผิดพลาดในการลบข้อมูล', 'error');
            }
        }

        // ข้อมูลจาก Controller
        const data2Top = {!! json_encode($twoTopHeatmapData) !!};
        const data2Bottom = {!! json_encode($twoBottomHeatmapData) !!};
        const data3Top = {!! json_encode($threeTopHeatmapData) !!};
        const data3Toad = {!! json_encode($threeToadHeatmapData) !!};
        const max2Top = {{ $maxTwoTop > 0 ? $maxTwoTop : 1 }};
        const max2Bottom = {{ $maxTwoBottom > 0 ? $maxTwoBottom : 1 }};
        const max3Top = {{ $maxThreeTop > 0 ? $maxThreeTop : 1 }};
        const max3Toad = {{ $maxThreeToad > 0 ? $maxThreeToad : 1 }};
        const maxPayout2D = {{ $settings['max_payout_2_digit'] }};
        const maxPayout3D = {{ $settings['max_payout_3_digit'] }};

        const HEAT_COLORS = ['#f1f5f9', '#86efac', '#fde047', '#fb923c', '#ef4444'];

        function make2DOption(data, maxVal, maxPayout, textColor) {
            return {
                tooltip: {
                    position: 'top',
                    formatter: function (params) {
                        const x = params.data[0], y = params.data[1];
                        const num = String(y * 10 + x).padStart(2, '0');
                        const val = params.data[2], cnt = params.data[3], amt = params.data[4] || 0;
                        if (val === 0 && cnt === 0) return `เลข <strong>${num}</strong><br/>ยังไม่มีคนซื้อ`;
                        const pct = ((val / maxPayout) * 100).toFixed(1);
                        return `เลข <strong>${num}</strong><br/>` +
                            `ยอดแทง: <strong>${amt.toLocaleString('th-TH')}</strong> บาท<br/>` +
                            `ยอดจ่าย: <strong>${val.toLocaleString('th-TH')}</strong> บาท<br/>` +
                            `จำนวนใบ: <strong>${cnt}</strong> ใบ<br/>` +
                            `เปอร์เซ็นต์: <strong>${pct}%</strong>`;
                    }
                },
                grid: { height: '80%', top: '5%', left: '4%', right: '13%', containLabel: true },
                xAxis: { type: 'category', data: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], splitArea: { show: true }, axisLabel: { color: textColor, fontSize: 11, fontWeight: 'bold' } },
                yAxis: {
                    type: 'category',
                    data: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                    splitArea: { show: true },
                    axisLabel: { color: textColor, fontSize: 11, fontWeight: 'bold' },
                    inverse: true   // ← 00 เริ่มซ้ายบน
                },
                visualMap: { type: 'continuous', min: 0, max: Math.max(maxVal, maxPayout), dimension: 2, calculable: true, orient: 'vertical', right: '0%', top: 'center', inRange: { color: HEAT_COLORS }, text: ['🛑', '⬜'], textStyle: { color: textColor, fontSize: 10 } },
                series: [{
                    name: 'Liability',
                    type: 'heatmap',
                    data: data,
                    label: {
                        show: true,
                        rich: {
                            num: { fontSize: 11, fontWeight: 'bold', color: '#000', lineHeight: 14 },
                            amt: { fontSize: 8, fontWeight: 'normal', color: '#374151', lineHeight: 11 }
                        },
                        formatter: function (params) {
                            const x = params.data[0], y = params.data[1];
                            const num = String(y * 10 + x).padStart(2, '0');
                            const amt = params.data[4] || 0;
                            if (amt === 0) return `{num|${num}}`;
                            const amtStr = amt >= 1000 ? (amt / 1000).toFixed(1) + 'k' : String(amt);
                            return `{num|${num}}\n{amt|${amtStr}}`;
                        }
                    },
                    emphasis: { itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0,0,0,0.5)' } }
                }]
            };
        }

        function make3DOption(data, maxVal, maxPayout, textColor) {
            const xL = Array.from({ length: 40 }, (_, i) => i);
            const yL = Array.from({ length: 25 }, (_, i) => i);
            return {
                tooltip: {
                    position: 'top',
                    formatter: function (params) {
                        const x = params.data[0], y = params.data[1];
                        const idx = y * 40 + x;
                        if (idx > 999) return '';
                        const num = String(idx).padStart(3, '0');
                        const val = params.data[2], cnt = params.data[3], amt = params.data[4] || 0;
                        if (val === 0 && cnt === 0) return `เลข <strong>${num}</strong><br/>ยังไม่มีคนซื้อ`;
                        const pct = ((val / maxPayout) * 100).toFixed(1);
                        return `เลข <strong>${num}</strong><br/>` +
                            `ยอดแทง: <strong>${amt.toLocaleString('th-TH')}</strong> บาท<br/>` +
                            `ยอดจ่าย: <strong>${val.toLocaleString('th-TH')}</strong> บาท<br/>` +
                            `จำนวนใบ: <strong>${cnt}</strong> ใบ<br/>` +
                            `เปอร์เซ็นต์: <strong>${pct}%</strong>`;
                    }
                },
                grid: { height: '85%', top: '5%', left: '2%', right: '12%', containLabel: true },
                xAxis: { type: 'category', data: xL, splitArea: { show: true }, axisLabel: { show: false } },
                yAxis: {
                    type: 'category',
                    data: yL,
                    splitArea: { show: true },
                    axisLabel: { show: false },
                    inverse: true   // ← 000 เริ่มซ้ายบน
                },
                visualMap: { min: 0, max: Math.max(maxVal, maxPayout), dimension: 2, calculable: true, orient: 'vertical', right: '0%', top: 'center', inRange: { color: HEAT_COLORS }, text: ['🛑', '⬜'], textStyle: { color: textColor, fontSize: 10 } },
                series: [{
                    name: 'Liability',
                    type: 'heatmap',
                    data: data,
                    label: {
                        show: true,
                        formatter: function (params) {
                            const i = params.data[1] * 40 + params.data[0];
                            return i <= 999 ? String(i).padStart(3, '0') : '';
                        },
                        fontSize: 7,
                        fontWeight: 'normal',
                        color: '#1e293b'
                    },
                    emphasis: { itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0,0,0,0.5)' } }
                }]
            };
        }

        let charts = [];
        function renderCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#e2e8f0' : '#1e293b';

            charts.forEach(c => c.dispose());
            charts = [];

            const c2Top = echarts.init(document.getElementById('heatmap2Top'));
            const c2Bottom = echarts.init(document.getElementById('heatmap2Bottom'));
            const c3Top = echarts.init(document.getElementById('heatmap3Top'));
            const c3Toad = echarts.init(document.getElementById('heatmap3Toad'));

            c2Top.setOption(make2DOption(data2Top, max2Top, maxPayout2D, textColor));
            c2Bottom.setOption(make2DOption(data2Bottom, max2Bottom, maxPayout2D, textColor));
            c3Top.setOption(make3DOption(data3Top, max3Top, maxPayout3D, textColor));
            c3Toad.setOption(make3DOption(data3Toad, max3Toad, maxPayout3D, textColor));

            charts = [c2Top, c2Bottom, c3Top, c3Toad];

            window.addEventListener('resize', function () {
                charts.forEach(c => c.resize());
            });
        }

        function exportSummaryExcel() {
            const params = new URLSearchParams(window.location.search);
            const url = '{{ route("admin.reports.export-excel", $draw->id) }}?' + params.toString();
            const a = document.createElement('a');
            a.href = url;
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function exportCustomerSummaryExcel() {
            const url = '{{ route("admin.reports.export-customer-summary", $draw->id) }}';
            const a = document.createElement('a');
            a.href = url;
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
        renderCharts();
    </script>

    @if(!$draw->is_announced)
        <!-- Floating: แทงหวย -->
        <a href="{{ route('bets.index') }}" id="floatBet"
            style="position:fixed;bottom:30px;left:30px;z-index:1000;display:flex;align-items:center;gap:8px;padding:12px 20px;background:#10b981;color:white;font-weight:700;border-radius:9999px;box-shadow:0 8px 24px rgba(16,185,129,0.4);transition:all 0.3s ease;text-decoration:none;font-size:14px;"
            onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 32px rgba(16,185,129,0.5)'"
            onmouseout="this.style.transform='';this.style.boxShadow='0 8px 24px rgba(16,185,129,0.4)'">
            🎯 แทงหวย
        </a>
    @endif

    <!-- Back to Top Button -->
    <div id="backToTop">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </div>

    <script>
        // Back to Top Button
        const backToTop = document.getElementById('backToTop');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>

</body>

</html>