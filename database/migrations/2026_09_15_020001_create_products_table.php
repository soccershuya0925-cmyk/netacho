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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // 誰の商品か（ユーザを消したら商品も消える）
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');                     // 商品名（例：かけるとポン酢）
            $table->text('description')->nullable();    // 特徴・売り（型に差し込む材料）
            $table->string('image_path')->nullable();   // 商品写真
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
