<!-- resources/views/bets/statistics.blade.php -->
<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติหวย - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Configure Tailwind Dark Mode
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">
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
                    <span class="text-slate-900 dark:text-white font-semibold">สถิติหวย</span>
                </nav>
                <div class="flex items-center gap-3">
                    <!-- Theme Toggle -->
                    <button id="themeToggle"
                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                        <span
                            class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                    </button>
                </div>
            </div>

            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">📊 สถิติหวย</h1>
                <p class="text-slate-600 dark:text-slate-400 text-sm">ตัวเลขที่ออกบ่อยที่สุด</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Two Digit Stats -->
            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span>🎯</span> สถิติเลข 2 หลัก (ออกบ่อยสุด)
                </h3>
                <div class="space-y-3">
                    @forelse($twoDigitStats as $index => $stat)
                        <div
                            class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-750 transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <span class="text-2xl font-bold text-slate-400 dark:text-slate-500">{{ $index + 1 }}</span>
                                <span
                                    class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stat->number }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stat->frequency }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-400">ครั้ง</p>
                                @if($stat->last_drawn)
                                    <p class="text-xs text-slate-500 dark:text-slate-500">ออกล่าสุด:
                                        {{ $stat->last_drawn->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="text-slate-400 dark:text-slate-500">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <p class="text-lg font-medium text-slate-600 dark:text-slate-400">ยังไม่มีข้อมูลสถิติ</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Three Digit Stats -->
            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span>🎰</span> สถิติเลข 3 หลัก (ออกบ่อยสุด)
                </h3>
                <div class="space-y-3">
                    @forelse($threeDigitStats as $index => $stat)
                        <div
                            class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-750 transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <span class="text-2xl font-bold text-slate-400 dark:text-slate-500">{{ $index + 1 }}</span>
                                <span
                                    class="text-3xl font-bold text-violet-600 dark:text-violet-400">{{ $stat->number }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stat->frequency }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-400">ครั้ง</p>
                                @if($stat->last_drawn)
                                    <p class="text-xs text-slate-500 dark:text-slate-500">ออกล่าสุด:
                                        {{ $stat->last_drawn->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="text-slate-400 dark:text-slate-500">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <p class="text-lg font-medium text-slate-600 dark:text-slate-400">ยังไม่มีข้อมูลสถิติ</p>
                            </div>
                        </div>
                    @endforelse
                </div>
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
    </script>
</body>

</html>