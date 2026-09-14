<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    // 出す先（増やす時はここに足す）
    public const PLATFORMS = ['X', 'Instagram', 'note'];

    protected $fillable = ['idea_id', 'platform', 'scheduled_for', 'posted_at'];

    /**
     * 日付として扱うカラム
     */
    protected function casts(): array
    {
        return [
            'scheduled_for' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * どのネタを出すか（多対1）
     */
    public function idea()
    {
        return $this->belongsTo(Idea::class);
    }
}
