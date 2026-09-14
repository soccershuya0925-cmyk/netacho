<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // どの商品のネタか（商品 1 : ネタ 多）。商品を消してもネタは残す
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');                        // ネタの見出し
            $table->text('body');                           // 投稿文の下書き
            $table->string('status')->default('下書き');    // 下書き / 予定あり / 使用済み
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ideas');
    }
};
