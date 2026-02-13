<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>รับแทงหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; กลับหน้าหลัก</a>
            <div class="text-sm text-gray-600">
                <span class="font-semibold">{{ Auth::user()->name }}</span>
                <span class="text-gray-400">|</span>
                <span class="text-gray-500">{{ Auth::user()->role === 'admin' ? 'ผู้ดูแลระบบ' : 'พนักงาน' }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline ml-3">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800">ออกจากระบบ</button>
                </form>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6 text-white">
            <h1 class="text-3xl font-bold">🎰 รับแทงหวย</h1>
            <p class="text-blue-100">หวยออกทุกวันที่ 1 และ 16 ของทุกเดือน</p>
        </div>

        <!-- ฟอร์มกรอกข้อมูล -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📝 ข้อมูลการแทง</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">งวดวันที่ *</label>
                    <select id="drawDate"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">ชื่อลูกค้า *</label>
                    <input type="text" id="customerName"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="ชื่อลูกค้า">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">โพย (เลือกรูปแบบ)</label>
                <div class="flex gap-2 mb-2">
                    <button type="button" onclick="switchFormat('short')" id="btnShort"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">
                        แบบพิมพ์ย่อ
                    </button>
                    <button type="button" onclick="switchFormat('full')" id="btnFull"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold">
                        แบบก๊อปจากแชท
                    </button>
                </div>

                <textarea id="betInput" rows="10"
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono"
                    placeholder="91 20*20 / 19 20*20 / 17 20*20"></textarea>

                <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                    <p class="font-bold mb-1">📋 รูปแบบที่รองรับ:</p>
                    <div id="formatExample" class="font-mono text-gray-700 space-y-1">
                        <p>• 91 20*20 / 19 20*20 (แยกด้วย /)</p>
                        <p>• 941 100*100 (3 ตัว: บน*โต๊ด)</p>
                        <p>• 91 20 (2 ตัว: บนอย่างเดียว)</p>
                        <p>• 941 100 (3 ตัว: บนอย่างเดียว)</p>
                    </div>
                </div>
            </div>

            <button onclick="parseAndPreview()"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition">
                🔍 อ่านโพยและแสดงผล
            </button>
        </div>

        <!-- แสดงผลลัพธ์ -->
        <div id="resultSection" class="hidden">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 ผลการแปลโพย</h2>

                <div class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-sm text-gray-600">งวดวันที่</p>
                            <p class="text-lg font-bold text-blue-600" id="displayDrawDate"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">ลูกค้า</p>
                            <p class="text-lg font-bold text-purple-600" id="displayCustomer"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">สร้างโดย</p>
                            <p class="text-lg font-bold text-green-600">{{ Auth::user()->name }}</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto mb-6">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-700 to-gray-800 text-white">
                                <th class="px-4 py-3 text-left">เลข</th>
                                <th class="px-4 py-3 text-right">บน</th>
                                <th class="px-4 py-3 text-right">ล่าง</th>
                                <th class="px-4 py-3 text-right">โต๊ด</th>
                                <th class="px-4 py-3 text-right">รวม</th>
                            </tr>
                        </thead>
                        <tbody id="resultTable" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-100 rounded-lg p-4 text-center border-2 border-blue-300">
                        <p class="text-sm text-gray-700 font-semibold">บนรวม</p>
                        <p class="text-2xl font-bold text-blue-700" id="totalTop">0</p>
                        <p class="text-xs text-gray-600">บาท</p>
                    </div>
                    <div class="bg-green-100 rounded-lg p-4 text-center border-2 border-green-300">
                        <p class="text-sm text-gray-700 font-semibold">ล่างรวม</p>
                        <p class="text-2xl font-bold text-green-700" id="totalBottom">0</p>
                        <p class="text-xs text-gray-600">บาท</p>
                    </div>
                    <div class="bg-purple-100 rounded-lg p-4 text-center border-2 border-purple-300">
                        <p class="text-sm text-gray-700 font-semibold">โต๊ดรวม</p>
                        <p class="text-2xl font-bold text-purple-700" id="totalToad">0</p>
                        <p class="text-xs text-gray-600">บาท</p>
                    </div>
                    <div
                        class="bg-gradient-to-br from-orange-100 to-red-100 rounded-lg p-4 text-center border-2 border-orange-300">
                        <p class="text-sm text-gray-700 font-semibold">ยอดแทงรวม</p>
                        <p class="text-3xl font-bold text-red-700" id="grandTotal">0</p>
                        <p class="text-xs text-gray-600">บาท</p>
                    </div>
                </div>

                <button onclick="saveBets()"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-lg transition text-lg">
                    💾 บันทึกการแทง
                </button>
            </div>
        </div>
    </div>

    <script>
        let parsedBets = [];
        let currentFormat = 'short';

        // อักษรย่อเดือนภาษาไทย
        const thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        // โหลดชื่อลูกค้าที่บันทึกไว้
        window.onload = function () {
            generateDrawDates();
            const savedCustomer = localStorage.getItem('lastCustomerName');
            if (savedCustomer) {
                document.getElementById('customerName').value = savedCustomer;
            }
        };

        /**
         * สร้างรายการวันที่งวดหวย
         */
        function generateDrawDates() {
            const select = document.getElementById('drawDate');
            const today = new Date();
            const dates = [];
            const currentDay = today.getDate();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();

            // สร้างวันที่ 1 และ 16 ของแต่ละเดือน
            for (let i = -3; i <= 2; i++) {
                let month = currentMonth + Math.floor(i / 2);
                let year = currentYear;
                if (month < 0) {
                    month += 12;
                    year--;
                }
                if (month > 11) {
                    month -= 12;
                    year++;
                }

                dates.push(createDateOption(new Date(year, month, 1)));
                dates.push(createDateOption(new Date(year, month, 16)));
            }

            // เรียงลำดับจากใหม่ไปเก่า
            const uniqueDates = [...new Map(dates.map(d => [d.value, d])).values()]
                .sort((a, b) => new Date(b.value) - new Date(a.value));

            // หาวันที่ที่ควรเลือก (งวดถัดไป)
            let selectedIndex = 0;
            if (currentDay >= 16) {
                // ถ้าวันที่ >= 16 เลือกวันที่ 1 เดือนหน้า
                const nextMonth = new Date(currentYear, currentMonth + 1, 1);
                const targetValue = formatDateForDatabase(nextMonth);
                selectedIndex = uniqueDates.findIndex(d => d.value === targetValue);
            } else {
                // ถ้าวันที่ < 16 เลือกวันที่ 16 เดือนนี้
                const thisMonth16 = new Date(currentYear, currentMonth, 16);
                const targetValue = formatDateForDatabase(thisMonth16);
                selectedIndex = uniqueDates.findIndex(d => d.value === targetValue);
            }

            // สร้าง options
            select.innerHTML = uniqueDates.map((dateOption, idx) =>
                `<option value="${dateOption.value}" ${idx === selectedIndex ? 'selected' : ''}>
                ${dateOption.label}
            </option>`
            ).join('');
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

        function switchFormat(format) {
            currentFormat = format;
            if (format === 'short') {
                document.getElementById('btnShort').className = 'px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold';
                document.getElementById('btnFull').className = 'px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold';
                document.getElementById('betInput').placeholder = '91 20*20 / 19 20*20 / 17 20*20';
            } else {
                document.getElementById('btnShort').className = 'px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold';
                document.getElementById('btnFull').className = 'px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold';
                document.getElementById('betInput').placeholder = 'จอม\n91=20*20\n19=20*20';
            }
        }

        function parseAndPreview() {
            const drawDate = document.getElementById('drawDate').value;
            const customerName = document.getElementById('customerName').value.trim();
            const input = document.getElementById('betInput').value.trim();

            document.getElementById('resultSection').classList.add('hidden');

            if (!drawDate || !customerName) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'กรุณากรอกข้อมูลให้ครบ: งวดวันที่ และ ชื่อลูกค้า' });
                return;
            }

            if (!input) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'กรุณากรอกโพย' });
                return;
            }

            try {
                parsedBets = currentFormat === 'short' ? parseShortFormat(input) : parseFullFormat(input);

                // แปลง drawDate เป็นรูปแบบไทยเพื่อแสดงผล
                const dateParts = drawDate.split('-');
                const displayDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                const thaiDate = formatDateThai(displayDate);

                displayResults(thaiDate, customerName, parsedBets);
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: error.message });
            }
        }

        function parseShortFormat(input) {
            const bets = [];
            const lines = input.split('/').map(s => s.trim());

            for (let line of lines) {
                if (!line) continue;
                const [numberPart, ...amountParts] = line.split(/\s+/);
                const number = numberPart.trim();
                const amounts = amountParts.join(' ').trim();

                if (!number || !/^\d{2,3}$/.test(number)) {
                    throw new Error(`เลขผิดรูปแบบ: "${number}"`);
                }

                const bet = parseAmounts(number, amounts);
                bets.push(bet);
            }

            return bets;
        }

        function parseFullFormat(input) {
            const bets = [];
            const lines = input.split('\n').map(s => s.trim());
            let currentGroup = null;

            for (let line of lines) {
                if (!line) continue;

                if (/^[ก-๙a-zA-Z]+$/.test(line)) {
                    currentGroup = line;
                    continue;
                }

                const match = line.match(/^(\d{2,3})\s*=\s*(.+)$/);
                if (!match) {
                    throw new Error(`รูปแบบผิด: "${line}"`);
                }

                const number = match[1];
                const amounts = match[2].trim();
                const bet = parseAmounts(number, amounts);
                bets.push(bet);
            }

            return bets;
        }

        function parseAmounts(number, amounts) {
            const is2Digit = number.length === 2;

            if (amounts.includes('*')) {
                const parts = amounts.split('*').map(s => s.trim());
                if (parts.length !== 2) throw new Error(`จำนวนเงินผิด: "${amounts}"`);
                const [first, second] = parts.map(a => {
                    const num = parseFloat(a);
                    if (isNaN(num) || num <= 0) throw new Error(`จำนวนเงินผิด: "${a}"`);
                    return num;
                });
                if (is2Digit) return { number, top: first, bottom: second, toad: 0 };
                else return { number, top: first, bottom: 0, toad: second };
            } else {
                const amount = parseFloat(amounts);
                if (isNaN(amount) || amount <= 0) throw new Error(`จำนวนเงินผิด: "${amounts}"`);
                return { number, top: amount, bottom: 0, toad: 0 };
            }
        }

        function displayResults(drawDate, customerName, bets) {
            document.getElementById('displayDrawDate').textContent = drawDate;
            document.getElementById('displayCustomer').textContent = customerName;

            let totalTop = 0, totalBottom = 0, totalToad = 0, html = '';

            bets.forEach(bet => {
                const rowTotal = bet.top + bet.bottom + bet.toad;
                totalTop += bet.top;
                totalBottom += bet.bottom;
                totalToad += bet.toad;
                html += `<tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-bold text-xl text-blue-600">${bet.number}</td>
                <td class="px-4 py-3 text-right ${bet.top > 0 ? 'font-semibold' : 'text-gray-400'}">${bet.top > 0 ? bet.top : '-'}</td>
                <td class="px-4 py-3 text-right ${bet.bottom > 0 ? 'font-semibold' : 'text-gray-400'}">${bet.bottom > 0 ? bet.bottom : '-'}</td>
                <td class="px-4 py-3 text-right ${bet.toad > 0 ? 'font-semibold' : 'text-gray-400'}">${bet.toad > 0 ? bet.toad : '-'}</td>
                <td class="px-4 py-3 text-right font-bold">${rowTotal}</td>
            </tr>`;
            });

            document.getElementById('resultTable').innerHTML = html;
            document.getElementById('totalTop').textContent = totalTop;
            document.getElementById('totalBottom').textContent = totalBottom;
            document.getElementById('totalToad').textContent = totalToad;
            document.getElementById('grandTotal').textContent = totalTop + totalBottom + totalToad;
            document.getElementById('resultSection').classList.remove('hidden');
        }

        async function saveBets() {
            const drawDate = document.getElementById('drawDate').value;
            const customerName = document.getElementById('customerName').value.trim();

            try {
                const response = await fetch('{{ route("bets.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        draw_date: drawDate,
                        customer_name: customerName,
                        bets: parsedBets
                    })
                });

                const data = await response.json();

                if (data.success) {
                    localStorage.setItem('lastCustomerName', customerName);
                    await Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: `บันทึก ${parsedBets.length} รายการเรียบร้อย`,
                        timer: 2000
                    });
                    document.getElementById('betInput').value = '';
                    document.getElementById('resultSection').classList.add('hidden');
                    parsedBets = [];
                } else {
                    Swal.fire({ icon: 'error', title: 'ERROR', text: data.message });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'เกิดข้อผิดพลาดในการบันทึก' });
            }
        }
    </script>
</body>

</html>