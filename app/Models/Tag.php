<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 多対多（このタグが付いているネタ）
     */
    public function ideas()
    {
        return $this->belongsToMany(Idea::class)->withTimestamps();
    }
}
