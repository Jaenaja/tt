<!-- resources/views/bets/history.blade.php -->
<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ประวัติการแทง - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <span class="text-slate-900 dark:text-white font-semibold">ประวัติการแทง</span>
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

            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">📜 ประวัติการแทง</h1>
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
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
            <!-- ฟิลเตอร์ -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">ค้นหาชื่อลูกค้า</label>
                    <input type="text" id="searchCustomer" value="{{ request('customer_name') }}"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all"
                        placeholder="ชื่อลูกค้า">
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">งวดวันที่</label>
                    <select id="searchDrawDate"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                        <option value="">ทั้งหมด</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="search()"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-2 rounded-lg transition-all">
                        🔍 ค้นหา
                    </button>
                </div>
            </div>

            <!-- ตาราง -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                            <th class="px-3 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">
                                <button onclick="sortBy('draw_date')"
                                    class="flex items-center gap-1 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                    งวดวันที่
                                    <span
                                        class="text-xs">{{ request('sort_by') === 'draw_date' ? (request('sort_order') === 'asc' ? '▲' : '▼') : '⇅' }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">
                                <button onclick="sortBy('customer_name')"
                                    class="flex items-center gap-1 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                    ลูกค้า
                                    <span
                                        class="text-xs">{{ request('sort_by') === 'customer_name' ? (request('sort_order') === 'asc' ? '▲' : '▼') : '⇅' }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">เลข</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">บน</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">ล่าง</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">โต๊ด</th>
                            <th class="px-3 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">
                                <button onclick="sortBy('total_amount')"
                                    class="flex items-center gap-1 hover:text-emerald-600 dark:hover:text-emerald-400 ml-auto transition-colors">
                                    รวม
                                    <span
                                        class="text-xs">{{ request('sort_by') === 'total_amount' ? (request('sort_order') === 'asc' ? '▲' : '▼') : '⇅' }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">สถานะ</th>
                            <th class="px-3 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">
                                <button onclick="sortBy('created_at')"
                                    class="flex items-center gap-1 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                    บันทึกเมื่อ
                                    <span
                                        class="text-xs">{{ request('sort_by') === 'created_at' ? (request('sort_order') === 'asc' ? '▲' : '▼') : '⇅' }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        @forelse($bets as $bet)
                            <tr class="transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-800"
                                id="bet-{{ $bet->id }}">
                                <td class="px-3 py-3 text-slate-800 dark:text-slate-300">
                                    @php
                                        $date = \Carbon\Carbon::parse($bet->draw_date);
                                        $thaiMonths = [
                                            '',
                                            'มกราคม',
                                            'กุมภาพันธ์',
                                            'มีนาคม',
                                            'เมษายน',
                                            'พฤษภาคม',
                                            'มิถุนายน',
                                            'กรกฎาคม',
                                            'สิงหาคม',
                                            'กันยายน',
                                            'ตุลาคม',
                                            'พฤศจิกายน',
                                            'ธันวาคม'
                                        ];
                                        $day = $date->day;
                                        $month = $thaiMonths[$date->month];
                                        $year = $date->year + 543;
                                    @endphp
                                    <span class="text-xs">{{ $day }} {{ $month }} {{ $year }}</span>
                                </td>
                                <td class="px-3 py-3 text-slate-900 dark:text-white font-semibold">{{ $bet->customer_name }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span
                                        class="font-bold text-lg {{ strlen($bet->number) === 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-violet-600 dark:text-violet-400' }}">
                                        {{ $bet->number }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right text-slate-800 dark:text-slate-300">
                                    {{ $bet->amount_top > 0 ? number_format($bet->amount_top, 2) : '-' }}
                                </td>
                                <td class="px-3 py-3 text-right text-slate-800 dark:text-slate-300">
                                    {{ $bet->amount_bottom > 0 ? number_format($bet->amount_bottom, 2) : '-' }}
                                </td>
                                <td class="px-3 py-3 text-right text-slate-800 dark:text-slate-300">
                                    {{ $bet->amount_toad > 0 ? number_format($bet->amount_toad, 2) : '-' }}
                                </td>
                                <td class="px-3 py-3 text-right font-bold text-slate-900 dark:text-white">
                                    {{ number_format($bet->total_amount, 2) }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad)
                                        <span
                                            class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">ถูกรางวัล</span>
                                    @elseif($bet->draw && $bet->draw->is_announced)
                                        <span
                                            class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">ไม่ถูก</span>
                                    @else
                                        <span
                                            class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">รอผล</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <div class="text-slate-800 dark:text-slate-300 text-sm">{{ $bet->creator->name }}</div>
                                    <div class="text-xs text-slate-600 dark:text-slate-400">
                                        {{ $bet->created_at->format('d/m/y H:i') }}</div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if(!$bet->draw || !$bet->draw->is_announced)
                                        <button
                                            onclick="deleteBet({{ $bet->id }}, '{{ $bet->customer_name }}', '{{ $bet->number }}')"
                                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">
                                            ลบ
                                        </button>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-3 py-16 text-center">
                                    <div class="text-slate-400 dark:text-slate-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-lg font-medium text-slate-600 dark:text-slate-400">ไม่พบข้อมูล</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $bets->links() }}
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

        // อักษรเดือนภาษาไทย
        const thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        // โหลดข้อมูลงวดที่มีการซื้อ
        window.onload = function () {
            populateDrawDates();
        };

        // --- แก้ไขฟังก์ชัน populateDrawDates ใน history.blade.php ---
        function populateDrawDates() {
            const select = document.getElementById('searchDrawDate');
            const drawDates = @json($drawDates);
            const currentDrawDate = "{{ request('draw_date') }}";

            let html = '<option value="">ทั้งหมด</option>';

            drawDates.forEach(item => {
                // 1. สร้าง Date Object จากค่าที่ส่งมา
                const date = new Date(item.draw_date);

                // 2. แปลงให้เป็นรูปแบบ YYYY-MM-DD (เช่น 2026-02-16) เพื่อใช้เป็น value สำหรับ Query
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const dateValue = `${year}-${month}-${day}`;

                // 3. เตรียมคำแสดงผลภาษาไทย (เช่น 16 กุมภาพันธ์ 2569)
                const dayThai = date.getDate();
                const monthThai = thaiMonths[date.getMonth() + 1];
                const yearThai = date.getFullYear() + 543;
                const label = `${dayThai} ${monthThai} ${yearThai}`;

                // 4. เช็คว่าตัวนี้คือค่าที่กำลังเลือกอยู่หรือไม่
                const selected = dateValue === currentDrawDate ? 'selected' : '';

                html += `<option value="${dateValue}" ${selected}>${label}</option>`;
            });

            select.innerHTML = html;
        }
        function search() {
            const customer = document.getElementById('searchCustomer').value;
            const drawDate = document.getElementById('searchDrawDate').value;

            const params = new URLSearchParams();
            if (customer) params.append('customer_name', customer);
            if (drawDate) params.append('draw_date', drawDate);

            // รักษา sort parameters
            const currentSort = "{{ request('sort_by') }}";
            const currentOrder = "{{ request('sort_order') }}";
            if (currentSort) params.append('sort_by', currentSort);
            if (currentOrder) params.append('sort_order', currentOrder);

            window.location.href = '{{ route("bets.history") }}?' + params.toString();
        }

        function sortBy(column) {
            const params = new URLSearchParams(window.location.search);
            const currentSort = "{{ request('sort_by', 'draw_date') }}";
            const currentOrder = "{{ request('sort_order', 'desc') }}";

            let newOrder = 'desc';
            if (column === currentSort) {
                // Toggle order
                newOrder = currentOrder === 'desc' ? 'asc' : 'desc';
            }

            params.set('sort_by', column);
            params.set('sort_order', newOrder);

            window.location.href = '{{ route("bets.history") }}?' + params.toString();
        }

        async function deleteBet(id, customerName, number) {
            const result = await Swal.fire({
                title: 'ยืนยันการลบ?',
                text: `ลบรายการ ${customerName} - ${number}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`{{ url('/bets') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire('ลบแล้ว!', data.message, 'success');
                        document.getElementById(`bet-${id}`).remove();
                    } else {
                        Swal.fire('ผิดพลาด!', data.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('ผิดพลาด!', 'เกิดข้อผิดพลาดในการลบข้อมูล', 'error');
                }
            }
        }
    </script>
</body>

</html>