<!DOCTYPE html>
<html lang="th" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>รับแทงหวย - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <script>if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) { document.documentElement.classList.add('dark') } else { document.documentElement.classList.remove('dark') }</script>
    <style>
        * {
            font-family: 'Sarabun', sans-serif;
        }

        #backToTop {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, .37);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
        }

        #backToTop.show {
            opacity: 1;
            visibility: visible;
        }

        .dark #backToTop {
            background: rgba(16, 185, 129, .15);
            border: 1px solid rgba(16, 185, 129, .3);
        }

        #backToTop svg {
            width: 24px;
            height: 24px;
            color: #10b981;
        }

        .dark #backToTop svg {
            color: #6ee7b7;
        }
    </style>
</head>

<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-5xl">

        {{-- Header --}}
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <div class="flex justify-between items-center mb-4">
                <nav class="text-sm text-slate-600 dark:text-slate-400">
                    <a href="{{ route('dashboard') }}"
                        class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                    <span class="mx-2">›</span><span
                        class="text-slate-900 dark:text-white font-semibold">รับแทงหวย</span>
                </nav>
                <div class="flex items-center gap-3">
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
                    <form action="{{ route('logout') }}" method="POST" class="inline">@csrf
                        <button type="submit"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">ออกจากระบบ</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Input Form --}}
        <div
            class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">📝 ข้อมูลการแทง</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">งวดวันที่ *</label>
                    <select id="drawDate"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 transition-all"></select>
                </div>
                <div>
                    <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">ชื่อลูกค้า *</label>
                    <input type="text" id="customerName"
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 transition-all"
                        placeholder="ชื่อลูกค้า" autocomplete="off" data-form-type="other" data-lpignore="true">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">รูปแบบการกรอก</label>
                <div class="flex gap-2 mb-4">
                    <button type="button" onclick="switchFormat('short')" id="btnShort"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white rounded-lg font-semibold transition-all">พิมพ์ย่อ
                        / ก๊อปจากแชท</button>
                    <button type="button" onclick="switchFormat('manual')" id="btnManual"
                        class="px-4 py-2 bg-slate-300 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-400 dark:hover:bg-slate-600 rounded-lg font-semibold transition-all">กรอกแบบเลือกประเภท</button>
                </div>

                {{-- Short format --}}
                <div id="textInputArea">
                    <textarea id="betInput" rows="10"
                        class="w-full px-4 py-3 border-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 font-mono transition-all"
                        placeholder="ตัวอย่าง:&#10;91 20&#10;19 20*20&#10;365 10*6 กลับ"></textarea>
                    <div
                        class="mt-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded text-sm">
                        <p class="font-bold mb-1 text-emerald-800 dark:text-emerald-300">📋 รูปแบบที่รองรับ:</p>
                        <div
                            class="font-mono text-slate-700 dark:text-slate-300 grid grid-cols-1 md:grid-cols-2 gap-x-4 text-xs">
                            <p>• 91 20 (2 ตัวบน)</p>
                            <p>• 91 20*20 (บน*ล่าง)</p>
                            <p>• 941 100 (3 ตัวบน)</p>
                            <p>• 941 100*100 (บน*โต๊ด)</p>
                            <p>• 365 10*6 (6 ประตู)</p>
                            <p>• 365 10 ก (6 ประตู)</p>
                            <p class="text-sky-600 dark:text-sky-400 col-span-2 mt-1">⚠️ 3 ตัวล่าง ใช้รูปแบบ
                                "กรอกแบบเลือกประเภท" เท่านั้น</p>
                            <p class="text-red-600 dark:text-red-400 col-span-2">⚠️ ห้ามใช้เครื่องหมาย / และ -</p>
                        </div>
                    </div>
                </div>

                {{-- Manual format --}}
                <div id="manualInputArea" class="hidden">
                    <div
                        class="bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-lg p-4">
                        <div class="mb-4">
                            <label
                                class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">เลือกประเภท:</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
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
                                    <span class="text-sm font-medium text-slate-900 dark:text-white">3 ตัวกลับ</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="betType" value="3bottom"
                                        onchange="updateManualInputFields()" class="w-4 h-4 text-sky-600">
                                    <span class="text-sm font-medium text-sky-600 dark:text-sky-400 font-bold">🔻 3
                                        ตัวล่าง</span>
                                </label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">กรอกตัวเลข
                                (คั่นด้วยเว้นวรรค):</label>
                            <textarea id="manualNumbers" rows="3"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg font-mono"
                                placeholder="เช่น: 355 108 868 424"></textarea>
                        </div>
                        <div id="manualPriceFields" class="mb-4"></div>
                    </div>
                </div>
            </div>

            <button onclick="parseAndPreview()"
                class="w-full bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-3 rounded-lg transition-all">🔍
                อ่านโพยและแสดงผล</button>
        </div>

        {{-- Results --}}
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
                                <th class="px-4 py-3 text-right text-sky-700 dark:text-sky-300 font-bold">3ตัวล่าง</th>
                                <th class="px-4 py-3 text-right text-slate-900 dark:text-slate-200 font-bold">รวม</th>
                                <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="resultTable"
                            class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900"></tbody>
                    </table>
                </div>
                <div
                    class="mt-6 p-4 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                    <div class="grid grid-cols-5 gap-4 text-center">
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
                            <p class="text-xs text-sky-700 dark:text-sky-300 mb-1 font-bold">3ตัวล่าง</p>
                            <p class="text-2xl font-bold text-sky-600 dark:text-sky-400" id="totalBottom3">0.00</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-1">รวมทั้งหมด</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white" id="grandTotal">0.00</p>
                        </div>
                    </div>
                </div>
                <button id="saveBetsBtn" onclick="saveBets()"
                    class="w-full mt-6 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-3 rounded-lg transition-all">💾
                    บันทึกข้อมูล</button>
            </div>
        </div>
    </div>

    <a href="{{ route('bets.history') }}" id="floatHistory"
        style="position:fixed;bottom:30px;left:30px;z-index:1000;display:flex;align-items:center;gap:8px;padding:12px 20px;background:#10b981;color:white;font-weight:700;border-radius:9999px;box-shadow:0 8px 24px rgba(16,185,129,0.4);transition:all .3s ease;text-decoration:none;font-size:14px;"
        onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">📜 ประวัติ</a>
    <div id="backToTop"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg></div>

    <script>
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', () => backToTop.classList.toggle('show', window.pageYOffset > 300));
        backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        document.getElementById('themeToggle').addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) { document.documentElement.classList.remove('dark'); localStorage.theme = 'light'; }
            else { document.documentElement.classList.add('dark'); localStorage.theme = 'dark'; }
        });

        let parsedBets = []; let currentFormat = 'short';
        const thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        function formatDateForDatabase(d) { return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`; }
        function formatDateThai(d) { return `${d.getDate()} ${thaiMonths[d.getMonth() + 1]} ${d.getFullYear() + 543}`; }

        function generateDrawDates() {
            const sel = document.getElementById('drawDate');
            const today = new Date(); const cm = today.getMonth(); const cy = today.getFullYear();
            const draws = []; for (let o = 0; o <= 3; o++) { draws.push(new Date(cy, cm + o, 1)); draws.push(new Date(cy, cm + o, 16)); }
            const todayStr = formatDateForDatabase(today);
            const futureDraws = draws.filter(d => formatDateForDatabase(d) >= todayStr);
            if (!futureDraws.length) { sel.innerHTML = '<option value="">ไม่พบงวด</option>'; return; }
            const def = formatDateForDatabase(futureDraws[0]);
            sel.innerHTML = futureDraws.map(d => { const v = formatDateForDatabase(d); return `<option value="${v}"${v === def ? ' selected' : ''}>${formatDateThai(d)}</option>`; }).join('');
        }

        function switchFormat(f) {
            currentFormat = f;
            document.getElementById('btnShort').className = f === 'short' ? 'px-4 py-2 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white rounded-lg font-semibold transition-all' : 'px-4 py-2 bg-slate-300 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-400 dark:hover:bg-slate-600 rounded-lg font-semibold transition-all';
            document.getElementById('btnManual').className = f === 'manual' ? 'px-4 py-2 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white rounded-lg font-semibold transition-all' : 'px-4 py-2 bg-slate-300 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-400 dark:hover:bg-slate-600 rounded-lg font-semibold transition-all';
            document.getElementById('textInputArea').classList.toggle('hidden', f !== 'short');
            document.getElementById('manualInputArea').classList.toggle('hidden', f !== 'manual');
        }

        function updateManualInputFields() {
            const betType = document.querySelector('input[name="betType"]:checked').value;
            const c = document.getElementById('manualPriceFields');
            let html = '<div class="grid grid-cols-2 gap-4">';
            if (betType === '2digit') {
                html += `<div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ราคาบน</label><input type="number" id="priceTop" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>
                   <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ราคาล่าง</label><input type="number" id="priceBottom" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>`;
            } else if (betType === '3digit') {
                html += `<div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ราคาบน</label><input type="number" id="priceTop" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>
                   <div><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ราคาโต๊ด</label><input type="number" id="priceToad" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>`;
            } else if (betType === '3bottom') {
                html += `<div class="col-span-2"><label class="block text-sm font-medium text-sky-700 dark:text-sky-300 mb-1 font-bold">🔻 ราคา 3 ตัวล่าง</label><input type="number" id="priceBottom3" value="10" class="w-full px-3 py-2 border-2 border-sky-300 dark:border-sky-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"><p class="text-xs text-slate-500 mt-1">ป้อนเลข 3 หลักที่ต้องการแทง 3 ตัวล่าง</p></div>`;
            } else {
                html += `<div class="col-span-2"><label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ยอดต่อประตู</label><input type="number" id="pricePerDoor" value="10" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg"></div>`;
            }
            c.innerHTML = html + '</div>';
        }

        function parseAndPreview() {
            parsedBets = [];
            const drawDate = document.getElementById('drawDate').value;
            const customerName = document.getElementById('customerName').value.trim();
            if (!drawDate || !customerName) { Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'กรุณากรอกวันที่และชื่อลูกค้า' }); return; }
            try {
                if (currentFormat === 'manual') {
                    const numbersInput = document.getElementById('manualNumbers').value.trim();
                    if (!numbersInput) return Swal.fire({ icon: 'warning', title: 'ว่างเปล่า', text: 'กรุณากรอกตัวเลข' });
                    const betType = document.querySelector('input[name="betType"]:checked').value;
                    const tokens = numbersInput.split(/\s+/).filter(n => n.length > 0);
                    const expectedLen = betType === '2digit' ? 2 : 3;
                    const invalid = tokens.filter(n => !/^\d+$/.test(n) || n.length !== expectedLen);
                    if (invalid.length > 0) { Swal.fire({ icon: 'error', title: 'พบเลขผิดประเภท!', html: `พบเลขที่ไม่ใช่ ${expectedLen} หลัก: <b class="text-red-600">${invalid.join(', ')}</b>` }); document.getElementById('resultSection').classList.add('hidden'); return; }

                    tokens.forEach(number => {
                        if (betType === '2digit') parsedBets.push({ number, top: parseFloat(document.getElementById('priceTop').value) || 0, bottom: parseFloat(document.getElementById('priceBottom').value) || 0, toad: 0, bottom_3: 0 });
                        else if (betType === '3digit') parsedBets.push({ number, top: parseFloat(document.getElementById('priceTop').value) || 0, bottom: 0, toad: parseFloat(document.getElementById('priceToad').value) || 0, bottom_3: 0 });
                        else if (betType === '3bottom') parsedBets.push({ number, top: 0, bottom: 0, toad: 0, bottom_3: parseFloat(document.getElementById('priceBottom3').value) || 0 });
                        else getAllPermutations(number).forEach(num => parsedBets.push({ number: num, top: parseFloat(document.getElementById('pricePerDoor').value) || 0, bottom: 0, toad: 0, bottom_3: 0 }));
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
            if (input.includes('/') || input.includes('-')) { let c = input.includes('/') ? '/' : '-'; throw new Error(`พบเครื่องหมายที่ไม่อนุญาต: "${c}"`); }
            const bets = [];
            let cleanInput = input.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim();
            const tokens = cleanInput.split(' ');
            let i = 0;
            while (i < tokens.length) {
                let token = tokens[i]; let number, amounts;
                if (token.includes('=')) { let p = token.split('='); number = p[0]; amounts = p[1]; i++; }
                else { number = token; amounts = tokens[i + 1] || ''; i += 2; }
                if (!number || !/^\d+$/.test(number)) continue;
                let isReverse = false;
                if (i < tokens.length && (tokens[i] === 'กลับ' || tokens[i] === 'ก')) { isReverse = true; i++; }
                else if (number.length === 3 && amounts && amounts.includes('*6')) { isReverse = true; }
                if (isReverse) { bets.push(...parseBetAmountReverse(number, amounts)); }
                else { bets.push(parseAmounts(number, amounts)); }
            }
            if (!bets.length) throw new Error('ไม่พบรูปแบบตัวเลขและราคาที่ถูกต้อง');
            return bets;
        }

        function parseAmounts(number, amounts) {
            amounts = (amounts || '0').replace(/×/g, '*');
            if (amounts.includes('*')) {
                const p = amounts.split('*'); const [f, s] = [parseFloat(p[0]), parseFloat(p[1])];
                if (number.length === 2) return { number, top: f || 0, bottom: s || 0, toad: 0, bottom_3: 0 };
                return { number, top: f || 0, bottom: 0, toad: s || 0, bottom_3: 0 };
            }
            return { number, top: parseFloat(amounts) || 0, bottom: 0, toad: 0, bottom_3: 0 };
        }

        function parseBetAmountReverse(number, amounts) {
            if (number.length !== 3) throw new Error(`เลขกลับใช้ได้กับ 3 หลักเท่านั้น: "${number}"`);
            amounts = (amounts || '0').replace(/×/g, '*').replace(/กลับ|ก/g, '');
            let topAmount = amounts.includes('*') ? parseFloat(amounts.split('*')[0]) : parseFloat(amounts);
            return getAllPermutations(number).map(num => ({ number: num, top: topAmount || 0, bottom: 0, toad: 0, bottom_3: 0 }));
        }

        function getAllPermutations(str) {
            const results = new Set(); const arr = str.split('');
            const permute = (a, m = []) => { if (!a.length) { results.add(m.join('')); return; } for (let i = 0; i < a.length; i++) { let cur = a.slice(); let nx = cur.splice(i, 1); permute(cur.slice(), m.concat(nx)); } };
            permute(arr); return Array.from(results).sort();
        }

        function displayResults(drawDate, customerName, bets) {
            document.getElementById('displayDrawDate').textContent = drawDate;
            document.getElementById('displayCustomer').textContent = customerName;
            let html = '';
            bets.forEach((bet, index) => {
                const numClass = bet.number.length === 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-violet-600 dark:text-violet-400';
                const b3 = bet.bottom_3 || 0;
                const is3Bottom = b3 > 0;
                html += `<tr class="transition-all duration-200 hover:bg-slate-50 dark:hover:bg-slate-800 ${is3Bottom ? 'bg-sky-50/50 dark:bg-sky-900/10' : ''}">
                <td class="px-4 py-3 font-bold text-xl ${numClass}">${bet.number}${is3Bottom ? '<span class="text-xs ml-1 text-sky-500">3ล่าง</span>' : ''}</td>
                <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-300">${bet.top > 0 ? bet.top.toFixed(2) : '-'}</td>
                <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-300">${bet.bottom > 0 ? bet.bottom.toFixed(2) : '-'}</td>
                <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-300">${bet.toad > 0 ? bet.toad.toFixed(2) : '-'}</td>
                <td class="px-4 py-3 text-right text-sky-600 dark:text-sky-400 font-semibold">${b3 > 0 ? b3.toFixed(2) : '-'}</td>
                <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">${(bet.top + bet.bottom + bet.toad + b3).toFixed(2)}</td>
                <td class="px-4 py-3 text-center"><button onclick="deleteRow(${index})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">❌</button></td>
            </tr>`;
            });
            document.getElementById('resultTable').innerHTML = html;
            updateTotals();
            document.getElementById('resultSection').classList.remove('hidden');
        }

        function deleteRow(index) {
            parsedBets.splice(index, 1);
            if (!parsedBets.length) document.getElementById('resultSection').classList.add('hidden');
            else displayResults(document.getElementById('displayDrawDate').textContent, document.getElementById('displayCustomer').textContent, parsedBets);
        }

        function updateTotals() {
            let tTop = 0, tBot = 0, tToad = 0, tBot3 = 0;
            parsedBets.forEach(b => { tTop += b.top; tBot += b.bottom; tToad += b.toad; tBot3 += (b.bottom_3 || 0); });
            document.getElementById('totalTop').textContent = tTop.toFixed(2);
            document.getElementById('totalBottom').textContent = tBot.toFixed(2);
            document.getElementById('totalToad').textContent = tToad.toFixed(2);
            document.getElementById('totalBottom3').textContent = tBot3.toFixed(2);
            document.getElementById('grandTotal').textContent = (tTop + tBot + tToad + tBot3).toFixed(2);
        }

        let isSubmitting = false;
        async function saveBets() {
            if (isSubmitting) return;
            const drawDate = document.getElementById('drawDate').value;
            const customerName = document.getElementById('customerName').value.trim();
            if (!parsedBets.length) return;
            isSubmitting = true;
            const btn = document.getElementById('saveBetsBtn');
            btn.disabled = true; btn.innerHTML = '<svg class="animate-spin inline w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>กำลังบันทึก...';
            btn.classList.replace('bg-emerald-600', 'bg-slate-400'); btn.classList.replace('hover:bg-emerald-700', 'hover:bg-slate-400');
            try {
                const resp = await fetch('{{ route("bets.store") }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ draw_date: drawDate, customer_name: customerName, bets: parsedBets }) });
                const data = await resp.json();
                if (data.success) {
                    const r = await Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ!', text: 'บันทึกรายการเรียบร้อยแล้ว', showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: '#64748b', confirmButtonText: '📜 ดูประวัติ', cancelButtonText: '➕ บันทึกต่อ', reverseButtons: true });
                    if (r.isConfirmed) { window.location.href = '{{ route("bets.history") }}'; } else { location.reload(); }
                } else {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message });
                    isSubmitting = false; btn.disabled = false; btn.innerHTML = '💾 บันทึกข้อมูล';
                    btn.classList.replace('bg-slate-400', 'bg-emerald-600'); btn.classList.replace('hover:bg-slate-400', 'hover:bg-emerald-700');
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'ERROR', text: 'เกิดข้อผิดพลาดในการบันทึก' });
                isSubmitting = false; btn.disabled = false; btn.innerHTML = '💾 บันทึกข้อมูล';
                btn.classList.replace('bg-slate-400', 'bg-emerald-600'); btn.classList.replace('hover:bg-slate-400', 'hover:bg-emerald-700');
            }
        }

        window.onload = function () { generateDrawDates(); updateManualInputFields(); };
    </script>
</body>

</html>