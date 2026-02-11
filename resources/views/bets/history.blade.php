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
                    <input type="text" id="searchCustomer" class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                        placeholder="ชื่อลูกค้า">
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
                            <th class="px-3 py-2 text-left">งวดวันที่</th>
                            <th class="px-3 py-2 text-left">ลูกค้า</th>
                            <th class="px-3 py-2 text-center">เลข</th>
                            <th class="px-3 py-2 text-right">บน</th>
                            <th class="px-3 py-2 text-right">ล่าง</th>
                            <th class="px-3 py-2 text-right">โต๊ด</th>
                            <th class="px-3 py-2 text-right">รวม</th>
                            <th class="px-3 py-2 text-center">สถานะ</th>
                            <th class="px-3 py-2 text-left">บันทึกเมื่อ</th>
                            <th class="px-3 py-2 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($bets as $bet)
                            <tr class="hover:bg-gray-50" id="bet-{{ $bet->id }}">
                                <td class="px-3 py-2">
                                    {{ $bet->draw_date->format('d/m/') . ($bet->draw_date->format('Y') - 2500) }}
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
        function search() {
            const customer = document.getElementById('searchCustomer').value;
            const drawDate = document.getElementById('searchDrawDate').value;

            const params = new URLSearchParams();
            if (customer) params.append('customer_name', customer);
            if (drawDate) params.append('draw_date', drawDate);

            window.location.href = '{{ route("bets.history") }}?' + params.toString();
        }

        async function deleteBet(id, customerName, number) {
            const result = await Swal.fire({
                title: 'ยืนยันการลบ?',
                html: `ลูกค้า: <strong>${customerName}</strong><br>เลข: <strong>${number}</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`/bets/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    document.getElementById(`bet-${id}`).remove();
                } else {
                    Swal.fire({ icon: 'error', title: 'ERROR', text: data.message });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'เกิดข้อผิดพลาด' });
            }
        }
    </script>
</body>

</html>