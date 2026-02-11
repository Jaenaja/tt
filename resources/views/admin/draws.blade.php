<!-- resources/views/admin/draws.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>กรอกผลหวย</title>
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

        <!-- ฟอร์มกรอกผล -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">🎯 กรอกผลหวย</h1>

            <form id="drawForm" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">งวดวันที่ *</label>
                    <select id="drawDate" name="draw_date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-purple-50 p-4 rounded-lg border-2 border-purple-300">
                        <label class="block text-gray-800 font-bold mb-2">🟣 3 ตัวบน *</label>
                        <input type="text" name="result_3_top" maxlength="3" pattern="[0-9]{3}" required
                            class="w-full px-4 py-3 text-3xl font-bold text-center border-2 border-purple-400 rounded-lg focus:ring-2 focus:ring-purple-500"
                            placeholder="123">
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg border-2 border-blue-300">
                        <label class="block text-gray-800 font-bold mb-2">🔵 2 ตัวบน *</label>
                        <input type="text" name="result_2_top" maxlength="2" pattern="[0-9]{2}" required
                            class="w-full px-4 py-3 text-3xl font-bold text-center border-2 border-blue-400 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="23">
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg border-2 border-green-300">
                        <label class="block text-gray-800 font-bold mb-2">🟢 2 ตัวล่าง *</label>
                        <input type="text" name="result_2_bottom" maxlength="2" pattern="[0-9]{2}" required
                            class="w-full px-4 py-3 text-3xl font-bold text-center border-2 border-green-400 rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="45">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-bold py-4 rounded-lg transition text-lg shadow-lg">
                    💾 บันทึกผลและคำนวณรางวัล
                </button>
            </form>
        </div>

        <!-- รายการงวดที่ผ่านมา -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📋 รายการงวดที่ผ่านมา</h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">งวดวันที่</th>
                            <th class="px-4 py-3 text-center">3 ตัวบน</th>
                            <th class="px-4 py-3 text-center">2 ตัวบน</th>
                            <th class="px-4 py-3 text-center">2 ตัวล่าง</th>
                            <th class="px-4 py-3 text-center">สถานะ</th>
                            <th class="px-4 py-3 text-center">ประกาศโดย</th>
                            <th class="px-4 py-3 text-center">ดูรายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($draws as $draw)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold">
                                    {{ $draw->draw_date->format('d/m/') . ($draw->draw_date->format('Y') - 2500) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-2xl font-bold text-purple-600">
                                        {{ $draw->result_3_top ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-2xl font-bold text-blue-600">
                                        {{ $draw->result_2_top ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-2xl font-bold text-green-600">
                                        {{ $draw->result_2_bottom ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($draw->is_announced)
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                            ประกาศแล้ว
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                            รอประกาศ
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    @if($draw->announcedBy)
                                        {{ $draw->announcedBy->name }}<br>
                                        <span class="text-gray-500">{{ $draw->announced_at->format('d/m/y H:i') }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($draw->is_announced)
                                        <a href="{{ route('admin.draws.results', $draw->id) }}"
                                            class="text-blue-600 hover:text-blue-800 font-semibold">
                                            ดูผล
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    ยังไม่มีข้อมูลงวดหวย
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
        function generateDrawDates() {
            const select = document.getElementById('drawDate');
            const today = new Date();
            const dates = [];
            const currentDay = today.getDate();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();

            for (let i = -3; i <= 2; i++) {
                let month = currentMonth + Math.floor(i / 2);
                let year = currentYear;
                if (month < 0) { month += 12; year--; }
                if (month > 11) { month -= 12; year++; }

                dates.push(formatDateThai(new Date(year, month, 1)));
                dates.push(formatDateThai(new Date(year, month, 16)));
            }

            const uniqueDates = [...new Set(dates)].sort((a, b) => parseThaiDate(b) - parseThaiDate(a));

            select.innerHTML = uniqueDates.map((date, idx) =>
                `<option value="${date}">${date}</option>`
            ).join('');
        }

        function formatDateThai(date) {
            const d = date.getDate();
            const m = date.getMonth() + 1;
            const y = date.getFullYear() + 543;
            return `${d}/${m}/${y - 2500}`;
        }

        function parseThaiDate(dateStr) {
            const [d, m, y] = dateStr.split('/').map(Number);
            return new Date(y + 2500 - 543, m - 1, d);
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
                html: `<div class="text-left">
                    <p><strong>งวดวันที่:</strong> ${drawDate}</p>
                    <p><strong>3 ตัวบน:</strong> ${formData.get('result_3_top')}</p>
                    <p><strong>2 ตัวบน:</strong> ${formData.get('result_2_top')}</p>
                    <p><strong>2 ตัวล่าง:</strong> ${formData.get('result_2_bottom')}</p>
                    <p class="text-red-600 mt-3">⚠️ ระบบจะคำนวณรางวัลอัตโนมัติ</p>
                </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch('{{ route("admin.draws.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        draw_date: formData.get('draw_date'),
                        result_3_top: formData.get('result_3_top'),
                        result_2_top: formData.get('result_2_top'),
                        result_2_bottom: formData.get('result_2_bottom'),
                    })
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
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'เกิดข้อผิดพลาดในการบันทึก' });
            }
        });
    </script>
</body>

</html>