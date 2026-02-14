<!-- resources/views/admin/reports/summary.blade.php -->
<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปผลงวด {{ thai_date_full($draw->draw_date) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Breadcrumb & Header -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center">
                <nav class="text-sm text-slate-600 dark:text-slate-400">
                    <a href="{{ route('dashboard') }}"
                        class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                    <span class="mx-2">›</span>
                    <a href="{{ route('admin.draws') }}"
                        class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">กรอกผลหวย</a>
                    <span class="mx-2">›</span>
                    <span class="text-slate-900 dark:text-white font-semibold">สรุปผลงวด</span>
                </nav>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.reports.pdf', $draw->id) }}" target="_blank"
                        class="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-all">
                        📄 Export PDF
                    </a>
                    <button id="themeToggle"
                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                        <span
                            class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ผลหวย -->
        <div
            class="bg-gradient-to-r from-amber-400 to-orange-400 dark:from-amber-600 dark:to-orange-600 rounded-2xl shadow-xl p-6 mb-6">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-4">📊 สรุปผลงวด
                {{ thai_date_full($draw->draw_date) }}
            </h1>
            <div class="grid grid-cols-3 gap-4">
                <div
                    class="bg-white dark:bg-slate-900 bg-opacity-95 dark:bg-opacity-95 rounded-xl p-4 text-center border border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">3 ตัวบน</p>
                    <p class="text-5xl font-bold text-violet-600 dark:text-violet-400">{{ $draw->result_3_top }}</p>
                </div>
                <div
                    class="bg-white dark:bg-slate-900 bg-opacity-95 dark:bg-opacity-95 rounded-xl p-4 text-center border border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">2 ตัวบน</p>
                    <p class="text-5xl font-bold text-emerald-600 dark:text-emerald-400">{{ $draw->result_2_top }}</p>
                </div>
                <div
                    class="bg-white dark:bg-slate-900 bg-opacity-95 dark:bg-opacity-95 rounded-xl p-4 text-center border border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">2 ตัวล่าง</p>
                    <p class="text-5xl font-bold text-teal-600 dark:text-teal-400">{{ $draw->result_2_bottom }}</p>
                </div>
            </div>
        </div>

        <!-- สรุปยอดรวม -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-xl shadow-lg p-6 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">ยอดแทงทั้งหมด</p>
                <p class="text-4xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalBetAmount, 2) }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-500">บาท</p>
            </div>
            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-xl shadow-lg p-6 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">ยอดจ่ายรางวัล</p>
                <p class="text-4xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($totalPayout, 2) }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-500">บาท</p>
            </div>
            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-xl shadow-lg p-6 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">กำไร/ขาดทุน</p>
                <p
                    class="text-4xl font-bold {{ $profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 2) }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-500">บาท</p>
            </div>
            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-xl shadow-lg p-6 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">จำนวนผู้ถูกรางวัล</p>
                <p class="text-4xl font-bold text-violet-600 dark:text-violet-400">{{ $winners->count() }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-500">คน</p>
            </div>
        </div>

        <!-- สถิติแยกตามประเภท -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">📈 สถิติแยกตามประเภท</h2>
            <div class="grid grid-cols-3 gap-4">
                <div
                    class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700">
                    <p class="text-sm text-slate-700 dark:text-slate-300 font-semibold mb-2">บน</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($stats['total_top_bet'], 2) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-500">แทง</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-2">
                        {{ number_format($stats['total_top_payout'], 2) }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-500">จ่าย</p>
                    <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-2">ถูก {{ $stats['winners_top'] }} ใบ
                    </p>
                </div>
                <div
                    class="text-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-700">
                    <p class="text-sm text-slate-700 dark:text-slate-300 font-semibold mb-2">ล่าง</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ number_format($stats['total_bottom_bet'], 2) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-500">แทง</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-2">
                        {{ number_format($stats['total_bottom_payout'], 2) }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-500">จ่าย</p>
                    <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-2">ถูก {{ $stats['winners_bottom'] }} ใบ
                    </p>
                </div>
                <div
                    class="text-center p-4 bg-violet-50 dark:bg-violet-900/20 rounded-xl border border-violet-200 dark:border-violet-700">
                    <p class="text-sm text-slate-700 dark:text-slate-300 font-semibold mb-2">โต๊ด</p>
                    <p class="text-2xl font-bold text-violet-600 dark:text-violet-400">
                        {{ number_format($stats['total_toad_bet'], 2) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-500">แทง</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-2">
                        {{ number_format($stats['total_toad_payout'], 2) }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-500">จ่าย</p>
                    <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-2">ถูก {{ $stats['winners_toad'] }} ใบ
                    </p>
                </div>
            </div>
        </div>

        <!-- รายการต้องจ่ายเงิน -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">💰 รายการต้องจ่ายเงิน</h2>

            @if($winners->count() > 0)
                <div class="space-y-4">
                    @foreach($winners as $winner)
                        <div
                            class="border-2 border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">👤
                                        {{ $winner['customer_name'] }}</h3>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">แทงรวม:
                                        {{ number_format($winner['total_bet'], 2) }} บาท
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-slate-600 dark:text-slate-400">ต้องจ่าย</p>
                                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ number_format($winner['total_win'], 2) }}
                                    </p>
                                    <p
                                        class="text-xs {{ $winner['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        กำไร {{ $winner['net'] >= 0 ? '+' : '' }}{{ number_format($winner['net'], 2) }}
                                    </p>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 dark:border-slate-700 pt-3">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-100 dark:bg-slate-800">
                                        <tr>
                                            <th class="px-2 py-2 text-left text-slate-900 dark:text-slate-200 font-bold">เลข
                                            </th>
                                            <th class="px-2 py-2 text-right text-slate-900 dark:text-slate-200 font-bold">แทง
                                            </th>
                                            <th class="px-2 py-2 text-right text-slate-900 dark:text-slate-200 font-bold">ถูก
                                            </th>
                                            <th class="px-2 py-2 text-left text-slate-900 dark:text-slate-200 font-bold">
                                                รายละเอียด</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                        @foreach($winner['details'] as $detail)
                                            @if($detail['win_amount'] > 0)
                                                <tr>
                                                    <td class="px-2 py-2 font-bold text-lg text-slate-900 dark:text-white">
                                                        {{ $detail['number'] }}</td>
                                                    <td class="px-2 py-2 text-right text-slate-800 dark:text-slate-300">
                                                        {{ number_format($detail['bet_amount'], 2) }}</td>
                                                    <td class="px-2 py-2 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                                        {{ number_format($detail['win_amount'], 2) }}
                                                    </td>
                                                    <td class="px-2 py-2 text-xs">
                                                        @if($detail['win_types']['top'] > 0)
                                                            <span
                                                                class="inline-block bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 px-2 py-1 rounded mr-1">บน
                                                                {{ number_format($detail['win_types']['top'], 2) }}</span>
                                                        @endif
                                                        @if($detail['win_types']['bottom'] > 0)
                                                            <span
                                                                class="inline-block bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 px-2 py-1 rounded mr-1">ล่าง
                                                                {{ number_format($detail['win_types']['bottom'], 2) }}</span>
                                                        @endif
                                                        @if($detail['win_types']['toad'] > 0)
                                                            <span
                                                                class="inline-block bg-violet-100 dark:bg-violet-900/40 text-violet-800 dark:text-violet-300 px-2 py-1 rounded">โต๊ด
                                                                {{ number_format($detail['win_types']['toad'], 2) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-slate-400 dark:text-slate-500">
                        <p class="text-6xl mb-4">🎉</p>
                        <p class="text-xl font-medium text-slate-600 dark:text-slate-400">ไม่มีผู้ถูกรางวัล</p>
                    </div>
                </div>
            @endif
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
    </script>
</body>

</html>