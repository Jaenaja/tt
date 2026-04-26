<!DOCTYPE html>
<html lang="th" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>สรุปผลงวด {{ \Carbon\Carbon::parse($draw->draw_date)->locale('th')->translatedFormat('j F Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={darkMode:'class'}</script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script>
        if(localStorage.theme==='dark'||(!('theme' in localStorage)&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}
        else{document.documentElement.classList.remove('dark');}
    </script>
    <style>
        *{font-family:'Sarabun',sans-serif;}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .animate-fade-in-up{animation:fadeInUp 0.6s ease-out;}
        .premium-card{position:relative;overflow:hidden;transition:all 0.3s ease;}
        .premium-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#10b981 0%,#059669 100%);opacity:0;transition:opacity 0.3s ease;}
        .premium-card:hover::before{opacity:1;}
        .rotate-icon{transition:transform 0.3s ease;}
        .rotate-icon.active{transform:rotate(180deg);}
        #backToTop{position:fixed;bottom:30px;right:30px;width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.1);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.2);box-shadow:0 8px 32px 0 rgba(0,0,0,0.37);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.3s ease;opacity:0;visibility:hidden;z-index:1000;}
        #backToTop.show{opacity:1;visibility:visible;}
        .dark #backToTop{background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);}
        #backToTop svg{width:24px;height:24px;color:#10b981;}
        @media print{.no-print{display:none!important}}
        .select2-container--default .select2-selection--multiple{background-color:rgb(248 250 252)!important;border:1px solid rgb(203 213 225)!important;border-radius:0.5rem!important;padding:0.5rem!important;min-height:42px!important;}
        .dark .select2-container--default .select2-selection--multiple{background-color:rgb(30 41 59)!important;border-color:rgb(51 65 85)!important;}
        .select2-container--default .select2-selection--multiple .select2-selection__choice{background-color:rgb(16 185 129)!important;color:white!important;border:none!important;border-radius:0.375rem!important;padding:4px 8px!important;margin:2px!important;}
        .select2-dropdown{background-color:white!important;border:1px solid rgb(203 213 225)!important;border-radius:0.5rem!important;}
        .dark .select2-dropdown{background-color:rgb(30 41 59)!important;border-color:rgb(51 65 85)!important;}
        .select2-results__option{padding:8px 12px!important;color:rgb(30 41 59)!important;}
        .dark .select2-results__option{color:white!important;}
        .select2-results__option--highlighted{background-color:rgb(16 185 129)!important;color:white!important;}
    </style>
</head>
<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
<div class="container mx-auto px-4 py-8">

    {{-- Breadcrumbs --}}
    <nav class="mb-6 text-sm text-slate-600 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400">หน้าหลัก</a>
        <span class="mx-2">›</span>
        <a href="{{ route('admin.reports.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400">จัดการความเสี่ยง</a>
        <span class="mx-2">›</span>
        <span class="text-slate-900 dark:text-white font-semibold">สรุปผลรวม</span>
    </nav>

    {{-- Header --}}
    <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-8 mb-8 animate-fade-in-up border border-slate-200 dark:border-slate-800">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2">🎰 สรุปผลงวด</h1>
                <p class="text-xl text-slate-800 dark:text-slate-300">วันที่ {{ \Carbon\Carbon::parse($draw->draw_date)->locale('th')->translatedFormat('j F Y') }}</p>
            </div>
            <div class="flex items-center gap-4">
                @if($draw->is_announced)
                    <span class="inline-block px-6 py-3 bg-emerald-500 text-white rounded-full font-bold text-lg shadow-lg">✓ ประกาศผลแล้ว</span>
                @else
                    <span class="inline-block px-6 py-3 bg-amber-500 text-white rounded-full font-bold text-lg shadow-lg">⏳ กำลังขาย</span>
                @endif
                <button id="themeToggle" class="no-print relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                </button>
                <button onclick="window.print()" class="no-print px-4 py-2 bg-slate-600 dark:bg-slate-700 text-white rounded-lg font-semibold hover:bg-slate-700 transition-colors">🖨️ พิมพ์</button>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="transition-all duration-300 premium-card bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-950/40 dark:to-purple-900/20 rounded-2xl shadow-lg p-6 border border-purple-200 dark:border-purple-800">
            <div class="flex items-center justify-between mb-4"><span class="text-4xl">📋</span><span class="text-sm text-slate-700 dark:text-slate-400">รายการทั้งหมด</span></div>
            <div class="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-1">{{ number_format($totalTransactions) }}</div>
            <div class="text-sm text-slate-700 dark:text-slate-400">รายการเดิมพัน</div>
        </div>
        <div class="transition-all duration-300 premium-card bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950/40 dark:to-blue-900/20 rounded-2xl shadow-lg p-6 border border-blue-200 dark:border-blue-800">
            <div class="flex items-center justify-between mb-4"><span class="text-4xl">💰</span><span class="text-sm text-slate-700 dark:text-slate-400">ยอดแทงรวม</span></div>
            <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-1">{{ number_format($totalSales, 0) }}</div>
            <div class="text-sm text-slate-700 dark:text-slate-400">บาท</div>
        </div>
        @if($draw->is_announced && $result)
        <div class="transition-all duration-300 premium-card bg-gradient-to-br {{ $result['net_profit']>=0 ? 'from-emerald-50 to-emerald-100 dark:from-emerald-950/40 dark:to-emerald-900/20 border-emerald-200 dark:border-emerald-800' : 'from-red-50 to-red-100 dark:from-red-950/40 dark:to-red-900/20 border-red-200 dark:border-red-800' }} rounded-2xl shadow-lg p-6 border">
            <div class="flex items-center justify-between mb-4"><span class="text-4xl">{{ $result['net_profit']>=0?'📈':'📉' }}</span><span class="text-sm text-slate-700 dark:text-slate-400">กำไรสุทธิ</span></div>
            <div class="text-4xl font-bold {{ $result['net_profit']>=0?'text-emerald-600 dark:text-emerald-400':'text-red-600 dark:text-red-400' }} mb-1">{{ number_format(abs($result['net_profit']),0) }}</div>
            <div class="text-sm text-slate-700 dark:text-slate-400">{{ $result['net_profit']>=0?'กำไร':'ขาดทุน' }} (หัก Com {{ number_format($settings['commission_rate'],0) }}%)</div>
        </div>
        <div class="transition-all duration-300 premium-card bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-950/40 dark:to-amber-900/20 rounded-2xl shadow-lg p-6 border border-amber-200 dark:border-amber-800">
            <div class="flex items-center justify-between mb-4"><span class="text-4xl">🏆</span><span class="text-sm text-slate-700 dark:text-slate-400">ผู้ถูกรางวัล</span></div>
            <div class="text-4xl font-bold text-amber-600 dark:text-amber-400 mb-1">{{ number_format($result['winners_count']) }}</div>
            <div class="text-sm text-slate-700 dark:text-slate-400">คน (Payout Ratio {{ number_format($result['payout_ratio'],1) }}%)</div>
        </div>
        @else
        <div class="col-span-2 transition-all duration-300 premium-card bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl shadow-lg p-6 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-center h-full text-center">
                <div><span class="text-6xl mb-4 block animate-pulse">⏳</span><div class="text-2xl font-bold text-slate-900 dark:text-white mb-2">รอประกาศผล</div><div class="text-sm text-slate-700 dark:text-slate-400">ข้อมูลรางวัลจะแสดงหลังประกาศผล</div></div>
            </div>
        </div>
        @endif
    </div>

    {{-- ผลรางวัลที่ออก --}}
    @if($draw->is_announced)
    <div class="transition-all duration-300 rounded-2xl shadow-xl p-8 mb-8 border bg-white border-emerald-200 dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 dark:border-emerald-500/30">
        <h2 class="text-3xl font-bold mb-6 flex items-center gap-3 text-emerald-600 dark:text-emerald-400"><span>🎉</span> รางวัลที่ออก</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="rounded-xl p-6 text-center border bg-violet-50 border-violet-200 dark:bg-white/5 dark:border-violet-500/20">
                <div class="text-sm font-semibold tracking-wider uppercase mb-2 text-violet-600 dark:text-violet-300/70">3 ตัวบน</div>
                <div class="text-5xl font-bold tracking-widest text-violet-600 dark:text-violet-400">{{ $draw->result_3_top ?? 'XXX' }}</div>
            </div>
            <div class="rounded-xl p-6 text-center border bg-emerald-50 border-emerald-200 dark:bg-white/5 dark:border-emerald-500/20">
                <div class="text-sm font-semibold tracking-wider uppercase mb-2 text-emerald-600 dark:text-emerald-300/70">2 ตัวบน</div>
                <div class="text-5xl font-bold tracking-widest text-emerald-600 dark:text-emerald-400">{{ $draw->result_2_top ?? 'XX' }}</div>
            </div>
            <div class="rounded-xl p-6 text-center border bg-sky-50 border-sky-200 dark:bg-white/5 dark:border-sky-500/20">
                <div class="text-sm font-semibold tracking-wider uppercase mb-2 text-sky-600 dark:text-sky-300/70">2 ตัวล่าง</div>
                <div class="text-5xl font-bold tracking-widest text-sky-600 dark:text-sky-400">{{ $draw->result_2_bottom ?? 'XX' }}</div>
            </div>
            <div class="rounded-xl p-6 text-center border bg-orange-50 border-orange-200 dark:bg-white/5 dark:border-orange-500/20">
                <div class="text-sm font-semibold tracking-wider uppercase mb-2 text-orange-600 dark:text-orange-300/70">3 ตัวล่าง (4 เลข)</div>
                @if($draw->result_3_bottom)
                    <div class="flex flex-wrap gap-1 justify-center mt-1">
                        @foreach($draw->result_3_bottom_array as $n3b)
                            <span class="text-2xl font-bold text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/30 rounded-lg px-2 py-1">{{ $n3b }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="text-2xl font-bold text-slate-400 dark:text-slate-600 mt-2">-</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Sales Summary Blocks --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-5 border border-violet-200 dark:border-violet-800">
            <div class="flex items-center justify-between mb-3"><span class="text-3xl">🟣</span><span class="text-xs font-bold px-2 py-1 bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 rounded-full">3 ตัวบน</span></div>
            <div class="text-3xl font-bold text-violet-600 dark:text-violet-400 mb-1">{{ number_format($sales['three_top']['total'],0) }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">฿ | {{ number_format($sales['three_top']['count']) }} รายการ</div>
        </div>
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-5 border border-amber-200 dark:border-amber-800">
            <div class="flex items-center justify-between mb-3"><span class="text-3xl">🟡</span><span class="text-xs font-bold px-2 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded-full">3 ตัวโต๊ด</span></div>
            <div class="text-3xl font-bold text-amber-600 dark:text-amber-400 mb-1">{{ number_format($sales['three_toad']['total'],0) }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">฿ | {{ number_format($sales['three_toad']['count']) }} รายการ</div>
        </div>
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-5 border border-orange-200 dark:border-orange-800">
            <div class="flex items-center justify-between mb-3"><span class="text-3xl">🟠</span><span class="text-xs font-bold px-2 py-1 bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 rounded-full">3 ตัวล่าง</span></div>
            <div class="text-3xl font-bold text-orange-600 dark:text-orange-400 mb-1">{{ number_format($sales['three_bottom']['total'],0) }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">฿ | {{ number_format($sales['three_bottom']['count']) }} รายการ</div>
        </div>
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-5 border border-emerald-200 dark:border-emerald-800">
            <div class="flex items-center justify-between mb-3"><span class="text-3xl">🟢</span><span class="text-xs font-bold px-2 py-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-full">2 ตัวบน</span></div>
            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mb-1">{{ number_format($sales['two_top']['total'],0) }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">฿ | {{ number_format($sales['two_top']['count']) }} รายการ</div>
        </div>
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-5 border border-sky-200 dark:border-sky-800">
            <div class="flex items-center justify-between mb-3"><span class="text-3xl">🔵</span><span class="text-xs font-bold px-2 py-1 bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 rounded-full">2 ตัวล่าง</span></div>
            <div class="text-3xl font-bold text-sky-600 dark:text-sky-400 mb-1">{{ number_format($sales['two_bottom']['total'],0) }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">฿ | {{ number_format($sales['two_bottom']['count']) }} รายการ</div>
        </div>
    </div>

    {{-- Heatmap Section --}}
    <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 mb-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-1 flex items-center gap-2"><span>🔥</span> Heatmap วิเคราะห์ความเสี่ยง</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">
            เพดาน: 2 ตัว = {{ number_format($settings['max_payout_2_digit'],0) }} ฿ |
            3 ตัวตรง = {{ number_format($settings['max_payout_3_digit'],0) }} ฿ |
            3 ตัวโต๊ด = {{ number_format($settings['max_payout_3_toad'],0) }} ฿ |
            3 ตัวล่าง = {{ number_format($settings['max_payout_3_bottom'],0) }} ฿
        </p>

        {{-- 2 ตัว --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <h3 class="text-sm font-bold text-emerald-700 dark:text-emerald-300 mb-2">🟢 2 ตัวบน (00-99)</h3>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-2"><div id="heatmap2Top" style="width:100%;height:320px;"></div></div>
            </div>
            <div>
                <h3 class="text-sm font-bold text-sky-700 dark:text-sky-300 mb-2">🔵 2 ตัวล่าง (00-99)</h3>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-2"><div id="heatmap2Bottom" style="width:100%;height:320px;"></div></div>
            </div>
        </div>

        {{-- 3 ตัว --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <h3 class="text-sm font-bold text-violet-700 dark:text-violet-300 mb-2">🟣 3 ตัวบน (000-999)</h3>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-2"><div id="heatmap3Top" style="width:100%;height:480px;"></div></div>
            </div>
            <div>
                <h3 class="text-sm font-bold text-amber-700 dark:text-amber-300 mb-2">🟡 3 ตัวโต๊ด (000-999)</h3>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-2"><div id="heatmap3Toad" style="width:100%;height:480px;"></div></div>
            </div>
            <div>
                <h3 class="text-sm font-bold text-orange-700 dark:text-orange-300 mb-2">🟠 3 ตัวล่าง (000-999)</h3>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-2"><div id="heatmap3Bottom" style="width:100%;height:480px;"></div></div>
            </div>
        </div>

        <div class="flex items-center gap-4 mt-4 text-xs flex-wrap">
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-slate-300 dark:bg-slate-600 rounded inline-block"></span>ไม่มีคนซื้อ</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-emerald-400 rounded inline-block"></span>ปกติ (&lt;50%)</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-orange-400 rounded inline-block"></span>เฝ้าระวัง (50-99%)</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-500 rounded inline-block"></span>อั้น/ตัดยอด (≥100%)</span>
        </div>
    </div>

    {{-- Top 10 Exposure (5 ประเภท) --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        @foreach([
            ['label'=>'🟣 Top 10 (3 ตัวบน)', 'color'=>'violet', 'data'=>$topThreeTopExposure],
            ['label'=>'🟡 Top 10 (3 ตัวโต๊ด)', 'color'=>'amber', 'data'=>$topThreeToadExposure],
            ['label'=>'🟠 Top 10 (3 ตัวล่าง)', 'color'=>'orange', 'data'=>$topThreeBottomExposure],
            ['label'=>'🟢 Top 10 (2 ตัวบน)', 'color'=>'emerald', 'data'=>$topTwoTopExposure],
            ['label'=>'🔵 Top 10 (2 ตัวล่าง)', 'color'=>'sky', 'data'=>$topTwoBottomExposure],
        ] as $section)
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-xl p-4 shadow-lg dark:shadow-2xl border border-{{ $section['color'] }}-200 dark:border-{{ $section['color'] }}-800">
            <h2 class="text-sm font-bold text-{{ $section['color'] }}-700 dark:text-{{ $section['color'] }}-300 mb-3">{{ $section['label'] }}</h2>
            <div class="space-y-1">
                @foreach($section['data'] as $index => $item)
                <div class="flex items-center justify-between px-2 py-1 rounded bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs w-4 h-4 flex items-center justify-center rounded-full font-bold {{ $index < 3 ? 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">{{ $index+1 }}</span>
                        <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $item['number'] }}</span>
                        <span class="text-xs text-slate-400">({{ $item['bet_count'] }})</span>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold {{ $item['status']==='critical'?'text-red-500':($item['status']==='warning'?'text-amber-500':'text-emerald-500') }}">{{ number_format($item['liability'],0) }}฿</div>
                        <div class="text-xs text-blue-500 dark:text-blue-400">แทง {{ number_format($item['total_amount'],0) }}฿</div>
                        <div class="text-xs text-slate-400">{{ number_format($item['percentage'],0) }}%</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- เลขเกิน 100% --}}
    @php
        $totalOver = count($overLimit2Top)+count($overLimit2Bottom)+count($overLimit3Top)+count($overLimit3Toad)+count($overLimit3Bottom);
    @endphp
    <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-5 mb-8 shadow-xl dark:shadow-2xl border {{ $totalOver>0?'border-red-300 dark:border-red-700':'border-slate-200 dark:border-slate-800' }}">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-bold {{ $totalOver>0?'text-red-600 dark:text-red-400':'text-slate-700 dark:text-slate-300' }} flex items-center gap-2">🛑 เลขเกิน 100% (อั้น/ตัดยอด)</h2>
                @if($totalOver>0)
                    <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300 rounded-full text-xs font-bold">{{ $totalOver }} เลข</span>
                @else
                    <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 rounded-full text-xs font-bold">✅ ไม่มีเลขเกินเพดาน</span>
                @endif
            </div>
            <a href="{{ route('admin.reports.export-over-limit', $draw->id) }}" class="no-print inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold text-sm transition-colors shadow">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" fill="white" fill-opacity="0.25"/><text x="12" y="16" text-anchor="middle" fill="white" font-size="11" font-weight="bold" font-family="Arial">X</text></svg>
                ดาวน์โหลด Excel เลขเกิน 100%
            </a>
        </div>
        @if($totalOver>0)
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach([
                ['label'=>'🟣 3 ตัวบน','color'=>'violet','data'=>$overLimit3Top],
                ['label'=>'🟡 3 ตัวโต๊ด','color'=>'amber','data'=>$overLimit3Toad],
                ['label'=>'🟠 3 ตัวล่าง','color'=>'orange','data'=>$overLimit3Bottom],
                ['label'=>'🟢 2 ตัวบน','color'=>'emerald','data'=>$overLimit2Top],
                ['label'=>'🔵 2 ตัวล่าง','color'=>'sky','data'=>$overLimit2Bottom],
            ] as $sec)
            <div>
                <div class="text-xs font-bold text-{{ $sec['color'] }}-600 dark:text-{{ $sec['color'] }}-400 mb-1.5 flex items-center gap-1">
                    {{ $sec['label'] }}
                    <span class="bg-{{ $sec['color'] }}-100 dark:bg-{{ $sec['color'] }}-900/40 text-{{ $sec['color'] }}-700 dark:text-{{ $sec['color'] }}-300 px-1.5 py-0.5 rounded-full">{{ count($sec['data']) }}</span>
                </div>
                @if(count($sec['data'])>0)
                <div class="space-y-1 max-h-48 overflow-y-auto">
                    @foreach($sec['data'] as $item)
                    <div class="flex items-center justify-between px-2 py-1 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800 text-xs">
                        <span class="font-bold text-slate-900 dark:text-white">{{ $item['number'] }}</span>
                        <div class="text-right">
                            <div class="font-bold text-red-600 dark:text-red-400">{{ number_format($item['liability'],0) }}฿</div>
                            <div class="text-red-500 dark:text-red-400">{{ number_format($item['percentage'],1) }}%</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-slate-400 italic">ไม่มี</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- สรุปผลงานรายบุคคล --}}
    @if($draw->is_announced && count($customerSummary)>0)
    <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800 mb-8"
         x-data="{ searchCustomer: '' }">
        <div class="flex items-start justify-between mb-1">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2"><span>💰</span> สรุปผลงานรายบุคคล</h2>
            <button onclick="exportCustomerSummaryExcel()" class="no-print px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg font-semibold text-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" fill="white" fill-opacity="0.25"/><text x="12" y="16" text-anchor="middle" fill="white" font-size="11" font-weight="bold" font-family="Arial">X</text></svg>
                Export Excel
            </button>
        </div>
        <p class="text-xs text-slate-600 dark:text-slate-400 mb-4">ส่วนลด {{ number_format($settings['commission_rate'],0) }}%</p>
        <div class="mb-4">
            <input type="text" x-model="searchCustomer" placeholder="🔍 ค้นหาชื่อลูกค้า..."
                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white text-sm">
        </div>
        <div class="space-y-2">
            @foreach($customerSummary as $summary)
            <div x-show="searchCustomer===''||'{{ strtolower($summary['customer_name']) }}'.includes(searchCustomer.toLowerCase())"
                 x-data="{open:false}" class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <button @click="open=!open" class="w-full p-3 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-750 transition-colors flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 rotate-icon" :class="{'active':open}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        <div class="text-left">
                            <div class="font-bold text-base text-slate-900 dark:text-white">{{ $summary['customer_name'] }}</div>
                            <div class="text-xs text-slate-600 dark:text-slate-400">{{ $summary['created_by'] }} | {{ \Carbon\Carbon::parse($summary['created_at'])->format('d/m H:i') }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($summary['net_amount']<0)
                            <div class="text-xl font-bold text-red-600 dark:text-red-400">จ่าย {{ number_format(abs($summary['net_amount']),0) }}</div>
                        @else
                            <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">รับ {{ number_format($summary['net_amount'],0) }}</div>
                        @endif
                        <div class="text-xs text-slate-600 dark:text-slate-400">{{ count($summary['winning_numbers']) }} เลขถูก</div>
                    </div>
                </button>
                <div x-show="open" x-transition class="p-3 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700">
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800"><div class="text-xs text-slate-700 dark:text-slate-400">ยอดซื้อ</div><div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($summary['total_bet_before_discount'],0) }}</div></div>
                        <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded border border-amber-200 dark:border-amber-800"><div class="text-xs text-slate-700 dark:text-slate-400">ส่วนลด</div><div class="text-lg font-bold text-amber-600 dark:text-amber-400">-{{ number_format($summary['discount'],0) }}</div></div>
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded border border-emerald-200 dark:border-emerald-800"><div class="text-xs text-slate-700 dark:text-slate-400">หลังหัก</div><div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($summary['total_bet_after_discount'],0) }}</div></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800"><div class="text-xs text-slate-700 dark:text-slate-400">รางวัล</div><div class="text-lg font-bold text-red-600 dark:text-red-400">{{ number_format($summary['total_payout'],0) }}</div></div>
                        <div class="p-2 bg-{{ $summary['net_amount']<0?'red':'emerald' }}-50 dark:bg-{{ $summary['net_amount']<0?'red':'emerald' }}-900/20 rounded border border-{{ $summary['net_amount']<0?'red':'emerald' }}-200 dark:border-{{ $summary['net_amount']<0?'red':'emerald' }}-800">
                            <div class="text-xs text-slate-700 dark:text-slate-400">สุทธิ</div>
                            <div class="text-lg font-bold text-{{ $summary['net_amount']<0?'red':'emerald' }}-600 dark:text-{{ $summary['net_amount']<0?'red':'emerald' }}-400">{{ $summary['net_amount']<0?'จ่าย':'รับ' }} {{ number_format(abs($summary['net_amount']),0) }}</div>
                        </div>
                    </div>
                    @if(count($summary['winning_numbers'])>0)
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-2">🏆 เลขที่ถูก</h4>
                        <div class="space-y-1">
                            @foreach($summary['winning_numbers'] as $win)
                            <div class="flex items-center justify-between p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded border border-emerald-200 dark:border-emerald-800">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $win['number'] }}</span>
                                    <span class="text-xs text-slate-700 dark:text-slate-300">({{ $win['win_type'] }})</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-600 dark:text-slate-400">แทง {{ number_format($win['bet_amount'],0) }}</div>
                                    <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($win['payout'],0) }} ฿</div>
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

    {{-- ประวัติการแทง --}}
    <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800 mb-8" id="bets-table">
        <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2"><span>📝</span> ประวัติการแทงของงวดนี้</h2>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('admin.reports.summary', $draw->id) }}#bets-table" class="no-print mb-6" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">ชื่อลูกค้า</label>
                    <select name="customer_names[]" id="customerSelect" multiple="multiple" class="w-full">
                        @foreach($customerNames as $name)
                        <option value="{{ $name }}" {{ in_array($name, request('customer_names',[])) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">ค้นหาเลข</label>
                    <input type="text" name="search_number" value="{{ request('search_number') }}" placeholder="เช่น 123, 45"
                        class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white">
                </div>
                @if($draw->is_announced)
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">สถานะรางวัล</label>
                    <select name="win_status" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white">
                        <option value="">ทั้งหมด</option>
                        <option value="won" {{ request('win_status')==='won'?'selected':'' }}>ถูกรางวัล</option>
                        <option value="lost" {{ request('win_status')==='lost'?'selected':'' }}>ไม่ถูกรางวัล</option>
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">ประเภทเลข</label>
                    <select name="number_type" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white">
                        <option value="">ทั้งหมด</option>
                        <option value="2_top"    {{ request('number_type')==='2_top'   ?'selected':'' }}>2 ตัวบน</option>
                        <option value="2_bottom" {{ request('number_type')==='2_bottom'?'selected':'' }}>2 ตัวล่าง</option>
                        <option value="3_top"    {{ request('number_type')==='3_top'   ?'selected':'' }}>3 ตัวบน</option>
                        <option value="3_toad"   {{ request('number_type')==='3_toad'  ?'selected':'' }}>3 ตัวโต๊ด</option>
                        <option value="3_bottom" {{ request('number_type')==='3_bottom'?'selected':'' }}>3 ตัวล่าง</option>
                        <option value="2_digit"  {{ request('number_type')==='2_digit' ?'selected':'' }}>2 ตัว (ทั้งหมด)</option>
                        <option value="3_digit"  {{ request('number_type')==='3_digit' ?'selected':'' }}>3 ตัว (ทั้งหมด)</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition-colors">🔍 ค้นหา</button>
                <a href="{{ route('admin.reports.summary', $draw->id) }}" class="px-6 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-semibold transition-colors">ล้างค่า</a>
                <button type="button" onclick="exportSummaryExcel()" class="no-print px-6 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg font-semibold transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" fill="white" fill-opacity="0.25"/><text x="12" y="16" text-anchor="middle" fill="white" font-size="11" font-weight="bold" font-family="Arial">X</text></svg>
                    Export Excel
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-100 dark:bg-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold">ชื่อลูกค้า</th>
                        <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold">เลข</th>
                        <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">บน</th>
                        <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">ล่าง</th>
                        <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">โต๊ด</th>
                        <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold text-orange-600 dark:text-orange-400">3ตัวล่าง</th>
                        <th class="px-4 py-3 text-right text-slate-700 dark:text-slate-300 font-semibold">รวม</th>
                        <th class="px-4 py-3 text-left text-slate-700 dark:text-slate-300 font-semibold text-xs">บันทึกเมื่อ</th>
                        @if($draw->is_announced)
                        <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold">ผล</th>
                        @else
                        <th class="px-4 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold no-print">จัดการ</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($betsHistory as $bet)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" data-bet-id="{{ $bet->id }}">
                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $bet->customer_name }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xl font-bold {{ strlen($bet->number)===2?'text-emerald-600 dark:text-emerald-400':'text-violet-600 dark:text-violet-400' }}">{{ $bet->number }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-200">{{ $bet->amount_top > 0 ? number_format($bet->amount_top,0) : '-' }}</td>
                        <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-200">{{ $bet->amount_bottom > 0 ? number_format($bet->amount_bottom,0) : '-' }}</td>
                        <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-200">{{ $bet->amount_toad > 0 ? number_format($bet->amount_toad,0) : '-' }}</td>
                        <td class="px-4 py-3 text-right text-orange-600 dark:text-orange-400 font-medium">{{ ($bet->amount_bottom_3??0) > 0 ? number_format($bet->amount_bottom_3,0) : '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-600 dark:text-blue-400">{{ number_format($bet->total_amount,0) }} ฿</td>
                        <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-400">
                            <div>{{ $bet->creator ? $bet->creator->name : '-' }}</div>
                            <div>{{ \Carbon\Carbon::parse($bet->created_at)->format('d/m/y H:i') }}</div>
                        </td>
                        @if($draw->is_announced)
                        <td class="px-4 py-3 text-center">
                            @if($bet->is_win_top||$bet->is_win_bottom||$bet->is_win_toad||($bet->is_win_bottom_3??false))
                                <span class="inline-block px-3 py-1 bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded-full text-sm font-bold">
                                    ✅ {{ number_format($bet->payout_top+$bet->payout_bottom+$bet->payout_toad+($bet->payout_bottom_3??0),0) }} ฿
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-full text-sm">ไม่ถูก</span>
                            @endif
                        </td>
                        @else
                        <td class="px-4 py-3 text-center no-print">
                            <button onclick="deleteBet({{ $bet->id }},'{{ $bet->customer_name }}','{{ $bet->number }}')"
                                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-semibold transition-colors">🗑️ ลบ</button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">ไม่มีข้อมูลการแทงที่ตรงกับเงื่อนไข</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($betsHistory->hasPages())
        <div class="no-print mt-6">{{ $betsHistory->appends(request()->query())->links() }}</div>
        @endif
    </div>

    <div class="text-center no-print">
        <a href="{{ route('admin.reports.index') }}" class="inline-block px-8 py-3 bg-slate-600 dark:bg-slate-700 text-white rounded-lg font-semibold hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors shadow-lg">← กลับหน้ารายการงวด</a>
    </div>
</div>

{{-- Back to Top --}}
<button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
</button>

<script>
// ── Theme Toggle ──
document.getElementById('themeToggle').addEventListener('click',()=>{
    if(document.documentElement.classList.contains('dark')){document.documentElement.classList.remove('dark');localStorage.theme='light';}
    else{document.documentElement.classList.add('dark');localStorage.theme='dark';}
    setTimeout(renderCharts,300);
});

// ── Back to Top ──
window.addEventListener('scroll',()=>{
    document.getElementById('backToTop').classList.toggle('show',window.scrollY>300);
});

// auto-scroll ลงตารางถ้า URL มี #bets-table (หลัง search/filter)
if (window.location.hash === '#bets-table') {
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('bets-table');
        if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
    });
}

// ── Heatmap Data from PHP ──
const data2Top    = {!! json_encode($twoTopHeatmapData) !!};
const data2Bottom = {!! json_encode($twoBottomHeatmapData) !!};
const data3Top    = {!! json_encode($threeTopHeatmapData) !!};
const data3Toad   = {!! json_encode($threeToadHeatmapData) !!};
const data3Bottom = {!! json_encode($threeBottomHeatmapData) !!};

const max2Top    = {{ $maxTwoTop    > 0 ? $maxTwoTop    : 1 }};
const max2Bottom = {{ $maxTwoBottom > 0 ? $maxTwoBottom : 1 }};
const max3Top    = {{ $maxThreeTop  > 0 ? $maxThreeTop  : 1 }};
const max3Toad   = {{ $maxThreeToad > 0 ? $maxThreeToad : 1 }};
const max3Bottom = {{ $maxThreeBottom > 0 ? $maxThreeBottom : 1 }};

const maxPayout2D     = {{ $settings['max_payout_2_digit'] }};
const maxPayout3D     = {{ $settings['max_payout_3_digit'] }};
const maxPayout3Toad  = {{ $settings['max_payout_3_toad'] }};
const maxPayout3Bot   = {{ $settings['max_payout_3_bottom'] }};

// ── Color logic ──
function getRiskColor(liability, maxPayout){
    if(liability<=0) return '#334155';
    const pct=liability/maxPayout*100;
    if(pct>=100) return '#ef4444';
    if(pct>=50)  return '#f97316';
    return '#10b981';
}

function make2DOption(data, maxVal, maxPayout, textColor){
    const xLabels=[]; for(let i=0;i<10;i++) xLabels.push(String(i));
    const yLabels=[]; for(let i=0;i<10;i++) yLabels.push(String(i));
    const visualData = data.map(d=>{
        const liability=d[2],betCount=d[3],amount=d[4];
        return {value:[d[0],d[1],liability],betCount,amount,
            itemStyle:{color:getRiskColor(liability,maxPayout)}};
    });
    return {
        tooltip:{trigger:'item',formatter:p=>{
            const d=p.data;
            const num=String(p.data.value[1])+String(p.data.value[0]);
            const pct=(d.value[2]/maxPayout*100).toFixed(1);
            return `<b>เลข ${num}</b><br/>จำนวนใบ: ${d.betCount}<br/>ยอดซื้อ: ${d.amount.toLocaleString()}<br/>ยอดจ่าย: ${Math.round(d.value[2]).toLocaleString()}<br/><b>%เพดาน: ${pct}%</b>`;
        }},
        grid:{top:30,bottom:30,left:30,right:30},
        xAxis:{type:'category',data:xLabels,splitArea:{show:true},axisLabel:{color:textColor,fontSize:10}},
        yAxis:{type:'category',data:yLabels,splitArea:{show:true},axisLabel:{color:textColor,fontSize:10}},
        series:[{type:'heatmap',data:visualData,label:{show:true,formatter:p=>p.data.value[2]>0?p.data.value[1]+''+p.data.value[0]:'',fontSize:8,color:'#fff'},emphasis:{itemStyle:{shadowBlur:10,shadowColor:'rgba(0,0,0,0.5)'}}}]
    };
}

function make3DOption(data, maxVal, maxPayout, textColor){
    const xLabels=[]; for(let i=0;i<40;i++) xLabels.push(i%10===0?String(i):'');
    const yLabels=[]; for(let i=0;i<25;i++) yLabels.push(String(i*40));
    const visualData = data.map(d=>{
        const liability=d[2],betCount=d[3],amount=d[4];
        return {value:[d[0],d[1],liability],betCount,amount,
            itemStyle:{color:getRiskColor(liability,maxPayout)}};
    });
    return {
        tooltip:{trigger:'item',formatter:p=>{
            const d=p.data;
            const num=String(p.data.value[1]*40+p.data.value[0]).padStart(3,'0');
            const pct=(d.value[2]/maxPayout*100).toFixed(1);
            return `<b>เลข ${num}</b><br/>จำนวนใบ: ${d.betCount}<br/>ยอดซื้อ: ${d.amount.toLocaleString()}<br/>ยอดจ่าย: ${Math.round(d.value[2]).toLocaleString()}<br/><b>%เพดาน: ${pct}%</b>`;
        }},
        grid:{top:10,bottom:20,left:40,right:10},
        xAxis:{type:'category',data:xLabels,splitArea:{show:true},axisLabel:{color:textColor,fontSize:8}},
        yAxis:{type:'category',data:yLabels,splitArea:{show:true},axisLabel:{color:textColor,fontSize:8}},
        series:[{type:'heatmap',data:visualData,label:{show:false},emphasis:{itemStyle:{shadowBlur:10,shadowColor:'rgba(0,0,0,0.5)'}}}]
    };
}

let c2Top,c2Bottom,c3Top,c3Toad,c3Bottom;

function renderCharts(){
    const isDark=document.documentElement.classList.contains('dark');
    const textColor=isDark?'#94a3b8':'#475569';

    if(c2Top)c2Top.dispose();
    if(c2Bottom)c2Bottom.dispose();
    if(c3Top)c3Top.dispose();
    if(c3Toad)c3Toad.dispose();
    if(c3Bottom)c3Bottom.dispose();

    c2Top    = echarts.init(document.getElementById('heatmap2Top'),   isDark?'dark':null);
    c2Bottom = echarts.init(document.getElementById('heatmap2Bottom'),isDark?'dark':null);
    c3Top    = echarts.init(document.getElementById('heatmap3Top'),   isDark?'dark':null);
    c3Toad   = echarts.init(document.getElementById('heatmap3Toad'),  isDark?'dark':null);
    c3Bottom = echarts.init(document.getElementById('heatmap3Bottom'),isDark?'dark':null);

    c2Top.setOption(make2DOption(data2Top,    max2Top,    maxPayout2D,    textColor));
    c2Bottom.setOption(make2DOption(data2Bottom, max2Bottom, maxPayout2D, textColor));
    c3Top.setOption(make3DOption(data3Top,    max3Top,    maxPayout3D,    textColor));
    c3Toad.setOption(make3DOption(data3Toad,  max3Toad,   maxPayout3Toad, textColor));
    c3Bottom.setOption(make3DOption(data3Bottom,max3Bottom,maxPayout3Bot, textColor));
}

window.addEventListener('resize',()=>{
    [c2Top,c2Bottom,c3Top,c3Toad,c3Bottom].forEach(c=>c&&c.resize());
});

// ── Delete Bet ──
async function deleteBet(id, customer, number){
    const codeRes = await Swal.fire({
        title:'🗑️ ลบรายการ?',
        html:`<div class="text-left mb-3"><b>ลูกค้า:</b> ${customer}<br/><b>เลข:</b> ${number}</div><input id="swalDeleteCode" type="tel" maxlength="6" class="swal2-input" placeholder="รหัสลบ 6 หลัก">`,
        showCancelButton:true,confirmButtonText:'ยืนยันลบ',cancelButtonText:'ยกเลิก',
        confirmButtonColor:'#ef4444',cancelButtonColor:'#64748b',
        preConfirm:()=>{
            const v=document.getElementById('swalDeleteCode').value;
            if(!v||!/^\d{6}$/.test(v)){Swal.showValidationMessage('กรุณากรอกรหัส 6 หลัก');return false;}
            return v;
        }
    });
    if(!codeRes.isConfirmed)return;
    try{
        const r=await fetch(`/admin/reports/bets/${id}`,{method:'DELETE',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({delete_code:codeRes.value})});
        const d=await r.json();
        if(d.success){
            await Swal.fire({icon:'success',title:'ลบสำเร็จ',timer:1500,showConfirmButton:false});
            document.querySelector(`tr[data-bet-id="${id}"]`)?.remove();
        } else {
            Swal.fire({icon:'error',title:'ไม่สามารถลบได้',text:d.message});
        }
    }catch(e){Swal.fire({icon:'error',title:'ERROR',text:'เกิดข้อผิดพลาด'});}
}

// ── Export Functions ──
function exportSummaryExcel(){
    const form=document.getElementById('filterForm');
    const params=new URLSearchParams(new FormData(form));
    window.location.href=`{{ route('admin.reports.export-excel', $draw->id) }}?${params.toString()}`;
}

function exportCustomerSummaryExcel(){
    window.location.href=`{{ route('admin.reports.export-customer-summary', $draw->id) }}`;
}

// ── Select2 Init ──
$(document).ready(function(){
    $('#customerSelect').select2({
        placeholder:'เลือกลูกค้า...',
        allowClear:true,
        closeOnSelect:false,
        language:{noResults:()=>'ไม่พบลูกค้า'},
    });
});

// ── Start ──
document.addEventListener('DOMContentLoaded',()=>renderCharts());
</script>
</body>
</html>
