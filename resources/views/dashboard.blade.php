<!-- resources/views/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">🎰 ระบบหวย</h1>
                    <p class="text-blue-100 mt-1">ยินดีต้อนรับ, {{ $user->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-100">
                        {{ $user->role === 'admin' ? '👑 ผู้ดูแลระบบ' : '👤 พนักงาน' }}
                    </p>
                    <form action="{{ route('logout') }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit"
                            class="text-sm bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-1 rounded transition">
                            ออกจากระบบ
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- สถิติวันนี้ -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm text-gray-600">ยอดแทงวันนี้</p>
                <p class="text-3xl font-bold text-blue-600" id="totalSales">
                    {{ number_format($todayStats['total_amount'], 2) }}
                </p>
                <p class="text-xs text-gray-500">บาท</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm text-gray-600">บน</p>
                <p class="text-3xl font-bold text-purple-600" id="totalTop">
                    {{ number_format($todayStats['total_top'], 2) }}
                </p>
                <p class="text-xs text-gray-500">บาท</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm text-gray-600">ล่าง</p>
                <p class="text-3xl font-bold text-green-600" id="totalBottom">
                    {{ number_format($todayStats['total_bottom'], 2) }}
                </p>
                <p class="text-xs text-gray-500">บาท</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm text-gray-600">โต๊ด</p>
                <p class="text-3xl font-bold text-orange-600" id="totalToad">
                    {{ number_format($todayStats['total_toad'], 2) }}
                </p>
                <p class="text-xs text-gray-500">บาท</p>
            </div>
        </div>

        <!-- งวดหวย -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- งวดถัดไป -->
            <div class="bg-gradient-to-br from-yellow-400 to-orange-400 rounded-lg shadow-lg p-6 text-gray-900">
                <h2 class="text-xl font-bold mb-3">📅 งวดถัดไป</h2>
                @if($upcomingDraw)
                    <div class="bg-white bg-opacity-90 rounded-lg p-4">
                        <p class="text-3xl font-bold text-center">
                            {{ thai_date_full($upcomingDraw->draw_date) }}
                        </p>
                        <p class="text-sm text-center text-gray-600 mt-2">รอประกาศผล</p>
                    </div>
                @else
                    <p class="text-center text-gray-700">ยังไม่มีงวดที่รอประกาศผล</p>
                @endif
            </div>

            <!-- งวดล่าสุด -->
            <div class="bg-gradient-to-br from-green-400 to-blue-400 rounded-lg shadow-lg p-6 text-white">
                <h2 class="text-xl font-bold mb-3">🏆 งวดล่าสุด</h2>
                @if($latestDraw)
                    <div class="bg-white bg-opacity-90 rounded-lg p-4 text-gray-900">
                        <p class="text-sm text-center text-gray-600">
                            {{ thai_date_full($latestDraw->draw_date) }}
                        </p>
                        <div class="grid grid-cols-3 gap-2 mt-2 text-center">
                            <div>
                                <p class="text-xs text-gray-600">3 ตัวบน</p>
                                <p class="text-xl font-bold text-purple-600">{{ $latestDraw->result_3_top }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">2 ตัวบน</p>
                                <p class="text-xl font-bold text-blue-600">{{ $latestDraw->result_2_top }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">2 ตัวล่าง</p>
                                <p class="text-xl font-bold text-green-600">{{ $latestDraw->result_2_bottom }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-center">ยังไม่มีผลหวย</p>
                @endif
            </div>
        </div>

        <!-- เมนูหลัก -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <a href="{{ route('bets.index') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-md p-6 text-center transition">
                <div class="text-4xl mb-2">📝</div>
                <p class="font-bold">รับแทงหวย</p>
            </a>
            <a href="{{ route('bets.history') }}"
                class="bg-purple-600 hover:bg-purple-700 text-white rounded-lg shadow-md p-6 text-center transition">
                <div class="text-4xl mb-2">📜</div>
                <p class="font-bold">ประวัติการแทง</p>
            </a>

            @if($user->isAdmin())
                <a href="{{ route('admin.draws') }}"
                    class="bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-md p-6 text-center transition">
                    <div class="text-4xl mb-2">🎯</div>
                    <p class="font-bold">กรอกผลหวย</p>
                </a>
                <a href="{{ route('admin.reports.statistics') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md p-6 text-center transition">
                    <div class="text-4xl mb-2">📊</div>
                    <p class="font-bold">สถิติและกราฟ</p>
                </a>
                <a href="{{ route('admin.users') }}"
                    class="bg-orange-600 hover:bg-orange-700 text-white rounded-lg shadow-md p-6 text-center transition">
                    <div class="text-4xl mb-2">👥</div>
                    <p class="font-bold">จัดการผู้ใช้</p>
                </a>
                <a href="{{ route('admin.config') }}"
                    class="bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-md p-6 text-center transition">
                    <div class="text-4xl mb-2">⚙️</div>
                    <p class="font-bold">ตั้งค่าอัตราจ่าย</p>
                </a>
            @endif
        </div>

        <!-- สถิติ Admin -->
        @if($adminStats)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📊 สถิติระบบ</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-gray-600">จำนวนผู้ใช้</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $adminStats['total_users'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600">งวดทั้งหมด</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $adminStats['total_draws'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600">ประกาศผลแล้ว</p>
                        <p class="text-3xl font-bold text-green-600">{{ $adminStats['announced_draws'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- รายการล่าสุด -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">🕐 รายการแทงล่าสุด</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">เวลา</th>
                            <th class="px-3 py-2 text-left">งวด</th>
                            <th class="px-3 py-2 text-left">ลูกค้า</th>
                            <th class="px-3 py-2 text-center">เลข</th>
                            <th class="px-3 py-2 text-right">ยอดแทง</th>
                            <th class="px-3 py-2 text-left">สร้างโดย</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($recentBets as $bet)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-xs text-gray-600">
                                    {{ $bet->created_at->format('H:i') }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $bet->draw_date->format('d/m/') . ($bet->draw_date->format('Y') + 543 - 2500) }}
                                </td>
                                <td class="px-3 py-2 font-semibold">{{ $bet->customer_name }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span
                                        class="font-bold text-lg {{ strlen($bet->number) === 2 ? 'text-blue-600' : 'text-purple-600' }}">
                                        {{ $bet->number }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right font-semibold">
                                    {{ number_format($bet->total_amount, 2) }}
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-600">
                                    {{ $bet->creator->name }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-500">
                                    ยังไม่มีรายการแทง
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // อัพเดทยอดแทงทุก 10 วินาที
        setInterval(() => {
            fetch('/api/sales/realtime')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalSales').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalSales);
                    document.getElementById('totalTop').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalTop);
                    document.getElementById('totalBottom').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalBottom);
                    document.getElementById('totalToad').textContent =
                        new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalToad);
                })
                .catch(error => console.error('Error:', error));
        }, 10000);
    </script>
</body>

</html>