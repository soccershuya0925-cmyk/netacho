<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    // フォームから入れてよいカラム
    protected $fillable = ['name', 'description', 'image_path'];

    /**
     * この商品の持ち主
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 1対多（この商品に紐づく投稿ネタ・新しい順）
     */
    public function ideas()
    {
        return $this->hasMany(Idea::class)->latest();
    }
}
