<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default rates
        DB::table('configs')->insert([
            ['key' => 'rate_2_top', 'value' => '90', 'description' => '2 ตัวบน', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'rate_2_bottom', 'value' => '90', 'description' => '2 ตัวล่าง', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'rate_3_top', 'value' => '900', 'description' => '3 ตัวบน', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'rate_3_toad', 'value' => '120', 'description' => '3 ตัวโต๊ด', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('configs');
    }
};