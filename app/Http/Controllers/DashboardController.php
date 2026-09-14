<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * 開いた瞬間に「次に何をすればいいか」が分かる画面
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $start = Carbon::today()->startOfWeek(Carbon::MONDAY);
        $end = $start->copy()->addDays(6);

        // 今週の予定
        $thisWeek = $user->posts()
            ->with('idea')
            ->whereBetween('scheduled_for', [$start->toDateString(), $end->toDateString()])
            ->orderBy('scheduled_for')
            ->get();

        // まだ1度も予定に入れていないネタ
        $unusedIdeaCount = $user->ideas()->doesntHave('posts')->count();

        // 直近で出した投稿
        $recentPosts = $user->posts()
            ->with('idea')
            ->whereNotNull('posted_at')
            ->orderByDesc('posted_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('thisWeek', 'unusedIdeaCount', 'recentPosts', 'start', 'end'));
    }
}
