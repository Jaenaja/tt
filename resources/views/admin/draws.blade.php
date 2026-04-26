<!DOCTYPE html>
<html lang="th" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>กรอกผลหวย - ระบบหวย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script>
        if (localStorage.theme==='dark'||(!('theme' in localStorage)&&window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else { document.documentElement.classList.remove('dark'); }
    </script>
    <style>* { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="transition-all duration-300 bg-slate-50 dark:bg-slate-950 min-h-screen">
<div class="container mx-auto px-4 py-8">

    <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
        <div class="flex justify-between items-center mb-4">
            <nav class="text-sm text-slate-600 dark:text-slate-400">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">หน้าหลัก</a>
                <span class="mx-2">›</span>
                <span class="text-slate-900 dark:text-white font-semibold">กรอกผลหวย</span>
            </nav>
            <button id="themeToggle" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-300 focus:outline-none bg-slate-300 dark:bg-emerald-600">
                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-300 translate-x-0.5 dark:translate-x-4.5"></span>
            </button>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">🎯 กรอกผลหวย</h1>
    </div>

    {{-- Form --}}
    <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 mb-6 border border-slate-200 dark:border-slate-800">
        <form id="drawForm" class="space-y-6">
            @csrf
            <div>
                <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-2">งวดวันที่ *</label>
                <select id="drawDate" name="draw_date" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500 transition-all"></select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-violet-50 dark:bg-violet-900/20 p-4 rounded-lg border-2 border-violet-300 dark:border-violet-700">
                    <label class="block text-slate-800 dark:text-slate-200 font-bold mb-2">🟣 3 ตัวบน *</label>
                    <input type="text" name="result_3_top" maxlength="3" pattern="[0-9]{3}" required
                        class="w-full px-4 py-3 text-3xl font-bold text-center border-2 border-violet-400 dark:border-violet-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-violet-500" placeholder="123">
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-lg border-2 border-emerald-300 dark:border-emerald-700">
                    <label class="block text-slate-800 dark:text-slate-200 font-bold mb-2">🔵 2 ตัวบน *</label>
                    <input type="text" name="result_2_top" maxlength="2" pattern="[0-9]{2}" required
                        class="w-full px-4 py-3 text-3xl font-bold text-center border-2 border-emerald-400 dark:border-emerald-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="23">
                </div>
                <div class="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-lg border-2 border-teal-300 dark:border-teal-700">
                    <label class="block text-slate-800 dark:text-slate-200 font-bold mb-2">🟢 2 ตัวล่าง *</label>
                    <input type="text" name="result_2_bottom" maxlength="2" pattern="[0-9]{2}" required
                        class="w-full px-4 py-3 text-3xl font-bold text-center border-2 border-teal-400 dark:border-teal-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-teal-500" placeholder="45">
                </div>
                <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border-2 border-orange-300 dark:border-orange-700">
                    <label class="block text-slate-800 dark:text-slate-200 font-bold mb-2">🟤 3 ตัวล่าง (4 เลข)</label>
                    <input type="text" name="result_3_bottom" id="result_3_bottom"
                        class="w-full px-4 py-3 text-lg font-bold text-center border-2 border-orange-400 dark:border-orange-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-orange-500"
                        placeholder="355,108,868,424">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">คั่นด้วยเครื่องหมาย , (4 ตัวเลข)</p>
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold py-4 rounded-lg transition-all text-lg shadow-lg">
                💾 บันทึกผลและคำนวณรางวัล
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="transition-all duration-300 bg-white dark:bg-slate-900 rounded-2xl shadow-xl dark:shadow-2xl p-6 border border-slate-200 dark:border-slate-800">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">📋 รายการงวดที่ผ่านมา</h2>
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800">
                    <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                        <th class="px-4 py-3 text-left text-slate-900 dark:text-slate-200 font-bold">งวดวันที่</th>
                        <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">3 ตัวบน</th>
                        <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">2 ตัวบน</th>
                        <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">2 ตัวล่าง</th>
                        <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">3 ตัวล่าง</th>
                        <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">สถานะ</th>
                        <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">ประกาศโดย</th>
                        <th class="px-4 py-3 text-center text-slate-900 dark:text-slate-200 font-bold">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                    @forelse($draws as $draw)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-300 font-semibold">{{ thai_date($draw->draw_date) }}</td>
                        <td class="px-4 py-3 text-center"><span class="text-2xl font-bold text-violet-600 dark:text-violet-400">{{ $draw->result_3_top ?? '-' }}</span></td>
                        <td class="px-4 py-3 text-center"><span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $draw->result_2_top ?? '-' }}</span></td>
                        <td class="px-4 py-3 text-center"><span class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ $draw->result_2_bottom ?? '-' }}</span></td>
                        <td class="px-4 py-3 text-center">
                            @if($draw->result_3_bottom)
                                <span class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ $draw->result_3_bottom }}</span>
                            @else
                                <span class="text-slate-400 dark:text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($draw->is_announced)
                                <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">ประกาศแล้ว</span>
                            @else
                                <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">รอประกาศ</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-slate-700 dark:text-slate-300">
                            @if($draw->announcedBy)
                                {{ $draw->announcedBy->name }}<br>
                                <span class="text-slate-500 dark:text-slate-500">{{ $draw->announced_at->format('d/m/y H:i') }}</span>
                            @else -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.reports.summary', $draw->id) }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-semibold transition-colors">
                                {{ $draw->is_announced ? 'ดูผล' : 'รายการ' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-16 text-center text-slate-400 dark:text-slate-500">ยังไม่มีข้อมูลงวดหวย</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $draws->links() }}</div>
    </div>
</div>
<script>
const thaiMonths=['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
function formatDateThai(s){const[y,m,d]=s.split('-');return`${parseInt(d)} ${thaiMonths[parseInt(m)]} ${parseInt(y)+543}`;}

document.getElementById('themeToggle').addEventListener('click',()=>{
    if(document.documentElement.classList.contains('dark')){document.documentElement.classList.remove('dark');localStorage.theme='light';}
    else{document.documentElement.classList.add('dark');localStorage.theme='dark';}
});

async function loadOpenDraws(){
    const sel=document.getElementById('drawDate');
    try{
        const res=await fetch('/api/open-draws');const data=await res.json();
        if(!data.success||!data.draws||!data.draws.length){sel.innerHTML='<option value="">ไม่มีงวดที่รอประกาศผล</option>';return;}
        sel.innerHTML=data.draws.map((d,i)=>`<option value="${d.value}"${i===0?' selected':''}>${formatDateThai(d.value)}</option>`).join('');
    }catch(e){sel.innerHTML='<option value="">เกิดข้อผิดพลาด</option>';}
}
window.onload=loadOpenDraws;

document.getElementById('drawForm').addEventListener('submit',async function(e){
    e.preventDefault();
    const fd=new FormData(this);
    const result3Bottom=fd.get('result_3_bottom')||'';
    // Validate 3-bottom format if provided
    if(result3Bottom.trim()){
        const parts=result3Bottom.split(',').map(s=>s.trim());
        const invalid=parts.filter(p=>!/^\d{3}$/.test(p));
        if(invalid.length>0||parts.length!==4){
            Swal.fire({icon:'warning',title:'รูปแบบ 3 ตัวล่างไม่ถูกต้อง',text:'กรุณากรอกเลข 3 หลัก 4 ตัว คั่นด้วย , เช่น 355,108,868,424'});return;
        }
    }
    const confirm=await Swal.fire({title:'ยืนยันการบันทึก?',text:`งวดวันที่ ${fd.get('draw_date')}`,icon:'question',showCancelButton:true,confirmButtonText:'บันทึก',cancelButtonText:'ยกเลิก'});
    if(!confirm.isConfirmed)return;
    try{
        const res=await fetch('{{ route("admin.draws.store") }}',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
            body:JSON.stringify({draw_date:fd.get('draw_date'),result_3_top:fd.get('result_3_top'),result_2_top:fd.get('result_2_top'),result_2_bottom:fd.get('result_2_bottom'),result_3_bottom:result3Bottom||null})
        });
        const data=await res.json();
        if(data.success){await Swal.fire({icon:'success',title:'สำเร็จ!',text:'บันทึกผลหวยเรียบร้อย',timer:2000});window.location.reload();}
        else Swal.fire({icon:'error',title:'ERROR',text:data.message});
    }catch(err){Swal.fire({icon:'error',title:'ERROR',text:'เกิดข้อผิดพลาดในการบันทึก'});}
});
</script>
</body>
</html>
