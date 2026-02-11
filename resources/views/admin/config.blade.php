<!-- resources/views/admin/config.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ตั้งค่าอัตราจ่าย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-3xl">
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

        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">⚙️ ตั้งค่าอัตราจ่าย</h1>

            <div class="mb-6 p-4 bg-yellow-50 border-2 border-yellow-200 rounded-lg">
                <p class="text-yellow-800 font-semibold">⚠️ คำเตือน</p>
                <p class="text-yellow-700 text-sm mt-1">การเปลี่ยนแปลงอัตราจ่ายจะมีผลกับการคำนวณรางวัลทั้งหมด</p>
            </div>

            <form id="configForm" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 2 ตัวบน -->
                    <div class="bg-blue-50 p-4 rounded-lg border-2 border-blue-200">
                        <label class="block text-gray-800 font-bold mb-3 text-base">
                            🔵 2 ตัวบน
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="rate_2_top" value="{{ $configs['rate_2_top'] }}" min="0" step="1"
                                required
                                class="w-24 px-3 py-2 text-xl font-bold text-center border-2 border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <span class="text-gray-600 font-semibold whitespace-nowrap">เท่า</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">แทง 10 บาท ได้
                            {{ number_format($configs['rate_2_top'] * 10) }} บาท</p>
                    </div>

                    <!-- 2 ตัวล่าง -->
                    <div class="bg-green-50 p-4 rounded-lg border-2 border-green-200">
                        <label class="block text-gray-800 font-bold mb-3 text-base">
                            🟢 2 ตัวล่าง
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="rate_2_bottom" value="{{ $configs['rate_2_bottom'] }}" min="0"
                                step="1" required
                                class="w-24 px-3 py-2 text-xl font-bold text-center border-2 border-green-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <span class="text-gray-600 font-semibold whitespace-nowrap">เท่า</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">แทง 10 บาท ได้
                            {{ number_format($configs['rate_2_bottom'] * 10) }} บาท</p>
                    </div>

                    <!-- 3 ตัวบน -->
                    <div class="bg-purple-50 p-4 rounded-lg border-2 border-purple-200">
                        <label class="block text-gray-800 font-bold mb-3 text-base">
                            🟣 3 ตัวบน
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="rate_3_top" value="{{ $configs['rate_3_top'] }}" min="0" step="1"
                                required
                                class="w-24 px-3 py-2 text-xl font-bold text-center border-2 border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <span class="text-gray-600 font-semibold whitespace-nowrap">เท่า</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">แทง 10 บาท ได้
                            {{ number_format($configs['rate_3_top'] * 10) }} บาท</p>
                    </div>

                    <!-- 3 ตัวโต๊ด -->
                    <div class="bg-orange-50 p-4 rounded-lg border-2 border-orange-200">
                        <label class="block text-gray-800 font-bold mb-3 text-base">
                            🟠 3 ตัวโต๊ด
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="rate_3_toad" value="{{ $configs['rate_3_toad'] }}" min="0"
                                step="1" required
                                class="w-24 px-3 py-2 text-xl font-bold text-center border-2 border-orange-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                            <span class="text-gray-600 font-semibold whitespace-nowrap">เท่า</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">แทง 10 บาท ได้
                            {{ number_format($configs['rate_3_toad'] * 10) }} บาท</p>
                    </div>
                </div>

                <div class="pt-6 border-t">
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 rounded-lg transition text-lg shadow-lg">
                        💾 บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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