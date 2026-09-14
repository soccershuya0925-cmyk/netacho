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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // どのネタを出すか（ネタ 1 : 投稿 多＝同じネタを別の媒体にも出せる）
            $table->foreignId('idea_id')->constrained()->cascadeOnDelete();
            $table->string('platform');                 // X / Instagram / note
            $table->date('scheduled_for');              // 出す予定の日
            $table->dateTime('posted_at')->nullable();  // 実際に出した日時（null なら未投稿）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
