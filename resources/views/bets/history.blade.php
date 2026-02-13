<!-- resources/views/bets/history.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ประวัติการแทง</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับหน้าหลัก</a>
            <div class="text-sm text-gray-600">
                <span class="font-semibold">{{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline ml-3">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800">ออกจากระบบ</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">📜 ประวัติการแทง</h1>

            <!-- ฟิลเตอร์ -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">ค้นหาชื่อลูกค้า</label>
                    <input type="text" id="searchCustomer" value="{{ request('customer_name') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="ชื่อลูกค้า">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">งวดวันที่</label>
                    <select id="searchDrawDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">ทั้งหมด</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="search()"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg">
                        🔍 ค้นหา
                    </button>
                </div>
            </div>

            <!-- ตาราง -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">
                                <button onclick="sortBy('draw_date')"
                                    class="flex items-center gap-1 hover:text-blue-600">
                                    งวดวันที่
                                    <span
                                        class="text-xs">{{ request('sort_by') === 'draw_date' ? (request('sort_order') === 'asc' ? '▲' : '▼') : '⇅' }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2 text-left">
                                <button onclick="sortBy('customer_name')"
                                    class="flex items-center gap-1 hover:text-blue-600">
                                    ลูกค้า
                                    <span
                                        class="text-xs">{{ request('sort_by') === 'customer_name' ? (request('sort_order') === 'asc' ? '▲' : '▼') : '⇅' }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2 text-center">เลข</th>
                            <th class="px-3 py-2 text-right">บน</th>
                            <th class="px-3 py-2 text-right">ล่าง</th>
                            <th class="px-3 py-2 text-right">โต๊ด</th>
                            <th class="px-3 py-2 text-right">
                                <button onclick="sortBy('total_amount')"
                                    class="flex items-center gap-1 hover:text-blue-600 ml-auto">
                                    รวม
                                    <span
                                        class="text-xs">{{ request('sort_by') === 'total_amount' ? (request('sort_order') === 'asc' ? '▲' : '▼') : '⇅' }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2 text-center">สถานะ</th>
                            <th class="px-3 py-2 text-left">
                                <button onclick="sortBy('created_at')"
                                    class="flex items-center gap-1 hover:text-blue-600">
                                    บันทึกเมื่อ
                                    <span
                                        class="text-xs">{{ request('sort_by') === 'created_at' ? (request('sort_order') === 'asc' ? '▲' : '▼') : '⇅' }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($bets as $bet)
                            <tr class="hover:bg-gray-50" id="bet-{{ $bet->id }}">
                                <td class="px-3 py-2">
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
                                    <span class="text-xs text-gray-600">{{ $day }} {{ $month }} {{ $year }}</span>
                                </td>
                                <td class="px-3 py-2 font-semibold">{{ $bet->customer_name }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span
                                        class="font-bold text-lg {{ strlen($bet->number) === 2 ? 'text-blue-600' : 'text-purple-600' }}">
                                        {{ $bet->number }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ $bet->amount_top > 0 ? number_format($bet->amount_top, 2) : '-' }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ $bet->amount_bottom > 0 ? number_format($bet->amount_bottom, 2) : '-' }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ $bet->amount_toad > 0 ? number_format($bet->amount_toad, 2) : '-' }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold">{{ number_format($bet->total_amount, 2) }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if($bet->is_win_top || $bet->is_win_bottom || $bet->is_win_toad)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">ถูกรางวัล</span>
                                    @elseif($bet->draw && $bet->draw->is_announced)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">ไม่ถูก</span>
                                    @else
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">รอผล</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-600">
                                    {{ $bet->creator->name }}<br>
                                    {{ $bet->created_at->format('d/m/y H:i') }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if(!$bet->draw || !$bet->draw->is_announced)
                                        <button
                                            onclick="deleteBet({{ $bet->id }}, '{{ $bet->customer_name }}', '{{ $bet->number }}')"
                                            class="text-red-600 hover:text-red-800">
                                            ลบ
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-3 py-8 text-center text-gray-500">
                                    ไม่พบข้อมูล
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