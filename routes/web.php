<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::resource('products', ProductController::class);
    Route::resource('ideas', IdeaController::class);
    Route::resource('templates', TemplateController::class)->except('show');

    // 投稿カレンダーと実績（/posts/history を先に置く＝/posts/{post} に食われないように）
    Route::get('posts/history', [PostController::class, 'history'])->name('posts.history');
    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('posts', [PostController::class, 'store'])->name('posts.store');
    Route::patch('posts/{post}/posted', [PostController::class, 'markPosted'])->name('posts.posted');
    Route::patch('posts/{post}/unposted', [PostController::class, 'unmarkPosted'])->name('posts.unposted');
    Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});

require __DIR__.'/auth.php';
