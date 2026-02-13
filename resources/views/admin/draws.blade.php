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
                                    {{ thai_date($draw->draw_date) }}
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

    <!-- แทนที่ส่วน <script> ใน resources/views/admin/draws.blade.php -->

    <script>
        // อักษรย่อเดือนภาษาไทย
        const thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        /**
         * สร้างรายการวันที่งวดหวย
         * - งวดย้อนหลัง 3 งวด
         * - งวดในอนาคต 4 งวด
         * - Auto-select งวดถัดไปที่ใกล้ที่สุด
         */
        function generateDrawDates() {
            const select = document.getElementById('drawDate');
            const today = new Date();
            today.setHours(0, 0, 0, 0); // ตั้งเวลาเป็น 00:00:00

            // หาวันที่งวดทั้งหมด
            const allDrawDates = getAllDrawDates(today, 3, 4); // 3 งวดย้อนหลัง, 4 งวดอนาคต

            // หางวดถัดไปที่ควรเลือก
            const nextDrawDate = getNextDrawDate(today);

            // สร้าง options
            select.innerHTML = allDrawDates.map((dateOption) => {
                const isSelected = dateOption.value === formatDateForDatabase(nextDrawDate);
                return `<option value="${dateOption.value}" ${isSelected ? 'selected' : ''}>
                ${dateOption.label}
            </option>`;
            }).join('');
        }

        /**
         * หาวันที่งวดทั้งหมด (ย้อนหลัง + อนาคต)
         * @param {Date} today - วันที่ปัจจุบัน
         * @param {number} pastCount - จำนวนงวดย้อนหลัง
         * @param {number} futureCount - จำนวนงวดในอนาคต
         * @returns {Array} - รายการวันที่งวด
         */
        function getAllDrawDates(today, pastCount, futureCount) {
            const draws = [];

            // เพิ่มงวดย้อนหลัง
            const pastDates = getPastDrawDates(today, pastCount);
            draws.push(...pastDates);

            // เพิ่มงวดในอนาคต
            const futureDates = getFutureDrawDates(today, futureCount);
            draws.push(...futureDates);

            // เรียงจากใหม่ไปเก่า
            draws.sort((a, b) => new Date(b.value) - new Date(a.value));

            return draws;
        }

        /**
         * หางวดย้อนหลัง
         * @param {Date} today - วันที่ปัจจุบัน
         * @param {number} count - จำนวนงวด
         * @returns {Array} - รายการวันที่งวดย้อนหลัง
         */
        function getPastDrawDates(today, count) {
            const draws = [];
            let currentDate = new Date(today);

            while (draws.length < count) {
                const prevDraw = getPreviousDrawDate(currentDate);
                draws.push(createDateOption(prevDraw));
                currentDate = new Date(prevDraw);
                currentDate.setDate(currentDate.getDate() - 1); // ถอยหลัง 1 วัน
            }

            return draws;
        }

        /**
         * หางวดในอนาคต
         * @param {Date} today - วันที่ปัจจุบัน
         * @param {number} count - จำนวนงวด
         * @returns {Array} - รายการวันที่งวดในอนาคต
         */
        function getFutureDrawDates(today, count) {
            const draws = [];
            let currentDate = new Date(today);

            while (draws.length < count) {
                const nextDraw = getNextDrawDate(currentDate);

                // ป้องกันการเพิ่มงวดซ้ำ
                const alreadyExists = draws.some(d => d.value === formatDateForDatabase(nextDraw));
                if (!alreadyExists) {
                    draws.push(createDateOption(nextDraw));
                }

                currentDate = new Date(nextDraw);
                currentDate.setDate(currentDate.getDate() + 1); // เดินหน้า 1 วัน
            }

            return draws;
        }

        /**
         * หางวดถัดไป (งวดที่ใกล้ที่สุดในอนาคต)
         * @param {Date} referenceDate - วันที่อ้างอิง
         * @returns {Date} - วันที่งวดถัดไป
         */
        function getNextDrawDate(referenceDate) {
            const date = new Date(referenceDate);
            const day = date.getDate();
            const month = date.getMonth();
            const year = date.getFullYear();

            // กรณีที่ 1: ถ้าวันที่ปัจจุบัน < 1 → งวดถัดไปคือวันที่ 1 เดือนนี้
            if (day < 1) {
                return new Date(year, month, 1);
            }
            // กรณีที่ 2: ถ้าวันที่ 1 <= วันที่ปัจจุบัน < 16 → งวดถัดไปคือวันที่ 16 เดือนนี้
            else if (day >= 1 && day < 16) {
                return new Date(year, month, 16);
            }
            // กรณีที่ 3: ถ้าวันที่ปัจจุบัน >= 16 → งวดถัดไปคือวันที่ 1 เดือนหน้า
            else {
                return new Date(year, month + 1, 1);
            }
        }

        /**
         * หางวดก่อนหน้า (งวดย้อนหลัง)
         * @param {Date} referenceDate - วันที่อ้างอิง
         * @returns {Date} - วันที่งวดก่อนหน้า
         */
        function getPreviousDrawDate(referenceDate) {
            const date = new Date(referenceDate);
            const day = date.getDate();
            const month = date.getMonth();
            const year = date.getFullYear();

            // กรณีที่ 1: ถ้าวันที่ปัจจุบัน <= 1 → งวดก่อนหน้าคือวันที่ 16 เดือนที่แล้ว
            if (day <= 1) {
                return new Date(year, month - 1, 16);
            }
            // กรณีที่ 2: ถ้าวันที่ 1 < วันที่ปัจจุบัน <= 16 → งวดก่อนหน้าคือวันที่ 1 เดือนนี้
            else if (day > 1 && day <= 16) {
                return new Date(year, month, 1);
            }
            // กรณีที่ 3: ถ้าวันที่ปัจจุบัน > 16 → งวดก่อนหน้าคือวันที่ 16 เดือนนี้
            else {
                return new Date(year, month, 16);
            }
        }

        /**
         * สร้าง object ของวันที่สำหรับ option
         */
        function createDateOption(date) {
            return {
                value: formatDateForDatabase(date),  // 2026-03-16
                label: formatDateThai(date)          // 16 มีนาคม 2569
            };
        }

        /**
         * แปลงวันที่เป็นรูปแบบ Y-m-d สำหรับ database
         */
        function formatDateForDatabase(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        /**
         * แปลงวันที่เป็นรูปแบบไทย
         */
        function formatDateThai(date) {
            const day = date.getDate();
            const month = thaiMonths[date.getMonth() + 1];
            const year = date.getFullYear() + 543;
            return `${day} ${month} ${year}`;
        }

        /**
         * สร้าง object ของวันที่สำหรับ option
         */
        function createDateOption(date) {
            return {
                value: formatDateForDatabase(date),  // 2026-03-16
                label: formatDateThai(date)          // 16 มีนาคม 2569
            };
        }

        /**
         * แปลงวันที่เป็นรูปแบบ Y-m-d สำหรับ database
         * เช่น: 2026-03-16
         */
        function formatDateForDatabase(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        /**
         * แปลงวันที่เป็นรูปแบบไทย
         * เช่น: 16 มีนาคม 2569
         */
        function formatDateThai(date) {
            const day = date.getDate();
            const month = thaiMonths[date.getMonth() + 1];
            const year = date.getFullYear() + 543; // แปลงเป็น พ.ศ. 4 หลัก
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