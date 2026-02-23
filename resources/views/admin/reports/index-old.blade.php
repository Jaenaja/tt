<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการงวดทั้งหมด</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700;800&display=swap');

        body {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">

    <div class="container mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-2xl p-8 mb-8 text-white">
            <h1 class="text-4xl md:text-5xl font-bold mb-2">📊 รายงานสรุปแต่ละงวด</h1>
            <p class="text-xl opacity-90">เลือกงวดที่ต้องการดูรายละเอียด</p>
        </div>

        {{-- รายการงวด --}}
        <div class="bg-white rounded-xl shadow-xl p-8">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                            <th class="px-6 py-4 text-left font-bold">งวดวันที่</th>
                            <th class="px-6 py-4 text-center font-bold">สถานะ</th>
                            <th class="px-6 py-4 text-center font-bold">รางวัล 3 ตัวบน</th>
                            <th class="px-6 py-4 text-center font-bold">รางวัล 2 ตัวบน</th>
                            <th class="px-6 py-4 text-center font-bold">รางวัล 2 ตัวล่าง</th>
                            <th class="px-6 py-4 text-center font-bold">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($draws as $draw)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::parse($draw->draw_date)->locale('th')->translatedFormat('j F Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($draw->is_announced)
                                        <span
                                            class="inline-block px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                            ✓ ประกาศแล้ว
                                        </span>
                                    @else
                                        <span
                                            class="inline-block px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                            ⏳ กำลังขาย
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($draw->is_announced)
                                        <span class="text-2xl font-bold text-indigo-600">{{ $draw->result_3_top }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($draw->is_announced)
                                        <span class="text-2xl font-bold text-blue-600">{{ $draw->result_2_top }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($draw->is_announced)
                                        <span class="text-2xl font-bold text-green-600">{{ $draw->result_2_bottom }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.reports.summary', $draw->id) }}"
                                        class="inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                                        ดูรายละเอียด
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
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
                class="inline-block px-8 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition-colors shadow-lg">
                ← กลับหน้าหลัก
            </a>
        </div>
    </div>

</body>

</html>