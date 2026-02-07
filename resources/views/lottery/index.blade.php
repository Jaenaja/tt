<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับหน้าหลัก</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800">ผลหวย</h1>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">บันทึกผลหวย</h2>

            <form action="{{ route('lottery.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">งวดวันที่</label>
                        <input type="date" name="draw_date" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               value="{{ old('draw_date') }}" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">เลข 3 หลัก</label>
                        <input type="text" name="three_digit" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-2xl font-bold text-center"
                               value="{{ old('three_digit') }}" 
                               pattern="[0-9]{3}"
                               maxlength="3"
                               placeholder="000" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">เลข 2 หลัก</label>
                        <input type="text" name="two_digit" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-2xl font-bold text-center"
                               value="{{ old('two_digit') }}" 
                               pattern="[0-9]{2}"
                               maxlength="2"
                               placeholder="00" required>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    บันทึกผลหวย
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">ผลหวยย้อนหลัง</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">งวดวันที่</th>
                            <th class="px-4 py-3 text-center">เลข 3 หลัก</th>
                            <th class="px-4 py-3 text-center">เลข 2 หลัก</th>
                            <th class="px-4 py-3 text-center">บันทึกเมื่อ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $result->draw_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-2xl font-bold text-purple-600">{{ $result->three_digit }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-2xl font-bold text-green-600">{{ $result->two_digit }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-500">
                                {{ $result->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-center text-gray-500">ยังไม่มีข้อมูลผลหวย</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $results->links() }}
            </div>
        </div>
    </div>
</body>
</html>
