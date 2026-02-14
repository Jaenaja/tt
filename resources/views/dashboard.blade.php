<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Configure Tailwind Dark Mode
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <script>
        // Theme initialization - Must run before page render
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

        .stat-value {
            font-family: 'DM Sans', sans-serif;
            font-weight: 700;
        }

        /* Premium Card Effects */
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

        /* Action Button Shimmer Effect */
        .action-btn {
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        /* Smooth Fade Animation */
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
    </style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header with Theme Toggle -->
        <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-8 mb-8 animate-fade-in-up border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2">🎰 ระบบหวย Dashboard</h1>
                    <p class="text-slate-800 dark:text-slate-300 text-sm">ยินดีต้อนรับ, <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $user->name }}</span></p>
                </div>
                <div class="text-right flex flex-col items-end gap-3">
                    <!-- Theme Toggle Switch -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                            <span class="dark:hidden">☀️</span>
                            <span class="hidden dark:inline">🌙</span>
                        </span>
                        <button id="themeToggle" 
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                            <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                        </button>
                    </div>

                    <!-- User Profile -->
                    <div class="transition-all duration-300 inline-flex items-center gap-3 bg-slate-100 dark:bg-slate-800 backdrop-blur-lg border border-slate-200 dark:border-slate-700 rounded-full px-6 py-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                        <div>
                            <p class="text-sm text-slate-900 dark:text-white font-medium">
                                {{ $user->role === 'admin' ? '👑 ผู้ดูแลระบบ' : '👤 พนักงาน' }}
                            </p>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="transition-all duration-300 w-full text-sm bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-900 dark:text-white px-6 py-2 rounded-lg border border-slate-300 dark:border-slate-700">
                            ออกจากระบบ
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- สถิติวันนี้ -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 text-center shadow-lg dark:shadow-2xl border border-slate-200 dark:border-slate-800 hover:shadow-xl dark:hover:shadow-emerald-500/10 hover:border-emerald-500 dark:hover:border-emerald-500">
                <p class="text-xs text-slate-800 dark:text-slate-400 uppercase tracking-wider mb-2">💰 ยอดแทงวันนี้</p>
                <p class="text-4xl font-bold text-slate-900 dark:text-white stat-value mb-1" id="totalSales">
                    {{ number_format($todayStats['total_amount'], 2) }}
                </p>
                <p class="text-xs text-slate-800 dark:text-slate-400">บาท</p>
            </div>
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 text-center shadow-lg dark:shadow-2xl border border-slate-200 dark:border-slate-800 hover:shadow-xl dark:hover:shadow-emerald-500/10 hover:border-emerald-500 dark:hover:border-emerald-500">
                <p class="text-xs text-slate-800 dark:text-slate-400 uppercase tracking-wider mb-2">🎯 3 ตัวบน</p>
                <p class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 stat-value mb-1" id="totalTop">
                    {{ number_format($todayStats['total_top'], 2) }}
                </p>
                <p class="text-xs text-slate-800 dark:text-slate-400">บาท</p>
            </div>
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 text-center shadow-lg dark:shadow-2xl border border-slate-200 dark:border-slate-800 hover:shadow-xl dark:hover:shadow-emerald-500/10 hover:border-emerald-500 dark:hover:border-emerald-500">
                <p class="text-xs text-slate-800 dark:text-slate-400 uppercase tracking-wider mb-2">🎲 2 ตัวล่าง</p>
                <p class="text-4xl font-bold text-emerald-600 dark:text-emerald-400 stat-value mb-1" id="totalBottom">
                    {{ number_format($todayStats['total_bottom'], 2) }}
                </p>
                <p class="text-xs text-slate-800 dark:text-slate-400">บาท</p>
            </div>
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-6 text-center shadow-lg dark:shadow-2xl border border-slate-200 dark:border-slate-800 hover:shadow-xl dark:hover:shadow-emerald-500/10 hover:border-emerald-500 dark:hover:border-emerald-500">
                <p class="text-xs text-slate-800 dark:text-slate-400 uppercase tracking-wider mb-2">🔄 3 ตัวโต๊ด</p>
                <p class="text-4xl font-bold text-orange-600 dark:text-orange-400 stat-value mb-1" id="totalToad">
                    {{ number_format($todayStats['total_toad'], 2) }}
                </p>
                <p class="text-xs text-slate-800 dark:text-slate-400">บาท</p>
            </div>
        </div>

        <!-- งวดหวย -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- งวดถัดไป -->
            <div class="transition-all duration-300 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-950/30 dark:to-emerald-900/20 rounded-2xl shadow-lg dark:shadow-2xl p-6 backdrop-blur-lg border border-emerald-200 dark:border-emerald-800">
                <h2 class="text-xl font-bold mb-4 text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                    📅 งวดถัดไป
                </h2>
                @if($upcomingDraw)
                    <div class="transition-all duration-300 bg-white dark:bg-slate-900 backdrop-blur-md rounded-xl p-6 border border-emerald-200 dark:border-slate-700 shadow-sm">
                        <p class="text-3xl font-bold text-center text-slate-900 dark:text-white mb-2">
                            {{ thai_date_full($upcomingDraw->draw_date) }}
                        </p>
                        <div class="flex justify-center">
                            <span class="transition-all duration-300 inline-block bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 px-4 py-1 rounded-full text-sm font-semibold">
                                รอประกาศผล
                            </span>
                        </div>
                    </div>
                @else
                    <p class="transition-all duration-300 text-center text-slate-800 dark:text-slate-300 bg-white dark:bg-slate-900/50 rounded-xl p-6 border border-slate-200 dark:border-slate-800">ยังไม่มีงวดที่รอประกาศผล</p>
                @endif
            </div>

            <!-- งวดล่าสุด -->
            <div class="transition-all duration-300 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-950/30 dark:to-indigo-900/20 rounded-2xl shadow-lg dark:shadow-2xl p-6 backdrop-blur-lg border border-indigo-200 dark:border-indigo-800">
                <h2 class="text-xl font-bold mb-4 text-indigo-700 dark:text-indigo-300 flex items-center gap-2">
                    🏆 งวดล่าสุด
                </h2>
                @if($latestDraw)
                    <div class="transition-all duration-300 bg-white dark:bg-slate-900 backdrop-blur-md rounded-xl p-6 border border-indigo-200 dark:border-slate-700 shadow-sm">
                        <p class="text-sm text-center text-slate-800 dark:text-slate-300 mb-3">
                            {{ thai_date_full($latestDraw->draw_date) }}
                        </p>
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="transition-all duration-300 bg-slate-50 dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                <p class="text-xs text-slate-700 dark:text-slate-400 mb-1">3 ตัวบน</p>
                                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-300 stat-value">{{ $latestDraw->result_3_top }}</p>
                            </div>
                            <div class="transition-all duration-300 bg-slate-50 dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                <p class="text-xs text-slate-700 dark:text-slate-400 mb-1">2 ตัวบน</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-300 stat-value">{{ $latestDraw->result_2_top }}</p>
                            </div>
                            <div class="transition-all duration-300 bg-slate-50 dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                <p class="text-xs text-slate-700 dark:text-slate-400 mb-1">2 ตัวล่าง</p>
                                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-300 stat-value">{{ $latestDraw->result_2_bottom }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="transition-all duration-300 text-center text-slate-800 dark:text-slate-300 bg-white dark:bg-slate-900/50 rounded-xl p-6 border border-slate-200 dark:border-slate-800">ยังไม่มีผลหวย</p>
                @endif
            </div>
        </div>

        <!-- เมนูหลัก -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <a href="{{ route('bets.index') }}"
                class="transition-all duration-300 action-btn bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-950/40 dark:to-emerald-900/20 hover:from-emerald-100 hover:to-emerald-200 dark:hover:from-emerald-900/60 dark:hover:to-emerald-800/40 rounded-2xl shadow-md dark:shadow-xl p-6 text-center block border border-emerald-200 dark:border-emerald-800 hover:shadow-xl dark:hover:shadow-emerald-500/20 hover:-translate-y-2">
                <div class="text-5xl mb-3 filter drop-shadow-lg">🎫</div>
                <p class="font-bold text-base mb-1 text-slate-900 dark:text-white">รับแทงหวย</p>
                <p class="text-xs text-slate-700 dark:text-slate-400">บันทึกรายการใหม่</p>
            </a>
            <a href="{{ route('bets.history') }}"
                class="transition-all duration-300 action-btn bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-950/40 dark:to-indigo-900/20 hover:from-indigo-100 hover:to-indigo-200 dark:hover:from-indigo-900/60 dark:hover:to-indigo-800/40 rounded-2xl shadow-md dark:shadow-xl p-6 text-center block border border-indigo-200 dark:border-indigo-800 hover:shadow-xl dark:hover:shadow-indigo-500/20 hover:-translate-y-2">
                <div class="text-5xl mb-3 filter drop-shadow-lg">📜</div>
                <p class="font-bold text-base mb-1 text-slate-900 dark:text-white">ประวัติการแทง</p>
                <p class="text-xs text-slate-700 dark:text-slate-400">ดูข้อมูลทั้งหมด</p>
            </a>

            @if($user->isAdmin())
                <a href="{{ route('admin.draws') }}"
                    class="transition-all duration-300 action-btn bg-gradient-to-br from-emerald-50 to-teal-100 dark:from-teal-950/40 dark:to-teal-900/20 hover:from-emerald-100 hover:to-teal-200 dark:hover:from-teal-900/60 dark:hover:to-teal-800/40 rounded-2xl shadow-md dark:shadow-xl p-6 text-center block border border-teal-200 dark:border-teal-800 hover:shadow-xl dark:hover:shadow-teal-500/20 hover:-translate-y-2">
                    <div class="text-5xl mb-3 filter drop-shadow-lg">✍️</div>
                    <p class="font-bold text-base mb-1 text-slate-900 dark:text-white">กรอกผลหวย</p>
                    <p class="text-xs text-slate-700 dark:text-slate-400">ประกาศผลรางวัล</p>
                </a>
                <a href="{{ route('admin.reports.statistics') }}"
                    class="transition-all duration-300 action-btn bg-gradient-to-br from-violet-50 to-violet-100 dark:from-violet-950/40 dark:to-violet-900/20 hover:from-violet-100 hover:to-violet-200 dark:hover:from-violet-900/60 dark:hover:to-violet-800/40 rounded-2xl shadow-md dark:shadow-xl p-6 text-center block border border-violet-200 dark:border-violet-800 hover:shadow-xl dark:hover:shadow-violet-500/20 hover:-translate-y-2">
                    <div class="text-5xl mb-3 filter drop-shadow-lg">📊</div>
                    <p class="font-bold text-base mb-1 text-slate-900 dark:text-white">สถิติและกราฟ</p>
                    <p class="text-xs text-slate-700 dark:text-slate-400">วิเคราะห์ข้อมูล</p>
                </a>
                <a href="{{ route('admin.users') }}"
                    class="transition-all duration-300 action-btn bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-950/40 dark:to-orange-900/20 hover:from-orange-100 hover:to-orange-200 dark:hover:from-orange-900/60 dark:hover:to-orange-800/40 rounded-2xl shadow-md dark:shadow-xl p-6 text-center block border border-orange-200 dark:border-orange-800 hover:shadow-xl dark:hover:shadow-orange-500/20 hover:-translate-y-2">
                    <div class="text-5xl mb-3 filter drop-shadow-lg">👥</div>
                    <p class="font-bold text-base mb-1 text-slate-900 dark:text-white">จัดการผู้ใช้</p>
                    <p class="text-xs text-slate-700 dark:text-slate-400">บัญชีผู้ใช้งาน</p>
                </a>
                <a href="{{ route('admin.config') }}"
                    class="transition-all duration-300 action-btn bg-gradient-to-br from-red-50 to-red-100 dark:from-red-950/40 dark:to-red-900/20 hover:from-red-100 hover:to-red-200 dark:hover:from-red-900/60 dark:hover:to-red-800/40 rounded-2xl shadow-md dark:shadow-xl p-6 text-center block border border-red-200 dark:border-red-800 hover:shadow-xl dark:hover:shadow-red-500/20 hover:-translate-y-2">
                    <div class="text-5xl mb-3 filter drop-shadow-lg">⚙️</div>
                    <p class="font-bold text-base mb-1 text-slate-900 dark:text-white">ตั้งค่าอัตราจ่าย</p>
                    <p class="text-xs text-slate-700 dark:text-slate-400">กำหนดเรทและคอม</p>
                </a>
            @endif
        </div>

        <!-- สถิติ Admin -->
        @if($adminStats)
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 mb-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span>📊</span> สถิติระบบ
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="transition-all duration-300 text-center p-6 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                        <p class="text-sm text-slate-800 dark:text-slate-400 mb-2 uppercase tracking-wide">จำนวนผู้ใช้</p>
                        <p class="text-5xl font-bold text-indigo-600 dark:text-indigo-400 stat-value">{{ $adminStats['total_users'] }}</p>
                    </div>
                    <div class="transition-all duration-300 text-center p-6 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                        <p class="text-sm text-slate-800 dark:text-slate-400 mb-2 uppercase tracking-wide">งวดทั้งหมด</p>
                        <p class="text-5xl font-bold text-violet-600 dark:text-violet-400 stat-value">{{ $adminStats['total_draws'] }}</p>
                    </div>
                    <div class="transition-all duration-300 text-center p-6 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                        <p class="text-sm text-slate-800 dark:text-slate-400 mb-2 uppercase tracking-wide">ประกาศผลแล้ว</p>
                        <p class="text-5xl font-bold text-emerald-600 dark:text-emerald-400 stat-value">{{ $adminStats['announced_draws'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- รายการแทงล่าสุด 100 รายการ -->
        <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl p-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🕐</span> รายการแทงล่าสุด (100 รายการ)
                </h2>
                <a href="{{ route('bets.history') }}" 
                   class="transition-all duration-300 bg-emerald-100 dark:bg-emerald-900/40 hover:bg-emerald-200 dark:hover:bg-emerald-800/60 border border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 px-4 py-2 rounded-lg text-sm font-semibold">
                    ดูทั้งหมด →
                </a>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-sm">
                    <thead class="transition-all duration-300 bg-slate-100 dark:bg-slate-800">
                        <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">งวดวันที่</th>
                            <th class="px-4 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">ลูกค้า</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">เลข</th>
                            <th class="px-4 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">บน</th>
                            <th class="px-4 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">ล่าง</th>
                            <th class="px-4 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">โต๊ด</th>
                            <th class="px-4 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">รวม</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">สถานะ</th>
                            <th class="px-4 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">บันทึกเมื่อ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        @forelse($recentBets as $bet)
                            <tr class="transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <td class="px-4 py-3 text-slate-800 dark:text-slate-300">
                                    {{ thai_date($bet->draw_date) }}
                                </td>
                                <td class="px-4 py-3 text-slate-900 dark:text-white font-medium">
                                    {{ $bet->customer_name }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-lg text-violet-600 dark:text-violet-400">
                                        {{ $bet->number }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-300">
                                    {{ $bet->amount_top > 0 ? number_format($bet->amount_top, 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-300">
                                    {{ $bet->amount_bottom > 0 ? number_format($bet->amount_bottom, 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-300">
                                    {{ $bet->amount_toad > 0 ? number_format($bet->amount_toad, 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">
                                    {{ number_format($bet->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($bet->draw && $bet->draw->is_announced)
                                        @php
                                            $isWin = false;
                                            if ($bet->amount_top > 0 && $bet->draw->result_3_top == $bet->number) {
                                                $isWin = true;
                                            }
                                            if ($bet->amount_bottom > 0 && $bet->draw->result_2_bottom == $bet->number) {
                                                $isWin = true;
                                            }
                                        @endphp
                                        @if($isWin)
                                            <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                                ถูก
                                            </span>
                                        @else
                                            <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
                                                ไม่ถูก
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                            รอผล
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-slate-800 dark:text-slate-300 text-sm">{{ $bet->creator->name }}</div>
                                    <div class="text-xs text-slate-700 dark:text-slate-400">{{ $bet->created_at->format('d/m/y H:i') }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-16 text-center">
                                    <div class="text-slate-400 dark:text-slate-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-lg font-medium text-slate-800 dark:text-slate-400">ยังไม่มีรายการแทง</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle Functionality
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

        // อัพเดทยอดแทงทุก 10 วินาที
        setInterval(() => {
            fetch('/api/sales/realtime')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalSales').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalSales);
                    document.getElementById('totalTop').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalTop);
                    document.getElementById('totalBottom').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalBottom);
                    document.getElementById('totalToad').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalToad);
                })
                .catch(error => console.error('Error:', error));
        }, 10000);
    </script>
</body>

</html>