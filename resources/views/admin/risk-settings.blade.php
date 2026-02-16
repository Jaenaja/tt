<!DOCTYPE html>
<html lang="th" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ตั้งค่าความเสี่ยง - ระบบหวย</title>
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
            <a href="{{ route('admin.reports.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">จัดการความเสี่ยง</a>
            <span class="mx-2">›</span>
            <span class="text-slate-900 dark:text-white font-semibold">ตั้งค่าระบบ</span>
        </nav>

        {{-- Header --}}
        <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-8 mb-8 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-3">
                        <span>⚙️</span> ตั้งค่าความเสี่ยง
                    </h1>
                    <p class="text-slate-700 dark:text-slate-400">กำหนดเพดานการจ่าย อัตราจ่าย และระบบตัดยอดอัตโนมัติ</p>
                </div>
                <button id="themeToggle" 
                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                </button>
            </div>
        </div>

        {{-- Alert Messages --}}
        @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 p-6 rounded-lg mb-8">
            <div class="flex items-center">
                <span class="text-2xl mr-3">✅</span>
                <p class="text-emerald-800 dark:text-emerald-200 font-semibold">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-6 rounded-lg mb-8">
            <div class="flex items-center">
                <span class="text-2xl mr-3">❌</span>
                <p class="text-red-800 dark:text-red-200 font-semibold">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <form action="{{ route('admin.risk-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Max Payout Limits --}}
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 mb-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span>💰</span> เพดานยอดจ่ายสูงสุด (Max Payout Limit)
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            เลข 2 ตัว (บาท)
                        </label>
                        <input type="number" name="max_payout_2_digit" value="{{ old('max_payout_2_digit', $settings['max_payout_2_digit']) }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white"
                               min="0" step="1000" required>
                        @error('max_payout_2_digit')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            เลข 3 ตัว (บาท)
                        </label>
                        <input type="number" name="max_payout_3_digit" value="{{ old('max_payout_3_digit', $settings['max_payout_3_digit']) }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white"
                               min="0" step="1000" required>
                        @error('max_payout_3_digit')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-4">
                    💡 ยอดจ่ายสูงสุดที่รับได้ต่อหนึ่งเลข (ก่อนตัดยอดส่งต่อ)
                </p>
            </div>

            {{-- Payout Rates --}}
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 mb-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span>📊</span> อัตราจ่าย (Payout Rates)
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            2 ตัวบน (เท่า)
                        </label>
                        <input type="number" name="rate_2_top" value="{{ old('rate_2_top', $settings['rate_2_top']) }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white"
                               min="0" step="0.01" required>
                        @error('rate_2_top')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            2 ตัวล่าง (เท่า)
                        </label>
                        <input type="number" name="rate_2_bottom" value="{{ old('rate_2_bottom', $settings['rate_2_bottom']) }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white"
                               min="0" step="0.01" required>
                        @error('rate_2_bottom')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            3 ตัวตรง (เท่า)
                        </label>
                        <input type="number" name="rate_3_top" value="{{ old('rate_3_top', $settings['rate_3_top']) }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white"
                               min="0" step="0.01" required>
                        @error('rate_3_top')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            3 ตัวโต๊ด (เท่า)
                        </label>
                        <input type="number" name="rate_3_toad" value="{{ old('rate_3_toad', $settings['rate_3_toad']) }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white"
                               min="0" step="0.01" required>
                        @error('rate_3_toad')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Auto Transfer --}}
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 mb-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span>🔄</span> ระบบตัดยอดอัตโนมัติ (Auto Transfer)
                </h2>
                
                <div class="flex items-center justify-between p-6 bg-slate-50 dark:bg-slate-800 rounded-lg mb-6">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white mb-1">เปิด/ปิด ระบบตัดยอดอัตโนมัติ</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">เมื่อยอด Liability เกินเพดาน จะตัดยอดส่วนเกินออกอัตโนมัติ</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_transfer_enabled" value="1" 
                               class="sr-only peer" {{ $settings['auto_transfer_enabled'] ? 'checked' : '' }}>
                        <div class="w-14 h-7 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer 
                                    peer-checked:after:translate-x-full peer-checked:after:border-white 
                                    after:content-[''] after:absolute after:top-0.5 after:left-[4px] 
                                    after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all 
                                    peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        เริ่มตัดยอดที่ (%)
                    </label>
                    <input type="number" name="auto_transfer_threshold" value="{{ old('auto_transfer_threshold', $settings['auto_transfer_threshold']) }}"
                           class="w-full md:w-1/2 px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white"
                           min="0" max="200" required>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">
                        💡 100 = ตัดเมื่อถึง 100% ของ Max Payout, 150 = ตัดเมื่อถึง 150%
                    </p>
                    @error('auto_transfer_threshold')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Commission --}}
            <div class="transition-all duration-300 premium-card bg-white dark:bg-slate-900 rounded-2xl p-8 mb-8 shadow-xl dark:shadow-2xl border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span>💸</span> ค่าคอมมิชชั่น
                </h2>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        อัตราค่าคอมมิชชั่น (%)
                    </label>
                    <input type="number" name="commission_rate" value="{{ old('commission_rate', $settings['commission_rate']) }}"
                           class="w-full md:w-1/2 px-4 py-3 bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg focus:border-emerald-500 focus:outline-none text-slate-900 dark:text-white"
                           min="0" max="100" step="0.01" required>
                    @error('commission_rate')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-4">
                <button type="submit" 
                        class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition-colors shadow-lg">
                    💾 บันทึกการตั้งค่า
                </button>
                <a href="{{ route('admin.reports.index') }}" 
                   class="px-8 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-semibold transition-colors">
                    ← กลับ
                </a>
            </div>
        </form>
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
