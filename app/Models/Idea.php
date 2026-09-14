<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idea extends Model
{
    /** @use HasFactory<\Database\Factories\IdeaFactory> */
    use HasFactory;

    protected $fillable = ['product_id', 'title', 'body', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * どの商品のネタか（多対1）
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 多対多（このネタに付いたタグ）＝中間テーブル idea_tag
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * 1対多（このネタを出す予定・出した実績）
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
