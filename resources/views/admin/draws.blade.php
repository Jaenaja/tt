<!-- resources/views/admin/draws.blade.php -->
<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>กรอกผลหวย - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
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
    <style>
        * { font-family: 'Sarabun', sans-serif; }
    </style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb & Header -->
        <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center mb-4">
                <nav class="text-sm text-slate-600 dark:text-slate-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                    <span class="mx-2">›</span>
                    <span class="text-slate-900 dark:text-white font-semibold">กรอกผลหวย</span>
                </nav>
                <button id="themeToggle" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                </button>
            </div>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">🎯 กรอกผลหวย</h1>
                </div>
                <div class="text-right">
                    <div class="transition-all duration-300 inline-flex items-center gap-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full px-4 py-2 mb-2">
                        <span class="text-sm text-slate-900 dark:text-white font-medium">{{ Auth::user()->name }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">ออกจากระบบ</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ฟอร์มกรอกผล -->
        <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <form id="drawForm" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">งวดวันที่ *</label>
                    <select id="drawDate" name="draw_date"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-violet-50 dark:bg-violet-900/20 p-4 rounded-lg border-2 border-violet-300 dark:border-violet-700">
                        <label class="block text-slate-800 dark:text-slate-200 font-bold mb-2">🟣 3 ตัวบน *</label>
                        <input type="text" name="result_3_top" maxlength="3" pattern="[0-9]{3}" required
                            class="w-full px-4 py-3 text-3xl font-bold text-center border-2 border-violet-400 dark:border-violet-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-600"
                            placeholder="123">
                    </div>

                    <div class="bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-lg border-2 border-emerald-300 dark:border-emerald-700">
                        <label class="block text-slate-800 dark:text-slate-200 font-bold mb-2">🔵 2 ตัวบน *</label>
                        <input type="text" name="result_2_top" maxlength="2" pattern="[0-9]{2}" required
                            class="w-full px-4 py-3 text-3xl font-bold text-center border-2 border-emerald-400 dark:border-emerald-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600"
                            placeholder="23">
                    </div>

                    <div class="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-lg border-2 border-teal-300 dark:border-teal-700">
                        <label class="block text-slate-800 dark:text-slate-200 font-bold mb-2">🟢 2 ตัวล่าง *</label>
                        <input type="text" name="result_2_bottom" maxlength="2" pattern="[0-9]{2}" required
                            class="w-full px-4 py-3 text-3xl font-bold text-center border-2 border-teal-400 dark:border-teal-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-600"
                            placeholder="45">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-4 rounded-lg transition-all text-lg shadow-lg">
                    💾 บันทึกผลและคำนวณรางวัล
                </button>
            </form>
        </div>

        <!-- รายการงวดที่ผ่านมา -->
        <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">📋 รายการงวดที่ผ่านมา</h2>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                            <th class="px-4 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">งวดวันที่</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">3 ตัวบน</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">2 ตัวบน</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">2 ตัวล่าง</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">สถานะ</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">ประกาศโดย</th>
                            <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">ดูรายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        @forelse($draws as $draw)
                            <tr class="transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <td class="px-4 py-3 text-slate-800 dark:text-slate-300 font-semibold">
                                    {{ thai_date($draw->draw_date) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-2xl font-bold text-violet-600 dark:text-violet-400">
                                        {{ $draw->result_3_top ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ $draw->result_2_top ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-2xl font-bold text-teal-600 dark:text-teal-400">
                                        {{ $draw->result_2_bottom ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($draw->is_announced)
                                        <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                            ประกาศแล้ว
                                        </span>
                                    @else
                                        <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                            รอประกาศ
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-slate-700 dark:text-slate-300">
                                    @if($draw->announcedBy)
                                        {{ $draw->announcedBy->name }}<br>
                                        <span class="text-slate-500 dark:text-slate-500">{{ $draw->announced_at->format('d/m/y H:i') }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($draw->is_announced)
                                        <a href="{{ route('admin.draws.results', $draw->id) }}"
                                            class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-semibold transition-colors">
                                            ดูผล
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-16 text-center">
                                    <div class="text-slate-400 dark:text-slate-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-lg font-medium text-slate-600 dark:text-slate-400">ยังไม่มีข้อมูลงวดหวย</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $draws->links() }}
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

        // === ORIGINAL LOGIC - DO NOT MODIFY ===
        const thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        function generateDrawDates() {
            const select = document.getElementById('drawDate');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const allDrawDates = getAllDrawDates(today, 3, 4);
            const nextDrawDate = getNextDrawDate(today);
            select.innerHTML = allDrawDates.map((dateOption) => {
                const isSelected = dateOption.value === formatDateForDatabase(nextDrawDate);
                return `<option value="${dateOption.value}" ${isSelected ? 'selected' : ''}>${dateOption.label}</option>`;
            }).join('');
        }

        function getAllDrawDates(referenceDate, pastCount, futureCount) {
            const draws = [];
            let currentDate = new Date(referenceDate);
            for (let i = 0; i < pastCount; i++) {
                currentDate = getPreviousDrawDate(currentDate);
                draws.unshift(createDateOption(new Date(currentDate)));
            }
            currentDate = new Date(referenceDate);
            for (let i = 0; i < futureCount; i++) {
                const nextDraw = getNextDrawDate(currentDate);
                const alreadyExists = draws.some(d => d.value === formatDateForDatabase(nextDraw));
                if (!alreadyExists) {
                    draws.push(createDateOption(nextDraw));
                }
                currentDate = new Date(nextDraw);
                currentDate.setDate(currentDate.getDate() + 1);
            }
            return draws;
        }

        function getNextDrawDate(referenceDate) {
            const date = new Date(referenceDate);
            const day = date.getDate();
            const month = date.getMonth();
            const year = date.getFullYear();
            if (day < 1) {
                return new Date(year, month, 1);
            } else if (day >= 1 && day < 16) {
                return new Date(year, month, 16);
            } else {
                return new Date(year, month + 1, 1);
            }
        }

        function getPreviousDrawDate(referenceDate) {
            const date = new Date(referenceDate);
            const day = date.getDate();
            const month = date.getMonth();
            const year = date.getFullYear();
            if (day <= 1) {
                return new Date(year, month - 1, 16);
            } else if (day > 1 && day <= 16) {
                return new Date(year, month, 1);
            } else {
                return new Date(year, month, 16);
            }
        }

        function createDateOption(date) {
            return {
                value: formatDateForDatabase(date),
                label: formatDateThai(date)
            };
        }

        function formatDateForDatabase(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function formatDateThai(date) {
            const day = date.getDate();
            const month = thaiMonths[date.getMonth() + 1];
            const year = date.getFullYear() + 543;
            return `${day} ${month} ${year}`;
        }

        window.onload = function () {
            generateDrawDates();
        };

        document.getElementById('drawForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const drawDate = formData.get('draw_date');

            const result = await Swal.fire({
                title: 'ยืนยันการบันทึก?',
                text: `งวดวันที่ ${drawDate}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch('{{ route("admin.draws.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        draw_date: formData.get('draw_date'),
                        result_3_top: formData.get('result_3_top'),
                        result_2_top: formData.get('result_2_top'),
                        result_2_bottom: formData.get('result_2_bottom')
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: 'บันทึกผลหวยเรียบร้อย',
                        timer: 2000
                    });
                    window.location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'ERROR', text: data.message });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'เกิดข้อผิดพลาดในการบันทึก' });
            }
        });
    </script>
</body>

</html>