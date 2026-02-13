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
                <label class="block text-gray-700 font-semibold mb-2">รูปแบบการกรอก</label>
                <div class="flex gap-2 mb-4">
                    <button type="button" onclick="switchFormat('short')" id="btnShort"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold transition">
                        พิมพ์ย่อ / ก๊อปจากแชท
                    </button>
                    <button type="button" onclick="switchFormat('manual')" id="btnManual"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold transition">
                        กรอกแบบเลือกประเภท
                    </button>
                </div>

                <div id="textInputArea">
                    <textarea id="betInput" rows="10"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono"
                        placeholder="ตัวอย่างการกรอก:&#10;91 20&#10;19 20*20&#10;77=100*0&#10;365 10*6 กลับ"></textarea>

                    <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                        <p class="font-bold mb-1 text-blue-800">📋 รูปแบบที่รองรับ (พิมพ์รวมหรือแยกบรรทัดก็ได้):</p>
                        <div id="formatExample" class="font-mono text-gray-700 grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <p>• 91 20 (บนอย่างเดียว)</p>
                            <p>• 91=20 (บนอย่างเดียว)</p>
                            <p>• 91 20*20 (บน*ล่าง)</p>
                            <p>• 91=20*20 (บน*ล่าง)</p>
                            <p>• 941 100 (3 ตัวบน)</p>
                            <p>• 941=100 (3 ตัวบน)</p>
                            <p>• 941 100*100 (3 ตัว: บน*โต๊ด)</p>
                            <p>• 941=100*100 (3 ตัว: บน*โต๊ด)</p>
                            <p>• 365 10*6 (6 ประตูอัตโนมัติ)</p>
                            <p>• 365=10*6 (6 ประตูอัตโนมัติ)</p>
                            <p>• 365 10 ก (6 ประตู)</p>
                            <p>• 365=10 ก (6 ประตู)</p>
                            <p>• 365 10 กลับ (6 ประตู)</p>
                            <p>• 365=10 กลับ (6 ประตู)</p>
                            <p class="text-red-600 font-bold col-span-2 mt-1">⚠️ ห้ามใช้เครื่องหมาย / และ - (ระบบจะแจ้ง
                                Error)</p>
                        </div>
                    </div>
                </div>

                <div id="manualInputArea" class="hidden">
                    <div class="bg-white border-2 border-gray-300 rounded-lg p-4">
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

                        <div class="mb-4">
                            <label class="block text-gray-700 font-semibold mb-2">กรอกตัวเลข (คั่นด้วยเว้นวรรค):</label>
                            <textarea id="manualNumbers" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono"
                                placeholder="เช่น: 91 20 19 41 52"></textarea>
                        </div>

                        <div id="manualPriceFields" class="mb-4"></div>
                    </div>
                </div>
            </div>

            <button onclick="parseAndPreview()"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition">
                🔍 อ่านโพยและแสดงผล
            </button>
        </div>

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

        const thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

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
                if (month < 0) { month += 12; year--; }
                if (month > 11) { month -= 12; year++; }
                dates.push({ value: formatDateForDatabase(new Date(year, month, 1)), label: formatDateThai(new Date(year, month, 1)) });
                dates.push({ value: formatDateForDatabase(new Date(year, month, 16)), label: formatDateThai(new Date(year, month, 16)) });
            }

            const uniqueDates = [...new Map(dates.map(d => [d.value, d])).values()].sort((a, b) => new Date(b.value) - new Date(a.value));

            let selectedIndex = currentDay >= 16 ? uniqueDates.findIndex(d => d.value === formatDateForDatabase(new Date(currentYear, currentMonth + 1, 1))) : uniqueDates.findIndex(d => d.value === formatDateForDatabase(new Date(currentYear, currentMonth, 16)));
            if (selectedIndex === -1) selectedIndex = 0;

            select.innerHTML = uniqueDates.map((d, idx) => `<option value="${d.value}" ${idx === selectedIndex ? 'selected' : ''}>${d.label}</option>`).join('');
        }

        function formatDateForDatabase(date) {
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        }

        function formatDateThai(date) {
            return `${date.getDate()} ${thaiMonths[date.getMonth() + 1]} ${date.getFullYear() + 543}`;
        }

        function switchFormat(format) {
            currentFormat = format;
            document.getElementById('btnShort').className = format === 'short' ? 'px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold' : 'px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold';
            document.getElementById('btnManual').className = format === 'manual' ? 'px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold' : 'px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold';
            document.getElementById('textInputArea').classList.toggle('hidden', format !== 'short');
            document.getElementById('manualInputArea').classList.toggle('hidden', format !== 'manual');
        }

        function updateManualInputFields() {
            const betType = document.querySelector('input[name="betType"]:checked').value;
            const container = document.getElementById('manualPriceFields');
            let html = '<div class="grid grid-cols-2 gap-4">';
            if (betType === '2digit') {
                html += `<div><label class="block text-sm font-medium mb-1">ราคาบน</label><input type="number" id="priceTop" value="10" class="w-full px-3 py-2 border rounded-lg"></div>
                         <div><label class="block text-sm font-medium mb-1">ราคาล่าง</label><input type="number" id="priceBottom" value="10" class="w-full px-3 py-2 border rounded-lg"></div>`;
            } else if (betType === '3digit') {
                html += `<div><label class="block text-sm font-medium mb-1">ราคาบน</label><input type="number" id="priceTop" value="10" class="w-full px-3 py-2 border rounded-lg"></div>
                         <div><label class="block text-sm font-medium mb-1">ราคาโต๊ด</label><input type="number" id="priceToad" value="10" class="w-full px-3 py-2 border rounded-lg"></div>`;
            } else {
                html += `<div class="col-span-2"><label class="block text-sm font-medium mb-1">ยอดต่อประตู</label><input type="number" id="pricePerDoor" value="10" class="w-full px-3 py-2 border rounded-lg"></div>`;
            }
            container.innerHTML = html + '</div>';
        }

        function parseAndPreview() {
            parsedBets = [];
            const drawDate = document.getElementById('drawDate').value;
            const customerName = document.getElementById('customerName').value.trim();

            if (!drawDate || !customerName) {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'กรุณากรอกวันที่และชื่อลูกค้า' });
                return;
            }

            try {
                if (currentFormat === 'manual') {
                    const numbersInput = document.getElementById('manualNumbers').value.trim();
                    if (!numbersInput) return Swal.fire({ icon: 'warning', title: 'ว่างเปล่า', text: 'กรุณากรอกตัวเลข' });

                    const betType = document.querySelector('input[name="betType"]:checked').value;
                    const tokens = numbersInput.split(/\s+/).filter(n => n.length > 0);
                    const expectedLength = betType === '2digit' ? 2 : 3;
                    const invalid = tokens.filter(n => !/^\d+$/.test(n) || n.length !== expectedLength);

                    if (invalid.length > 0) {
                        Swal.fire({ icon: 'error', title: 'พบเลขผิดประเภท!', html: `พบเลขที่ไม่ใช่ ${expectedLength} หลัก: <b class="text-red-600">${invalid.join(', ')}</b>` });
                        return document.getElementById('resultSection').classList.add('hidden');
                    }

                    tokens.forEach(number => {
                        if (betType === '2digit') parsedBets.push({ number, top: parseFloat(document.getElementById('priceTop').value) || 0, bottom: parseFloat(document.getElementById('priceBottom').value) || 0, toad: 0 });
                        else if (betType === '3digit') parsedBets.push({ number, top: parseFloat(document.getElementById('priceTop').value) || 0, bottom: 0, toad: parseFloat(document.getElementById('priceToad').value) || 0 });
                        else getAllPermutations(number).forEach(num => parsedBets.push({ number: num, top: parseFloat(document.getElementById('pricePerDoor').value) || 0, bottom: 0, toad: 0 }));
                    });
                } else {
                    const input = document.getElementById('betInput').value.trim();
                    if (!input) return Swal.fire({ icon: 'warning', title: 'ว่างเปล่า', text: 'กรุณากรอกโพย' });
                    parsedBets = parseShortFormat(input);
                }

                const dateParts = drawDate.split('-');
                displayResults(formatDateThai(new Date(dateParts[0], dateParts[1] - 1, dateParts[2])), customerName, parsedBets);
                document.getElementById('resultSection').scrollIntoView({ behavior: 'smooth' });

            } catch (error) {
                parsedBets = [];
                Swal.fire({ icon: 'error', title: 'ข้อมูลไม่ถูกต้อง', text: error.message });
                document.getElementById('resultSection').classList.add('hidden');
            }
        }

        function parseShortFormat(input) {
            // 1. ตรวจสอบ Strict Rules: ห้ามใช้ / และ -
            if (input.includes('/') || input.includes('-')) {
                let char = input.includes('/') ? '/' : '-';
                throw new Error(`พบเครื่องหมายที่ไม่อนุญาต: "${char}" กรุณาใช้เว้นวรรค หรือเครื่องหมาย * และ = เท่านั้น`);
            }

            const bets = [];
            // เปลี่ยนการขึ้นบรรทัดใหม่เป็นเว้นวรรคเพื่อให้ Logic อ่านได้เหมือนกันทั้ง 2 รูปแบบ
            let cleanInput = input.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim();
            const tokens = cleanInput.split(' ');

            let i = 0;
            while (i < tokens.length) {
                let token = tokens[i];
                let number, amounts;

                if (token.includes('=')) {
                    let parts = token.split('=');
                    number = parts[0];
                    amounts = parts[1];
                    i++;
                } else {
                    number = token;
                    amounts = tokens[i + 1] || "";
                    i += 2;
                }

                if (!number || !/^\d+$/.test(number)) continue;

                let isReverse = false;
                if (i < tokens.length && (tokens[i] === 'กลับ' || tokens[i] === 'ก')) {
                    isReverse = true;
                    i++;
                } else if (number.length === 3 && amounts && amounts.includes('*6')) {
                    isReverse = true;
                }

                if (isReverse) {
                    bets.push(...parseBetAmountReverse(number, amounts));
                } else {
                    bets.push(parseAmounts(number, amounts));
                }
            }

            if (bets.length === 0) throw new Error('ไม่พบรูปแบบตัวเลขและราคาที่ถูกต้อง');
            return bets;
        }

        function parseAmounts(number, amounts) {
            amounts = (amounts || "0").replace(/×/g, '*');
            if (amounts.includes('*')) {
                const parts = amounts.split('*');
                const [first, second] = [parseFloat(parts[0]), parseFloat(parts[1])];
                if (number.length === 2) return { number, top: first || 0, bottom: second || 0, toad: 0 };
                return { number, top: first || 0, bottom: 0, toad: second || 0 };
            }
            return { number, top: parseFloat(amounts) || 0, bottom: 0, toad: 0 };
        }

        function parseBetAmountReverse(number, amounts) {
            if (number.length !== 3) throw new Error(`เลขกลับใช้ได้กับ 3 หลักเท่านั้น: "${number}"`);
            amounts = (amounts || "0").replace(/×/g, '*').replace(/กลับ|ก/g, '');
            let topAmount = amounts.includes('*') ? parseFloat(amounts.split('*')[0]) : parseFloat(amounts);
            return getAllPermutations(number).map(num => ({ number: num, top: topAmount || 0, bottom: 0, toad: 0 }));
        }

        function getAllPermutations(str) {
            const results = new Set();
            const arr = str.split('');
            const permute = (a, m = []) => {
                if (a.length === 0) results.add(m.join(''));
                else for (let i = 0; i < a.length; i++) {
                    let curr = a.slice();
                    let next = curr.splice(i, 1);
                    permute(curr.slice(), m.concat(next));
                }
            };
            permute(arr);
            return Array.from(results).sort();
        }

        function displayResults(drawDate, customerName, bets) {
            document.getElementById('displayDrawDate').textContent = drawDate;
            document.getElementById('displayCustomer').textContent = customerName;
            let html = '';
            bets.forEach((bet, index) => {
                const numClass = bet.number.length === 2 ? 'text-blue-600' : 'text-purple-600';
                html += `<tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-bold text-xl ${numClass}">${bet.number}</td>
                    <td class="px-4 py-3 text-right">${bet.top > 0 ? bet.top.toFixed(2) : '-'}</td>
                    <td class="px-4 py-3 text-right">${bet.bottom > 0 ? bet.bottom.toFixed(2) : '-'}</td>
                    <td class="px-4 py-3 text-right">${bet.toad > 0 ? bet.toad.toFixed(2) : '-'}</td>
                    <td class="px-4 py-3 text-right font-bold">${(bet.top + bet.bottom + bet.toad).toFixed(2)}</td>
                    <td class="px-4 py-3 text-center"><button onclick="deleteRow(${index})" class="text-red-600">❌</button></td>
                </tr>`;
            });
            document.getElementById('resultTable').innerHTML = html;
            updateTotals();
            document.getElementById('resultSection').classList.remove('hidden');
        }

        function deleteRow(index) {
            parsedBets.splice(index, 1);
            if (parsedBets.length === 0) document.getElementById('resultSection').classList.add('hidden');
            else displayResults(document.getElementById('displayDrawDate').textContent, document.getElementById('displayCustomer').textContent, parsedBets);
        }

        function updateTotals() {
            let tTop = 0, tBottom = 0, tToad = 0;
            parsedBets.forEach(b => { tTop += b.top; tBottom += b.bottom; tToad += b.toad; });
            document.getElementById('totalTop').textContent = tTop.toFixed(2);
            document.getElementById('totalBottom').textContent = tBottom.toFixed(2);
            document.getElementById('totalToad').textContent = tToad.toFixed(2);
            document.getElementById('grandTotal').textContent = (tTop + tBottom + tToad).toFixed(2);
        }

        async function saveBets() {
            const drawDate = document.getElementById('drawDate').value;
            const customerName = document.getElementById('customerName').value.trim();
            if (parsedBets.length === 0) return;
            try {
                const response = await fetch('{{ route("bets.store") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ draw_date: drawDate, customer_name: customerName, bets: parsedBets })
                });
                const data = await response.json();
                if (data.success) {
                    localStorage.setItem('lastCustomerName', customerName);
                    await Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: `บันทึกเรียบร้อย`, timer: 2000 });
                    location.reload();
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