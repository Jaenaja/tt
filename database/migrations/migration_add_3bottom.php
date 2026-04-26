<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('lottery_draws', function (Blueprint $table) {
            $table->string('result_3_bottom', 60)->nullable()->after('result_2_bottom');
        });
        Schema::table('lottery_bets', function (Blueprint $table) {
            $table->decimal('amount_bottom_3', 10, 2)->default(0)->after('amount_toad');
            $table->decimal('payout_bottom_3', 10, 2)->default(0)->after('payout_toad');
            $table->boolean('is_win_bottom_3')->default(false)->after('is_win_toad');
        });
        foreach ([
            ['key'=>'rate_3_bottom','value'=>'500','type'=>'decimal','description'=>'อัตราจ่าย 3 ตัวล่าง','group'=>'payout'],
            ['key'=>'max_payout_3_bottom','value'=>'50000','type'=>'decimal','description'=>'ยอดจ่ายสูงสุดต่อเลข 3 ตัวล่าง (บาท)','group'=>'risk'],
        ] as $s) {
            DB::table('settings')->insertOrIgnore(array_merge($s, ['created_at'=>now(),'updated_at'=>now()]));
        }
    }
    public function down(): void {
        Schema::table('lottery_draws', fn(Blueprint $t) => $t->dropColumn('result_3_bottom'));
        Schema::table('lottery_bets', fn(Blueprint $t) => $t->dropColumn(['amount_bottom_3','payout_bottom_3','is_win_bottom_3']));
        DB::table('settings')->whereIn('key',['rate_3_bottom','max_payout_3_bottom'])->delete();
    }
};
