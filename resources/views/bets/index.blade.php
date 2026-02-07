<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มการเดิมพัน</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับหน้าหลัก</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">เพิ่มการเดิมพัน</h2>

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

            <form action="{{ route('bets.store') }}" method="POST" id="betForm">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">ชื่อลูกค้า</label>
                        <input type="text" name="customer_name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="{{ old('customer_name') }}" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">งวดวันที่</label>
                        <input type="date" name="bet_date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="{{ old('bet_date', now()->toDateString()) }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">เลข (2 หรือ 3 หลัก)</label>
                        <input type="text" name="number" id="numberInput"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-2xl font-bold text-center"
                            value="{{ old('number') }}" pattern="[0-9]{2,3}" maxlength="3" placeholder="00 หรือ 000"
                            required>
                        <p class="text-sm text-gray-500 mt-1">กรอกเลข 2 หลัก (00-99) หรือ 3 หลัก (000-999)</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">จำนวนเงิน (บาท)</label>
                        <input type="number" name="amount" id="amountInput"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-2xl font-bold"
                            value="{{ old('amount') }}" min="1" step="1" required>
                        <p class="text-sm text-gray-500 mt-1" id="amountHint">สูงสุด: -</p>
                        <p class="text-sm text-blue-600 mt-1" id="payoutHint"></p>
                    </div>
                </div>

                <!-- ตัวเลือกสำหรับ 2 หลัก -->
                <div class="mb-6" id="twoDigitOptions" style="display: none;">
                    <label class="block text-gray-700 font-semibold mb-3">ประเภท (สำหรับ 2 หลัก)</label>
                    <div class="flex gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="two_digit_type" value="straight" class="mr-2"
                                onchange="calculatePayout()">
                            <span class="px-4 py-2 bg-green-100 text-green-800 rounded-lg">เลขตรง (จ่าย 90 เท่า)</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="two_digit_type" value="toad" class="mr-2"
                                onchange="calculatePayout()">
                            <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg">เลขโต้ด (จ่าย 45 เท่า)</span>
                        </label>
                    </div>
                </div>

                <!-- Hidden field สำหรับ bet_type -->
                <input type="hidden" name="bet_type" id="betTypeInput" value="">

                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                        บันทึกการเดิมพัน
                    </button>
                    <button type="button" onclick="resetForm()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition">
                        รีเซ็ต
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const numberInput = document.getElementById('numberInput');
        const amountInput = document.getElementById('amountInput');
        const amountHint = document.getElementById('amountHint');
        const payoutHint = document.getElementById('payoutHint');
        const twoDigitOptions = document.getElementById('twoDigitOptions');
        const betTypeInput = document.getElementById('betTypeInput');

        // ตรวจสอบความยาวของเลขที่กรอก
        numberInput.addEventListener('input', function () {
            const length = this.value.length;

            if (length === 2) {
                // แสดงตัวเลือกเลขตรง/เลขโต้ด
                twoDigitOptions.style.display = 'block';
                amountInput.max = 1000;
                amountHint.textContent = 'สูงสุด: 1,000 บาท (2 หลัก)';
                betTypeInput.value = 'two_digit';

                // เลือกเลขตรงเป็นค่าเริ่มต้น
                if (!document.querySelector('input[name="two_digit_type"]:checked')) {
                    document.querySelector('input[name="two_digit_type"][value="straight"]').checked = true;
                }
            } else if (length === 3) {
                // ซ่อนตัวเลือกเลขตรง/เลขโต้ด
                twoDigitOptions.style.display = 'none';
                amountInput.max = 500;
                amountHint.textContent = 'สูงสุด: 500 บาท (3 หลัก)';
                betTypeInput.value = 'three_digit';

                // ยกเลิกการเลือกเลขตรง/เลขโต้ด
                document.querySelectorAll('input[name="two_digit_type"]').forEach(radio => {
                    radio.checked = false;
                });
            } else {
                // ยังกรอกไม่ครบ
                twoDigitOptions.style.display = 'none';
                amountHint.textContent = 'สูงสุด: -';
                betTypeInput.value = '';
            }

            calculatePayout();
        });

        function calculatePayout() {
            const amount = parseFloat(amountInput.value) || 0;
            const length = numberInput.value.length;

            if (amount === 0 || (length !== 2 && length !== 3)) {
                payoutHint.textContent = '';
                return;
            }

            let multiplier = 0;
            let typeText = '';

            if (length === 2) {
                const twoDigitType = document.querySelector('input[name="two_digit_type"]:checked');
                if (twoDigitType) {
                    if (twoDigitType.value === 'straight') {
                        multiplier = 90;
                        typeText = 'เลขตรง';
                    } else {
                        multiplier = 45;
                        typeText = 'เลขโต้ด';
                    }
                }
            } else if (length === 3) {
                multiplier = 500;
                typeText = '3 หลัก';
            }

            if (multiplier > 0) {
                const payout = amount * multiplier;
                payoutHint.textContent = `ถ้าถูก${typeText}จะได้: ` + payout.toLocaleString('th-TH', { minimumFractionDigits: 2 }) + ' บาท';
            } else {
                payoutHint.textContent = '';
            }
        }

        function resetForm() {
            document.getElementById('betForm').reset();
            twoDigitOptions.style.display = 'none';
            payoutHint.textContent = '';
            amountHint.textContent = 'สูงสุด: -';
            betTypeInput.value = '';
        }

        amountInput.addEventListener('input', calculatePayout);

        // จำกัดให้กรอกได้เฉพาะตัวเลข
        numberInput.addEventListener('keypress', function (e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>