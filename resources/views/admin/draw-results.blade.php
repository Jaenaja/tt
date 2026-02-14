<!-- resources/views/admin/draw-results.blade.php -->
<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลรางวัล - งวด {{ thai_date_full($draw->draw_date) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>* { font-family: 'Sarabun', sans-serif; }</style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb & Header -->
        <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center">
                <nav class="text-sm text-slate-600 dark:text-slate-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                    <span class="mx-2">›</span>
                    <a href="{{ route('admin.draws') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">กรอกผลหวย</a>
                    <span class="mx-2">›</span>
                    <span class="text-slate-900 dark:text-white font-semibold">ผลรางวัล</span>
                </nav>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.reports.summary', $draw->id) }}"
                        class="bg-violet-600 hover:bg-violet-700 dark:bg-violet-700 dark:hover:bg-violet-600 text-white font-bold py-2 px-6 rounded-lg transition-all">
                        📊 ดูสรุปและพิมพ์ PDF
                    </a>
                    <button id="themeToggle" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ผลหวย -->
        <div class="bg-gradient-to-r from-amber-400 to-orange-400 dark:from-amber-600 dark:to-orange-600 rounded-2xl shadow-xl p-6 mb-6 text-slate-900 dark:text-white">
            <h1 class="text-3xl font-bold mb-4">🏆 ผลรางวัล</h1>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="bg-white dark:bg-slate-900 bg-opacity-95 dark:bg-opacity-95 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">3 ตัวบน</p>
                    <p class="text-5xl font-bold text-violet-600 dark:text-violet-400">{{ $draw->result_3_top }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 bg-opacity-95 dark:bg-opacity-95 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">2 ตัวบน</p>
                    <p class="text-5xl font-bold text-emerald-600 dark:text-emerald-400">{{ $draw->result_2_top }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 bg-opacity-95 dark:bg-opacity-95 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">2 ตัวล่าง</p>
                    <p class="text-5xl font-bold text-teal-600 dark:text-teal-400">{{ $draw->result_2_bottom }}</p>
                </div>
            </div>
            <p class="text-center mt-4 text-sm text-slate-900 dark:text-slate-200">
                งวดวันที่:
                <strong>{{ thai_date_full($draw->draw_date) }}</strong> |
                ประกาศโดย: <strong>{{ $draw->announcedBy->name }}</strong> |
                เมื่อ: {{ $draw->announced_at->format('d/m/Y H:i') }}
            </p>
        </div>

        <!-- สรุปยอด -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-xl shadow-lg p-4 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">จำนวนรายการ</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($summary['total_bets']) }}</p>
            </div>
            <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-xl shadow-lg p-4 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">ยอดแทงรวม</p>
                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($summary['total_amount'], 2) }}</p>
            </div>
            <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-xl shadow-lg p-4 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">ยอดจ่ายรางวัล</p>
                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($summary['total_payout'], 2) }}</p>
            </div>
            <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-xl shadow-lg p-4 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">กำไร/ขาดทุน</p>
                <p class="text-3xl font-bold {{ $summary['total_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $summary['total_profit'] >= 0 ? '+' : '' }}{{ number_format($summary['total_profit'], 2) }}
                </p>
            </div>
            <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-xl shadow-lg p-4 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">จำนวนผู้ถูกรางวัล</p>
                <p class="text-3xl font-bold text-violet-600 dark:text-violet-400">{{ number_format($summary['winners_count']) }}</p>
            </div>
        </div>

        <!-- รายการเดิมพันทั้งหมด -->
        <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">📋 รายการเดิมพันทั้งหมด</h2>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                            <th class="px-3 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">ลูกค้า</th>
                            <th class="px-3 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">เลข</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">บน</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">ล่าง</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">โต๊ด</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">รวม</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">ได้รางวัล</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">กำไร/ขาดทุน</th>
                            <th class="px-3 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">สถานะ</th>
                            <th class="px-3 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">สร้างโดย</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        @foreach($draw->bets->groupBy('customer_name') as $customerName => $customerBets)
                            @php
                                $customerTotal = $customerBets->sum('total_amount');
                                $customerPayout = $customerBets->sum('total_payout');
                                $customerProfit = $customerPayout - $customerTotal;
                            @endphp
                            <tr class="bg-slate-50 dark:bg-slate-800/50 font-semibold">
                                <td colspan="5" class="px-3 py-3 text-slate-900 dark:text-white">👤 {{ $customerName }}</td>
                                <td class="px-3 py-3 text-right text-slate-900 dark:text-white">{{ number_format($customerTotal, 2) }}</td>
                                <td class="px-3 py-3 text-right text-slate-900 dark:text-white">{{ number_format($customerPayout, 2) }}</td>
                                <td class="px-3 py-3 text-right {{ $customerProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $customerProfit >= 0 ? '+' : '' }}{{ number_format($customerProfit, 2) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                            @foreach($customerBets as $bet)
                                <tr class="transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <td class="px-3 py-3 pl-8 text-slate-500 dark:text-slate-500">└─</td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="font-bold text-lg {{ strlen($bet->number) === 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-violet-600 dark:text-violet-400' }}">
                                            {{ $bet->number }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right text-slate-800 dark:text-slate-300">
                                        {{ $bet->amount_top > 0 ? number_format($bet->amount_top, 2) : '-' }}
                                        @if($bet->is_win_top)
                                            <br><span class="text-emerald-600 dark:text-emerald-400 text-xs">✓
                                                {{ number_format($bet->payout_top, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right text-slate-800 dark:text-slate-300">
                                        {{ $bet->amount_bottom > 0 ? number_format($bet->amount_bottom, 2) : '-' }}
                                        @if($bet->is_win_bottom)
                                            <br><span class="text-emerald-600 dark:text-emerald-400 text-xs">✓
                                                {{ number_format($bet->payout_bottom, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right text-slate-800 dark:text-slate-300">
                                        {{ $bet->amount_toad > 0 ? number_format($bet->amount_toad, 2) : '-' }}
                                        @if($bet->is_win_toad)
                                            <br><span class="text-emerald-600 dark:text-emerald-400 text-xs">✓
                                                {{ number_format($bet->payout_toad, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right text-slate-900 dark:text-white">{{ number_format($bet->total_amount, 2) }}</td>
                                    <td class="px-3 py-3 text-right font-semibold {{ $bet->total_payout > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-800 dark:text-slate-300' }}">
                                        {{ $bet->total_payout > 0 ? number_format($bet->total_payout, 2) : '-' }}
                                    </td>
                                    <td class="px-3 py-3 text-right {{ $bet->net_profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $bet->net_profit >= 0 ? '+' : '' }}{{ number_format($bet->net_profit, 2) }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @if($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad)
                                            <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">ถูกรางวัล</span>
                                        @else
                                            <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">ไม่ถูก</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-xs text-slate-600 dark:text-slate-400">
                                        {{ $bet->creator->name }}<br>
                                        {{ $bet->created_at->format('d/m/y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
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
    </script>
</body>

</html>