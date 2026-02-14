<!-- resources/views/admin/config.blade.php -->
<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ตั้งค่าอัตราจ่าย - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <!-- Breadcrumb & Header -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center mb-4">
                <nav class="text-sm text-slate-600 dark:text-slate-400">
                    <a href="{{ route('dashboard') }}"
                        class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                    <span class="mx-2">›</span>
                    <span class="text-slate-900 dark:text-white font-semibold">ตั้งค่าอัตราจ่าย</span>
                </nav>
                <button id="themeToggle"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                    <span
                        class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                </button>
            </div>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">⚙️ ตั้งค่าอัตราจ่าย</h1>
                </div>
                <div class="text-right">
                    <div
                        class="transition-all duration-300 inline-flex items-center gap-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full px-4 py-2 mb-2">
                        <span class="text-sm text-slate-900 dark:text-white font-medium">{{ Auth::user()->name }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">ออกจากระบบ</button>
                    </form>
                </div>
            </div>
        </div>

        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-8 border border-slate-200 dark:border-slate-800">
            <div
                class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-700 rounded-lg">
                <p class="text-amber-800 dark:text-amber-300 font-semibold">⚠️ คำเตือน</p>
                <p class="text-amber-700 dark:text-amber-400 text-sm mt-1">
                    การเปลี่ยนแปลงอัตราจ่ายจะมีผลกับการคำนวณรางวัลทั้งหมด</p>
            </div>

            <form id="configForm" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 2 ตัวบน -->
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border-2 border-blue-200 dark:border-blue-700">
                        <label class="block text-slate-800 dark:text-slate-200 font-bold mb-3 text-base">
                            🔵 2 ตัวบน
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="rate_2_top" value="{{ $configs['rate_2_top'] }}" min="0" step="1"
                                required
                                class="w-24 px-3 py-2 text-xl font-bold text-center border-2 border-blue-300 dark:border-blue-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600 transition-all">
                            <span class="text-slate-600 dark:text-slate-400 font-semibold whitespace-nowrap">เท่า</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">แทง 10 บาท ได้
                            {{ number_format($configs['rate_2_top'] * 10) }} บาท
                        </p>
                    </div>

                    <!-- 2 ตัวล่าง -->
                    <div
                        class="bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-lg border-2 border-emerald-200 dark:border-emerald-700">
                        <label class="block text-slate-800 dark:text-slate-200 font-bold mb-3 text-base">
                            🟢 2 ตัวล่าง
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="rate_2_bottom" value="{{ $configs['rate_2_bottom'] }}" min="0"
                                step="1" required
                                class="w-24 px-3 py-2 text-xl font-bold text-center border-2 border-emerald-300 dark:border-emerald-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                            <span class="text-slate-600 dark:text-slate-400 font-semibold whitespace-nowrap">เท่า</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">แทง 10 บาท ได้
                            {{ number_format($configs['rate_2_bottom'] * 10) }} บาท
                        </p>
                    </div>

                    <!-- 3 ตัวบน -->
                    <div
                        class="bg-violet-50 dark:bg-violet-900/20 p-4 rounded-lg border-2 border-violet-200 dark:border-violet-700">
                        <label class="block text-slate-800 dark:text-slate-200 font-bold mb-3 text-base">
                            🟣 3 ตัวบน
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="rate_3_top" value="{{ $configs['rate_3_top'] }}" min="0" step="1"
                                required
                                class="w-24 px-3 py-2 text-xl font-bold text-center border-2 border-violet-300 dark:border-violet-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-600 transition-all">
                            <span class="text-slate-600 dark:text-slate-400 font-semibold whitespace-nowrap">เท่า</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">แทง 10 บาท ได้
                            {{ number_format($configs['rate_3_top'] * 10) }} บาท
                        </p>
                    </div>

                    <!-- 3 ตัวโต๊ด -->
                    <div
                        class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border-2 border-orange-200 dark:border-orange-700">
                        <label class="block text-slate-800 dark:text-slate-200 font-bold mb-3 text-base">
                            🟠 3 ตัวโต๊ด
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="rate_3_toad" value="{{ $configs['rate_3_toad'] }}" min="0"
                                step="1" required
                                class="w-24 px-3 py-2 text-xl font-bold text-center border-2 border-orange-300 dark:border-orange-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-orange-500 dark:focus:ring-orange-600 transition-all">
                            <span class="text-slate-600 dark:text-slate-400 font-semibold whitespace-nowrap">เท่า</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">แทง 10 บาท ได้
                            {{ number_format($configs['rate_3_toad'] * 10) }} บาท
                        </p>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200 dark:border-slate-700">
                    <button type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-4 rounded-lg transition-all text-lg shadow-lg">
                        💾 บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
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

        // === ORIGINAL LOGIC - DO NOT MODIFY ===
        document.getElementById('configForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const result = await Swal.fire({
                title: 'ยืนยันการเปลี่ยนแปลง?',
                text: 'การเปลี่ยนแปลงอัตราจ่ายจะมีผลทันที',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            });

            if (!result.isConfirmed) return;

            const formData = new FormData(this);

            try {
                const response = await fetch('{{ route("admin.config.update") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'ERROR', text: data.message });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'เกิดข้อผิดพลาด' });
            }
        });
    </script>
</body>

</html>