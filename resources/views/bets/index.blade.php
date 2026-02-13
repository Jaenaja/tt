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
                    <button type="button" onclick="switchFormat('manual')" id="btnManual"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold">
                        กรอกแบบเลือกประเภท
                    </button>
                </div>

                <!-- Tab 1 & 2: แบบพิมพ์ย่อ และ แบบก๊อปจากแชท -->
                <div id="textInputArea">
                    <textarea id="betInput" rows="10"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono"
                        placeholder="91 20*20 19 20*20 17 20*20"></textarea>

                    <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                        <p class="font-bold mb-1">📋 รูปแบบที่รองรับ:</p>
                        <div id="formatExample" class="font-mono text-gray-700 space-y-1">
                            <p class="font-semibold text-blue-600">แบบพิมพ์ย่อ:</p>
                            <p>• 91 20*20 19 20*20 (แยกด้วยเว้นวรรค)</p>
                            <p>• 91 20*20 / 19 20*20 (แยกด้วย /)</p>
                            <p>• 91 20*20 - 19 20*20 (แยกด้วย -)</p>
                            <p>• 365 10*6 กลับ (3 ตัวกลับ 6 ประตู)</p>
                            <p>• 365 10*6 (3 ตัวกลับ - auto detect)</p>
                            <p>• 365 10*6 ก (3 ตัวกลับ แบบสั้น)</p>
                            <p>• 941 100*100 (3 ตัว: บน*โต๊ด)</p>
                            <p>• 91 20 (2 ตัว: บนอย่างเดียว)</p>
                            <hr class="my-2 border-gray-300">
                            <p class="font-semibold text-blue-600">แบบก๊อปจากแชท:</p>
                            <p>• 91=10*10 (รูปแบบเดิม)</p>
                            <p>• 91 10*10 (รูปแบบใหม่ ไม่ต้องมี =)</p>
                            <p>• 365=10*6 กลับ (3 ตัวกลับ 6 ประตู)</p>
                            <p>• 365 10*6 ก (3 ตัวกลับ 6 ประตู)</p>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: กรอกแบบเลือกประเภท -->
                <div id="manualInputArea" class="hidden">
                    <div class="bg-white border-2 border-gray-300 rounded-lg p-4">
                        <!-- ประเภทการแทง -->
                        <div class="mb-4">
                            <label class="block text-gray-700 font-semibold mb-2">เลือกประเภท:</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="betType" value="2digit" checked
                                        onchange="updateManualInputFields()" class="w-4 h-4 text-blue-600">
                                    <span class="text-sm font-medium">2 ตัว</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="betType" value="3digit"
                                        onchange="updateManualInputFields()" class="w-4 h-4 text-blue-600">
                                    <span class="text-sm font-medium">3 ตัว</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="betType" value="3reverse"
                                        onchange="updateManualInputFields()" class="w-4 h-4 text-blue-600">
                                    <span class="text-sm font-medium">3 ตัวกลับ (6 ประตู)</span>
                                </label>
                            </div>
                        </div>

                        <!-- ช่องกรอกตัวเลข -->
                        <div class="mb-4">
                            <label class="block text-gray-700 font-semibold mb-2">กรอกตัวเลข (คั่นด้วยเว้นวรรค):</label>
                            <textarea id="manualNumbers" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono"
                                placeholder="เช่น: 91 20 19 41 52"></textarea>
                        </div>

                        <!-- ช่องกรอกราคา -->
                        <div id="manualPriceFields" class="mb-4">
                            <!-- Fields will be dynamically updated by updateManualInputFields() -->
                        </div>

                        <div class="mt-2 p-3 bg-purple-50 border border-purple-200 rounded text-sm">
                            <p class="text-purple-700">💡 กรอกตัวเลขและราคาแล้วกดปุ่ม "🔍 อ่านโพยและแสดงผล" ด้านล่าง</p>
                        </div>
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

                <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200">
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
                                <th class="px-4 py-3 text-center">ลบ</th>
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
            updateManualInputFields();
        };

        function generateDrawDates() {
            const select = document.getElementById('drawDate');
            const today = new Date();
            const dates = [];
            const currentDay = today.getDate();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();

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

            const uniqueDates = [...new Map(dates.map(d => [d.value, d])).values()]
                .sort((a, b) => new Date(b.value) - new Date(a.value));

            let selectedIndex = 0;
            if (currentDay >= 16) {
                const nextMonth = new Date(currentYear, currentMonth + 1, 1);
                const targetValue = formatDateForDatabase(nextMonth);
                selectedIndex = uniqueDates.findIndex(d => d.value === targetValue);
            } else {
                const thisMonth16 = new Date(currentYear, currentMonth, 16);
                const targetValue = formatDateForDatabase(thisMonth16);
                selectedIndex = uniqueDates.findIndex(d => d.value === targetValue);
            }

            select.innerHTML = uniqueDates.map((dateOption, idx) =>
                `<option value="${dateOption.value}" ${idx === selectedIndex ? 'selected' : ''}>
                ${dateOption.label}
            </option>`
            ).join('');
        }

        function createDateOption(date) {
            return {
                value: formatDateForDatabase(date),
                label: formatDateThai(date)
            };
        }

        function formatDateForDatabase(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function formatDateThai(date) {
            const day = date.getDate();
            const month = thaiMonths[date.getMonth() + 1];
            const year = date.getFullYear() + 543;
            return `${day} ${month} ${year}`;
        }

        function switchFormat(format) {
            currentFormat = format;

            // Reset button styles
            document.getElementById('btnShort').className = 'px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold';
            document.getElementById('btnFull').className = 'px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold';
            document.getElementById('btnManual').className = 'px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold';

            if (format === 'short') {
                document.getElementById('btnShort').className = 'px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold';
                document.getElementById('textInputArea').classList.remove('hidden');
                document.getElementById('manualInputArea').classList.add('hidden');
                document.getElementById('betInput').placeholder = '91 20*20 19 20*20 17 20*20';
            } else if (format === 'full') {
                document.getElementById('btnFull').className = 'px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold';
                document.getElementById('textInputArea').classList.remove('hidden');
                document.getElementById('manualInputArea').classList.add('hidden');
                document.getElementById('betInput').placeholder = '91 20*20\n19 20*20';
            } else if (format === 'manual') {
                document.getElementById('btnManual').className = 'px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold';
                document.getElementById('textInputArea').classList.add('hidden');
                document.getElementById('manualInputArea').classList.remove('hidden');
            }
        }

        // อัปเดตช่องกรอกราคาตามประเภทที่เลือก
        function updateManualInputFields() {
            const betType = document.querySelector('input[name="betType"]:checked').value;
            const container = document.getElementById('manualPriceFields');

            let html = '<div class="grid grid-cols-2 gap-4">';

            if (betType === '2digit') {
                html += `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ราคาบน (บาท)</label>
                        <input type="number" id="priceTop" min="0" step="0.01" value="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ราคาล่าง (บาท)</label>
                        <input type="number" id="priceBottom" min="0" step="0.01" value="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                `;
            } else if (betType === '3digit') {
                html += `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ราคาบน (บาท)</label>
                        <input type="number" id="priceTop" min="0" step="0.01" value="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ราคาโต๊ด (บาท)</label>
                        <input type="number" id="priceToad" min="0" step="0.01" value="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                `;
            } else if (betType === '3reverse') {
                html += `
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">ยอดต่อประตู (บาท)</label>
                        <input type="number" id="pricePerDoor" min="0" step="0.01" value="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <p class="text-xs text-gray-500 mt-1">ระบบจะกระจายเป็น 6 ประตูอัตโนมัติ</p>
                    </div>
                `;
            }

            html += '</div>';
            container.innerHTML = html;
        }

        function parseAndPreview() {
            const drawDate = document.getElementById('drawDate').value;
            const customerName = document.getElementById('customerName').value.trim();

            if (!drawDate || !customerName) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'กรุณากรอกข้อมูลให้ครบ: งวดวันที่ และ ชื่อลูกค้า' });
                return;
            }

            // 1. รีเซ็ต parsedBets ทุกครั้งเพื่อป้องกันเลขซ้ำ
            parsedBets = [];

            try {
                // ตรวจสอบว่าอยู่ Tab ไหน
                if (currentFormat === 'manual') {
                    // Tab 3: กรอกแบบเลือกประเภท - Strict Validation
                    const numbersInput = document.getElementById('manualNumbers').value.trim();

                    if (!numbersInput) {
                        Swal.fire({ icon: 'error', title: 'ERROR', text: 'กรุณากรอกตัวเลข' });
                        return;
                    }

                    const betType = document.querySelector('input[name="betType"]:checked').value;
                    const numbers = numbersInput.split(/\s+/).filter(n => n.match(/^\d+$/));

                    if (numbers.length === 0) {
                        Swal.fire({ icon: 'error', title: 'ERROR', text: 'ไม่พบตัวเลขที่ถูกต้อง' });
                        return;
                    }

                    // 2. Strict Validation - ตรวจสอบความถูกต้องก่อนทำงาน
                    const invalidNumbers = [];
                    const expectedLength = betType === '2digit' ? 2 : 3;

                    numbers.forEach(number => {
                        if (number.length !== expectedLength) {
                            invalidNumbers.push(number);
                        }
                    });

                    if (invalidNumbers.length > 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'พบเลขผิดประเภท!',
                            html: `<p>ประเภทที่เลือก: <strong>${betType === '2digit' ? '2 ตัว' : (betType === '3digit' ? '3 ตัว' : '3 ตัวกลับ')}</strong></p>
                                   <p class="text-red-600 mt-2">เลขที่ผิด: <strong>${invalidNumbers.join(', ')}</strong></p>
                                   <p class="text-sm text-gray-600 mt-2">กรุณาแก้ไขให้ตรงตามประเภทที่เลือก</p>`
                        });
                        return;
                    }

                    // ผ่านการตรวจสอบแล้ว - เริ่มประมวลผล
                    numbers.forEach(number => {
                        if (betType === '2digit') {
                            const top = parseFloat(document.getElementById('priceTop').value) || 0;
                            const bottom = parseFloat(document.getElementById('priceBottom').value) || 0;
                            parsedBets.push({ number, top, bottom, toad: 0 });

                        } else if (betType === '3digit') {
                            const top = parseFloat(document.getElementById('priceTop').value) || 0;
                            const toad = parseFloat(document.getElementById('priceToad').value) || 0;
                            parsedBets.push({ number, top, bottom: 0, toad });

                        } else if (betType === '3reverse') {
                            const pricePerDoor = parseFloat(document.getElementById('pricePerDoor').value) || 0;
                            const permutations = getAllPermutations(number);
                            permutations.forEach(num => {
                                parsedBets.push({ number: num, top: pricePerDoor, bottom: 0, toad: 0 });
                            });
                        }
                    });

                } else {
                    // Tab 1 & 2: แบบพิมพ์ย่อ และ แบบก๊อปจากแชท
                    const input = document.getElementById('betInput').value.trim();

                    if (!input) {
                        Swal.fire({ icon: 'error', title: 'ERROR', text: 'กรุณากรอกโพย' });
                        return;
                    }

                    parsedBets = currentFormat === 'short' ? parseShortFormat(input) : parseFullFormat(input);
                }

                // แสดงผล
                const dateParts = drawDate.split('-');
                const displayDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                const thaiDate = formatDateThai(displayDate);

                displayResults(thaiDate, customerName, parsedBets);

                document.getElementById('resultSection').scrollIntoView({ behavior: 'smooth', block: 'start' });

            } catch (error) {
                parsedBets = []; // รีเซ็ตถ้าเกิด error
                Swal.fire({ icon: 'error', title: 'ERROR', text: error.message });
            }
        }

        function parseShortFormat(input) {
            // Normalize: แทนที่ - / = ซ้ำซ้อนด้วย space + ลบ space ซ้ำ
            input = input.replace(/[\-\/=]+/g, ' ').replace(/\s+/g, ' ').trim();

            const bets = [];
            const tokens = input.split(/\s+/).filter(s => s);

            let i = 0;
            while (i < tokens.length) {
                if (!/^\d{1,3}$/.test(tokens[i])) {
                    i++;
                    continue;
                }

                const number = tokens[i];

                if (i + 1 >= tokens.length) {
                    throw new Error(`เลข "${number}" ไม่มีจำนวนเงิน`);
                }

                const amounts = tokens[i + 1];

                // Auto-Detect: *6 = 3 ตัวกลับ
                const hasReverseKeyword = (i + 2 < tokens.length) && (tokens[i + 2] === 'กลับ' || tokens[i + 2] === 'ก');
                const isAutoReverse = number.length === 3 && amounts.includes('*6');
                const hasReverse = hasReverseKeyword || isAutoReverse;

                if (hasReverse) {
                    const reversedBets = parseBetAmountReverse(number, amounts);
                    bets.push(...reversedBets);
                    i += hasReverseKeyword ? 3 : 2;
                } else {
                    const bet = parseAmounts(number, amounts);
                    bets.push(bet);
                    i += 2;
                }
            }

            if (bets.length === 0) {
                throw new Error('ไม่พบรายการเดิมพันที่ถูกต้อง');
            }

            return bets;
        }

        function parseFullFormat(input) {
            const bets = [];
            const lines = input.split('\n').map(s => s.trim());

            for (let line of lines) {
                if (!line) continue;

                if (/^[ก-๙a-zA-Z]+$/.test(line) && !line.includes('=') && !line.includes('*')) {
                    continue;
                }

                let number, amounts;
                if (line.includes('=')) {
                    const match = line.match(/^(\d{1,3})\s*=\s*(.+)$/);
                    if (!match) continue;
                    number = match[1];
                    amounts = match[2].trim();
                } else {
                    const parts = line.split(/\s+/);
                    if (parts.length < 2 || !/^\d{1,3}$/.test(parts[0])) {
                        continue;
                    }
                    number = parts[0];
                    amounts = parts.slice(1).join(' ');
                }

                const hasReverseKeyword = amounts.includes('กลับ') || amounts.endsWith(' ก') || amounts.endsWith('\tก');
                const isAutoReverse = number.length === 3 && amounts.includes('*6');
                const hasReverse = hasReverseKeyword || isAutoReverse;

                if (hasReverse) {
                    amounts = amounts.replace(/\s*(กลับ|ก)\s*$/, '').trim();
                    const reversedBets = parseBetAmountReverse(number, amounts);
                    bets.push(...reversedBets);
                } else {
                    const bet = parseAmounts(number, amounts);
                    bets.push(bet);
                }
            }

            return bets;
        }

        function parseAmounts(number, amounts) {
            const is2Digit = number.length === 2;

            if (!number || !/^\d{1,3}$/.test(number)) {
                throw new Error(`เลขผิดรูปแบบ: "${number}"`);
            }

            amounts = amounts.replace(/×/g, '*');

            if (amounts.includes('*')) {
                const parts = amounts.split('*').map(s => s.trim());
                if (parts.length !== 2) throw new Error(`จำนวนเงินผิด: "${amounts}"`);
                const [first, second] = parts.map(a => {
                    const num = parseFloat(a);
                    if (isNaN(num) || num < 0) throw new Error(`จำนวนเงินผิด: "${a}"`);
                    return num;
                });
                if (is2Digit) return { number, top: first, bottom: second, toad: 0 };
                else return { number, top: first, bottom: 0, toad: second };
            } else {
                const amount = parseFloat(amounts);
                if (isNaN(amount) || amount < 0) throw new Error(`จำนวนเงินผิด: "${amounts}"`);
                return { number, top: amount, bottom: 0, toad: 0 };
            }
        }

        function parseBetAmountReverse(number, amounts) {
            if (number.length !== 3) throw new Error(`รูปแบบ "กลับ 6 ประตู" ใช้ได้เฉพาะเลข 3 หลักเท่านั้น`);
            if (!/^\d+$/.test(number)) throw new Error(`เลขผิดรูปแบบ: "${number}"`);

            amounts = amounts.replace(/×/g, '*');

            let topAmount, doorCount;
            if (amounts.includes('*')) {
                const parts = amounts.split('*');
                if (parts.length !== 2) throw new Error(`รูปแบบผิด: "${amounts}" - ต้องเป็น top*door`);
                topAmount = parseFloat(parts[0].trim());
                doorCount = parseInt(parts[1].trim());
                if (isNaN(topAmount) || topAmount < 0) throw new Error(`จำนวนเงินผิด: "${parts[0]}"`);
                if (isNaN(doorCount) || doorCount <= 0 || doorCount > 6) throw new Error(`จำนวนประตูผิด: "${parts[1]}" - ต้องเป็น 1-6`);
            } else {
                throw new Error(`รูปแบบผิด: "${amounts}" - ต้องเป็น top*door`);
            }

            const permutations = getAllPermutations(number);

            if (permutations.length !== doorCount) {
                console.warn(`เลข ${number} มีประตูที่ไม่ซ้ำ ${permutations.length} ประตู แต่ระบุ ${doorCount} ประตู`);
            }

            return permutations.map(num => ({
                number: num,
                top: topAmount,
                bottom: 0,
                toad: 0
            }));
        }

        function getAllPermutations(str) {
            const digits = str.split('');
            const permSet = new Set();

            function permute(arr, m = []) {
                if (arr.length === 0) {
                    permSet.add(m.join(''));
                } else {
                    for (let i = 0; i < arr.length; i++) {
                        let curr = arr.slice();
                        let next = curr.splice(i, 1);
                        permute(curr.slice(), m.concat(next));
                    }
                }
            }

            permute(digits);
            return Array.from(permSet).sort();
        }

        function displayResults(drawDate, customerName, bets) {
            document.getElementById('displayDrawDate').textContent = drawDate;
            document.getElementById('displayCustomer').textContent = customerName;

            let totalTop = 0, totalBottom = 0, totalToad = 0, html = '';

            bets.forEach((bet, index) => {
                const rowTotal = bet.top + bet.bottom + bet.toad;
                totalTop += bet.top;
                totalBottom += bet.bottom;
                totalToad += bet.toad;

                // แสดงสีแยกตามประเภท: 2 ตัว น้ำเงิน / 3 ตัว ม่วง
                const numberClass = bet.number.length === 2 ? 'text-blue-600' : 'text-purple-600';
                const bgClass = bet.number.length === 2 ? 'bg-blue-50' : 'bg-purple-50';

                html += `<tr class="hover:${bgClass}" data-index="${index}">
                <td class="px-4 py-3 font-bold text-xl ${numberClass}">${bet.number}</td>
                <td class="px-4 py-3 text-right ${bet.top > 0 ? 'font-semibold text-gray-900' : 'text-gray-400'}">${bet.top > 0 ? bet.top.toFixed(2) : '-'}</td>
                <td class="px-4 py-3 text-right ${bet.bottom > 0 ? 'font-semibold text-gray-900' : 'text-gray-400'}">${bet.bottom > 0 ? bet.bottom.toFixed(2) : '-'}</td>
                <td class="px-4 py-3 text-right ${bet.toad > 0 ? 'font-semibold text-gray-900' : 'text-gray-400'}">${bet.toad > 0 ? bet.toad.toFixed(2) : '-'}</td>
                <td class="px-4 py-3 text-right font-bold text-gray-900">${rowTotal.toFixed(2)}</td>
                <td class="px-4 py-3 text-center">
                    <button onclick="deleteRow(${index})" class="text-red-600 hover:text-red-800 font-bold transition">❌</button>
                </td>
            </tr>`;
            });

            document.getElementById('resultTable').innerHTML = html;
            updateTotals();
            document.getElementById('resultSection').classList.remove('hidden');
        }

        function deleteRow(index) {
            Swal.fire({
                title: 'ยืนยันการลบ',
                text: `ต้องการลบรายการ ${parsedBets[index].number} หรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    parsedBets.splice(index, 1);

                    if (parsedBets.length === 0) {
                        document.getElementById('resultSection').classList.add('hidden');
                        Swal.fire({
                            icon: 'info',
                            title: 'ลบหมดแล้ว',
                            text: 'ไม่มีรายการเหลืออยู่',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        // อัปเดตตารางและยอดรวมใหม่
                        const drawDate = document.getElementById('drawDate').value;
                        const customerName = document.getElementById('customerName').value.trim();
                        const dateParts = drawDate.split('-');
                        const displayDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                        const thaiDate = formatDateThai(displayDate);
                        displayResults(thaiDate, customerName, parsedBets);

                        Swal.fire({
                            icon: 'success',
                            title: 'ลบแล้ว!',
                            text: 'รายการถูกลบเรียบร้อย',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    }
                }
            });
        }

        function updateTotals() {
            let totalTop = 0, totalBottom = 0, totalToad = 0;
            parsedBets.forEach(bet => {
                totalTop += bet.top;
                totalBottom += bet.bottom;
                totalToad += bet.toad;
            });

            document.getElementById('totalTop').textContent = totalTop.toFixed(2);
            document.getElementById('totalBottom').textContent = totalBottom.toFixed(2);
            document.getElementById('totalToad').textContent = totalToad.toFixed(2);
            document.getElementById('grandTotal').textContent = (totalTop + totalBottom + totalToad).toFixed(2);
        }

        async function saveBets() {
            const drawDate = document.getElementById('drawDate').value;
            const customerName = document.getElementById('customerName').value.trim();

            if (parsedBets.length === 0) {
                Swal.fire({ icon: 'warning', title: 'ไม่มีรายการ', text: 'ไม่มีรายการให้บันทึก' });
                return;
            }

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
                    document.getElementById('manualNumbers').value = '';
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