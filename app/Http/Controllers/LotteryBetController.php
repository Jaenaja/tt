<?php
namespace App\Http\Controllers;

use App\Models\LotteryBet;
use App\Models\LotteryDraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LotteryBetController extends Controller
{
    public function index() { return view('bets.index'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'draw_date'=>'required|string','customer_name'=>'required|string',
            'bets'=>'required|array','bets.*.number'=>'required|string',
            'bets.*.top'=>'required|numeric|min:0','bets.*.bottom'=>'required|numeric|min:0',
            'bets.*.toad'=>'required|numeric|min:0','bets.*.bottom_3'=>'nullable|numeric|min:0',
        ]);
        try {
            $drawDate = $validated['draw_date'];
            $draw = LotteryDraw::where('draw_date',$drawDate)->first();
            if ($draw && $draw->is_announced) return response()->json(['success'=>false,'message'=>'งวดนี้ประกาศผลแล้ว'],400);
            if (!$draw) $draw = LotteryDraw::create(['draw_date'=>$drawDate,'is_announced'=>0]);

            DB::transaction(function () use ($validated,$drawDate) {
                foreach ($validated['bets'] as $bet) {
                    $len = strlen($bet['number']); $b3 = $bet['bottom_3']??0;
                    if ($len===2 && $bet['toad']>0)   throw new \Exception("ERROR: เลข 2 ตัว ไม่มีโต๊ด");
                    if ($len===2 && $b3>0)             throw new \Exception("ERROR: เลข 2 ตัว ไม่มี 3 ตัวล่าง");
                    if ($len===3 && $bet['bottom']>0)  throw new \Exception("ERROR: เลข 3 ตัว ไม่มีล่าง 2 ตัว");
                    if ($len!==2 && $len!==3)          throw new \Exception("ERROR: เลขต้องเป็น 2 หรือ 3 หลัก");
                }
                $cutoff = now()->subSeconds(10);
                $existing = LotteryBet::whereNull('deleted_at')->where('draw_date',$drawDate)->where('customer_name',$validated['customer_name'])->where('created_at','>=',$cutoff)->get(['number','amount_top','amount_bottom','amount_toad','amount_bottom_3']);
                $recentKeys = $existing->map(fn($r) => "{$validated['customer_name']}|{$r->number}|{$r->amount_top}|{$r->amount_bottom}|{$r->amount_toad}|{$r->amount_bottom_3}")->flip()->all();
                $now = now(); $rows = [];
                foreach ($validated['bets'] as $bet) {
                    $b3 = $bet['bottom_3']??0;
                    $key = "{$validated['customer_name']}|{$bet['number']}|{$bet['top']}|{$bet['bottom']}|{$bet['toad']}|{$b3}";
                    if (isset($recentKeys[$key])) throw new \Exception("พบข้อมูลซ้ำ: เลข {$bet['number']}");
                    $rows[] = ['draw_date'=>$drawDate,'customer_name'=>$validated['customer_name'],'number'=>$bet['number'],'amount_top'=>$bet['top'],'amount_bottom'=>$bet['bottom'],'amount_toad'=>$bet['toad'],'amount_bottom_3'=>$b3,'created_by'=>Auth::id(),'created_at'=>$now,'updated_at'=>$now];
                }
                DB::table('lottery_bets')->insert($rows);
            });
            return response()->json(['success'=>true,'message'=>'บันทึกข้อมูลสำเร็จ']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()],400);
        }
    }

    public function history(Request $request)
    {
        $query = LotteryBet::with(['creator','deleter','draw'])->whereNull('deleted_at');
        if ($request->customer_name) $query->where('customer_name','like','%'.$request->customer_name.'%');
        if ($request->draw_date)     $query->where('draw_date',$request->draw_date);
        if ($request->search_number) $query->where('number','like','%'.$request->search_number.'%');
        $sortBy = $request->get('sort_by','draw_date'); $sortOrd = $request->get('sort_order','desc');
        if ($sortBy==='draw_date') $query->orderBy('draw_date',$sortOrd)->orderBy('created_at','desc');
        else $query->orderBy($sortBy,$sortOrd);
        $bets = $query->paginate(50)->appends($request->all());
        $drawDates = LotteryBet::select('draw_date')->whereNull('deleted_at')->distinct()->orderBy('draw_date','desc')->get();
        return view('bets.history', compact('bets','drawDates'));
    }

    public function destroy(Request $request, $id)
    {
        try {
            $deleteCode = \App\Models\Setting::get('delete_code','');
            if (empty($deleteCode)) return response()->json(['success'=>false,'message'=>'กรุณาตั้งรหัสลบก่อนใช้งาน'],400);
            $request->validate(['delete_code'=>'required|digits:6'],['delete_code.required'=>'กรุณากรอกรหัสลบ','delete_code.digits'=>'รหัสลบต้องเป็นตัวเลข 6 หลัก']);
            if ($request->delete_code !== $deleteCode) return response()->json(['success'=>false,'message'=>'รหัสลบไม่ถูกต้อง'],403);
            $bet = LotteryBet::findOrFail($id);
            $draw = LotteryDraw::where('draw_date',$bet->draw_date)->first();
            if ($draw && $draw->is_announced) return response()->json(['success'=>false,'message'=>'งวดนี้ประกาศผลแล้ว'],400);
            $bet->deleted_by = Auth::id(); $bet->save(); $bet->delete();
            return response()->json(['success'=>true,'message'=>'ลบรายการเรียบร้อยแล้ว']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success'=>false,'message'=>$e->validator->errors()->first()],422);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'เกิดข้อผิดพลาด: '.$e->getMessage()],500);
        }
    }

    public function exportExcel(Request $request)
    {
        $query = LotteryBet::with(['creator','draw'])->whereNull('deleted_at');
        if ($request->customer_name) $query->where('customer_name','like','%'.$request->customer_name.'%');
        if ($request->draw_date)     $query->where('draw_date',$request->draw_date);
        if ($request->search_number) $query->where('number','like','%'.$request->search_number.'%');
        $sortBy=$request->get('sort_by','draw_date'); $sortOrd=$request->get('sort_order','desc');
        if ($sortBy==='draw_date') $query->orderBy('draw_date',$sortOrd)->orderBy('created_at','desc');
        else $query->orderBy($sortBy,$sortOrd);
        $bets = $query->get();
        $rows = []; $rows[] = ['งวดวันที่','ชื่อลูกค้า','เลข','บน','ล่าง','โต๊ด','3ตัวล่าง','รวม (฿)','บันทึกโดย','วันที่บันทึก','สถานะ'];
        foreach ($bets as $bet) {
            $total=$bet->total_amount; $dDate=\Carbon\Carbon::parse($bet->draw_date)->format('d/m/Y');
            $cDate=\Carbon\Carbon::parse($bet->created_at)->format('d/m/y H:i'); $creator=$bet->creator?$bet->creator->name:'-';
            if ($bet->draw && $bet->draw->is_announced) {
                $status=($bet->is_win_top||$bet->is_win_bottom||$bet->is_win_toad||($bet->is_win_bottom_3??false))?'ถูกรางวัล':'ไม่ถูก';
            } else { $status='รอประกาศ'; }
            $rows[]=[$dDate,$bet->customer_name,$bet->number,$bet->amount_top>0?$bet->amount_top:'',
                $bet->amount_bottom>0?$bet->amount_bottom:'', $bet->amount_toad>0?$bet->amount_toad:'',
                ($bet->amount_bottom_3??0)>0?$bet->amount_bottom_3:'',$total,$creator,$cDate,$status];
        }
        $fn='ประวัติการแทง_'.now()->format('Y-m-d').'.csv';
        $handle=fopen('php://temp','r+'); fwrite($handle,"\xEF\xBB\xBF");
        foreach ($rows as $r) fputcsv($handle,$r); rewind($handle); $csv=stream_get_contents($handle); fclose($handle);
        return response($csv,200,['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>'attachment; filename="'.$fn.'"']);
    }
}
