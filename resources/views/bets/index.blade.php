<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>รับแทงหวย - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Configure Tailwind Dark Mode
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">
    <script>
        // Theme initialization - Must run before page render
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        * {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <!-- Breadcrumb & Header -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center mb-4">
                <nav class="text-sm text-slate-600 dark:text-slate-400">
                    <a href="{{ route('dashboard') }}"
                        class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                    <span class="mx-2">›</span>
                    <span class="text-slate-900 dark:text-white font-semibold">รับแทงหวย</span>
                </nav>
                <div class="flex items-center gap-3">
                    <!-- Theme Toggle -->
                    <button id="themeToggle"
                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                        <span
                            class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
                    </button>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">🎰 รับแทงหวย</h1>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">หวยออกทุกวันที่ 1 และ 16 ของทุกเดือน</p>
                </div>
                <div class="text-right">
                    <div
                        class="transition-all duration-300 inline-flex items-center gap-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full px-4 py-2 mb-2">
                        <span class="text-sm text-slate-900 dark:text-white font-medium">{{ Auth::user()->name }}</span>
                        <span class="text-slate-400">|</span>
                        <span
                            class="text-sm text-slate-600 dark:text-slate-400">{{ Auth::user()->role === 'admin' ? '👑 ผู้ดูแลระบบ' : '👤 พนักงาน' }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">ออกจากระบบ</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Input Form -->
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">📝 ข้อมูลการแทง</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">งวดวันที่ *</label>
                    <select id="drawDate"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all">
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">ชื่อลูกค้า *</label>
                    <input type="text" id="customerName"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 transition-all"
                        placeholder="ชื่อลูกค้า">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">รูปแบบการกรอก</label>
                <div class="flex gap-2 mb-4">
                    <button type="button" onclick="switchFormat('short')" id="btnShort"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white rounded-lg font-semibold transition-all">
                        พิมพ์ย่อ / ก๊อปจากแชท
                    </button>
                    <button type="button" onclick="switchFormat('manual')" id="btnManual"
                        class="px-4 py-2 bg-slate-300 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-400 dark:hover:bg-slate-600 rounded-lg font-semibold transition-all">
                        กรอกแบบเลือกประเภท
                    </button>
                </div>

                <div id="textInputArea">
                    <textarea id="betInput" rows="10"
                        class="w-full px-4 py-3 border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-600 font-mono transition-all"
                        placeholder="ตัวอย่างการกรอก:&#10;91 20&#10;19 20*20&#10;77=100*0&#10;365 10*6 กลับ"></textarea>

                    <div
                        class="mt-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded text-sm">
                        <p class="font-bold mb-1 text-emerald-800 dark:text-emerald-300">📋 รูปแบบที่รองรับ
                            (พิมพ์รวมหรือแยกบรรทัดก็ได้):</p>
                        <div id="formatExample"
                            class="font-mono text-slate-700 dark:text-slate-300 grid grid-cols-1 md:grid-cols-2 gap-x-4">
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
                            <p class="text-red-600 dark:text-red-400 font-bold col-span-2 mt-1">⚠️ ห้ามใช้เครื่องหมาย /
                                และ - (ระบบจะแจ้ง Error)</p>
                        </div>
                    </div>
                </div>

                <div id="manualInputArea" class="hidden">
                    <div
                        class="bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg p-4">
                        <div class="mb-4">
                            <label
                                class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">เลือกประเภท:</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="betType" value="2digit" checked
                                        onchange="updateManualInputFields()" class="w-4 h-4 text-emerald-600">
                                    <span class="text-sm font-medium text-slate-900 dark:text-white">2 ตัว</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="betType" value="3digit"
                                        onchange="updateManualInputFields()" class="w-4 h-4 text-emerald-600">
                                    <span class="text-sm font-medium text-slate-900 dark:text-white">3 ตัว</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="betType" value="3reverse"
                                        onchange="updateManualInputFields()" class="w-4 h-4 text-emerald-600">
                                    <span class="text-sm font-medium text-slate-900 dark:text-white">3 ตัวกลับ (6
                                        ประตู)</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">กรอกตัวเลข
                                (คั่นด้วยเว้นวรรค):</label>
                            <textarea id="manualNumbers" rows="3"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg font-mono"
                                placeholder="เช่น: 91 20 19 41 52"></textarea>
                        </div>

                        <div id="manualPriceFields" class="mb-4"></div>
                    </div>
                </div>
            </div>

            <button onclick="parseAndPreview()"
                class="w-full bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-3 rounded-lg transition-all">
                🔍 อ่านโพยและแสดงผล
            </button>
        </div>

        <!-- Results Section -->
        <div id="resultSection" class="hidden">
            <div
                class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">📊 ผลการแปลโพย</h2>

                <div
                    class="mb-6 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-sm text-slate-600 dark:text-slate-400">งวดวันที่</p>
                            <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400" id="displayDrawDate">
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600 dark:text-slate-400">ลูกค้า</p>
                            <p class="text-lg font-bold text-teal-600 dark:text-teal-400" id="displayCustomer"></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600 dark:text-slate-400">สร้างโดย</p>
                            <p class="text-lg font-bold text-violet-600 dark:text-violet-400">{{ Auth::user()->name }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">เลข</th>
                                <th class="px-4 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">บน</th>
                                <th class="px-4 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">ล่าง</th>
                                <th class="px-4 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">โต๊ด</th>
                                <th class="px-4 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">รวม</th>
                                <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="resultTable"
                            class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        </tbody>
                    </table>
                </div>

                <div
                    class="mt-6 p-4 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                    <div class="grid grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">บน</p>
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" id="totalTop">0.00</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">ล่าง</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400" id="totalBottom">0.00</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">โต๊ด</p>
                            <p class="text-2xl font-bold text-violet-600 dark:text-violet-400" id="totalToad">0.00</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">รวมทั้งหมด</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white" id="grandTotal">0.00</p>
                        </div>
                    </div>
                </div>

                <button onclick="saveBets()"
                    class="w-full mt-6 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-3 rounded-lg transition-all">
                    💾 บันทึกข้อมูล
                </button>
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;

        themeToggle.addEventListener('click', () => {
            if (htmlElement.classList.contains('dark')) {
                htmlElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                htmlElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        });

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
            document.getElementById('btnShort').className = format === 'short'
                ? 'px-4 py-2 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white rounded-lg font-semibold transition-all'
                : 'px-4 py-2 bg-slate-300 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-400 dark:hover:bg-slate-600 rounded-lg font-semibold transition-all';
            document.getElementById('btnManual').className = format === 'manual'
                ? 'px-4 py-2 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white rounded-lg font-semibold transition-all'
                : 'px-4 py-2 bg-slate-300 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-400 dark:hover:bg-slate-600 rounded-lg font-semibold transition-all';
            document.getElementById('textInputArea').classList.toggle('hidden', format !== 'short');
            document.getElementById('manualInputArea').classList.toggle('hidden', format !== 'manual');
        }

        function updateManualInputFields() {
            const betType = document.querySelector('input[name="betType"]:checked').value;
            const container = document.getElementById('manualPriceFields');
            let html = '<div class="grid grid-cols-2 gap-4">';
            if (betType === '2digit') {
                html += `<div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ราคาบน</label><input type="number" id="priceTop" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>
                         <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ราคาล่าง</label><input type="number" id="priceBottom" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>`;
            } else if (betType === '3digit') {
                html += `<div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ราคาบน</label><input type="number" id="priceTop" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>
                         <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ราคาโต๊ด</label><input type="number" id="priceToad" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>`;
            } else {
                html += `<div class="col-span-2"><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ยอดต่อประตู</label><input type="number" id="pricePerDoor" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>`;
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
                const numClass = bet.number.length === 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-violet-600 dark:text-violet-400';
                html += `<tr class="transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                    <td class="px-4 py-3 font-bold text-xl ${numClass}">${bet.number}</td>
                    <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-300">${bet.top > 0 ? bet.top.toFixed(2) : '-'}</td>
                    <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-300">${bet.bottom > 0 ? bet.bottom.toFixed(2) : '-'}</td>
                    <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-300">${bet.toad > 0 ? bet.toad.toFixed(2) : '-'}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">${(bet.top + bet.bottom + bet.toad).toFixed(2)}</td>
                    <td class="px-4 py-3 text-center"><button onclick="deleteRow(${index})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">❌</button></td>
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