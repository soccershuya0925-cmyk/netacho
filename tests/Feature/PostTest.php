<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    private function ideaFor(User $user, string $title = 'ネタ')
    {
        return $user->ideas()->create(['title' => $title, 'body' => '本文', 'status' => '下書き']);
    }

    // 予定を入れられる（ネタの状態が「予定あり」になる）
    public function test_schedules_post(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $idea = $this->ideaFor($user);

        $this->post(route('posts.store'), [
            'idea_id' => $idea->id,
            'platform' => 'X',
            'scheduled_for' => '2026-09-16',
        ])->assertRedirect(route('posts.index', ['week' => '2026-09-16']));

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'idea_id' => $idea->id,
            'platform' => 'X',
            'posted_at' => null,
        ]);

        $this->assertSame('予定あり', $idea->fresh()->status);
    }

    // 知らない出し先は受け付けない
    public function test_rejects_unknown_platform(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('posts.store'), [
            'idea_id' => $this->ideaFor($user)->id,
            'platform' => 'Facebook',
            'scheduled_for' => '2026-09-16',
        ])->assertSessionHasErrors('platform');
    }

    // 他人のネタは予定に入れられない
    public function test_cannot_schedule_other_users_idea(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me);

        $this->post(route('posts.store'), [
            'idea_id' => $this->ideaFor($other)->id,
            'platform' => 'X',
            'scheduled_for' => '2026-09-16',
        ])->assertSessionHasErrors('idea_id');
    }

    // 「投稿した」を押すとその瞬間の日時が入り、ネタが「使用済み」になる
    public function test_marks_as_posted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $idea = $this->ideaFor($user);
        $post = $user->posts()->create([
            'idea_id' => $idea->id, 'platform' => 'X', 'scheduled_for' => '2026-09-16',
        ]);

        $this->from(route('posts.index'))
            ->patch(route('posts.posted', $post))
            ->assertRedirect(route('posts.index'));

        $post->refresh();

        $this->assertNotNull($post->posted_at);
        $this->assertSame('使用済み', $idea->fresh()->status);
    }

    // 「投稿した」を取り消せる
    public function test_unmarks_as_posted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $idea = $this->ideaFor($user);
        $post = $user->posts()->create([
            'idea_id' => $idea->id, 'platform' => 'X', 'scheduled_for' => '2026-09-16', 'posted_at' => now(),
        ]);

        $this->from(route('posts.index'))->patch(route('posts.unposted', $post));

        $this->assertNull($post->fresh()->posted_at);
        $this->assertSame('予定あり', $idea->fresh()->status);
    }

    // 予定を消すとネタが「下書き」に戻る
    public function test_deletes_post(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $idea = $this->ideaFor($user);
        $post = $user->posts()->create([
            'idea_id' => $idea->id, 'platform' => 'X', 'scheduled_for' => '2026-09-16',
        ]);
        $idea->update(['status' => '予定あり']);

        $this->from(route('posts.index'))->delete(route('posts.destroy', $post));

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertSame('下書き', $idea->fresh()->status);
    }

    // カレンダーはその週の予定だけを出す
    public function test_calendar_shows_only_that_week(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $inWeek = $this->ideaFor($user, '今週のネタ');
        $outWeek = $this->ideaFor($user, '来月のネタ');

        $user->posts()->create(['idea_id' => $inWeek->id, 'platform' => 'X', 'scheduled_for' => '2026-09-16']);
        $user->posts()->create(['idea_id' => $outWeek->id, 'platform' => 'X', 'scheduled_for' => '2026-10-20']);

        $this->get(route('posts.index', ['week' => '2026-09-16']))
            ->assertSee('今週のネタ')
            ->assertDontSee('来月のネタ');
    }

    // 実績一覧には出し終えた物だけ出る
    public function test_history_shows_only_posted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $done = $this->ideaFor($user, '出したネタ');
        $notYet = $this->ideaFor($user, 'まだのネタ');

        $user->posts()->create(['idea_id' => $done->id, 'platform' => 'note', 'scheduled_for' => '2026-09-16', 'posted_at' => now()]);
        $user->posts()->create(['idea_id' => $notYet->id, 'platform' => 'X', 'scheduled_for' => '2026-09-17']);

        $this->get(route('posts.history'))
            ->assertSee('出したネタ')
            ->assertDontSee('まだのネタ');
    }

    // 他人の投稿は動かせない
    public function test_cannot_touch_other_users_post(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $post = $other->posts()->create([
            'idea_id' => $this->ideaFor($other)->id, 'platform' => 'X', 'scheduled_for' => '2026-09-16',
        ]);

        $this->actingAs($me);

        $this->patch(route('posts.posted', $post))->assertStatus(403);
        $this->delete(route('posts.destroy', $post))->assertStatus(403);
    }

    // ダッシュボードに今週の件数が出る
    public function test_dashboard_shows_this_week(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $idea = $this->ideaFor($user, 'ダッシュボードのネタ');
        $user->posts()->create([
            'idea_id' => $idea->id, 'platform' => 'X', 'scheduled_for' => now()->startOfWeek()->addDay()->toDateString(),
        ]);

        $this->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('今週の予定')
            ->assertSee('ダッシュボードのネタ');
    }
}
