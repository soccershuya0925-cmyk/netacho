<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * 投稿カレンダー（月曜はじまりの1週間を7日ぶん並べる）
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // ?week=2026-09-15 が来たらその週、無ければ今週
        $start = $request->filled('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : Carbon::today()->startOfWeek(Carbon::MONDAY);

        $end = $start->copy()->addDays(6);

        $days = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i));

        // その週の投稿を日付ごとにまとめる
        $postsByDate = $user->posts()
            ->with('idea')
            ->whereBetween('scheduled_for', [$start->toDateString(), $end->toDateString()])
            ->orderBy('scheduled_for')
            ->get()
            ->groupBy(fn ($post) => $post->scheduled_for->toDateString());

        return view('posts.index', compact('days', 'postsByDate', 'start', 'end'));
    }

    /**
     * 予定を入れるフォーム（カレンダーの「＋」から日付つきで開く）
     */
    public function create(Request $request)
    {
        $user = $request->user();

        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))->toDateString()
            : Carbon::today()->toDateString();

        // まだ使っていないネタを上に出す
        $ideas = $user->ideas()->with('product')->orderByRaw("FIELD(status, '下書き', '予定あり', '使用済み')")->latest()->get();

        return view('posts.create', compact('ideas', 'date'));
    }

    /**
     * 予定を登録する
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idea_id' => [
                'required',
                Rule::exists('ideas', 'id')->where('user_id', $request->user()->id),
            ],
            'platform' => ['required', Rule::in(Post::PLATFORMS)],
            'scheduled_for' => 'required|date',
        ]);

        $post = $request->user()->posts()->create($validated);

        // ネタの状態を「予定あり」にする（まだ出していない物だけ）
        if ($post->idea->status === '下書き') {
            $post->idea->update(['status' => '予定あり']);
        }

        return redirect()->route('posts.index', ['week' => $validated['scheduled_for']]);
    }

    /**
     * 「投稿した」を押した時（その瞬間の日時を記録する）
     */
    public function markPosted(Post $post)
    {
        $this->authorizeOwner($post);

        $post->update(['posted_at' => now()]);
        $post->idea->update(['status' => '使用済み']);

        return back();
    }

    /**
     * 「投稿した」を取り消す
     */
    public function unmarkPosted(Post $post)
    {
        $this->authorizeOwner($post);

        $post->update(['posted_at' => null]);

        // 他に出し終えた投稿が無ければ「予定あり」に戻す
        if (! $post->idea->posts()->whereNotNull('posted_at')->exists()) {
            $post->idea->update(['status' => '予定あり']);
        }

        return back();
    }

    /**
     * 予定を消す
     */
    public function destroy(Post $post)
    {
        $this->authorizeOwner($post);

        $idea = $post->idea;
        $post->delete();

        // この予定を消した結果、予定も実績も無くなったら「下書き」に戻す
        if ($idea->posts()->count() === 0) {
            $idea->update(['status' => '下書き']);
        }

        return back();
    }

    /**
     * 投稿の実績（出した物を新しい順に）
     */
    public function history(Request $request)
    {
        $posts = $request->user()->posts()
            ->with('idea.product')
            ->whereNotNull('posted_at')
            ->orderByDesc('posted_at')
            ->get();

        return view('posts.history', compact('posts'));
    }

    private function authorizeOwner(Post $post): void
    {
        abort_if($post->user_id !== auth()->id(), 403);
    }
}
