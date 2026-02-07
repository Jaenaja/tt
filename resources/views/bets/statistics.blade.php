<!-- resources/views/bets/statistics.blade.php -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับหน้าหลัก</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">สถิติหวย</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Two Digit Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">สถิติเลข 2 หลัก (ออกบ่อยสุด)</h3>
                <div class="space-y-3">
                    @forelse($twoDigitStats as $index => $stat)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                        <div class="flex items-center space-x-4">
                            <span class="text-2xl font-bold text-gray-400">{{ $index + 1 }}</span>
                            <span class="text-3xl font-bold text-green-600">{{ $stat->number }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-800">{{ $stat->frequency }}</p>
                            <p class="text-xs text-gray-500">ครั้ง</p>
                            @if($stat->last_drawn)
                            <p class="text-xs text-gray-400">ออกล่าสุด: {{ $stat->last_drawn->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-8">ยังไม่มีข้อมูลสถิติ</p>
                    @endforelse
                </div>
            </div>

            <!-- Three Digit Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">สถิติเลข 3 หลัก (ออกบ่อยสุด)</h3>
                <div class="space-y-3">
                    @forelse($threeDigitStats as $index => $stat)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                        <div class="flex items-center space-x-4">
                            <span class="text-2xl font-bold text-gray-400">{{ $index + 1 }}</span>
                            <span class="text-3xl font-bold text-purple-600">{{ $stat->number }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-800">{{ $stat->frequency }}</p>
                            <p class="text-xs text-gray-500">ครั้ง</p>
                            @if($stat->last_drawn)
                            <p class="text-xs text-gray-400">ออกล่าสุด: {{ $stat->last_drawn->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-8">ยังไม่มีข้อมูลสถิติ</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</body>
</html>