<!-- resources/views/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>222ระบบจัดการหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800">ระบบจัดการหวย</h1>
            <p class="text-gray-600">วันที่: {{ now()->format('d/m/Y') }}</p>
        </div>

        <!-- Quick Betting Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">รับแทงด่วน</h2>

            <form id="quickBetForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">ชื่อลูกค้า</label>
                        <div class="flex gap-2">
                            <input type="text" id="customerName"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="ชื่อลูกค้า" required>
                            <button type="button" onclick="clearCustomer()"
                                class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm"
                                title="เปลี่ยนลูกค้า">
                                ✕
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">งวดวันที่</label>
                        <input type="date" id="betDate"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            value="{{ now()->toDateString() }}" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">ประเภท</label>
                        <select id="betType"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="two_digit">2 หลัก (สูงสุด 1,000)</option>
                            <option value="three_digit">3 หลัก (สูงสุด 500)</option>
                        </select>
                    </div>
                </div>

                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">พิมพ์รายการ (เลข จำนวนเงิน)</label>
                    <input type="text" id="quickInput"
                        class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg font-mono"
                        placeholder="ตัวอย่าง: 10 20, 555 100, 88 50" autocomplete="off">
                    <p class="text-sm text-gray-600 mt-2">
                        💡 กรอกหลายรายการได้ เช่น: <span class="font-mono bg-white px-2 py-1 rounded">10 20, 25 50, 88
                            100</span>
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="addFromQuickInput()"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                        เพิ่มรายการ
                    </button>
                    <button type="button" onclick="saveAllBets()"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition">
                        บันทึกทั้งหมด (<span id="betCount">0</span>)
                    </button>
                    <button type="button" onclick="clearAllBets()"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition">
                        ล้าง
                    </button>
                </div>
            </form>

            <!-- Bet List -->
            <div class="mt-6" id="betListContainer" style="display: none;">
                <h3 class="text-lg font-bold text-gray-800 mb-3">รายการที่รอบันทึก</h3>
                <div class="bg-gray-50 rounded-lg p-4 max-h-96 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left">เลข</th>
                                <th class="px-3 py-2 text-right">จำนวนเงิน</th>
                                <th class="px-3 py-2 text-right">จ่าย</th>
                                <th class="px-3 py-2 text-center">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="betListBody"></tbody>
                    </table>
                    <div class="mt-3 pt-3 border-t-2 border-gray-300">
                        <div class="flex justify-between font-bold text-lg">
                            <span>รวมทั้งหมด:</span>
                            <span id="totalAmount" class="text-blue-600">0.00 บาท</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">ยอดขายรวม</h3>
                <p class="text-4xl font-bold" id="totalSales">{{ number_format($totalSales ?? 0, 2) }}</p>
                <p class="text-sm mt-2">บาท</p>
            </div>
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">ยอดขาย 2 หลัก</h3>
                <p class="text-4xl font-bold" id="twoDigitSales">{{ number_format($twoDigitSales ?? 0, 2) }}</p>
                <p class="text-sm mt-2">บาท</p>
            </div>
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">ยอดขาย 3 หลัก</h3>
                <p class="text-4xl font-bold" id="threeDigitSales">{{ number_format($threeDigitSales ?? 0, 2) }}</p>
                <p class="text-sm mt-2">บาท</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">สถิติเลข 2 หลัก (Top 10)</h3>
                <canvas id="twoDigitChart"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">สถิติเลข 3 หลัก (Top 10)</h3>
                <canvas id="threeDigitChart"></canvas>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <a href="{{ route('bets.index') }}"
                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-4 px-6 rounded-lg text-center transition">
                เพิ่มการเดิมพัน
            </a>
            <a href="{{ route('lottery.index') }}"
                class="bg-green-500 hover:bg-green-600 text-white font-semibold py-4 px-6 rounded-lg text-center transition">
                ผลหวย
            </a>
            <a href="{{ route('bets.history') }}"
                class="bg-purple-500 hover:bg-purple-600 text-white font-semibold py-4 px-6 rounded-lg text-center transition">
                ประวัติ
            </a>
            <a href="{{ route('bets.statistics') }}"
                class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-4 px-6 rounded-lg text-center transition">
                สถิติ
            </a>
        </div>

        <!-- Recent Bets -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">รายการเดิมพันล่าสุด</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">ชื่อลูกค้า</th>
                            <th class="px-4 py-3">ประเภท</th>
                            <th class="px-4 py-3">เลข</th>
                            <th class="px-4 py-3">จำนวนเงิน</th>
                            <th class="px-4 py-3">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody id="recentBets">
                        @forelse($recentBets ?? [] as $bet)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $bet->customer_name }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 rounded {{ $bet->bet_type === 'three_digit' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $bet->bet_type === 'three_digit' ? '3 หลัก' : '2 หลัก' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-bold">{{ $bet->number }}</td>
                                <td class="px-4 py-3">{{ number_format($bet->amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 rounded {{ $bet->status === 'won' ? 'bg-green-100 text-green-800' : '' }} {{ $bet->status === 'lost' ? 'bg-red-100 text-red-800' : '' }} {{ $bet->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        @if($bet->status === 'won') ถูกรางวัล
                                        @elseif($bet->status === 'lost') ไม่ถูก
                                        @else รอผล
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-center text-gray-500">ยังไม่มีรายการเดิมพัน</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let betList = [];

        // โหลดชื่อลูกค้าที่บันทึกไว้
        window.onload = function () {
            const savedName = localStorage.getItem('lastCustomerName');
            const savedDate = localStorage.getItem('lastBetDate');
            const savedType = localStorage.getItem('lastBetType');

            if (savedName) {
                document.getElementById('customerName').value = savedName;
            }
            if (savedDate) {
                document.getElementById('betDate').value = savedDate;
            }
            if (savedType) {
                document.getElementById('betType').value = savedType;
            }

            // โฟกัสที่ช่องพิมพ์เลข
            document.getElementById('quickInput').focus();
        };

        function addFromQuickInput() {
            const input = document.getElementById('quickInput').value.trim();
            const betType = document.getElementById('betType').value;

            if (!input) {
                alert('กรุณากรอกรายการ');
                return;
            }

            const entries = input.split(',');
            const maxAmount = betType === 'three_digit' ? 500 : 1000;
            const maxLength = betType === 'three_digit' ? 3 : 2;

            entries.forEach(entry => {
                const parts = entry.trim().split(/\s+/);
                if (parts.length >= 2) {
                    const number = parts[0];
                    const amount = parseFloat(parts[1]);

                    if (number.length !== maxLength) {
                        alert(`เลข "${number}" ไม่ถูกต้อง ต้องเป็น ${maxLength} หลัก`);
                        return;
                    }

                    if (amount > maxAmount) {
                        alert(`จำนวนเงิน ${amount} บาท เกินกำหนด (สูงสุด ${maxAmount} บาท)`);
                        return;
                    }

                    if (!isNaN(amount) && amount > 0) {
                        betList.push({ number, amount, betType });
                    }
                }
            });

            document.getElementById('quickInput').value = '';
            document.getElementById('quickInput').focus();
            updateBetList();
        }

        function updateBetList() {
            const tbody = document.getElementById('betListBody');
            const container = document.getElementById('betListContainer');
            const betCount = document.getElementById('betCount');

            if (betList.length === 0) {
                container.style.display = 'none';
                betCount.textContent = '0';
                return;
            }

            container.style.display = 'block';
            betCount.textContent = betList.length;

            let html = '';
            let total = 0;
            const multipliers = { two_digit: 90, three_digit: 500 };

            betList.forEach((bet, index) => {
                const payout = bet.amount * multipliers[bet.betType];
                total += bet.amount;

                html += `
                    <tr class="border-b hover:bg-gray-100">
                        <td class="px-3 py-2 font-bold text-lg">${bet.number}</td>
                        <td class="px-3 py-2 text-right">${bet.amount.toFixed(2)}</td>
                        <td class="px-3 py-2 text-right text-green-600">${payout.toFixed(2)}</td>
                        <td class="px-3 py-2 text-center">
                            <button onclick="removeBet(${index})" class="text-red-600 hover:text-red-800">
                                ✕
                            </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            document.getElementById('totalAmount').textContent = total.toFixed(2) + ' บาท';
        }

        function removeBet(index) {
            betList.splice(index, 1);
            updateBetList();
        }

        function clearAllBets() {
            if (betList.length > 0 && !confirm('ต้องการล้างรายการทั้งหมดหรือไม่?')) {
                return;
            }
            betList = [];
            updateBetList();
        }

        function clearCustomer() {
            if (confirm('ต้องการเปลี่ยนลูกค้าใหม่?')) {
                document.getElementById('customerName').value = '';
                localStorage.removeItem('lastCustomerName');
                document.getElementById('customerName').focus();
            }
        }

        async function saveAllBets() {
            const customerName = document.getElementById('customerName').value.trim();
            const betDate = document.getElementById('betDate').value;
            const betType = document.getElementById('betType').value;

            if (!customerName) {
                alert('กรุณากรอกชื่อลูกค้า');
                return;
            }

            if (betList.length === 0) {
                alert('ไม่มีรายการเดิมพัน');
                return;
            }

            if (!confirm(`บันทึก ${betList.length} รายการ สำหรับ ${customerName}?`)) {
                return;
            }

            // เก็บข้อมูลไว้ใน localStorage
            localStorage.setItem('lastCustomerName', customerName);
            localStorage.setItem('lastBetDate', betDate);
            localStorage.setItem('lastBetType', betType);

            try {
                const promises = betList.map(bet => {
                    return fetch('{{ route("bets.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                        },
                        body: JSON.stringify({
                            customer_name: customerName,
                            bet_type: bet.betType,
                            number: bet.number,
                            amount: bet.amount,
                            bet_date: betDate
                        })
                    });
                });

                await Promise.all(promises);

                // แสดงข้อความสำเร็จ
                alert(`✅ บันทึกสำเร็จ ${betList.length} รายการ!`);

                // ล้างเฉพาะรายการเดิมพัน
                betList = [];
                updateBetList();

                // โฟกัสกลับที่ช่องพิมพ์เลข
                document.getElementById('quickInput').focus();

                // รีโหลดตารางรายการล่าสุดด้านล่าง (ไม่ reload ทั้งหน้า)
                await updateRecentBets();
                await updateSalesStats();

            } catch (error) {
                alert('เกิดข้อผิดพลาด: ' + error.message);
            }
        }

        // อัพเดทรายการเดิมพันล่าสุด
        async function updateRecentBets() {
            try {
                const response = await fetch(window.location.href);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newBets = doc.querySelector('#recentBets');
                if (newBets) {
                    document.getElementById('recentBets').innerHTML = newBets.innerHTML;
                }
            } catch (error) {
                console.error('Error updating recent bets:', error);
            }
        }

        // อัพเดทยอดขาย
        async function updateSalesStats() {
            try {
                const response = await fetch('/api/sales/realtime');
                const data = await response.json();
                document.getElementById('totalSales').textContent =
                    new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.totalSales);
                document.getElementById('twoDigitSales').textContent =
                    new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.twoDigitSales);
                document.getElementById('threeDigitSales').textContent =
                    new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(data.threeDigitSales);
            } catch (error) {
                console.error('Error updating sales:', error);
            }
        }

        document.getElementById('quickInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addFromQuickInput();
            }
        });

        const chartConfig = {
            type: 'bar',
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: { y: { beginAtZero: true } }
            }
        };

        const twoDigitCtx = document.getElementById('twoDigitChart').getContext('2d');
        new Chart(twoDigitCtx, {
            ...chartConfig,
            data: {
                labels: {!! json_encode($twoDigitStats->pluck('number') ?? []) !!},
                datasets: [{
                    label: 'จำนวนครั้ง',
                    data: {!! json_encode($twoDigitStats->pluck('frequency') ?? []) !!},
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }]
            }
        });

        const threeDigitCtx = document.getElementById('threeDigitChart').getContext('2d');
        new Chart(threeDigitCtx, {
            ...chartConfig,
            data: {
                labels: {!! json_encode($threeDigitStats->pluck('number') ?? []) !!},
                datasets: [{
                    label: 'จำนวนครั้ง',
                    data: {!! json_encode($threeDigitStats->pluck('frequency') ?? []) !!},
                    backgroundColor: 'rgba(168, 85, 247, 0.5)',
                    borderColor: 'rgba(168, 85, 247, 1)',
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>

</html>