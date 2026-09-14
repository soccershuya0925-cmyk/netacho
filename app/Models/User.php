<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * 1対多（このユーザが登録した商品）
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * 1対多（このユーザの投稿ネタ）
     */
    public function ideas()
    {
        return $this->hasMany(Idea::class);
    }

    /**
     * 1対多（このユーザのタグ）
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * 1対多（このユーザの投稿の型）
     */
    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    /**
     * 1対多（このユーザの投稿予定・実績）
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
