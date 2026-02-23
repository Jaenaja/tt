<!DOCTYPE html>
<html lang="th" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>รายงานสรุปแต่ละงวด - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        * { font-family: 'Sarabun', sans-serif; }
        .premium-card {
            position: relative;
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
    </style>
</head>
<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
    
    <div class="container mx-auto px-4 py-8">
        
        {{-- Breadcrumb --}}
        <nav class="mb-6 text-sm text-slate-600 dark:text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
            <span class="mx-2">›</span>
            <span class="text-slate-900 dark:text-white font-semibold">รายงานสรุปแต่ละงวด</span>
        </nav>

        {{-- Header --}}
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-8 mb-8 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-3">
                        <span>📊</span> รายงานสรุปแต่ละงวด
                    </h1>
                    <p class="text-slate-700 dark:text-slate-400">เลือกงวดที่ต้องการดูรายละเอียด สถิติ และวิเคราะห์ข้อมูล</p>
                </div>
                <button id="themeToggle" 
                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                </button>
            </div>
        </div>

        {{-- รายการงวด --}}
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-8 border border-slate-200 dark:border-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-4 text-left font-bold text-slate-900 dark:text-white">งวดวันที่</th>
                            <th class="px-6 py-4 text-center font-bold text-slate-900 dark:text-white">สถานะ</th>
                            <th class="px-6 py-4 text-center font-bold text-slate-900 dark:text-white">รางวัล 3 ตัวบน</th>
                            <th class="px-6 py-4 text-center font-bold text-slate-900 dark:text-white">รางวัล 2 ตัวบน</th>
                            <th class="px-6 py-4 text-center font-bold text-slate-900 dark:text-white">รางวัล 2 ตัวล่าง</th>
                            <th class="px-6 py-4 text-center font-bold text-slate-900 dark:text-white">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($draws as $draw)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($draw->draw_date)->locale('th')->translatedFormat('j F Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($draw->is_announced)
                                        <span class="inline-block px-4 py-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 rounded-full text-sm font-semibold">
                                            ✓ ประกาศแล้ว
                                        </span>
                                    @else
                                        <span class="inline-block px-4 py-2 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 rounded-full text-sm font-semibold">
                                            ⏳ กำลังขาย
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($draw->is_announced)
                                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $draw->result_3_top }}</span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($draw->is_announced)
                                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $draw->result_2_top }}</span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($draw->is_announced)
                                        <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $draw->result_2_bottom }}</span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.reports.summary', $draw->id) }}"
                                        class="inline-block px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition-colors shadow-md">
                                        ดูรายละเอียด
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-xl font-semibold">ยังไม่มีข้อมูลงวด</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($draws->hasPages())
                <div class="mt-6">
                    {{ $draws->links() }}
                </div>
            @endif
        </div>

        {{-- ปุ่มกลับ --}}
        <div class="text-center mt-8">
            <a href="{{ route('dashboard') }}"
                class="inline-block px-8 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-semibold transition-colors shadow-lg">
                ← กลับหน้าหลัก
            </a>
        </div>
    </div>

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
        });
    </script>
</body>
</html>
